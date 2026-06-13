"use client";

import { useSyncExternalStore } from "react";

const REDUCED_MOTION_QUERY = "(prefers-reduced-motion: reduce)";

let mediaQuery: MediaQueryList | null = null;

function getMediaQuery(): MediaQueryList | null {
  if (typeof window === "undefined") return null;

  mediaQuery ??= window.matchMedia(REDUCED_MOTION_QUERY);
  return mediaQuery;
}

function subscribe(callback: () => void): () => void {
  const query = getMediaQuery();
  if (!query) return () => {};

  query.addEventListener("change", callback);

  return () => query.removeEventListener("change", callback);
}

function getSnapshot(): boolean {
  return getMediaQuery()?.matches ?? false;
}

export default function useReducedMotion(): boolean {
  return useSyncExternalStore(subscribe, getSnapshot, () => false);
}
