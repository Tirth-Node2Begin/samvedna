import "server-only";

import { blogPosts } from "@/constants/blogs";
import team from "@/constants/team";
import videoTestimonials from "@/constants/videoTestimonials";
import type { BlogPost, TeamMember, VideoTestimonial } from "@/types";

/**
 * Server-side content layer.
 *
 * Reads live content from the Core PHP admin API (blogs, doctors, video
 * testimonials). If the PHP server is unreachable (e.g. a production build where
 * only the static site is deployed) or returns nothing, we fall back to the
 * bundled static constants so the site always renders.
 *
 * These helpers are server-only — `PHP_ADMIN_URL` is a server env var and the
 * relative image paths returned by the API resolve through Next's rewrites.
 */

const PHP_API = (process.env.PHP_ADMIN_URL || "http://localhost:8080").replace(
  /\/+$/,
  ""
);

// Re-fetch admin content at most once per this many seconds (ISR).
const REVALIDATE_SECONDS = 30;

async function fetchList<T>(endpoint: string, fallback: T[]): Promise<T[]> {
  try {
    const res = await fetch(`${PHP_API}/api/${endpoint}`, {
      next: { revalidate: REVALIDATE_SECONDS },
    });
    if (!res.ok) return fallback;
    const data = (await res.json()) as unknown;
    // Only trust a non-empty array; otherwise keep the static fallback.
    if (Array.isArray(data) && data.length > 0) {
      return data as T[];
    }
    return fallback;
  } catch {
    return fallback;
  }
}

export function getBlogPosts(): Promise<BlogPost[]> {
  return fetchList<BlogPost>("blogs.php", blogPosts);
}

export function getTeam(): Promise<TeamMember[]> {
  return fetchList<TeamMember>("doctors.php", team);
}

export function getVideoTestimonials(): Promise<VideoTestimonial[]> {
  return fetchList<VideoTestimonial>("testimonials.php", videoTestimonials);
}

export async function getBlogPost(slug: string): Promise<BlogPost | null> {
  const posts = await getBlogPosts();
  return posts.find((post) => post.slug === slug) ?? null;
}
