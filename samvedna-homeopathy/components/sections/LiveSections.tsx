"use client";

import Blogs from "@/components/sections/Blogs";
import ConditionsTreated from "@/components/sections/ConditionsTreated";
import MedicalTeam from "@/components/sections/MedicalTeam";
import VideoTestimonials from "@/components/sections/VideoTestimonials";
import {
  useLiveBlogPosts,
  useLiveConditions,
  useLiveTeam,
  useLiveVideoTestimonials,
} from "@/lib/content.client";
import type { BlogPost, Condition, TeamMember, VideoTestimonial } from "@/types";

/**
 * Client wrappers for the admin-managed sections.
 *
 * Each renders the build-time snapshot on first paint, then swaps in whatever the
 * PHP API currently returns. Without this the static export would show whatever
 * content existed when the site was last built, and publishing from the admin
 * panel would have no visible effect.
 */

export function LiveBlogs({ initial }: { initial: BlogPost[] }) {
  return <Blogs posts={useLiveBlogPosts(initial)} />;
}

export function LiveConditionsTreated({ initial }: { initial: Condition[] }) {
  return <ConditionsTreated conditions={useLiveConditions(initial)} />;
}

export function LiveMedicalTeam({ initial }: { initial: TeamMember[] }) {
  return <MedicalTeam members={useLiveTeam(initial)} />;
}

export function LiveVideoTestimonials({
  initial,
}: {
  initial: VideoTestimonial[];
}) {
  return <VideoTestimonials videos={useLiveVideoTestimonials(initial)} />;
}
