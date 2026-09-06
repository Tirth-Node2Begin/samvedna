"use client";

import type { Question } from "@/constants/assessment";
import { cn } from "@/lib/utils";

const inputClassName =
  "mt-1.5 block w-full rounded-xl border border-border bg-white px-4 py-2.5 text-base text-text shadow-sm transition-all placeholder:text-muted/60 hover:border-primary/50 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10";

export type AnswerValue = string | string[];

type QuestionFieldProps = {
  question: Question;
  value: AnswerValue | undefined;
  onChange: (id: string, value: AnswerValue) => void;
  error?: string;
  /** "grid" = general-info field (label above input); "qa" = clinical question. */
  layout: "grid" | "qa";
  idPrefix: string;
};

export default function QuestionField({
  question,
  value,
  onChange,
  error,
  layout,
  idPrefix,
}: QuestionFieldProps) {
  const fieldId = `${idPrefix}-${question.id}`;
  const stringValue = typeof value === "string" ? value : "";
  const arrayValue = Array.isArray(value) ? value : [];

  function toggleCheckbox(option: string): void {
    const next = arrayValue.includes(option)
      ? arrayValue.filter((item) => item !== option)
      : [...arrayValue, option];
    onChange(question.id, next);
  }

  // ---- Control ------------------------------------------------------------
  let control: React.ReactNode;

  if (question.type === "radio") {
    control = (
      <div className="flex flex-wrap gap-x-6 gap-y-2.5">
        {question.options?.map((option) => (
          <label key={option} className="inline-flex cursor-pointer items-center gap-2 text-[15px] text-text">
            <input
              type="radio"
              name={fieldId}
              value={option}
              checked={stringValue === option}
              onChange={() => onChange(question.id, option)}
              className="h-4 w-4 accent-primary"
              suppressHydrationWarning
            />
            <span>{option}</span>
          </label>
        ))}
      </div>
    );
  } else if (question.type === "checkbox") {
    control = (
      <div className="flex flex-wrap gap-x-6 gap-y-2.5">
        {question.options?.map((option) => (
          <label key={option} className="inline-flex cursor-pointer items-center gap-2 text-[15px] text-text">
            <input
              type="checkbox"
              value={option}
              checked={arrayValue.includes(option)}
              onChange={() => toggleCheckbox(option)}
              className="h-4 w-4 accent-primary"
              suppressHydrationWarning
            />
            <span>{option}</span>
          </label>
        ))}
      </div>
    );
  } else if (question.type === "textarea") {
    control = (
      <textarea
        id={fieldId}
        value={stringValue}
        onChange={(event) => onChange(question.id, event.target.value)}
        placeholder={question.placeholder}
        className={cn(inputClassName, "min-h-[104px] resize-none leading-relaxed")}
        suppressHydrationWarning
      />
    );
  } else if (question.suffix) {
    control = (
      <div className="mt-1.5 flex items-stretch overflow-hidden rounded-xl border border-border bg-white shadow-sm transition-all focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 hover:border-primary/50">
        <input
          id={fieldId}
          type={question.type}
          value={stringValue}
          onChange={(event) => onChange(question.id, event.target.value)}
          placeholder={question.placeholder}
          className="block w-full bg-transparent px-4 py-2.5 text-base text-text placeholder:text-muted/60 focus:outline-none"
          suppressHydrationWarning
        />
        <span className="flex items-center border-l border-border bg-bg-soft px-3 text-sm font-medium text-muted">
          {question.suffix}
        </span>
      </div>
    );
  } else {
    control = (
      <input
        id={fieldId}
        type={question.type}
        inputMode={question.type === "tel" ? "tel" : question.type === "email" ? "email" : undefined}
        value={stringValue}
        onChange={(event) => onChange(question.id, event.target.value)}
        placeholder={question.placeholder}
        className={inputClassName}
        suppressHydrationWarning
      />
    );
  }

  // ---- Layout -------------------------------------------------------------
  const labelNode = (
    <span className={cn("text-sm font-semibold text-text", layout === "qa" && "md:pt-0.5")}>
      {question.label}
      {question.required ? <span className="ml-0.5 text-red-600">*</span> : null}
    </span>
  );

  const errorNode = error ? (
    <p className="mt-2 text-sm font-medium text-red-700">{error}</p>
  ) : null;

  if (layout === "grid") {
    return (
      <label className={cn("block", question.wide && "md:col-span-2")}>
        {labelNode}
        {control}
        {errorNode}
      </label>
    );
  }

  // "qa": label on the left, control on the right (stacks on mobile).
  return (
    <div className="grid gap-2 border-b border-border/40 py-4 last:border-b-0 md:grid-cols-[minmax(180px,240px)_1fr] md:gap-6">
      <div>{labelNode}</div>
      <div>
        {control}
        {errorNode}
      </div>
    </div>
  );
}
