/**
 * Thin fetch wrapper around the Core PHP API.
 *
 * Everything is same-origin (next.config.ts proxies /api to the PHP server), so
 * the session cookie rides along automatically — there is no token to manage.
 */

export class ApiError extends Error {
  readonly status: number;
  readonly code: string;
  /** Per-field messages the server rejected, keyed by form field name. */
  readonly fields: Record<string, string>;

  constructor(status: number, code: string, message: string, fields: Record<string, string> = {}) {
    super(message);
    this.name = "ApiError";
    this.status = status;
    this.code = code;
    this.fields = fields;
  }

  /** True when the failure is the baseline activation gate. */
  get isBaselineBlock() {
    return this.code === "baseline_incomplete";
  }
}

type Method = "GET" | "POST" | "PUT" | "DELETE";

interface RequestOptions {
  signal?: AbortSignal;
}

interface CachedGetOptions extends RequestOptions {
  force?: boolean;
  staleTime?: number;
}

interface CacheEntry<T> {
  data?: T;
  updatedAt: number;
  promise?: Promise<T>;
}

const DEFAULT_STALE_TIME = 45_000;
const getCache = new Map<string, CacheEntry<unknown>>();

async function request<T>(method: Method, path: string, body?: unknown, options: RequestOptions = {}): Promise<T> {
  let response: Response;

  try {
    response = await fetch(`/api${path}`, {
      method,
      credentials: "same-origin",
      headers: body === undefined ? undefined : { "Content-Type": "application/json" },
      body: body === undefined ? undefined : JSON.stringify(body),
      cache: "no-store",
      signal: options.signal,
    });
  } catch (caught) {
    // A caller cancelling its own request is not a backend outage. Re-throw it
    // so callers can tell the two apart (React StrictMode aborts on unmount).
    if (caught instanceof DOMException && caught.name === "AbortError") {
      throw new ApiError(0, "aborted", "Request cancelled.");
    }
    throw new ApiError(
      0,
      "network",
      "Cannot reach the CMS backend. Start it with: php -S 127.0.0.1:8001 -t samvedna-cms/backend samvedna-cms/backend/router.php"
    );
  }

  const text = await response.text();
  let payload: unknown = null;
  if (text) {
    try {
      payload = JSON.parse(text);
    } catch {
      // A non-JSON body means PHP emitted a warning or fatal before our handler.
      throw new ApiError(response.status, "bad_response", text.slice(0, 300));
    }
  }

  if (!response.ok) {
    const data = (payload ?? {}) as { error?: string; message?: string; fields?: Record<string, string> };
    throw new ApiError(
      response.status,
      data.error ?? "error",
      data.message ?? `Request failed (${response.status}).`,
      data.fields ?? {}
    );
  }

  return payload as T;
}

function clearGetCache() {
  getCache.clear();
}

function cachedGet<T>(path: string, options: CachedGetOptions = {}): Promise<T> {
  const staleTime = options.staleTime ?? DEFAULT_STALE_TIME;
  const now = Date.now();
  const existing = getCache.get(path) as CacheEntry<T> | undefined;

  if (!options.force && existing?.data !== undefined && now - existing.updatedAt < staleTime) {
    return Promise.resolve(existing.data);
  }

  if (!options.force && existing?.promise) {
    return existing.promise;
  }

  // Deliberately NOT bound to options.signal: this promise is shared with every
  // other caller for the same path, so one subscriber aborting (a StrictMode
  // remount, a fast navigation) must not reject it for everyone else. Callers
  // still ignore the result via their own signal.
  const promise = request<T>("GET", path, undefined)
    .then((data) => {
      getCache.set(path, { data, updatedAt: Date.now() });
      return data;
    })
    .catch((error) => {
      if (existing?.data !== undefined) {
        getCache.set(path, { data: existing.data, updatedAt: existing.updatedAt });
      } else {
        getCache.delete(path);
      }
      throw error;
    });

  getCache.set(path, { data: existing?.data, updatedAt: existing?.updatedAt ?? 0, promise });
  return promise;
}

export function getCachedApiData<T>(path: string): T | null {
  const entry = getCache.get(path) as CacheEntry<T> | undefined;
  return entry?.data ?? null;
}

export function setCachedApiData<T>(path: string, data: T | null) {
  if (data === null) {
    getCache.delete(path);
    return;
  }

  getCache.set(path, { data, updatedAt: Date.now() });
}

export const api = {
  get: <T,>(path: string, options?: RequestOptions) => request<T>("GET", path, undefined, options),
  cachedGet,
  clearGetCache,
  post: async <T,>(path: string, body?: unknown) => {
    const result = await request<T>("POST", path, body ?? {});
    clearGetCache();
    return result;
  },
  put: async <T,>(path: string, body?: unknown) => {
    const result = await request<T>("PUT", path, body ?? {});
    clearGetCache();
    return result;
  },
  del: async <T,>(path: string) => {
    const result = await request<T>("DELETE", path);
    clearGetCache();
    return result;
  },
};

/** Build a query string, dropping empty values. */
export function qs(params: Record<string, string | number | undefined | null>): string {
  const search = new URLSearchParams();
  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== null && value !== "") {
      search.set(key, String(value));
    }
  }
  const out = search.toString();
  return out ? `?${out}` : "";
}
