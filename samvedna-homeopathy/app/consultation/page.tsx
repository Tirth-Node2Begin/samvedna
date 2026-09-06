import type { Metadata } from "next";
import { Suspense } from "react";
import AssessmentWizard from "@/components/consultation/AssessmentWizard";

export const metadata: Metadata = {
  title: "Care Plan Assessment | Samvedna Homeopathy",
  description:
    "Complete your child's assessment to begin structured homeopathic care with Samvedna.",
  robots: { index: false, follow: true },
};

export default function ConsultationPage() {
  return (
    <main className="min-h-screen bg-bg px-5 pb-24 pt-28 md:px-8 md:pt-32">
      <Suspense
        fallback={
          <div className="mx-auto max-w-4xl py-24 text-center text-muted">Loading assessment…</div>
        }
      >
        <AssessmentWizard />
      </Suspense>
    </main>
  );
}
