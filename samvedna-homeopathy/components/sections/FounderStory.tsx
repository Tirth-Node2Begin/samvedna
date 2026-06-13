import Image from "next/image";
import { founderCredentials } from "@/constants/site";
import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import { blurDataUrl } from "@/lib/utils";
import { Award, Quote } from "lucide-react";

export default function FounderStory() {
  return (
    <section id="founder" className="relative overflow-hidden bg-[#F8FAF9] py-16 md:py-24 lg:py-28">
      {/* Decorative background blob */}
      <div className="absolute right-0 top-0 h-[500px] w-[500px] -translate-y-1/2 translate-x-1/3 rounded-full bg-primary/5 blur-[80px]" />

      <div className="relative mx-auto grid max-w-content gap-12 px-5 md:px-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-center lg:gap-16">
        
        {/* Left: Premium Image Column */}
        <AnimatedReveal
          className="relative mx-auto w-full max-w-[320px] lg:max-w-[360px] lg:justify-self-center"
          variant="slideRight"
        >
          {/* Offset accent block */}
          <div className="absolute -bottom-5 -left-5 h-full w-full rounded-[2rem] bg-primary/10 transition-transform duration-500 hover:-translate-x-2 hover:translate-y-2 lg:-bottom-6 lg:-left-6" />
          
          <div className="relative aspect-[4/5] w-full overflow-hidden rounded-[2rem] shadow-xl ring-1 ring-black/5">
            <Image
              src="/images/dr-krunal-kosada.jpg"
              alt="Dr. Krunal Kosada"
              fill
              sizes="(min-width: 1024px) 45vw, 100vw"
              className="object-cover object-[50%_18%] transition-transform duration-700 hover:scale-105"
              placeholder="blur"
              blurDataURL={blurDataUrl}
              loading="lazy"
            />
          </div>

          {/* Floating Glass Badge */}
          <div className="absolute -right-4 bottom-8 flex items-center gap-3 rounded-2xl border border-white/40 bg-white/80 p-3 font-semibold text-text shadow-xl backdrop-blur-md lg:-right-8">
            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
              <Award className="h-5 w-5" />
            </div>
            <div>
              <p className="text-sm font-bold text-primary">20+ Years</p>
              <p className="text-xs text-muted">Clinical Experience</p>
            </div>
          </div>
        </AnimatedReveal>

        {/* Right: Premium Text Column */}
        <AnimatedReveal className="relative z-10 lg:pl-4" variant="slideLeft">
          <div className="flex items-center gap-3">
            <div className="h-px w-10 bg-primary/40" />
            <p className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Doctor-led care</p>
          </div>
          
          <AnimatedText
            as="h2"
            className="mt-4 font-display text-3xl font-bold leading-tight text-text md:text-4xl lg:text-5xl"
            text="Dr. Krunal Kosada"
          />
          
          <p className="mt-3 text-base font-semibold text-primary/80">
            BHMS, FCAH | Pediatric Neurodevelopmental Homeopathy
          </p>
          
          <div className="mt-6 space-y-5 text-base leading-relaxed text-muted">
            <p>
              Dr. Kosada built Samvedna for families navigating autism, ADHD,
              speech delay, learning difficulty, developmental delay, genetic
              concerns, and pediatric neurological disorders.
            </p>
            <p>
              The clinical focus is simple and demanding: understand the whole
              child, create a personalized plan, and stay close through
              continuous follow-ups as parents track progress over time.
            </p>
          </div>

          {/* Stylized Blockquote */}
          <div className="relative mt-8 rounded-3xl bg-white p-6 shadow-md ring-1 ring-black/5 md:p-8">
            <Quote className="absolute -top-5 left-6 h-10 w-10 rotate-180 text-primary/20" />
            <blockquote className="relative z-10 font-display text-lg font-medium leading-relaxed text-primary md:text-xl">
              &ldquo;Every child deserves to be understood as an individual before a treatment plan is chosen.&rdquo;
            </blockquote>
          </div>

          {/* Premium Tags */}
          <div className="mt-10 flex flex-wrap gap-2">
            {founderCredentials.map((credential) => (
              <span
                key={credential}
                className="rounded-full bg-primary/5 border border-primary/10 px-3 py-1.5 text-xs font-semibold text-primary transition-colors hover:bg-primary/10 hover:border-primary/20"
              >
                {credential}
              </span>
            ))}
          </div>
        </AnimatedReveal>
      </div>
    </section>
  );
}
