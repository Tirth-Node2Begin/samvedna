import type { BlogPost, Condition, TeamMember, VideoTestimonial } from "@/types";

/**
 * Build-time content layer.
 *
 * The site is a static export, so these run once during `next build` on the dev
 * machine and their result is baked into the HTML. That is what search engines
 * and first paint see. Anything the client edits in the admin panel afterwards
 * is picked up in the browser by lib/content.client.ts, which re-fetches the same
 * endpoints — see useLiveContent().
 *
 * The admin panel is the only source of this content. There is deliberately no
 * bundled fallback: a section with no rows must render as absent rather than as
 * demo data, otherwise the site advertises doctors and parent stories that do
 * not exist.
 *
 * PHP_ADMIN_URL must therefore point at a PHP server reachable FROM THE BUILD
 * MACHINE. If it is unreachable the export ships with those sections missing,
 * which is why scripts/build-live.ps1 aborts on an empty build.
 */

const PHP_API = (process.env.PHP_ADMIN_URL || "http://127.0.0.1:8000").replace(
  /\/+$/,
  ""
);

/**
 * Cache-buster, fixed for the lifetime of one build process.
 *
 * These fetches MUST NOT use `cache: "no-store"`. With output: "export" every
 * route is `dynamic = "error"`, so a no-store fetch is treated as dynamic
 * rendering and throws - which silently produced a homepage with no content
 * while the blog pages (built via generateStaticParams, which is exempt) worked.
 * So we use the default cache and defeat staleness with a per-build query
 * param instead, otherwise Next's persistent fetch cache in .next/cache would
 * serve yesterday's content after the client publishes a post.
 */
const BUILD_TOKEN = process.env.CONTENT_BUILD_TOKEN || String(Date.now());

/**
 * One in-flight request per endpoint for the whole build.
 *
 * generateStaticParams, generateMetadata and every blog page each call
 * getBlogPosts(), so an uncached implementation fires ~20 concurrent requests at
 * the same endpoint. `php -S` is single-threaded: its listen backlog overflows
 * and refuses connections, so some pages silently got no content. Memoising
 * collapses that to one request per endpoint per build.
 */
const inFlight = new Map<string, Promise<unknown[]>>();

function fetchList<T>(endpoint: string): Promise<T[]> {
  const hit = inFlight.get(endpoint);
  if (hit) {
    return hit as Promise<T[]>;
  }
  const request = fetchListOnce<T>(endpoint) as Promise<unknown[]>;
  inFlight.set(endpoint, request);
  return request as Promise<T[]>;
}

/**
 * Retries with backoff. `php -S` is single-threaded, so while it is serving one
 * of Next's build workers a second worker's connection is refused outright
 * rather than queued. A refusal here is nearly always that, not a dead server.
 */
const RETRY_DELAYS_MS = [300, 800, 2000];

async function fetchListOnce<T>(endpoint: string, attempt = 0): Promise<T[]> {
  try {
    const res = await fetch(`${PHP_API}/api/${endpoint}?_b=${BUILD_TOKEN}`, {
      cache: "force-cache",
    });
    if (!res.ok) {
      console.error(
        `[content] ${endpoint} -> HTTP ${res.status}. That section will be EMPTY in the export.`
      );
      return [];
    }
    const data = (await res.json()) as unknown;
    if (!Array.isArray(data)) {
      console.error(
        `[content] ${endpoint} did not return an array. That section will be EMPTY in the export.`
      );
      return [];
    }
    if (data.length === 0) {
      console.warn(`[content] ${endpoint} returned no rows - section hidden.`);
    }
    return data as T[];
  } catch (err) {
    if (attempt < RETRY_DELAYS_MS.length) {
      await new Promise((r) => setTimeout(r, RETRY_DELAYS_MS[attempt]));
      return fetchListOnce<T>(endpoint, attempt + 1);
    }
    const cause = (err as { cause?: { code?: string; message?: string } }).cause;
    const detail = cause?.code ?? cause?.message ?? (err as Error).message;
    console.error(
      `[content] ${endpoint} UNREACHABLE at ${PHP_API} (${detail}). That section will be EMPTY in the export - is the PHP server running?`
    );
    return [];
  }
}

export function getBlogPosts(): Promise<BlogPost[]> {
  return fetchList<BlogPost>("blogs.php");
}

export function getTeam(): Promise<TeamMember[]> {
  return fetchList<TeamMember>("doctors.php");
}

export function getConditions(): Promise<Condition[]> {
  return fetchList<Condition>("conditions.php");
}

export function getVideoTestimonials(): Promise<VideoTestimonial[]> {
  return fetchList<VideoTestimonial>("testimonials.php");
}

export async function getBlogPost(slug: string): Promise<BlogPost | null> {
  const posts = await getBlogPosts();
  return posts.find((post) => post.slug === slug) ?? null;
}
