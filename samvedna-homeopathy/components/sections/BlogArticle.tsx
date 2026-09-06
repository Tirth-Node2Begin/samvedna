"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useMemo, useRef, useState } from "react";
import {
  ArrowLeft,
  ArrowUpRight,
  Calendar,
  Check,
  Clock,
  Link2,
  Phone,
} from "lucide-react";
import Footer from "@/components/sections/Footer";
import BlogCard from "@/components/ui/BlogCard";
import Button from "@/components/ui/Button";
import { useLiveBlogPost, useLiveBlogPosts } from "@/lib/content.client";
import { blurDataUrl, cn, contact, defaultBlogImage, siteUrl } from "@/lib/utils";
import type { BlogPost } from "@/types";

/**
 * Blog article body, shared by two routes:
 *
 *   app/blog/[slug]  - prebuilt at build time for every post that existed then
 *                      (real HTML for crawlers), refreshed here on mount.
 *   app/blog-view    - the fallback for posts published after the last build;
 *                      .htaccess rewrites unmatched /blog/* here and the slug is
 *                      read from the URL, so `initial` is null and we fetch.
 */

function ArticleShell({ children }: { children: React.ReactNode }) {
  return (
    <>
      <article className="bg-white pb-16 pt-28 md:pt-32">
        <div className="mx-auto max-w-3xl px-5 md:px-8">
          <Link
            href="/#blogs"
            className="inline-flex items-center gap-1.5 text-sm font-semibold text-primary transition hover:gap-2.5"
          >
            <ArrowLeft className="h-4 w-4" aria-hidden="true" />
            Back to all articles
          </Link>
          {children}
        </div>
      </article>
      <Footer />
    </>
  );
}

/** Thin bar pinned to the top edge that fills as the article is scrolled. */
function ReadingProgress({
  targetRef,
}: {
  targetRef: React.RefObject<HTMLElement | null>;
}) {
  const [progress, setProgress] = useState(0);

  useEffect(() => {
    let frame = 0;
    const update = () => {
      frame = 0;
      const el = targetRef.current;
      if (!el) return;
      const total = el.offsetHeight - window.innerHeight;
      const scrolled = Math.min(
        Math.max(-el.getBoundingClientRect().top, 0),
        Math.max(total, 0)
      );
      setProgress(total > 0 ? scrolled / total : 0);
    };
    const onScroll = () => {
      if (!frame) frame = window.requestAnimationFrame(update);
    };
    update();
    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll);
    return () => {
      window.removeEventListener("scroll", onScroll);
      window.removeEventListener("resize", onScroll);
      if (frame) window.cancelAnimationFrame(frame);
    };
  }, [targetRef]);

  return (
    <div
      className="fixed inset-x-0 top-0 z-[60] h-[3px] bg-transparent"
      aria-hidden="true"
    >
      <div
        className="h-full origin-left bg-gradient-to-r from-primary to-secondary transition-[width] duration-150 ease-out"
        style={{ width: `${Math.round(progress * 100)}%` }}
      />
    </div>
  );
}

/** Author + date row shown under the title. */
function Byline({ author, date }: { author?: string; date: string }) {
  const name = author?.trim() || "Samvedna Homeopathy";
  const initial = name.replace(/^Dr\.?\s*/i, "").charAt(0).toUpperCase() || "S";
  return (
    <div className="flex items-center gap-3">
      <span
        className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary to-secondary text-base font-semibold text-white shadow-sm"
        aria-hidden="true"
      >
        {initial}
      </span>
      <span className="flex flex-col">
        <span className="text-sm font-semibold text-text">{name}</span>
        {date ? (
          <span className="inline-flex items-center gap-1.5 text-xs text-muted">
            <Calendar className="h-3.5 w-3.5" aria-hidden="true" />
            {date}
          </span>
        ) : null}
      </span>
    </div>
  );
}

/** Inline brand glyph (lucide dropped brand logos). */
function BrandIcon({ path }: { path: string }) {
  return (
    <svg
      viewBox="0 0 24 24"
      className="h-4 w-4"
      fill="currentColor"
      aria-hidden="true"
    >
      <path d={path} />
    </svg>
  );
}

const BRAND = {
  whatsapp:
    "M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z",
  facebook:
    "M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z",
  x: "M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z",
} as const;

