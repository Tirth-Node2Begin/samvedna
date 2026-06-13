"use client";

import { motion } from "framer-motion";
import type { ReactNode } from "react";
import {
  textRevealContainer,
  textRevealWord
} from "@/lib/animations";
import { cn } from "@/lib/utils";
import useReducedMotion from "@/hooks/useReducedMotion";

type AnimatedTextProps = {
  as?: "h1" | "h2" | "h3" | "p" | "span";
  className?: string;
  delay?: number;
  text: string;
  viewportAmount?: number;
};

type MotionElementProps = {
  ariaLabel: string;
  children: ReactNode;
  className?: string;
  delay: number;
  tag: NonNullable<AnimatedTextProps["as"]>;
  viewportAmount: number;
};

function MotionElement({
  ariaLabel,
  children,
  className,
  delay,
  tag,
  viewportAmount
}: MotionElementProps) {
  const sharedProps = {
    "aria-label": ariaLabel,
    className,
    initial: "initial",
    variants: textRevealContainer,
    viewport: { once: true, amount: viewportAmount },
    whileInView: "animate"
  } as const;

  const transition = {
    ...textRevealContainer.animate.transition,
    delayChildren: delay
  };

  switch (tag) {
    case "h1":
      return (
        <motion.h1 {...sharedProps} transition={transition}>
          {children}
        </motion.h1>
      );
    case "h2":
      return (
        <motion.h2 {...sharedProps} transition={transition}>
          {children}
        </motion.h2>
      );
    case "h3":
      return (
        <motion.h3 {...sharedProps} transition={transition}>
          {children}
        </motion.h3>
      );
    case "p":
      return (
        <motion.p {...sharedProps} transition={transition}>
          {children}
        </motion.p>
      );
    default:
      return (
        <motion.span {...sharedProps} transition={transition}>
          {children}
        </motion.span>
      );
  }
}

function StaticElement({
  children,
  className,
  tag
}: {
  children: string;
  className?: string;
  tag: NonNullable<AnimatedTextProps["as"]>;
}) {
  switch (tag) {
    case "h1":
      return <h1 className={className}>{children}</h1>;
    case "h2":
      return <h2 className={className}>{children}</h2>;
    case "h3":
      return <h3 className={className}>{children}</h3>;
    case "p":
      return <p className={className}>{children}</p>;
    default:
      return <span className={className}>{children}</span>;
  }
}

export default function AnimatedText({
  as = "span",
  className,
  delay = 0,
  text,
  viewportAmount = 0.72
}: AnimatedTextProps) {
  const prefersReducedMotion = useReducedMotion();

  if (prefersReducedMotion) {
    return (
      <StaticElement className={className} tag={as}>
        {text}
      </StaticElement>
    );
  }

  const words = text.split(" ");

  return (
    <MotionElement
      ariaLabel={text}
      className={cn(className)}
      delay={delay}
      tag={as}
      viewportAmount={viewportAmount}
    >
      <span aria-hidden="true">
        {words.map((word, index) => (
          <span key={`${word}-${index}`}>
            <span className="inline-block overflow-hidden align-bottom">
              <motion.span
                className="inline-block will-change-transform"
                variants={textRevealWord}
                transition={textRevealWord.transition}
              >
                {word}
              </motion.span>
            </span>
            {index < words.length - 1 ? " " : null}
          </span>
        ))}
      </span>
    </MotionElement>
  );
}
