"use client";

import { useEffect, useState } from "react";
import BlogArticle from "@/components/sections/BlogArticle";

/**
 * Fallback renderer for blog posts published after the last build.
 *
 * A static export only contains the slugs that existed at build time, so
 * /blog/a-brand-new-post would 404 on Apache. The site .htaccess internally
 * rewrites any unmatched /blog/<slug> to this page (URL unchanged), and we read
 * the slug back out of the address bar and fetch it from the PHP API.
 *
 * Rendered client-side only, so it carries no SEO weight — but the moment the
 * site is rebuilt, that post gets a real prerendered page under /blog/<slug>.
 */
export default function BlogViewFallback() {
  const [slug, setSlug] = useState<string | null>(null);

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    // ?slug= wins (direct links), otherwise take the last path segment of
    // /blog/<slug> as rewritten by Apache.
    const fromQuery = params.get("slug");
    const fromPath = window.location.pathname
      .replace(/\/+$/, "")
      .split("/")
      .pop();

    setSlug(fromQuery || fromPath || "");
  }, []);

  // slug stays null until the effect runs, which keeps BlogArticle in its loading
  // state instead of briefly flashing "Article not found".
  return <BlogArticle slug={slug} initial={null} />;
}
