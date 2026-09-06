"use client";

import { useCallback, useEffect, useMemo, useState, type ReactNode } from "react";
import type { Currency } from "@/constants/plans";
import { CurrencyContext } from "@/hooks/useCurrency";

/**
 * Provides USD/INR currency state to all children.
 *
 * The choice is persisted to localStorage because the care-plan cards link to
 * /consultation with a plain <a>, which is a full document load: React state
 * alone resets to the USD default, so a visitor who picked ₹ on the pricing
 * section was quoted $ on the payment step of the very plan they just chose.
 * Persisting also keeps the currency across refreshes and new tabs.
 */

const STORAGE_KEY = "samvedna:currency";

function isCurrency(value: unknown): value is Currency {
  return value === "USD" || value === "INR";
}

/** Writes are best-effort: private browsing and blocked storage must not throw. */
function persist(value: Currency): void {
  try {
    window.localStorage.setItem(STORAGE_KEY, value);
  } catch {
    // Storage unavailable — the choice simply won't survive this navigation.
  }
}

export default function CurrencyProvider({ children }: { children: ReactNode }) {
  const [currency, setCurrencyState] = useState<Currency>("USD");

  // Restored in an effect rather than in useState's initialiser: every page is
  // statically exported with the USD default baked in, so reading localStorage
  // during the first render would be a hydration mismatch.
  useEffect(() => {
    try {
      const stored = window.localStorage.getItem(STORAGE_KEY);
      if (isCurrency(stored)) {
        setCurrencyState(stored);
      }
    } catch {
      // Storage unavailable — keep the default.
    }
  }, []);

  const setCurrency = useCallback((next: Currency) => {
    setCurrencyState(next);
    persist(next);
  }, []);

  const toggleCurrency = useCallback(() => {
    setCurrencyState((prev) => {
      const next: Currency = prev === "USD" ? "INR" : "USD";
      persist(next);
      return next;
    });
  }, []);

  const value = useMemo(
    () => ({ currency, setCurrency, toggleCurrency }),
    [currency, setCurrency, toggleCurrency]
  );

  return (
    <CurrencyContext.Provider value={value}>{children}</CurrencyContext.Provider>
  );
}
