"use client";

import { Check, Search, X } from "lucide-react";
import { cn } from "@/lib/format";

/* ----------------------------------------------------------------- Field -- */

/**
 * Label + control + inline error.
 *
 * Labels are `text-xs font-bold uppercase tracking-wider text-slate-500`; the
 * required marker is a rose asterisk. Errors carry `role="alert"` and the
 * control gets `aria-invalid` from its own `invalid` prop.
 */
export function Field({
  label,
  hint,
  required,
  error,
  locked,
  className,
  children,
}: {
  label?: string;
  hint?: string;
  required?: boolean;
  error?: string;
  locked?: boolean;
  className?: string;
  children: React.ReactNode;
}) {
  return (
    <div className={cn("flex flex-col gap-1.5", className)}>
      {label && (
        <label className="flex flex-wrap items-baseline gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-500">
          {label}
          {required && <span className="text-rose-600">*</span>}
          {locked && (
            <span className="text-[10px] font-bold normal-case tracking-normal text-slate-400">
              auto
            </span>
          )}
          {hint && (
            <span className="text-[10.5px] font-medium normal-case tracking-normal text-slate-400">
              {hint}
            </span>
          )}
        </label>
      )}
      {children}
      {error && (
        <p role="alert" className="text-[11.5px] font-semibold text-rose-600">
          {error}
        </p>
      )}
    </div>
  );
}

export function FieldRow({ cols = 2, children }: { cols?: 1 | 2 | 3 | 4; children: React.ReactNode }) {
  const grid = {
    1: "grid-cols-1",
    2: "sm:grid-cols-2",
    3: "sm:grid-cols-2 lg:grid-cols-3",
    4: "sm:grid-cols-2 lg:grid-cols-4",
  }[cols];
  return <div className={cn("grid grid-cols-1 gap-4", grid)}>{children}</div>;
}

/* ---------------------------------------------------------------- Inputs -- */

type InputProps = React.InputHTMLAttributes<HTMLInputElement> & { invalid?: boolean };

export function Input({ invalid, className, ...props }: InputProps) {
  return (
    <input
      className={cn("field-control", className)}
      data-invalid={invalid || undefined}
      aria-invalid={invalid || undefined}
      {...props}
    />
  );
}

type TextareaProps = React.TextareaHTMLAttributes<HTMLTextAreaElement> & { invalid?: boolean };

export function Textarea({ invalid, className, ...props }: TextareaProps) {
  return (
    <textarea
      className={cn("field-control", className)}
      data-invalid={invalid || undefined}
      aria-invalid={invalid || undefined}
      {...props}
    />
  );
}

type SelectProps = React.SelectHTMLAttributes<HTMLSelectElement> & {
  invalid?: boolean;
  options: { value: string; label: string }[];
  placeholder?: string;
};

export function Select({ invalid, options, placeholder, className, ...props }: SelectProps) {
  return (
    <select
      className={cn("field-control", className)}
      data-invalid={invalid || undefined}
      aria-invalid={invalid || undefined}
      {...props}
    >
      {placeholder && <option value="">{placeholder}</option>}
      {options.map((option) => (
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      ))}
    </select>
  );
}

/** The shared search field — leading icon, clear affordance. */
export function SearchInput({
  value,
  onChange,
  placeholder = "Search",
  className,
}: {
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  className?: string;
}) {
  return (
    <div className={cn("relative", className)}>
      <Search
        className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
        aria-hidden
      />
      <input
        value={value}
        onChange={(event) => onChange(event.target.value)}
        placeholder={placeholder}
        className="field-control pl-9 pr-9"
      />
      {value && (
        <button
          type="button"
          onClick={() => onChange("")}
          aria-label="Clear search"
          className="absolute right-2 top-1/2 grid size-6 -translate-y-1/2 place-items-center rounded-full text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
        >
          <X className="size-3.5" />
        </button>
      )}
    </div>
  );
}

/** A read-only value styled like an input — for auto-computed fields. */
export function LockedValue({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex h-10 items-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-500">
      {children || "—"}
    </div>
  );
}

/* ------------------------------------------------------------- Checklist -- */