/** Copy-link + social share controls. */
function ShareRow({ title, url }: { title: string; url: string }) {
  const [copied, setCopied] = useState(false);

  const copy = async () => {
    try {
      await navigator.clipboard.writeText(url);
      setCopied(true);
      window.setTimeout(() => setCopied(false), 2000);
    } catch {
      /* clipboard blocked — no-op */
    }
  };

  const enc = encodeURIComponent;
  const links = [
    {
      label: "Share on WhatsApp",
      href: `https://wa.me/?text=${enc(`${title} ${url}`)}`,
      icon: <BrandIcon path={BRAND.whatsapp} />,
    },
    {
      label: "Share on Facebook",
      href: `https://www.facebook.com/sharer/sharer.php?u=${enc(url)}`,
      icon: <BrandIcon path={BRAND.facebook} />,
    },
    {
      label: "Share on X",
      href: `https://twitter.com/intent/tweet?url=${enc(url)}&text=${enc(title)}`,
      icon: <BrandIcon path={BRAND.x} />,
    },
  ];

  return (
    <div className="flex flex-wrap items-center gap-2.5">
      <span className="mr-1 text-xs font-semibold uppercase tracking-wider text-muted">
        Share
      </span>
      {links.map((l) => (
        <a
          key={l.label}
          href={l.href}
          target="_blank"
          rel="noopener noreferrer"
          aria-label={l.label}
          className="flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted transition hover:border-primary hover:bg-primary/5 hover:text-primary"
        >
          {l.icon}
        </a>
      ))}
      <button
        type="button"
        onClick={copy}
        aria-label="Copy link"
        className={cn(
          "flex h-9 items-center gap-1.5 rounded-full border px-3 text-xs font-semibold transition",
          copied
            ? "border-success/40 bg-success/10 text-success"
            : "border-border text-muted hover:border-primary hover:bg-primary/5 hover:text-primary"
        )}
      >
        {copied ? (
          <Check className="h-4 w-4" aria-hidden="true" />
        ) : (
          <Link2 className="h-4 w-4" aria-hidden="true" />
        )}
        {copied ? "Copied" : "Copy link"}
      </button>
    </div>
  );
}

/** Compact, sticky consultation card shown in the desktop sidebar. */
function SidebarCta() {
  const points = [
    "20+ years of experience",
    "10,000+ patients treated",
    "Worldwide online consultations",
  ];
  return (
    <div className="rounded-2xl border border-border bg-white p-6 shadow-premium">
      <p className="text-xs font-semibold uppercase tracking-wider text-primary">
        Next step
      </p>
      <h3 className="mt-2 font-display text-xl font-semibold leading-snug text-text">
        Book a consultation for your child
      </h3>
      <p className="mt-2 text-sm leading-relaxed text-muted">
        Online or in-clinic, with a team experienced in autism and
        developmental care.
      </p>
      <div className="mt-5 flex flex-col gap-3">
        <Button href="/consultation/" size="md" className="w-full">
          Book Consultation
        </Button>
        <Button
          href={contact.whatsappHref}
          target="_blank"
          rel="noopener noreferrer"
          size="md"
          variant="secondary"
          className="w-full"
          icon={<Phone className="h-4 w-4" aria-hidden="true" />}
          iconPosition="left"
        >
          Chat on WhatsApp
        </Button>
      </div>
      <ul className="mt-6 space-y-3 border-t border-border pt-5">
        {points.map((p) => (
          <li key={p} className="flex items-start gap-2.5 text-sm text-text/80">
            <span className="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-primary/10">
              <Check className="h-3 w-3 text-primary" aria-hidden="true" />
            </span>
            {p}
          </li>
        ))}
      </ul>
    </div>
  );
}

/** End-of-article call to action (mobile, where there is no sidebar). */
function ConsultCTA() {
  return (
    <div className="overflow-hidden rounded-3xl border border-border bg-primary/[0.04] p-8 md:p-10">
      <p className="text-sm font-semibold uppercase tracking-wider text-primary">
        Next step
      </p>
      <h2 className="mt-3 font-display text-2xl font-semibold leading-tight text-text md:text-3xl">
        Have concerns about your child&apos;s development?
      </h2>
      <p className="mt-3 max-w-xl text-base leading-relaxed text-muted">
        Talk to the Samvedna care team about your child&apos;s history, current
        therapies and daily challenges — online or in-clinic.
      </p>
      <div className="mt-7 flex flex-wrap items-center gap-3">
        <Button href="/#consultation" size="lg" className="w-full sm:w-auto">
          Book a Consultation
        </Button>
        <Button
          href={contact.whatsappHref}
          target="_blank"
          rel="noopener noreferrer"
          size="lg"
          variant="secondary"
          className="w-full sm:w-auto"
          icon={<Phone className="h-5 w-5" aria-hidden="true" />}
          iconPosition="left"
        >
          Chat on WhatsApp
        </Button>
      </div>
    </div>
  );
}

/** Up to three more posts — same category first, then latest. */
function RelatedArticles({
  current,
  initialPosts,
}: {
  current: BlogPost;
  initialPosts: BlogPost[];
}) {
  const posts = useLiveBlogPosts(initialPosts);

  const related = useMemo(() => {
    const others = posts.filter((p) => p.slug !== current.slug);
    const sameCat = others.filter((p) => p.category === current.category);
    const rest = others.filter((p) => p.category !== current.category);
    return [...sameCat, ...rest].slice(0, 3);
  }, [posts, current.slug, current.category]);

  if (related.length === 0) return null;

  return (
    <section className="border-t border-border bg-bg-soft py-14 md:py-20">
      <div className="mx-auto max-w-6xl px-5 md:px-8">
        <div className="flex flex-wrap items-end justify-between gap-4">
          <div>
            <p className="text-sm font-semibold uppercase tracking-wider text-primary">
              Keep reading
            </p>
            <h2 className="mt-2 font-display text-2xl font-semibold text-text md:text-3xl">
              More articles
            </h2>
          </div>
          <Link
            href="/#blogs"
            className="inline-flex items-center gap-1 text-sm font-semibold text-primary transition hover:gap-2"
          >
            View all
            <ArrowUpRight className="h-4 w-4" aria-hidden="true" />
          </Link>
        </div>
        <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {related.map((post) => (
            <BlogCard key={post.slug} post={post} />
          ))}
        </div>
      </div>
    </section>
  );
}

