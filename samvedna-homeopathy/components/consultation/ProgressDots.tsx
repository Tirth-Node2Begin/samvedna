"use client";

import { cn } from "@/lib/utils";

type ProgressDotsProps = {
  total: number;
  current: number;
  onSelect?: (index: number) => void;
};

export default function ProgressDots({ total, current, onSelect }: ProgressDotsProps) {
  return (
    <div className="flex flex-wrap items-center justify-center gap-2.5">
      {Array.from({ length: total }).map((_, index) => {
        const isActive = index === current;
        const isDone = index < current;
        return (
          <button
            key={index}
            type="button"
            aria-label={`Go to step ${index + 1}`}
            aria-current={isActive ? "step" : undefined}
            onClick={onSelect ? () => onSelect(index) : undefined}
            disabled={!onSelect}
            // Form-filler browser extensions stamp `fdprocessedid` onto buttons
            // before React hydrates, which reads as a hydration mismatch. Same
            // reason the wizard's inputs carry this.
            suppressHydrationWarning
            className={cn(
              "h-3 w-3 rounded-full transition-all",
              isActive ? "scale-110 bg-primary" : isDone ? "bg-primary/60" : "bg-primary/25",
              onSelect ? "cursor-pointer hover:bg-primary/80" : "cursor-default"
            )}
          />
        );
      })}
    </div>
  );
}
