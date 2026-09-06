import type { Metadata } from "next";
import BlogArticle from "@/components/sections/BlogArticle";
import { getBlogPost, getBlogPosts } from "@/lib/content";

type BlogDetailPageProps = {
  params: Promise<{ slug: string }>;
};

/**
 * Static export requires every dynamic segment to be known at build time, so we
 * prebuild one page per post that exists when the site is built. Posts published
 * later are handled by app/blog-view via the .htaccess fallback — see
 * deploy/htaccess-site.conf.
 */
export async function generateStaticParams() {
  const posts = await getBlogPosts();

  // Next refuses to export a dynamic route with zero params ("missing
  // generateStaticParams()"), so a site whose admin has no published posts yet
  // could not build at all. Emit one throwaway path to satisfy the exporter: it
  // is linked from nowhere, renders the "Article not found" state, and is marked
  // noindex below. Real slugs published later are served by app/blog-view
  // through the .htaccess fallback.
  if (posts.length === 0) {
    return [{ slug: "no-posts-yet" }];
  }

  return posts.map((post) => ({ slug: post.slug }));
}

export async function generateMetadata({
  params,
}: BlogDetailPageProps): Promise<Metadata> {
  const { slug } = await params;
  const post = await getBlogPost(slug);

  if (!post) {
    return {
      title: "Article not found | Samvedna Homeopathy",
      robots: { index: false, follow: false },
    };
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
  const [post, posts] = await Promise.all([getBlogPost(slug), getBlogPosts()]);

  // No notFound() here: the post is baked in at build time, and BlogArticle
  // re-fetches on mount so edits made in the admin panel appear without a rebuild.
  // `initialPosts` seeds the "More articles" section into the prerendered HTML.
  return <BlogArticle slug={slug} initial={post} initialPosts={posts} />;
}
