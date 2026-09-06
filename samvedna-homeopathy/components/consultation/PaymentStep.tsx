"use client";

import { CheckCircle2, MessageCircle, QrCode } from "lucide-react";
import { useState } from "react";
import Button from "@/components/ui/Button";
import { getPlanPrice, type Plan } from "@/constants/plans";
import {
  PAYMENT_QR_BY_PLAN,
  PAYMENT_QR_SRC,
  PAYMENT_WHATSAPP,
  PAYMENT_WHATSAPP_DISPLAY,
} from "@/constants/assessment";
import useCurrency from "@/hooks/useCurrency";

type PaymentStepProps = {
  plan?: Plan;
};

export default function PaymentStep({ plan }: PaymentStepProps) {
  const whatsappHref = `https://wa.me/${PAYMENT_WHATSAPP}`;
  const [qrMissing, setQrMissing] = useState(false);
  const { currency } = useCurrency();

  // The plan's QR already carries its amount, so the payer confirms rather than
  // types it. Without a plan (or if the generated file was removed) this falls
  // back to the bank's static QR, where the amount is entered by hand.
  const planQr = plan ? PAYMENT_QR_BY_PLAN[plan.slug] : undefined;
  const [planQrFailed, setPlanQrFailed] = useState(false);

  const showsPlanQr = Boolean(planQr) && !planQrFailed;
  const qrSrc = showsPlanQr ? (planQr as string) : PAYMENT_QR_SRC;

  return (
    <div className="text-center">
      <div className="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-success/10">
        <CheckCircle2 className="h-8 w-8 text-success" aria-hidden="true" />
      </div>

      <h2 className="font-display text-2xl font-semibold uppercase tracking-wide text-muted md:text-3xl">
        Payment
      </h2>

      {plan ? (
        <p className="mt-3 text-base text-text">
          <span className="font-semibold">{plan.name}</span>
          <span className="mx-2 text-muted">·</span>
          <span className="font-semibold text-primary">{getPlanPrice(plan, currency)}</span>
          <span className="text-muted"> / {plan.duration}</span>
        </p>
      ) : null}

      <p className="mx-auto mt-4 max-w-xl text-lg font-medium leading-8 text-primary">
        Please scan the QR code and make the payment.
        <br />
        Please send the screenshot of the payment receipt on our WhatsApp Number:{" "}
        {PAYMENT_WHATSAPP_DISPLAY}.
      </p>

      {/* The QR is a dense 64-module code: below ~320px the modules fall under
          4px each and scanners start failing, so the card is sized around it
          rather than the other way round. */}
      <div className="mx-auto mt-8 w-full max-w-sm rounded-2xl border border-border bg-white p-6 shadow-sm">
        <p className="text-sm font-medium text-muted">Scan QR to pay</p>
        <p className="mt-1 font-display text-xl font-bold text-primary">SAMVEDNA</p>
        {/* The QR carries no amount, so the figure the payer must type is shown
            as prominently as the code itself. */}
        {plan ? (
          <div className="mt-3">
            <p className="text-xs font-medium uppercase tracking-wide text-muted">
              Amount to pay
            </p>
            <p className="font-display text-3xl font-bold tracking-tight text-text">
              {plan.priceINR}
            </p>
          </div>
        ) : null}
        {qrMissing ? (
          <div className="mx-auto mt-4 flex aspect-square w-full max-w-[320px] flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-border bg-bg-soft p-4 text-center text-muted">
            <QrCode className="h-10 w-10" aria-hidden="true" />
            <span className="text-xs leading-5">
              Payment QR — add <code>payment-qr.png</code> to <code>public/images/</code>.
            </span>
          </div>
        ) : (
          /* eslint-disable-next-line @next/next/no-img-element */
          <img
            src={qrSrc}
            alt={
              plan
                ? `Scan this QR with any UPI app to pay ${plan.priceINR} to Samvedna for the ${plan.name} plan`
                : "Scan this QR code with any UPI or BharatQR enabled app to pay Samvedna"
            }
            // A missing per-plan file drops to the bank's static QR rather than
            // to the "no QR" placeholder, so payment is never blocked outright.
            onError={() =>
              showsPlanQr ? setPlanQrFailed(true) : setQrMissing(true)
            }
            className="mx-auto mt-4 aspect-square w-full max-w-[320px] rounded-lg border border-border bg-white object-contain"
          />
        )}
        <p className="mt-4 text-xs leading-5 text-muted">
          {showsPlanQr
            ? "The amount is already filled in — scan and confirm in any UPI app (GPay, PhonePe, Paytm, BHIM…)."
            : "Scan the QR with any BharatQR / UPI enabled app (GPay, PhonePe, Paytm, BHIM…) and enter the amount shown above."}
        </p>
        {plan && currency === "USD" ? (
          <p className="mt-2 text-xs leading-5 text-muted">
            UPI is charged in rupees, so this is {plan.priceINR} — the{" "}
            {getPlanPrice(plan, currency)} shown on the site is the equivalent.
          </p>
        ) : null}
      </div>

      <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
        <Button
          href={whatsappHref}
          target="_blank"
          rel="noopener noreferrer"
          size="lg"
          icon={<MessageCircle className="h-5 w-5" aria-hidden="true" />}
          iconPosition="left"
        >
          Send receipt on WhatsApp
        </Button>
        <Button href="/" size="lg" variant="secondary">
          Back to home
        </Button>
      </div>
    </div>
  );
}
