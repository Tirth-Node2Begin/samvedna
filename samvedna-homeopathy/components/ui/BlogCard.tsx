"use client";

import { motion, useScroll, useTransform } from "framer-motion";
import Image from "next/image";
import { ArrowUpRight, Clock } from "lucide-react";
import { useRef } from "react";
import type { BlogPost } from "@/types";
import { blurDataUrl } from "@/lib/utils";
import useReducedMotion from "@/hooks/useReducedMotion";

type BlogCardProps = {
  post: BlogPost;
};

export default function BlogCard({ post }: BlogCardProps) {
  const prefersReducedMotion = useReducedMotion();
  const cardRef = useRef<HTMLElement>(null);

  // Progress from when the card enters the viewport (0) to when it leaves (1).
  const { scrollYProgress } = useScroll({
    target: cardRef,
    offset: ["start end", "end start"]
  });

  // The inner image wrapper is 120% of the frame (-inset-[10%]), so translating
  // it by ±6% of its own height (≈7.2% of the frame) never reveals an edge.
  const y = useTransform(scrollYProgress, [0, 1], ["-6%", "6%"]);

  return (
    <article
      ref={cardRef}
      className="group flex h-full flex-col overflow-hidden rounded-card border border-border bg-white transition duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-premium"
    >
      <a href={post.href} className="relative block aspect-[16/10] overflow-hidden bg-surface">
        <motion.div
          className="absolute -inset-[10%] will-change-transform"
          style={prefersReducedMotion ? undefined : { y }}
        >
          <Image
            src={post.image}
            alt={post.alt}
            fill
            sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
            className="object-cover transition-transform duration-500 group-hover:scale-[1.04]"
            placeholder="blur"
            blurDataURL={blurDataUrl}
            loading="lazy"
          />
        </motion.div>
        <span className="absolute left-3 top-3 z-10 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-primary shadow-sm backdrop-blur">
          {post.category}
        </span>
      </a>

      <div className="flex flex-1 flex-col p-6">
        <div className="flex items-center gap-3 text-xs font-medium text-muted">
          <span>{post.date}</span>
          <span className="inline-flex items-center gap-1">
            <Clock className="h-3.5 w-3.5" aria-hidden="true" />
            {post.readTime}
          </span>
        </div>

        <h3 className="mt-3 font-display text-lg font-semibold leading-snug text-text transition duration-200 group-hover:text-primary">
          <a href={post.href} className="focus-visible:underline">
            {post.title}
          </a>
        </h3>

        <p className="mt-3 text-sm leading-6 text-muted">{post.excerpt}</p>

        <a
          href={post.href}
          className="mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold text-primary"
        >
          Read article
          <ArrowUpRight
            className="h-4 w-4 transition-transform duration-200 group-hover:-translate-y-0.5 group-hover:translate-x-0.5"
            aria-hidden="true"
          />
        </a>
      </div>
    </article>
  );
}
