import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import BlogsCarousel from "@/components/ui/BlogsCarousel";

export default function Blogs() {
  return (
    <section id="blogs" className="scroll-mt-24 bg-bg-soft py-16 md:scroll-mt-28 md:py-20 lg:py-[120px]">
      <div className="mx-auto max-w-content px-5 md:px-8">
        <AnimatedReveal className="flex flex-col justify-between gap-6 md:flex-row md:items-end">
          <div className="max-w-3xl">
            <p className="text-sm font-semibold text-primary">From our blog</p>
            <AnimatedText
              as="h2"
              className="mt-4 font-display text-3xl font-semibold leading-tight text-text md:text-4xl"
              text="Guidance for parents, written from real clinical experience."
            />
          </div>
          <p className="max-w-md text-base leading-7 text-muted">
            Practical articles on autism, ADHD, speech delay and developmental
            care — to help families take confident next steps.
          </p>
        </AnimatedReveal>

        {/* Anchor for the "Parent stories" nav link. Parent video stories now
            live as small thumbnails on each blog card below. */}
        <div id="testimonials" aria-hidden="true" className="scroll-mt-24 md:scroll-mt-28" />

        <AnimatedReveal>
          <BlogsCarousel />
        </AnimatedReveal>
      </div>
    </section>
  );
}