export function CheckItem({
  checked,
  onToggle,
  children,
  disabled,
}: {
  checked: boolean;
  onToggle?: () => void;
  children: React.ReactNode;
  disabled?: boolean;
}) {
  const Wrapper = onToggle ? "button" : "div";
  return (
    <Wrapper
      {...(onToggle ? { type: "button" as const, onClick: onToggle, disabled } : {})}
      className={cn(
        "flex w-full items-start gap-2.5 rounded-xl px-2 py-2 text-left text-[13px] font-medium text-slate-700",
        onToggle && !disabled && "transition-colors hover:bg-slate-50",
        disabled && "opacity-70"
      )}
    >
      <span
        className={cn(
          "mt-px grid size-[18px] shrink-0 place-items-center rounded-md border transition-colors",
          checked ? "border-indigo-600 bg-indigo-600 text-white" : "border-slate-300 bg-white"
        )}
        aria-hidden
      >
        {checked && <Check className="size-3" strokeWidth={3.5} />}
      </span>
      <span className="leading-snug">{children}</span>
    </Wrapper>
  );
}

/* ------------------------------------------------------------ RadioCards -- */

export function RadioCards<T extends string>({
  value,
  onChange,
  options,
  columns = 2,
  invalid,
}: {
  value: T | "";
  onChange: (value: T) => void;
  options: { value: T; label: string; description?: string }[];
  columns?: 1 | 2 | 3;
  invalid?: boolean;
}) {
  const grid = { 1: "grid-cols-1", 2: "sm:grid-cols-2", 3: "sm:grid-cols-3" }[columns];
  return (
    <div className={cn("grid grid-cols-1 gap-2", grid)}>
      {options.map((option) => {
        const selected = value === option.value;
        return (
          <button
            key={option.value}
            type="button"
            onClick={() => onChange(option.value)}
            aria-pressed={selected}
            className={cn(
              "rounded-xl border px-3.5 py-2.5 text-left transition-colors",
              selected
                ? "border-indigo-300 bg-indigo-50 ring-1 ring-indigo-200"
                : invalid
                  ? "border-rose-300 bg-white hover:border-rose-400"
                  : "border-slate-200 bg-white hover:bg-slate-50"
            )}
          >
            <span
              className={cn(
                "block text-[13px] font-bold",
                selected ? "text-indigo-700" : "text-slate-700"
              )}
            >
              {option.label}
            </span>
            {option.description && (
              <span className="mt-0.5 block text-[11.5px] font-medium text-slate-500">
                {option.description}
              </span>
            )}
          </button>
        );
      })}
    </div>
  );
}

/* ------------------------------------------------------------- Segmented -- */

/** The segmented filter rail — a sliding white pill on a slate well. */
export function Segmented<T extends string>({
  value,
  onChange,
  options,
}: {
  value: T;
  onChange: (value: T) => void;
  options: { value: T; label: string; count?: number }[];
}) {
  return (
    <nav className="hide-scrollbar inline-flex max-w-full gap-1 overflow-x-auto rounded-2xl border border-slate-200/70 bg-slate-100/70 p-1 shadow-[inset_0_1px_2px_rgba(15,23,42,0.04)]">
      {options.map((option) => {
        const active = value === option.value;
        return (
          <button
            key={option.value}
            type="button"
            onClick={() => onChange(option.value)}
            aria-current={active ? "true" : undefined}
            className={cn(
              "inline-flex shrink-0 items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-[12.5px] font-bold transition-colors",
              active
                ? "bg-white text-indigo-700 shadow-[0_1px_2px_rgba(15,23,42,0.06)] ring-1 ring-indigo-100"
                : "text-slate-500 hover:bg-white/60 hover:text-navy"
            )}
          >
            {option.label}
            {option.count !== undefined && option.count > 0 && (
              <span
                className={cn(
                  "rounded-full px-1.5 py-0.5 text-[10px] font-bold tabular-nums",
                  active ? "bg-indigo-600 text-white" : "bg-slate-200 text-slate-600"
                )}
              >
                {option.count}
              </span>
            )}
          </button>
        );
      })}
    </nav>
  );
}
