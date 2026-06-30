"use client";

import dynamic from "next/dynamic";
import SectionSkeleton from "@/components/ui/SectionSkeleton";

const VideoTestimonials = dynamic(
  () => import("@/components/sections/VideoTestimonials"),
  {
    loading: () => <SectionSkeleton />
  }
);

export default function VideoTestimonialsLazy() {
  return <VideoTestimonials />;
}
