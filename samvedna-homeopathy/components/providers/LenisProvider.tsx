"use client";

import { ReactLenis } from "lenis/react";
import type { ReactNode } from "react";

type LenisProviderProps = {
  children: ReactNode;
};

export default function LenisProvider({ children }: LenisProviderProps) {
  return (
    <ReactLenis root options={{ lerp: 0.08, duration: 1.08, smoothWheel: true }}>
      {children}
    </ReactLenis>
  );
}
