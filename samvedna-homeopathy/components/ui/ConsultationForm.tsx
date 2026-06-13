"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { LoaderCircle, Send } from "lucide-react";
import { useState, useTransition } from "react";
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
} from "@/lib/actions/booking";
import Button from "@/components/ui/Button";
import { cn } from "@/lib/utils";

const fieldClassName =
  "mt-2 block w-full rounded-xl border border-transparent bg-bg-soft px-4 py-3 text-base text-text transition-all placeholder:text-muted/60 hover:border-border focus:border-primary focus:bg-white focus:outline-none focus:ring-4 focus:ring-primary/10";

function fieldErrorId(name: keyof ConsultationInput): string {
  return `${name}-error`;
}

type FormFieldProps = {
  error?: string;
  id: keyof ConsultationInput;
};

function FormError({ error, id }: FormFieldProps) {
  if (!error) {
    return null;
  }

  return (
    <p id={fieldErrorId(id)} className="mt-2 text-sm font-medium text-red-700">
      {error}
    </p>
  );
}

export default function ConsultationForm() {
  const [result, setResult] = useState<BookingActionResult | null>(null);
  const [isPending, startTransition] = useTransition();

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

  const onSubmit = handleSubmit((values: ConsultationInput) => {
    const formData = new FormData();
    Object.entries(values).forEach(([key, value]) => {
      formData.append(key, String(value ?? ""));
    });

    startTransition(() => {
      void submitBooking(formData).then((response) => {
        setResult(response);
        if (response.status === "success") {
          reset();
        }
      });
    });
  });

  return (
    <form
      onSubmit={onSubmit}
      className="flex w-full flex-col text-left"
      noValidate
    >
      <div className="mb-10 flex flex-col gap-3 border-b border-border/60 pb-6">
        <div>
          <p className="text-sm font-semibold uppercase tracking-wider text-primary">Consultation request</p>
          <h3 className="mt-2 font-display text-2xl font-semibold text-text md:text-3xl">
            Tell us what your child needs help with.
          </h3>
        </div>
        <p className="text-sm leading-6 text-muted">
          A team member will review your details and guide you through the next step.
        </p>
      </div>

      <fieldset disabled={isPending} className="grid gap-5 md:grid-cols-2">
        <label className="block">
          <span className="text-sm font-semibold text-text">Parent name</span>
          <input
            {...register("parentName")}
            aria-describedby={errors.parentName ? fieldErrorId("parentName") : undefined}
            className={fieldClassName}
            placeholder="Full name"
            suppressHydrationWarning
          />
          <FormError id="parentName" error={errors.parentName?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Child age</span>
          <input
            {...register("childAge", { valueAsNumber: true })}
            aria-describedby={errors.childAge ? fieldErrorId("childAge") : undefined}
            className={fieldClassName}
            min={0}
            max={18}
            suppressHydrationWarning
            type="number"
          />
          <FormError id="childAge" error={errors.childAge?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Primary concern</span>
          <select
            {...register("condition")}
            aria-describedby={errors.condition ? fieldErrorId("condition") : undefined}
            className={fieldClassName}
            suppressHydrationWarning
          >
            {conditionList.map((condition) => (
              <option key={condition} value={condition}>
                {condition}
              </option>
            ))}
          </select>
          <FormError id="condition" error={errors.condition?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Country</span>
          <input
            {...register("country")}
            aria-describedby={errors.country ? fieldErrorId("country") : undefined}
            className={fieldClassName}
            placeholder="India"
            suppressHydrationWarning
          />
          <FormError id="country" error={errors.country?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Phone</span>
          <input
            {...register("phone")}
            aria-describedby={errors.phone ? fieldErrorId("phone") : undefined}
            className={fieldClassName}
            inputMode="tel"
            placeholder="+917874876777"
            suppressHydrationWarning
          />
          <FormError id="phone" error={errors.phone?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Email</span>
          <input
            {...register("email")}
            aria-describedby={errors.email ? fieldErrorId("email") : undefined}
            className={fieldClassName}
            inputMode="email"
            placeholder="parent@example.com"
            suppressHydrationWarning
            type="email"
          />
          <FormError id="email" error={errors.email?.message} />
        </label>

        <label className="block">
          <span className="text-sm font-semibold text-text">Preferred time</span>
          <select
            {...register("preferredTime")}
            aria-describedby={
              errors.preferredTime ? fieldErrorId("preferredTime") : undefined
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
          <FormError id="preferredTime" error={errors.preferredTime?.message} />
        </label>

        <label className="block md:col-span-2">
          <span className="text-sm font-semibold text-text">Message</span>
          <textarea
            {...register("message")}
            aria-describedby={errors.message ? fieldErrorId("message") : undefined}
            className={cn(fieldClassName, "min-h-[140px] resize-none leading-relaxed")}
            placeholder="Briefly share speech, attention, behavior, sleep, learning, therapy, or report details."
            suppressHydrationWarning
          />
          <FormError id="message" error={errors.message?.message} />
        </label>
      </fieldset>

      <div className="mt-7 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <Button
          type="submit"
          size="lg"
          icon={
            isPending ? (
              <LoaderCircle className="h-5 w-5 animate-spin" aria-hidden="true" />
            ) : (
              <Send className="h-5 w-5" aria-hidden="true" />
            )
          }
          disabled={isPending}
        >
          {isPending ? "Sending Request" : "Start Assessment"}
        </Button>

        {result ? (
          <p
            className={cn(
              "text-sm font-medium",
              result.status === "success" ? "text-primary" : "text-red-700"
            )}
            role="status"
          >
            {result.message}
          </p>
        ) : null}
      </div>
    </form>
  );
}
