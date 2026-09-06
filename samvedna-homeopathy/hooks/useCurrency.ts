"use client";

import { createContext, useContext } from "react";
import type { Currency } from "@/constants/plans";

/**
 * Simple currency toggle — defaults to USD.
 * No location detection. Users can manually switch between USD and INR
 * via the toggle in the Pricing section.
 */

type CurrencyContextValue = {
  currency: Currency;
  setCurrency: (c: Currency) => void;
  toggleCurrency: () => void;
};

export const CurrencyContext = createContext<CurrencyContextValue>({
  currency: "USD",
  setCurrency: () => {},
  toggleCurrency: () => {},
});

export default function useCurrency(): CurrencyContextValue {
  return useContext(CurrencyContext);
}
