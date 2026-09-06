"use client";

import { cn } from "@/lib/utils";
import useCurrency from "@/hooks/useCurrency";

/**
 * USD / INR toggle switch.
 * A client island — the parent Pricing section stays a server component.
 */
export default function CurrencyToggle() {
  const { currency, toggleCurrency } = useCurrency();
  const isUSD = currency === "USD";

  return (
    <div className="mt-10 flex items-center justify-center gap-3">
      <span
        className={cn(
          "text-sm font-semibold transition-colors duration-200",
          isUSD ? "text-primary" : "text-muted"
        )}
      >
        $ USD
      </span>
      <button
        type="button"
        role="switch"
        aria-checked={!isUSD}
        aria-label="Toggle between Dollar and Rupee pricing"
        onClick={toggleCurrency}
        suppressHydrationWarning
        className={cn(
          "relative inline-flex h-7 w-12 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2",
          "bg-primary"
        )}
      >
        <span
          className={cn(
            "pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-md ring-0 transition-transform duration-200 ease-in-out",
            isUSD ? "translate-x-0.5" : "translate-x-[22px]"
          )}
        />
      </button>
      <span
        className={cn(
          "text-sm font-semibold transition-colors duration-200",
          !isUSD ? "text-primary" : "text-muted"
        )}
      >
        ₹ INR
      </span>
    </div>
  );
}
