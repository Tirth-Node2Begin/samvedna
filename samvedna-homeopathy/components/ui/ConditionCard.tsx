"use client";

import Image from "next/image";
import { motion } from "framer-motion";
import type { ElementType, ReactNode } from "react";
import type { Condition } from "@/types";
import { blurDataUrl, cn } from "@/lib/utils";
import { 
  Brain, Activity, MessageCircle, Sprout, 
  BookOpen, HeartPulse, Sparkles, ArrowUpRight 
} from "lucide-react";
import useReducedMotion from "@/hooks/useReducedMotion";

const iconMap: Record<string, ElementType> = {
  "Autism Spectrum Disorder Support": Brain,
  "ADHD Support": Activity,
  "Learning Disability Support": BookOpen,
  "Speech Delay Support": MessageCircle,
  "Developmental Delay Support": Sprout,
  "Genetic Disorders Support": Sparkles,
  "Neurological Disorders Support": HeartPulse,
};

type ConditionCardProps = {
  condition: Condition;
  delay?: number;
  isLarge?: boolean;
  variant?: "dark" | "light" | "soft";
};

const cardReveal = {
  initial: { opacity: 0, y: 34, scale: 0.96 },
  animate: { opacity: 1, y: 0, scale: 1 }
} as const;

const contentReveal = {
  initial: {},
  animate: {
    transition: {
      delayChildren: 0.14,
      staggerChildren: 0.08
    }
  }
} as const;

const itemReveal = {
  initial: { opacity: 0, y: 16 },
  animate: { opacity: 1, y: 0 }
} as const;

type StaticArticleProps = {
  children: ReactNode;
  className: string;
};

function StaticArticle({ children, className }: StaticArticleProps) {
  return <article className={className}>{children}</article>;
}

export default function ConditionCard({
  condition,
  delay = 0,
  isLarge,
  variant = "light"
}: ConditionCardProps) {
  const prefersReducedMotion = useReducedMotion();
  const Icon = iconMap[condition.name] || Activity;
  const isFeatured = variant === "dark";
  const articleClassName = cn(
    "group relative flex h-full w-full flex-col overflow-hidden rounded-3xl p-6 sm:p-8 transition-all duration-500 hover:-translate-y-1",
    isFeatured
      ? "bg-primary/[0.03] border-2 border-primary/10 text-text shadow-sm hover:shadow-md hover:border-primary/30"
      : variant === "soft"
      ? "bg-[#f4f7f6] border border-transparent hover:border-primary/20 hover:bg-white hover:shadow-lg"
      : "bg-white border border-border hover:border-primary/30 hover:shadow-lg"
  );

  const content = (
    <>
      {isFeatured && (
        <div className="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-primary/5 blur-[56px] transition-transform duration-700 group-hover:scale-110" />
      )}

      {!isFeatured && isLarge && (
        <div className="absolute bottom-0 right-0 h-40 w-40 bg-[radial-gradient(circle_at_bottom_right,var(--color-primary)_0%,transparent_70%)] opacity-5" />
      )}

      <motion.div
        className="relative z-10 flex h-full flex-col gap-5"
        initial={prefersReducedMotion ? false : "initial"}
        whileInView={prefersReducedMotion ? undefined : "animate"}
        variants={contentReveal}
        viewport={{ once: true, amount: 0.35 }}
      >
        {/* Admin-uploaded photo — optional, and the card is designed to look
            complete without one (that is how every card looked before images
            became manageable from the admin panel).
            A FIXED height, not an aspect ratio: the featured card is twice as
            wide as the rest, so a shared ratio made its image tower over the
            others. Fixed heights keep every image in proportion to its card and
            keep the row heights predictable. */}
        {condition.image ? (
          <motion.div
            className={cn(
              "relative -mx-6 -mt-6 overflow-hidden sm:-mx-8 sm:-mt-8",
              isLarge ? "h-52 lg:h-64" : "h-44"
            )}
            variants={itemReveal}
            transition={{ duration: 0.55, ease: [0.22, 1, 0.36, 1] }}
          >
            <Image
              src={condition.image}
              alt={condition.alt || condition.name}
              fill
              sizes={isLarge ? "(min-width: 1024px) 50vw, 100vw" : "(min-width: 1024px) 25vw, (min-width: 768px) 50vw, 100vw"}
              className="object-cover transition duration-700 group-hover:scale-105"
              placeholder="blur"
              blurDataURL={blurDataUrl}
              loading="lazy"
            />
            {/* The icon rides on the image instead of sitting in a row beneath
                it, so the photo runs straight into the heading as one block
                rather than reading as a separate banner. */}
            <span
              className={cn(
                "absolute bottom-4 left-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/90 text-primary shadow-sm backdrop-blur transition-transform duration-500 group-hover:scale-110 sm:left-8"
              )}
            >
              <Icon className="h-6 w-6" strokeWidth={1.5} aria-hidden="true" />
            </span>
          </motion.div>
        ) : null}

        {/* Without an image the icon keeps its original row; with one it has
            moved onto the photo, so only the hover arrow remains — pinned to the
            card corner so it does not reintroduce a gap above the heading. */}
        {condition.image ? (
          <motion.div
            className={cn(
              "absolute right-0 top-0 z-20 flex h-8 w-8 items-center justify-center rounded-full opacity-0 -translate-x-4 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100",
              "bg-white/90 text-primary shadow-sm backdrop-blur"
            )}
            variants={itemReveal}
            transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }}
          >
            <ArrowUpRight className="h-4 w-4" />
          </motion.div>
        ) : (
          <div className="flex items-start justify-between">
            <motion.div
              className={cn(
                "flex h-12 w-12 items-center justify-center rounded-2xl transition-transform duration-500 group-hover:scale-110",
                isFeatured ? "bg-primary/10 text-primary" : "bg-primary/5 text-primary"
              )}
              variants={itemReveal}
              transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }}
            >
              <Icon className="h-6 w-6" strokeWidth={1.5} />
            </motion.div>
            <motion.div
              className={cn(
                "flex h-8 w-8 items-center justify-center rounded-full opacity-0 -translate-x-4 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100",
                isFeatured ? "bg-primary/10 text-primary" : "bg-primary/5 text-primary"
              )}
              variants={itemReveal}
              transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }}
            >
              <ArrowUpRight className="h-4 w-4" />
            </motion.div>
          </div>
        )}

        <div className={condition.image ? "" : "mt-4"}>
          <motion.h3
            className={cn(
              "font-display text-xl font-bold leading-tight text-text",
              isLarge ? "lg:text-3xl" : "lg:text-xl"
            )}
            variants={itemReveal}
            transition={{ duration: 0.55, ease: [0.22, 1, 0.36, 1] }}
          >
            {condition.name}
          </motion.h3>
          <motion.p
            className="mt-3 text-sm leading-relaxed text-muted"
            variants={itemReveal}
            transition={{ duration: 0.55, ease: [0.22, 1, 0.36, 1] }}
          >
            {condition.description}
          </motion.p>
        </div>
      </motion.div>
    </>
  );

  if (prefersReducedMotion) {
    return <StaticArticle className={articleClassName}>{content}</StaticArticle>;
  }

  return (
    <motion.article
      className={articleClassName}
      initial={cardReveal.initial}
      whileInView={cardReveal.animate}
      viewport={{ once: true, amount: 0.24 }}
      transition={{ duration: 0.72, delay, ease: [0.22, 1, 0.36, 1] }}
    >
      {content}
    </motion.article>
  );
}
