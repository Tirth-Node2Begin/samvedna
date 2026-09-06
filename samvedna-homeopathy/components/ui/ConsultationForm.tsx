"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { LoaderCircle, Send } from "lucide-react";
import { useRouter } from "next/navigation";
import { useEffect, useId, useRef, useState } from "react";
import { useForm } from "react-hook-form";
import { conditionList } from "@/constants/conditions";
import {
  consultationSchema,
  type ConsultationFormValues,
  preferredTimes,
  type ConsultationInput
} from "@/lib/schemas/consultation";
import {
  submitBooking,
  type BookingActionResult
} from "@/lib/api/leads";
import { getFormRedirect } from "@/lib/config/forms";
import Button from "@/components/ui/Button";
import { cn } from "@/lib/utils";

const fieldClassName =
  "mt-1.5 block w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-base text-text shadow-sm transition-all placeholder:text-muted/60 hover:border-primary/50 focus:border-primary focus:bg-white focus:outline-none focus:ring-4 focus:ring-primary/10";

type FormErrorProps = {
  error?: string;
  errorId: string;
};

function FormError({ error, errorId }: FormErrorProps) {
  if (!error) {
    return null;
  }

  return (
    <p id={errorId} className="mt-2 text-sm font-medium text-red-700">
      {error}
    </p>
  );
}

type ConsultationFormProps = {
  /** Where the submission originated — stored alongside the inquiry. */
  source?: "website" | "popup";
};

