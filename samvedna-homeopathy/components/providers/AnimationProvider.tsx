"use client";

import { MotionConfig } from "framer-motion";
import type { ReactNode } from "react";
import useReducedMotion from "@/hooks/useReducedMotion";

type AnimationProviderProps = {
  children: ReactNode;
};

export default function AnimationProvider({ children }: AnimationProviderProps) {
  const prefersReducedMotion = useReducedMotion();

  return (
    <MotionConfig reducedMotion={prefersReducedMotion ? "always" : "never"}>
      {children}
    </MotionConfig>
  );
}
