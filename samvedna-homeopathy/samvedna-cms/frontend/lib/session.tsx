"use client";

import { createContext, useCallback, useContext, useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { api, ApiError } from "@/lib/api";
import type { Meta, Role, User } from "@/types";

interface SessionValue {
  user: User | null;
  meta: Meta | null;
  loading: boolean;
  demo: boolean;
  signIn: (username: string, password: string) => Promise<User>;
  signOut: () => Promise<void>;
  refresh: () => Promise<void>;
  /** True when the signed-in user holds any of the given roles. */
  can: (...roles: Role[]) => boolean;
}

const SessionContext = createContext<SessionValue | null>(null);

export function SessionProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [meta, setMeta] = useState<Meta | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  const load = useCallback(async () => {
    try {
      const [session, metaPayload] = await Promise.all([
        api.get<{ user: User | null }>("/auth/me"),
        api.get<Meta>("/meta"),
      ]);
      setUser(session.user);
      setMeta(metaPayload);
    } catch {
      // A dead backend leaves the app signed out rather than stuck on a spinner;
      // the login screen surfaces the real connection error on the next attempt.
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  const signIn = useCallback(
    async (username: string, password: string) => {
      const { user: signedIn } = await api.post<{ user: User }>("/auth/login", { username, password });
      setUser(signedIn);
      if (!meta) {
        setMeta(await api.get<Meta>("/meta"));
      }
      return signedIn;
    },
    [meta]
  );

  const signOut = useCallback(async () => {
    try {
      await api.post("/auth/logout");
    } catch (error) {
      if (!(error instanceof ApiError)) throw error;
    }
    setUser(null);
    router.push("/login");
  }, [router]);

  const can = useCallback(
    (...roles: Role[]) => (user ? roles.includes(user.role) : false),
    [user]
  );

  const value = useMemo<SessionValue>(
    () => ({ user, meta, loading, demo: meta?.demo ?? false, signIn, signOut, refresh: load, can }),
    [user, meta, loading, signIn, signOut, load, can]
  );

  return <SessionContext.Provider value={value}>{children}</SessionContext.Provider>;
}

export function useSession(): SessionValue {
  const context = useContext(SessionContext);
  if (!context) {
    throw new Error("useSession must be used inside <SessionProvider>.");
  }
  return context;
}
