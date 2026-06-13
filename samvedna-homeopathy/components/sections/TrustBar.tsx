import { trustMetrics } from "@/constants/site";

export default function TrustBar() {
  return (
    <section className="border-y border-border bg-bg-soft">
      <div className="mx-auto grid max-w-content grid-cols-1 px-5 py-8 md:grid-cols-2 md:px-8 lg:grid-cols-4">
        {trustMetrics.map((metric, index) => (
          <div
            key={metric.label}
            className="border-border py-5 md:px-8 lg:border-l first:lg:border-l-0"
          >
            <p className="text-sm font-semibold text-primary">{metric.label}</p>
            <p className="mt-3 font-display text-2xl font-semibold leading-tight text-text">
              {metric.value}
            </p>
            {index < trustMetrics.length - 1 ? (
              <div className="mt-5 h-px bg-border lg:hidden" />
            ) : null}
          </div>
        ))}
      </div>
    </section>
  );
}
