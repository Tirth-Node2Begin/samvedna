import Button from "@/components/ui/Button";

export default function NotFound() {
  return (
    <main className="flex min-h-screen items-center bg-bg-soft px-5 py-20">
      <div className="mx-auto max-w-2xl text-center">
        <p className="text-sm font-semibold text-primary">Page not found</p>
        <h1 className="mt-4 font-display text-5xl font-semibold leading-tight text-text">
          This page is not available.
        </h1>
        <p className="mt-6 text-lg leading-8 text-muted">
          The Samvedna Homeopathy homepage has the consultation details,
          conditions, approach, and contact information you may need.
        </p>
        <div className="mt-8">
          <Button href="/">Return Home</Button>
        </div>
      </div>
    </main>
  );
}
