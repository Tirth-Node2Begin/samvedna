# QR Code Implementation Notes

## What cannot go wrong

**Funds cannot be misdirected.** The destination account is tags 26 and 27 (your UPI merchant identifiers). I copied those byte-for-byte from your bank's QR and the script asserts they're unchanged after decoding — that's the `merchant-identical: True` column. Same for merchant name, city, category code and currency. Anyone who pays reaches SAMVEDNA ADVANCED HOMEO CHILD NEURO LLP, exactly as before. I never generated a QR from scratch or typed a VPA by hand.

## What could go wrong

| Risk | Severity | Fails how |
| :--- | :--- | :--- |
| **A UPI app or your PSP rejects the modified QR** | Medium | Fails closed — customer sees an error, no money moves |
| **Prices change in plans.ts without re-running the script** | High | QR silently charges the old amount |
| **App locks the amount, customer can't pay a different figure** | Low | Inconvenience only |

The one I'd genuinely flag: I set tag `01` to `12` ("dynamic"). By convention a dynamic QR is generated per transaction with a unique reference, and is sometimes short-lived. Yours is a fixed QR reused by every customer on that plan. Structurally it's valid EMVCo and I found no signature field that re-encoding would invalidate — but whether your acquirer (HDFC SmartHub) is happy with a reused dynamic QR is their policy, and I cannot verify that from here.

The stale-price risk is the one most likely to actually bite you, and it's a silent failure. It's documented in both files, but it depends on someone remembering.

## The test that costs nothing

Scan a plan's QR with GPay or PhonePe and stop before confirming. The app will show the merchant name and the pre-filled amount. If both look right, the QR is accepted. Then cancel. That validates the whole chain without spending a rupee.

## Rollback

Delete the three files and it reverts to your untouched bank QR immediately — no code change, no rebuild logic:

```bash
rm public/images/payment-qr-{starter,standard,premium}.png
```

`PaymentStep` falls back to `payment-qr.png` (the plain crop of your original) whenever a plan's file is absent. The large rupee amount above the QR stays either way, so customers still see what to pay.

## If you'd rather not take the risk

Say so and I'll revert to the static QR only. You'd lose the pre-filled amount, but the big ₹ figure above the QR already tells the customer exactly what to enter — which is how most merchants on a static QR operate. That's a one-minute change and carries zero payment risk.

**My recommendation:** do the cancel-before-confirm scan test first. If it works cleanly on two apps, keep it; if anything looks off, revert.
