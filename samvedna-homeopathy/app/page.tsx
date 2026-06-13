import ConditionsTreated from "@/components/sections/ConditionsTreated";
import DoctorAchievements from "@/components/sections/DoctorAchievements";
import FAQ from "@/components/sections/FAQ";
import FinalCTA from "@/components/sections/FinalCTA";
import Footer from "@/components/sections/Footer";
import FounderStory from "@/components/sections/FounderStory";
import Hero from "@/components/sections/Hero";
import DoctorIntro from "@/components/sections/DoctorIntro";
import InternationalReach from "@/components/sections/InternationalReach";
import MedicalTeam from "@/components/sections/MedicalTeam";
import TestimonialsLazy from "@/components/sections/TestimonialsLazy";
import TreatmentJourney from "@/components/sections/TreatmentJourney";
import TrustBar from "@/components/sections/TrustBar";
import WhyFamiliesTrust from "@/components/sections/WhyFamiliesTrust";
import Pricing from "@/components/sections/Pricing";

import { LayoutGroup } from "framer-motion";

export default function Home() {
  return (
    <LayoutGroup>
      <Hero />
      <DoctorIntro />
      <TrustBar />
      <ConditionsTreated />
      {/* <FounderStory /> */}
      <DoctorAchievements />
      <WhyFamiliesTrust />
      <TreatmentJourney />
      <MedicalTeam />
      <InternationalReach />
      <TestimonialsLazy />
      <Pricing />
      <FinalCTA />
      <FAQ />
      <Footer />
    </LayoutGroup>
  );
}
