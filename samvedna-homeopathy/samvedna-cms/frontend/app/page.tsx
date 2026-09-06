"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useSession } from "@/lib/session";
import { Spinner } from "@/components/ui/primitives";

/** Entry point: straight to the dashboard, or to sign-in. */
export default function RootPage() {
  const { user, loading } = useSession();
  const router = useRouter();

  useEffect(() => {
    if (loading) return;
    router.replace(user ? "/dashboard" : "/login");
  }, [user, loading, router]);

  return (
    <div className="grid min-h-screen place-items-center">
      <Spinner label="Starting Samvedna CMS" />
    </div>
  );
}
