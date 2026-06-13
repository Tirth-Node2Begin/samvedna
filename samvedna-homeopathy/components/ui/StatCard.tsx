import { cn } from "@/lib/utils";

type StatCardProps = {
  value: string;
  label: string;
  detail: string;
  className?: string;
};

export default function StatCard({ value, label, detail, className }: StatCardProps) {
  return (
    <div
      className={cn(
        "flex h-full flex-col justify-between rounded-card border border-white/70 bg-white/70 p-4 shadow-glass backdrop-blur-md",
        className
      )}
    >
      <p className="font-display text-xl font-semibold leading-none text-primary md:text-2xl">
        {value}
      </p>
      <p className="mt-2 text-xs font-semibold text-text">{label}</p>
      <p className="mt-0.5 text-xs leading-5 text-muted">{detail}</p>
    </div>
  );
}
