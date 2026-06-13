"use client";

import dynamic from "next/dynamic";
import SectionSkeleton from "@/components/ui/SectionSkeleton";

const Testimonials = dynamic(
  () => import("@/components/sections/Testimonials"),
  {
    loading: () => <SectionSkeleton />
  }
);

export default function TestimonialsLazy() {
  return <Testimonials />;
}
