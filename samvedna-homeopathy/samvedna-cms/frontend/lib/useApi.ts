"use client";

import { useCallback, useEffect, useState } from "react";
import { api, ApiError, getCachedApiData, setCachedApiData } from "@/lib/api";

interface Result<T> {
  data: T | null;
  error: string | null;
  loading: boolean;
  refreshing: boolean;
  reload: () => Promise<void>;
  /** Patch the cached payload without a round trip, after a successful write. */
  setData: React.Dispatch<React.SetStateAction<T | null>>;
}

/**
 * Small stale-while-revalidate GET hook.
 *
 * Clinical data still refreshes from the server, but revisiting a route paints
 * the last known payload immediately while one deduped request updates it.
 */
export function useApi<T>(path: string | null): Result<T> {
  const [data, setLocalData] = useState<T | null>(() => (path ? getCachedApiData<T>(path) : null));
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(Boolean(path && !getCachedApiData<T>(path)));
  const [refreshing, setRefreshing] = useState(false);

  const setData: React.Dispatch<React.SetStateAction<T | null>> = useCallback(
    (next) => {
      setLocalData((current) => {
        const resolved = typeof next === "function" ? (next as (value: T | null) => T | null)(current) : next;
        if (path) setCachedApiData(path, resolved);
        return resolved;
      });
    },
    [path]
  );

  const reload = useCallback(async () => {
    if (!path) {
      setLoading(false);
      setRefreshing(false);
      return;
    }

    const cached = getCachedApiData<T>(path);
    const hasData = cached !== null;
    if (hasData) setLocalData(cached);

    setLoading(!hasData);
    setRefreshing(hasData);
    setError(null);

    try {
      setLocalData(await api.cachedGet<T>(path, { force: true }));
    } catch (caught) {
      if (!hasData) {
        setError(caught instanceof ApiError ? caught.message : "Could not load this data.");
        setLocalData(null);
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [path]);

  useEffect(() => {
    if (!path) {
      setLocalData(null);
      setLoading(false);
      setRefreshing(false);
      return;
    }

    const controller = new AbortController();
    const cached = getCachedApiData<T>(path);
    const hasData = cached !== null;

    setLocalData(cached);
    setError(null);
    setLoading(!hasData);
    setRefreshing(hasData);

    void api
      .cachedGet<T>(path, { signal: controller.signal })
      .then((payload) => {
        setLocalData(payload);
      })
      .catch((caught) => {
        // Ignore cancellations - our own, or one from another subscriber that
        // shared this request. Neither means the data failed to load.
        const cancelled = caught instanceof ApiError && caught.code === "aborted";
        if (!controller.signal.aborted && !cancelled && !hasData) {
          setError(caught instanceof ApiError ? caught.message : "Could not load this data.");
          setLocalData(null);
        }
      })
      .finally(() => {
        if (!controller.signal.aborted) {
          setLoading(false);
          setRefreshing(false);
        }
      });

    return () => controller.abort();
  }, [path]);

  return { data, error, loading, refreshing, reload, setData };
}
