import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ArrowLeft, Clock } from "lucide-react";
import Footer from "@/components/sections/Footer";
import { getBlogPost } from "@/lib/content";
import { blurDataUrl } from "@/lib/utils";

type BlogDetailPageProps = {
  params: Promise<{ slug: string }>;
};

export async function generateMetadata({
  params,
}: BlogDetailPageProps): Promise<Metadata> {
  const { slug } = await params;
  const post = await getBlogPost(slug);

  if (!post) {
    return { title: "Article not found | Samvedna Homeopathy" };
  }

  return {
    title: `${post.title} | Samvedna Homeopathy`,
    description: post.excerpt,
    openGraph: {
      title: post.title,
      description: post.excerpt,
      images: post.image ? [{ url: post.image }] : undefined,
      type: "article",
    },
  };
}

export default async function BlogDetailPage({ params }: BlogDetailPageProps) {
  const { slug } = await params;
  const post = await getBlogPost(slug);

  if (!post) {
    notFound();
  }

  return (
    <>
      <article className="bg-white pb-16 pt-28 md:pt-32">
        <div className="mx-auto max-w-3xl px-5 md:px-8">
          <Link
            href="/#blogs"
            className="inline-flex items-center gap-1.5 text-sm font-semibold text-primary transition hover:gap-2.5"
          >
            <ArrowLeft className="h-4 w-4" aria-hidden="true" />
            Back to all articles
          </Link>

          <div className="mt-6 flex flex-wrap items-center gap-3 text-xs font-medium text-muted">
            {post.category ? (
              <span className="rounded-full bg-primary/10 px-3 py-1 font-semibold text-primary">
                {post.category}
              </span>
            ) : null}
            {post.date ? <span>{post.date}</span> : null}
            {post.readTime ? (
              <span className="inline-flex items-center gap-1">
                <Clock className="h-3.5 w-3.5" aria-hidden="true" />
                {post.readTime}
              </span>
            ) : null}
          </div>

          <h1 className="mt-4 font-display text-3xl font-semibold leading-tight text-text md:text-4xl lg:text-[2.75rem]">
            {post.title}
          </h1>

          {post.excerpt ? (
            <p className="mt-4 text-lg leading-8 text-muted">{post.excerpt}</p>
          ) : null}
        </div>

        {post.image ? (
          <div className="mx-auto mt-10 max-w-4xl px-5 md:px-8">
            <div className="relative aspect-[16/9] overflow-hidden rounded-card bg-surface">
              <Image
                src={post.image}
                alt={post.alt || post.title}
                fill
                sizes="(min-width: 1024px) 896px, 100vw"
                className="object-cover"
                placeholder="blur"
                blurDataURL={blurDataUrl}
                priority
              />
            </div>
          </div>
        ) : null}

        <div className="mx-auto mt-10 max-w-3xl px-5 md:px-8">
          {post.content ? (
            <div
              className="blog-content"
              dangerouslySetInnerHTML={{ __html: post.content }}
            />
          ) : (
            <p className="text-base leading-8 text-text">{post.excerpt}</p>
          )}
        </div>
      </article>

      <Footer />
    </>
  );
}
