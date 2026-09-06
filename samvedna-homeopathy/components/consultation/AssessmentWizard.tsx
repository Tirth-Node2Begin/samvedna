"use client";

import { LoaderCircle } from "lucide-react";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { useEffect, useId, useRef, useState } from "react";
import Button from "@/components/ui/Button";
import PaymentStep from "@/components/consultation/PaymentStep";
import ProgressDots from "@/components/consultation/ProgressDots";
import QuestionField, { type AnswerValue } from "@/components/consultation/QuestionField";
import { STEPS } from "@/constants/assessment";
import { getPlanBySlug, getPlanPrice } from "@/constants/plans";
import {
  submitConsultation,
  type AnswerSection,
  type ConsultationPayload,
} from "@/lib/api/consultations";
import { cn } from "@/lib/utils";
import useCurrency from "@/hooks/useCurrency";

const PHONE_RE = /^\+?[0-9]{8,15}$/;
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export default function AssessmentWizard() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const planSlug = searchParams.get("plan") ?? "";
  const plan = getPlanBySlug(planSlug);
  const { currency } = useCurrency();

  /**
   * Two journeys share this wizard, and they ask for very different amounts of
   * detail:
   *
   *  - Plan checkout (?plan=<slug>, from a care-plan card): the visitor has
   *    already decided to pay, so asking for the full clinical intake before
   *    taking payment loses them. Contact details only, then the payment step.
   *    The clinical picture is gathered by the doctor at the first consultation.
   *
   *  - Trial consultation booking (no ?plan=, from "Book Consultation"): nothing
   *    is being paid, so this is the moment to collect the whole questionnaire.
   *    It ends on the thank-you page instead of a QR code.
   */
  const isPlanCheckout = Boolean(plan);
  const steps = isPlanCheckout ? STEPS.slice(0, 1) : STEPS;

  const uid = useId();
  const containerRef = useRef<HTMLDivElement | null>(null);

  const [answers, setAnswers] = useState<Record<string, AnswerValue>>({});
  const [currentStep, setCurrentStep] = useState(0);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [isPending, setIsPending] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [honeypot, setHoneypot] = useState("");

  // Only the plan checkout has a payment step to count / navigate to.
  const totalDots = steps.length + (isPlanCheckout ? 1 : 0);
  const isPaymentStep = isPlanCheckout && currentStep >= steps.length;
  const step = steps[currentStep];
  const isLastQuestionStep = currentStep === steps.length - 1;

  // Scroll the card into view on step change so long forms restart at the top.
  useEffect(() => {
    containerRef.current?.scrollIntoView({ behavior: "smooth", block: "start" });
  }, [currentStep]);

  function setAnswer(id: string, value: AnswerValue): void {
    setAnswers((prev) => ({ ...prev, [id]: value }));
    if (errors[id]) {
      setErrors((prev) => {
        const next = { ...prev };
        delete next[id];
        return next;
      });
    }
  }

  /** Trimmed string form of an answer (checkbox arrays are comma-joined). */
  function val(id: string): string {
    const v = answers[id];
    return (Array.isArray(v) ? v.join(", ") : v ?? "").trim();
  }

  /** Only the general (contact) step has required fields. */
  function validateGeneral(): Record<string, string> {
    const errs: Record<string, string> = {};
    if (val("patientName").length < 2) {
      errs.patientName = "Enter the patient's name.";
    }
    if (!PHONE_RE.test(val("mobile"))) {
      errs.mobile = "Use 8 to 15 digits, with optional country code.";
    }
    const email = val("email");
    if (email !== "" && !EMAIL_RE.test(email)) {
      errs.email = "Enter a valid email address.";
    }
    return errs;
  }

  function buildPayload(): ConsultationPayload {
    // Empty for a plan checkout, which has no clinical steps.
    const sections: AnswerSection[] = steps
      .slice(1)
      .map((s) => ({
        title: s.title,
        items: s.questions
          .map((q) => ({ label: q.label, value: val(q.id) }))
          .filter((item) => item.value !== ""),
      }))
      .filter((section) => section.items.length > 0);

    return {
      // A booking with no plan is still labelled, so the admin list can tell a
      // trial consultation request apart from a blank/broken plan slug.
      plan: planSlug || "trial-consultation",
      planName: plan?.name ?? "Trial Consultation Booking",
      amount: plan ? getPlanPrice(plan, currency) : undefined,
      patientName: val("patientName"),
      fatherName: val("fatherName"),
      mobile: val("mobile"),
      altPhone: val("altPhone"),
      address: val("address"),
      city: val("city"),
      state: val("state"),
      zip: val("zip"),
      email: val("email"),
      remarks: val("remarks"),
      childAge: val("childAge"),
      answers: sections,
      company: honeypot,
    };
  }

  function goNext(): void {
    setSubmitError(null);
    if (currentStep === 0) {
      const errs = validateGeneral();
      if (Object.keys(errs).length > 0) {
        setErrors(errs);
        return;
      }
    }
    setCurrentStep((s) => Math.min(s + 1, steps.length));
  }

  function goPrevious(): void {
    setSubmitError(null);
    setCurrentStep((s) => Math.max(s - 1, 0));
  }

  async function handleSubmit(): Promise<void> {
    // Re-check the required contact fields before the network round-trip.
    const errs = validateGeneral();
    if (Object.keys(errs).length > 0) {
      setErrors(errs);
      setCurrentStep(0);
      setSubmitError("Please complete the required contact details.");
      return;
    }

    setIsPending(true);
    setSubmitError(null);
    try {
      const result = await submitConsultation(buildPayload());
      if (result.status === "success") {
        if (isPlanCheckout) {
          setCurrentStep(steps.length); // reveal the payment step
        } else {
          // Nothing to pay for a trial booking — confirm and stop.
          router.push("/thank-you/");
        }
      } else {
        if (result.fieldErrors) {
          const mapped: Record<string, string> = {};
          for (const [field, messages] of Object.entries(result.fieldErrors)) {
            if (messages && messages[0]) mapped[field] = messages[0];
          }
          setErrors(mapped);
          setCurrentStep(0);
        }
        setSubmitError(result.message);
      }
    } finally {
      setIsPending(false);
    }
  }

  return (
    <div ref={containerRef} className="mx-auto w-full max-w-4xl scroll-mt-28">
      {/* Plan banner */}
      <div className="mb-5 flex flex-col items-center gap-1 text-center">
        <p className="text-sm font-semibold uppercase tracking-wider text-primary">
          {isPlanCheckout ? "Care Plan Assessment" : "Book a Consultation"}
        </p>
        {plan ? (
          <p className="text-base text-text">
            You&apos;re applying for{" "}
            <span className="font-semibold">{plan.name}</span>{" "}
            <span className="text-muted">
              ({getPlanPrice(plan, currency)} / {plan.duration})
            </span>
            <span className="mx-2 text-muted">·</span>
            <Link href="/#pricing" className="font-medium text-primary underline-offset-2 hover:underline">
              change
            </Link>
          </p>
        ) : (
          <p className="text-base text-text">
            Tell us about your child and our care desk will get in touch.
            <span className="mx-2 text-muted">·</span>
            <Link href="/#pricing" className="font-medium text-primary underline-offset-2 hover:underline">
              see care plans
            </Link>
          </p>
        )}
      </div>

      <div className="rounded-3xl border border-border bg-bg-soft p-6 shadow-sm md:p-10">
        {isPaymentStep ? (
          <PaymentStep plan={plan} />
        ) : (
          <>
            <h2 className="mb-6 text-center font-display text-2xl font-semibold uppercase tracking-wide text-muted md:mb-8 md:text-3xl">
              {step.title}
            </h2>

            {/* Honeypot: hidden from people, irresistible to bots. */}
            <div aria-hidden="true" className="absolute left-[-9999px] h-0 w-0 overflow-hidden">
              <label>
                Company
                <input
                  name="company"
                  tabIndex={-1}
                  autoComplete="off"
                  value={honeypot}
                  onChange={(event) => setHoneypot(event.target.value)}
                  suppressHydrationWarning
                />
              </label>
            </div>

            {step.general ? (
              <div className="grid gap-x-6 gap-y-4 md:grid-cols-2">
                {step.questions.map((question) => (
                  <QuestionField
                    key={question.id}
                    question={question}
                    value={answers[question.id]}
                    onChange={setAnswer}
                    error={errors[question.id]}
                    layout="grid"
                    idPrefix={uid}
                  />
                ))}
              </div>
            ) : (
              <div>
                {step.questions.map((question) => (
                  <QuestionField
                    key={question.id}
                    question={question}
                    value={answers[question.id]}
                    onChange={setAnswer}
                    error={errors[question.id]}
                    layout="qa"
                    idPrefix={uid}
                  />
                ))}
              </div>
            )}

            {submitError ? (
              <p className="mt-5 text-sm font-medium text-red-700" role="alert">
                {submitError}
              </p>
            ) : null}

            <div className="mt-8 flex flex-col-reverse items-stretch justify-end gap-3 sm:flex-row sm:items-center">
              {currentStep > 0 ? (
                <Button
                  variant="secondary"
                  onClick={goPrevious}
                  disabled={isPending}
                  className="w-full sm:w-auto"
                >
                  Previous
                </Button>
              ) : null}

              {isLastQuestionStep ? (
                <Button
                  onClick={handleSubmit}
                  disabled={isPending}
                  className="w-full sm:w-auto"
                  icon={
                    isPending ? (
                      <LoaderCircle className="h-5 w-5 animate-spin" aria-hidden="true" />
                    ) : undefined
                  }
                >
                  {isPending
                    ? "Submitting"
                    : isPlanCheckout
                    ? "Submit & Pay"
                    : "Submit Booking"}
                </Button>
              ) : (
                <Button onClick={goNext} disabled={isPending} className="w-full sm:w-auto">
                  Next
                </Button>
              )}
            </div>
          </>
        )}
      </div>

      <div className={cn("mt-8", isPaymentStep && "opacity-70")}>
        <ProgressDots
          total={totalDots}
          current={currentStep}
          onSelect={
            isPaymentStep
              ? undefined
              : (index) => {
                  // Allow jumping back to a visited step only.
                  if (index < currentStep) {
                    setSubmitError(null);
                    setCurrentStep(index);
                  }
                }
          }
        />
      </div>
    </div>
  );
}
