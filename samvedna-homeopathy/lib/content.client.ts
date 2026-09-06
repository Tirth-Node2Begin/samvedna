"use client";

import { useEffect, useState } from "react";
import type { BlogPost, Condition, TeamMember, VideoTestimonial } from "@/types";

/**
 * Browser-side content refresh.
 *
 * A static export freezes content at build time, which would make the admin CMS
 * useless — a post published by the client would never appear. So every page
 * renders the build-time snapshot first (good SEO, no loading flash) and then
 * re-fetches the same PHP endpoint on mount, swapping in fresher data if it
 * differs.
 *
 * Same-origin relative URLs: the static export and core-php share one docroot.
 */

type Endpoint = "blogs.php" | "doctors.php" | "testimonials.php" | "conditions.php";

async function fetchLive<T>(endpoint: Endpoint): Promise<T[] | null> {
  try {
    // no-store bypasses the 60s Cache-Control that helpers.php json_response sets,
    // so a just-published post shows up immediately rather than up to a minute later.
    const res = await fetch(`/api/${endpoint}`, { cache: "no-store" });
    if (!res.ok) return null;
    const data = (await res.json()) as unknown;
    // An empty array is a legitimate answer - "the admin has no rows" - and must
    // be applied so the section disappears. Only a genuine failure returns null.
    return Array.isArray(data) ? (data as T[]) : null;
  } catch {
    // Offline, or PHP down — keep whatever was baked in at build time.
    return null;
  }
}

/**
 * Returns `initial` immediately, then the live list once it arrives.
 * Falls back to `initial` forever if the API is unreachable, so an outage never
 * blanks a page that rendered fine.
 */
function useLiveList<T>(endpoint: Endpoint, initial: T[]): T[] {
  const [items, setItems] = useState<T[]>(initial);

  useEffect(() => {
    let cancelled = false;

    void fetchLive<T>(endpoint).then((live) => {
      if (cancelled || !live) return;
      // Avoid a pointless re-render (and image re-decode) when nothing changed.
      setItems((current) =>
        JSON.stringify(current) === JSON.stringify(live) ? current : live
      );
    });

    return () => {
      cancelled = true;
    };
  }, [endpoint]);

  return items;
}

export function useLiveBlogPosts(initial: BlogPost[]): BlogPost[] {
  return useLiveList<BlogPost>("blogs.php", initial);
}

export function useLiveTeam(initial: TeamMember[]): TeamMember[] {
  return useLiveList<TeamMember>("doctors.php", initial);
}

export function useLiveConditions(initial: Condition[]): Condition[] {
  return useLiveList<Condition>("conditions.php", initial);
}

export function useLiveVideoTestimonials(
  initial: VideoTestimonial[]
): VideoTestimonial[] {
  return useLiveList<VideoTestimonial>("testimonials.php", initial);
}

/**
 * One post by slug, for the blog detail page and its dynamic fallback.
 * `slug === null` means "not resolved yet" (the fallback reads it from the URL
 * in an effect) and stays in the loading state rather than flashing "not found".
 */
export function useLiveBlogPost(
  slug: string | null,
  initial: BlogPost | null
): { post: BlogPost | null; loading: boolean } {
  const [post, setPost] = useState<BlogPost | null>(initial);
  const [loading, setLoading] = useState(initial === null);

  useEffect(() => {
    let cancelled = false;
    if (slug === null) return; // still resolving
    if (slug === "") {
      setLoading(false);
      return;
    }

    void fetchLive<BlogPost>("blogs.php").then((posts) => {
      if (cancelled) return;
      if (posts) {
        // The fetch succeeded, so the API is authoritative: no match means the
        // post was deleted or unpublished, and the page must say so rather than
        // keep serving a prerendered copy. A failed fetch (posts === null) keeps
        // whatever rendered.
        setPost(posts.find((p) => p.slug === slug) ?? null);
      }
      setLoading(false);
    });

    return () => {
      cancelled = true;
    };
  }, [slug]);

  return { post, loading };
}
