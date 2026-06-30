import dynamic from "next/dynamic";
import Hero from "@/components/sections/Hero";
import DoctorIntro from "@/components/sections/DoctorIntro";
import DoctorScrollAvatar from "@/components/sections/DoctorScrollAvatar";
import TrustBar from "@/components/sections/TrustBar";

const ConditionsTreated = dynamic(() => import("@/components/sections/ConditionsTreated"));
const DoctorAchievements = dynamic(() => import("@/components/sections/DoctorAchievements"));
const WhyFamiliesTrust = dynamic(() => import("@/components/sections/WhyFamiliesTrust"));
const TreatmentJourney = dynamic(() => import("@/components/sections/TreatmentJourney"));
const MedicalTeam = dynamic(() => import("@/components/sections/MedicalTeam"));
const InternationalReach = dynamic(() => import("@/components/sections/InternationalReach"));
const TestimonialsLazy = dynamic(() => import("@/components/sections/TestimonialsLazy"));
const Blogs = dynamic(() => import("@/components/sections/Blogs"));
const Pricing = dynamic(() => import("@/components/sections/Pricing"));
const FinalCTA = dynamic(() => import("@/components/sections/FinalCTA"));
const FAQ = dynamic(() => import("@/components/sections/FAQ"));
const Footer = dynamic(() => import("@/components/sections/Footer"));
const FormPopup = dynamic(() => import("@/components/ui/FormPopup"));

export default function Home() {
  return (
    <>
      <Hero />
      <DoctorIntro />
      <DoctorScrollAvatar />
      <TrustBar />
      <ConditionsTreated />
      <DoctorAchievements />
      <WhyFamiliesTrust />
      <TreatmentJourney />
      <MedicalTeam />
      <InternationalReach />
      <TestimonialsLazy />
      <Blogs />
      <Pricing />
      <FinalCTA />
      <FAQ />
      <Footer />
      <FormPopup />
    </>
  );
}