export default function BlogArticle({
  slug,
  initial,
  initialPosts = [],
}: {
  /** null while the fallback route is still reading the slug from the URL. */
  slug: string | null;
  initial: BlogPost | null;
  /** Seeds "More articles"; empty on the fallback route (fetched on mount). */
  initialPosts?: BlogPost[];
}) {
  const { post, loading } = useLiveBlogPost(slug, initial);
  const articleRef = useRef<HTMLElement | null>(null);

  if (loading) {
    return (
      <ArticleShell>
        <div className="mt-10 animate-pulse space-y-4" aria-busy="true">
          <div className="h-4 w-32 rounded bg-surface" />
          <div className="h-10 w-3/4 rounded bg-surface" />
          <div className="h-4 w-full rounded bg-surface" />
          <div className="h-4 w-5/6 rounded bg-surface" />
        </div>
      </ArticleShell>
    );
  }

  if (!post) {
    return (
      <ArticleShell>
        <h1 className="mt-6 font-display text-3xl font-semibold text-text">
          Article not found
        </h1>
        <p className="mt-4 text-lg leading-8 text-muted">
          This article may have been removed or the link is incorrect.
        </p>
      </ArticleShell>
    );
  }

  const shareUrl = `${siteUrl}${post.href}`;

  return (
    <>
      <ReadingProgress targetRef={articleRef} />

      <article ref={articleRef} className="bg-white pb-4 pt-28 md:pt-32">
        <div className="mx-auto max-w-6xl px-5 md:px-8">
          {/* Header — spans the full content width */}
          <header>
            <Link
              href="/#blogs"
              className="inline-flex items-center gap-1.5 text-sm font-semibold text-primary transition hover:gap-2.5"
            >
              <ArrowLeft className="h-4 w-4" aria-hidden="true" />
              Back to all articles
            </Link>

            <div className="mt-6 flex flex-wrap items-center gap-3 text-xs font-medium text-muted">
              {post.category ? (
                <span className="rounded-full bg-primary/10 px-3 py-1 font-semibold text-primary">
                  {post.category}
                </span>
              ) : null}
              {post.readTime ? (
                <span className="inline-flex items-center gap-1">
                  <Clock className="h-3.5 w-3.5" aria-hidden="true" />
                  {post.readTime}
                </span>
              ) : null}
            </div>

            <h1 className="mt-4 max-w-4xl text-balance font-display text-3xl font-semibold leading-[1.12] tracking-tight text-text md:text-4xl lg:text-5xl">
              {post.title}
            </h1>

            {post.excerpt ? (
              <p className="mt-5 max-w-3xl text-lg leading-8 text-muted md:text-xl">
                {post.excerpt}
              </p>
            ) : null}

            <div className="mt-7 flex flex-wrap items-center justify-between gap-5 border-y border-border py-5">
              <Byline author={post.author} date={post.date} />
              <ShareRow title={post.title} url={shareUrl} />
            </div>
          </header>

          {/* Hero image — full content width */}
          <div className="mt-9 overflow-hidden rounded-2xl bg-surface shadow-premium ring-1 ring-black/5">
            <div className="relative aspect-[16/9] w-full md:aspect-[21/9]">
              <Image
                src={post.image || defaultBlogImage}
                alt={post.alt || post.title}
                fill
                sizes="(min-width: 1152px) 1088px, 100vw"
                className="object-cover"
                placeholder="blur"
                blurDataURL={blurDataUrl}
                priority
              />
            </div>
          </div>

          {/* Two columns: article body + sticky sidebar */}
          <div className="mt-12 grid grid-cols-1 gap-10 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-14">
            <div className="min-w-0">
              {post.content ? (
                <div
                  className="blog-content"
                  dangerouslySetInnerHTML={{ __html: post.content }}
                />
              ) : (
                <p className="text-lg leading-8 text-text">{post.excerpt}</p>
              )}

              {/* Mobile CTA (the sidebar is hidden on small screens) */}
              <div className="mt-12 lg:hidden">
                <ConsultCTA />
              </div>
            </div>

            <aside className="hidden lg:block">
              <div className="sticky top-28">
                <SidebarCta />
              </div>
            </aside>
          </div>
        </div>
      </article>

      <RelatedArticles current={post} initialPosts={initialPosts} />

      <Footer />
    </>
  );
}
