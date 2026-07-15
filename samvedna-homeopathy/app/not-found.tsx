import Image from "next/image";
import Button from "@/components/ui/Button";

export default function NotFound() {
  return (
    <main className="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-bg via-bg-soft to-blue-50/40 px-5 py-24">
      {/* Ambient background glows */}
      <div className="pointer-events-none absolute left-1/2 top-1/3 -translate-x-1/2 -translate-y-1/2 h-96 w-96 rounded-full bg-primary/10 blur-[100px]" />
      <div className="pointer-events-none absolute right-10 bottom-10 h-72 w-72 rounded-full bg-cyan-500/10 blur-[80px]" />

      <div className="relative z-10 mx-auto max-w-xl text-center">
        <div className="mb-8 flex justify-center">
          <a href="/" className="inline-block transition-transform hover:scale-105">
            <Image
              src="/images/samvedna-logo.webp"
              alt="Samvedna Homeopathy"
              width={160}
              height={50}
              priority
              className="h-auto w-40 drop-shadow-sm"
            />
          </a>
        </div>

        <div className="rounded-3xl border border-border/80 bg-white/90 p-8 shadow-[0_20px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl sm:p-12">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-red-600 border border-red-100">
            Error 404
          </span>
          <h1 className="mt-5 font-display text-4xl font-bold tracking-tight text-text sm:text-5xl">
            Page Not Found
          </h1>
          <p className="mt-4 text-base leading-relaxed text-muted sm:text-lg">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
          </p>

          <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
            <Button href="/" size="md" className="w-full sm:w-auto">
              Return Home
            </Button>
            <Button
              href="https://autismhomeohelp.com/online-consulting/"
              variant="secondary"
              size="md"
              className="w-full sm:w-auto"
            >
              Book Consultation
            </Button>
          </div>
        </div>
      </div>
    </main>
  );
}

