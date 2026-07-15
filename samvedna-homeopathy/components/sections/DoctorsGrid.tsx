"use client";

import { useState } from "react";
import team from "@/constants/team";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import DoctorCard from "@/components/ui/DoctorCard";
import DoctorProfileModal from "@/components/ui/DoctorProfileModal";
import type { TeamMember } from "@/types";

export default function DoctorsGrid({
  members = team,
}: {
  members?: TeamMember[];
}) {
  const [activeDoctor, setActiveDoctor] = useState<TeamMember | null>(null);

  return (
    <>
      <div className="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
        {members.map((member, index) => (
          <AnimatedReveal key={member.name} className="h-full w-full" delay={index * 0.08}>
            <DoctorCard member={member} onSelect={() => setActiveDoctor(member)} />
          </AnimatedReveal>
        ))}
      </div>

      <DoctorProfileModal member={activeDoctor} onClose={() => setActiveDoctor(null)} />
    </>
  );
}
