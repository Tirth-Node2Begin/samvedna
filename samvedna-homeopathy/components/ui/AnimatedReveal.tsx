"use client";

import { motion } from "framer-motion";
import type { ReactNode } from "react";
import {
  fadeIn,
  fadeUp,
  scaleIn,
  slideLeft,
  slideRight
} from "@/lib/animations";
import { cn } from "@/lib/utils";
import useReducedMotion from "@/hooks/useReducedMotion";

const revealVariants = {
  fadeIn,
  fadeUp,
  scaleIn,
  slideLeft,
  slideRight
} as const;

type AnimatedRevealProps = {
  children: ReactNode;
  className?: string;
  delay?: number;
  once?: boolean;
  variant?: keyof typeof revealVariants;
  viewportAmount?: number;
};

export default function AnimatedReveal({
  children,
  className,
  delay = 0,
  once = true,
  variant = "fadeUp",
  viewportAmount = 0.18
}: AnimatedRevealProps) {
  const prefersReducedMotion = useReducedMotion();
  const animation = revealVariants[variant];

  if (prefersReducedMotion) {
    return <div className={className}>{children}</div>;
  }

  return (
    <motion.div
      className={cn(className)}
      initial={animation.initial}
      whileInView={animation.animate}
      viewport={{ once, amount: viewportAmount }}
      transition={{ ...animation.transition, delay }}
    >
      {children}
    </motion.div>
  );
}
