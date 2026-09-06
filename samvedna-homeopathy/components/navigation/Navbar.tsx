"use client";

import dynamic from "next/dynamic";
import { Menu } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useRef, useState } from "react";
import { navItems } from "@/constants/site";
import Button from "@/components/ui/Button";
import { cn } from "@/lib/utils";

const MobileMenu = dynamic(() => import("@/components/navigation/MobileMenu"));

export default function Navbar() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const [isHidden, setIsHidden] = useState(false);

  // Only the homepage has a dark hero behind the transparent navbar. Every other
  // route (blog articles, etc.) has a light background, so the navbar must render
  // its solid dark-on-white variant from the top or the links are invisible.
  const pathname = usePathname();
  const forceSolid = pathname !== "/";
  const solid = isScrolled || forceSolid;
  const scrollStateRef = useRef({
    frame: 0,
    isHidden: false,
    isScrolled: false,
    lastScrollY: 0,
  });

  useEffect(() => {
    const scrollState = scrollStateRef.current;
    scrollState.lastScrollY = window.scrollY;

    const update = (): void => {
      scrollState.frame = 0;

      const currentScrollY = window.scrollY;
      const delta = currentScrollY - scrollState.lastScrollY;
      const nextIsScrolled = currentScrollY > 80;
      let nextIsHidden = currentScrollY > 180 && delta > 16;

      if (delta < 0) {
        nextIsHidden = false;
      }

      if (scrollState.isScrolled !== nextIsScrolled) {
        scrollState.isScrolled = nextIsScrolled;
        setIsScrolled(nextIsScrolled);
      }

      if (scrollState.isHidden !== nextIsHidden) {
        scrollState.isHidden = nextIsHidden;
        setIsHidden(nextIsHidden);
      }

      scrollState.lastScrollY = currentScrollY;
    };

    const scheduleUpdate = (): void => {
      if (scrollState.frame) return;
      scrollState.frame = window.requestAnimationFrame(update);
    };

    update();
    window.addEventListener("scroll", scheduleUpdate, { passive: true });

    return () => {
      if (scrollState.frame) {
        window.cancelAnimationFrame(scrollState.frame);
      }
      window.removeEventListener("scroll", scheduleUpdate);
    };
  }, []);

  return (
    <>
      <header
        className={cn(
          "fixed left-0 right-0 top-0 z-40 transition duration-300",
          isHidden ? "-translate-y-full" : "translate-y-0",
          solid
            ? "border-b border-border bg-white/95 shadow-[0_10px_40px_rgba(17,24,39,0.06)] backdrop-blur-md"
            : "bg-transparent"
        )}
      >
        <div className="mx-auto flex h-16 max-w-content items-center justify-between px-4 sm:px-5 md:h-20 md:px-8">
          <Link href="/#home" className="flex items-center gap-3" aria-label="Samvedna Homeopathy home">
            <Image
              src="/images/samvedna-logo.webp"
              alt="Samvedna Homeopathy"
              width={126}
              height={40}
              priority
              className="h-8 w-auto md:h-10"
            />
          </Link>

          <nav className="hidden items-center gap-7 lg:flex" aria-label="Primary navigation">
            {navItems.map((item) => (
              <div key={item.label} className="group relative">
                <Link
                  href={item.href}
                  className={cn(
                    "flex items-center gap-1 py-4 text-[13px] font-bold uppercase tracking-wide transition",
                    solid
                      ? "text-text/80 hover:text-primary"
                      : "text-white/90 drop-shadow-sm hover:text-white"
                  )}
                >
                  {item.label}
                  {item.subItems && (
                    <svg
                      className={cn(
                        "h-3.5 w-3.5 transition-transform group-hover:rotate-180",
                        solid
                          ? "text-text/50 group-hover:text-primary"
                          : "text-white/70 group-hover:text-white"
                      )}
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  )}
                </Link>

                {item.subItems && (
                  <div className="absolute left-0 top-[100%] hidden w-56 flex-col overflow-hidden rounded-xl border border-border bg-white p-2 shadow-premium group-hover:flex">
                    {item.subItems.map((subItem) => (
                      <Link
                        key={subItem.label}
                        href={subItem.href}
                        className="rounded-lg px-4 py-2.5 text-sm font-medium text-text/80 transition hover:bg-bg-soft hover:text-primary"
                      >
                        {subItem.label}
                      </Link>
                    ))}
                  </div>
                )}
              </div>
            ))}
          </nav>

          <div className="hidden lg:block">
            <Button href="/consultation/" size="sm">
              Book Consultation
            </Button>
          </div>

          <button
            type="button"
            aria-label="Open menu"
            onClick={() => setIsMenuOpen(true)}
            suppressHydrationWarning
            className="inline-flex h-11 w-11 items-center justify-center rounded-control border border-border bg-white/80 text-text lg:hidden"
          >
            <Menu className="h-5 w-5" aria-hidden="true" />
          </button>
        </div>
      </header>

      {isMenuOpen ? (
        <MobileMenu isOpen={isMenuOpen} onClose={() => setIsMenuOpen(false)} />
      ) : null}
    </>
  );
}
