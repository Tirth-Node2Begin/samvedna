import dynamic from "next/dynamic";
import Hero from "@/components/sections/Hero";
import DoctorIntro from "@/components/sections/DoctorIntro";
import DoctorScrollAvatar from "@/components/sections/DoctorScrollAvatar";
import TrustBar from "@/components/sections/TrustBar";
import { getBlogPosts, getConditions, getTeam, getVideoTestimonials } from "@/lib/content";

const DoctorAchievements = dynamic(() => import("@/components/sections/DoctorAchievements"));
const WhyFamiliesTrust = dynamic(() => import("@/components/sections/WhyFamiliesTrust"));
const TreatmentJourney = dynamic(() => import("@/components/sections/TreatmentJourney"));
const InternationalReach = dynamic(() => import("@/components/sections/InternationalReach"));
// Admin-managed sections render the build-time snapshot, then refresh in the
// browser so newly published content appears without a rebuild.
const ConditionsTreated = dynamic(() =>
  import("@/components/sections/LiveSections").then((m) => m.LiveConditionsTreated)
);
const MedicalTeam = dynamic(() =>
  import("@/components/sections/LiveSections").then((m) => m.LiveMedicalTeam)
);
const VideoTestimonials = dynamic(() =>
  import("@/components/sections/LiveSections").then((m) => m.LiveVideoTestimonials)
);
const Blogs = dynamic(() =>
  import("@/components/sections/LiveSections").then((m) => m.LiveBlogs)
);
const Pricing = dynamic(() => import("@/components/sections/Pricing"));
const FinalCTA = dynamic(() => import("@/components/sections/FinalCTA"));
const FAQ = dynamic(() => import("@/components/sections/FAQ"));
const Footer = dynamic(() => import("@/components/sections/Footer"));
const FormPopup = dynamic(() => import("@/components/ui/FormPopup"));

export default async function Home() {
  // Live content from the Core PHP admin (falls back to static seed data when
  // the PHP server is unavailable).
  const [posts, members, videos, conditions] = await Promise.all([
    getBlogPosts(),
    getTeam(),
    getVideoTestimonials(),
    getConditions(),
  ]);

  return (
    <>
      <Hero />
      <DoctorIntro />
      <DoctorScrollAvatar />
      <TrustBar />
      <ConditionsTreated initial={conditions} />
      <DoctorAchievements />
      <VideoTestimonials initial={videos} />
      <WhyFamiliesTrust />
      <TreatmentJourney />
      <MedicalTeam initial={members} />
      <InternationalReach />
      <Blogs initial={posts} />
      <Pricing />
      <FinalCTA />
      <FAQ />
      <Footer />
      <FormPopup />
    </>
  );
}
