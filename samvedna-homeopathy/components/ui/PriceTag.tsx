"use client";

import { getPlanPrice, type Plan } from "@/constants/plans";
import useCurrency from "@/hooks/useCurrency";

/**
 * Renders a plan's price in the selected currency (USD default, togglable to INR).
 * A client island so the server-rendered Pricing section stays static.
 */
export default function PriceTag({
  plan,
  className,
}: {
  plan: Plan;
  className?: string;
}) {
  const { currency } = useCurrency();
  return <span className={className}>{getPlanPrice(plan, currency)}</span>;
}
