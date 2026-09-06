"""
Generate a per-plan UPI payment QR with the amount pre-filled.

The QR the bank issued (public/images/qr.jpeg) is a STATIC merchant QR: it
carries no amount, so the payer has to type it in. This script re-encodes that
same merchant payload as a DYNAMIC QR with EMVCo tag 54 (transaction amount)
set to the plan price, so the UPI app opens with the amount already filled.

Nothing about the merchant is invented or altered: every tag is copied verbatim
from the bank's payload. Only three things change, and the script proves it by
decoding its own output and comparing every other tag against the original:

    tag 01  "11" (static)  ->  "12" (dynamic)
    tag 54  absent         ->  the plan amount
    tag 63  CRC            ->  recomputed over the new payload

Funds therefore always route to the merchant account in tags 26/27, which are
never touched. The residual risk is acceptance, not misdirection: if a UPI app
or the acquirer declines a reused dynamic QR the payment simply fails. Deleting
the generated PNGs reverts the site to the bank's static QR with no code change.

UPI settles in INR only (tag 53 = 356), so the amount is always the rupee price.
The dollar figure on the site is an indicative conversion and cannot be encoded.

Run from the project root after changing any price in constants/plans.ts:

    python scripts/make-payment-qr.py
"""

import sys
import cv2
import numpy as np
from PIL import Image

SRC = "public/images/qr.jpeg"
OUT = "public/images/payment-qr-{slug}.png"

# MUST match priceINR in constants/plans.ts. Amounts are plain rupee integers.
PLANS = {
    "starter": "14999",
    "standard": "39999",
    "premium": "54999",
}

QUIET_MODULES = 4      # EMVCo/ISO quiet zone
TARGET_PX = 620        # final image size, before the quiet zone is added


def crc16(data: str) -> str:
    """CRC-16/CCITT-FALSE, the checksum EMVCo tag 63 uses."""
    crc = 0xFFFF
    for byte in data.encode():
        crc ^= byte << 8
        for _ in range(8):
            crc = ((crc << 1) ^ 0x1021) & 0xFFFF if crc & 0x8000 else (crc << 1) & 0xFFFF
    return f"{crc:04x}"          # lowercase, matching the bank's own payload


def parse(payload: str):
    """Flat EMVCo TLV -> list of (tag, value), order preserved."""
    out, i = [], 0
    while i < len(payload):
        tag = payload[i:i + 2]
        length = int(payload[i + 2:i + 4])
        out.append((tag, payload[i + 4:i + 4 + length]))
        i += 4 + length
    return out


def build(tlv) -> str:
    body = "".join(f"{t}{len(v):02d}{v}" for t, v in tlv if t != "63")
    return body + "6304" + crc16(body + "6304")


def with_amount(tlv, amount: str):
    """Return a copy marked dynamic with tag 54 set, in EMVCo tag order."""
    out = []
    for tag, value in tlv:
        if tag in ("63", "54"):
            continue                      # CRC re-added by build(); 54 set below
        out.append(("01", "12") if tag == "01" else (tag, value))
        if tag == "53":                   # amount belongs directly after currency
            out.append(("54", amount))
    return out


def decode(image) -> str:
    """
    Decode with the Aruco-based detector.

    NOT cv2.QRCodeDetector: the legacy detector is unreliable on synthetic
    images -- it round-trips a 334-character payload but fails a 280-character
    one -- so it cannot be trusted to sign off a payment QR. The Aruco detector
    reads both the bank's original photo and this script's output correctly.
    """
    return cv2.QRCodeDetectorAruco().detectAndDecode(image)[0]


def encode_png(payload: str, path: str) -> int:
    params = cv2.QRCodeEncoder_Params()
    params.correction_level = cv2.QRCodeEncoder_CORRECT_LEVEL_L
    matrix = cv2.QRCodeEncoder_create(params).encode(payload)
    modules = matrix.shape[0]

    scale = max(1, round(TARGET_PX / modules))
    big = np.kron(matrix, np.ones((scale, scale), dtype=matrix.dtype))
    pad = QUIET_MODULES * scale
    canvas = np.full((big.shape[0] + 2 * pad, big.shape[1] + 2 * pad), 255, dtype=np.uint8)
    canvas[pad:pad + big.shape[0], pad:pad + big.shape[1]] = big
    Image.fromarray(canvas).convert("RGB").save(path, "PNG", optimize=True)
    return modules


def main() -> int:
    source = decode(cv2.imread(SRC))
    if not source:
        print(f"ERROR: could not decode {SRC}", file=sys.stderr)
        return 1
    if crc16(source[:-4]).lower() != source[-4:].lower():
        print("ERROR: source QR fails its own CRC check", file=sys.stderr)
        return 1

    original = parse(source)
    frozen = {t: v for t, v in original if t not in ("01", "54", "63")}
    print(f"source OK - {len(original)} tags, merchant "
          f"{dict(original).get('59', '?').strip()}\n")

    for slug, amount in PLANS.items():
        payload = build(with_amount(original, amount))
        path = OUT.format(slug=slug)
        modules = encode_png(payload, path)

        # Verify by decoding the file that was just written.
        decoded = decode(cv2.imread(path))
        if not decoded:
            print(f"FAIL {slug}: generated QR does not decode", file=sys.stderr)
            return 1
        tags = dict(parse(decoded))
        checks = {
            "round-trips": decoded == payload,
            "crc valid": crc16(decoded[:-4]).lower() == decoded[-4:].lower(),
            "amount set": tags.get("54") == amount,
            "dynamic": tags.get("01") == "12",
            "currency INR": tags.get("53") == "356",
            "merchant untouched": all(tags.get(t) == v for t, v in frozen.items()),
        }
        status = "OK " if all(checks.values()) else "FAIL"
        print(f"{status} {slug:9} Rs {amount:>6}  {modules}x{modules} modules  -> {path}")
        for name, ok in checks.items():
            if not ok:
                print(f"      ^ {name}: FAILED", file=sys.stderr)
        if not all(checks.values()):
            return 1

    print("\nAll QRs verified against the bank payload. Test-scan one with a UPI "
          "app (cancel before confirming) before going live.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