export default function ConsultationForm({ source = "website" }: ConsultationFormProps) {
  const [result, setResult] = useState<BookingActionResult | null>(null);
  const [isRedirecting, setIsRedirecting] = useState(false);
  // Plain state, not useTransition: the submit is now an ordinary fetch to PHP,
  // and a transition would report "not pending" the moment the callback returns.
  const [isPending, setIsPending] = useState(false);
  const [honeypot, setHoneypot] = useState("");
  const router = useRouter();
  const redirectTimer = useRef<number | null>(null);

  // Unique id base so multiple form instances (inline + popup) never collide.
  const uid = useId();
  const errorId = (name: keyof ConsultationInput): string => `${uid}-${name}-error`;

  useEffect(
    () => () => {
      if (redirectTimer.current) window.clearTimeout(redirectTimer.current);
    },
    []
  );

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors }
  } = useForm<ConsultationFormValues, undefined, ConsultationInput>({
    resolver: zodResolver(consultationSchema),
    defaultValues: {
      parentName: "",
      childAge: 4,
      condition: "Autism Spectrum Disorder Support",
      country: "India",
      phone: "",
      email: "",
      message: "",
      preferredTime: "morning"
    }
  });

  const onSubmit = handleSubmit(async (values: ConsultationInput) => {
    setIsPending(true);
    try {
      const response = await submitBooking({ ...values, company: honeypot }, source);
      setResult(response);

      if (response.status === "success") {
        reset();

        // Redirect only after a successful submission, once the success
        // message has had a moment to display. Behaviour is configurable.
        const redirect = getFormRedirect();
        if (redirect.url) {
          setIsRedirecting(true);
          redirectTimer.current = window.setTimeout(() => {
            if (redirect.external) {
              window.location.href = redirect.url as string;
            } else {
              router.push(redirect.url as string);
            }
          }, redirect.delayMs);
        }
      }
    } finally {
      setIsPending(false);
    }
  });

  const disabled = isPending || isRedirecting;

  return (
    <form
      onSubmit={onSubmit}
      className="relative flex w-full flex-col text-left"
      noValidate
    >
      <div className="mb-6 flex flex-col gap-2 border-b border-border/60 pb-5 pr-12">
        <div>
          <p className="text-sm font-semibold uppercase tracking-wider text-primary">Consultation request</p>
          <h3 className="mt-1.5 font-display text-xl font-semibold text-text md:text-2xl">
            Tell us what your child needs help with.
          </h3>
        </div>
        <p className="text-sm leading-6 text-muted">
          A team member will review your details and guide you through the next step.
        </p>
      </div>

      {/* Honeypot: hidden from people, irresistible to bots. leads.php rejects
          any submission that fills it. Not `type="hidden"` — bots skip those. */}
      <div aria-hidden="true" className="absolute left-[-9999px] h-0 w-0 overflow-hidden">
        <label>
          Company
          <input
            name="company"
            tabIndex={-1}
            autoComplete="off"
            value={honeypot}
            onChange={(event) => setHoneypot(event.target.value)}
          />
        </label>
      </div>

      <fieldset disabled={disabled} className="grid gap-x-5 gap-y-4 md:grid-cols-2">
        <label className="block">
          <span className="text-sm font-semibold text-text">Parent name</span>
          <input
            {...register("parentName")}
            aria-describedby={errors.parentName ? errorId("parentName") : undefined}
            className={fieldClassName}
            placeholder="Full name"
            suppressHydrationWarning
          />
          <FormError errorId={errorId("parentName")} error={errors.parentName?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Child age</span>
          <input
            {...register("childAge", { valueAsNumber: true })}
            aria-describedby={errors.childAge ? errorId("childAge") : undefined}
            className={fieldClassName}
            min={0}
            max={18}
            suppressHydrationWarning
            type="number"
          />
          <FormError errorId={errorId("childAge")} error={errors.childAge?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Primary concern</span>
          <select
            {...register("condition")}
            aria-describedby={errors.condition ? errorId("condition") : undefined}
            className={fieldClassName}
            suppressHydrationWarning
          >
            {conditionList.map((condition) => (
              <option key={condition} value={condition}>
                {condition}
              </option>
            ))}
          </select>
          <FormError errorId={errorId("condition")} error={errors.condition?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Country</span>
          <input
            {...register("country")}
            aria-describedby={errors.country ? errorId("country") : undefined}
            className={fieldClassName}
            placeholder="India"
            suppressHydrationWarning
          />
          <FormError errorId={errorId("country")} error={errors.country?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Phone</span>
          <input
            {...register("phone")}
            aria-describedby={errors.phone ? errorId("phone") : undefined}
            className={fieldClassName}
            inputMode="tel"
            placeholder="+917874876777"
            suppressHydrationWarning
          />
          <FormError errorId={errorId("phone")} error={errors.phone?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Email</span>
          <input
            {...register("email")}
            aria-describedby={errors.email ? errorId("email") : undefined}
            className={fieldClassName}
            inputMode="email"
            placeholder="parent@example.com"
            suppressHydrationWarning
            type="email"
          />
          <FormError errorId={errorId("email")} error={errors.email?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Preferred time</span>
          <select
            {...register("preferredTime")}
            aria-describedby={
              errors.preferredTime ? errorId("preferredTime") : undefined
            }
            className={fieldClassName}
            suppressHydrationWarning
          >
            {preferredTimes.map((time) => (
              <option key={time} value={time}>
                {time.charAt(0).toUpperCase() + time.slice(1)}
              </option>
            ))}
          </select>
          <FormError errorId={errorId("preferredTime")} error={errors.preferredTime?.message} />
        </label>

        <label className="block md:col-span-2">
          <span className="text-sm font-semibold text-text">Message</span>
          <textarea
            {...register("message")}
            aria-describedby={errors.message ? errorId("message") : undefined}
            className={cn(fieldClassName, "min-h-[104px] resize-none leading-relaxed")}
            placeholder="Briefly share speech, attention, behavior, sleep, learning, therapy, or report details."
            suppressHydrationWarning
          />
          <FormError errorId={errorId("message")} error={errors.message?.message} />
        </label>
      </fieldset>

      <div className="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <Button
          type="submit"
          size="lg"
          icon={
            disabled ? (
              <LoaderCircle className="h-5 w-5 animate-spin" aria-hidden="true" />
            ) : (
              <Send className="h-5 w-5" aria-hidden="true" />
            )
          }
          disabled={disabled}
        >
          {isRedirecting ? "Redirecting" : isPending ? "Sending Request" : "Start Assessment"}
        </Button>

        {result ? (
          <p
            className={cn(
              "text-sm font-medium",
              result.status === "success" ? "text-primary" : "text-red-700"
            )}
            role="status"
            aria-live="polite"
          >
            {result.message}
          </p>
        ) : null}
      </div>
    </form>
  );
}
