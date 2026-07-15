import AnimatedReveal from "@/components/ui/AnimatedReveal";
import AnimatedText from "@/components/ui/AnimatedText";
import BlogsCarousel from "@/components/ui/BlogsCarousel";
import { blogPosts } from "@/constants/blogs";
import type { BlogPost } from "@/types";

export default function Blogs({ posts = blogPosts }: { posts?: BlogPost[] }) {
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

        <AnimatedReveal>
          <BlogsCarousel posts={posts} />
        </AnimatedReveal>
      </div>
    </section>
  );
}
