"use client";

import { Menu } from "lucide-react";
import Image from "next/image";
import { useEffect, useState } from "react";
import { navItems } from "@/constants/site";
import Button from "@/components/ui/Button";
import MobileMenu from "@/components/navigation/MobileMenu";
import { cn } from "@/lib/utils";

export default function Navbar() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const [isHidden, setIsHidden] = useState(false);

  useEffect(() => {
    let lastScrollY = window.scrollY;

    const update = (): void => {
      const currentScrollY = window.scrollY;
      const delta = currentScrollY - lastScrollY;

      setIsScrolled(currentScrollY > 80);
      setIsHidden(currentScrollY > 180 && delta > 16);

      if (delta < 0) {
        setIsHidden(false);
      }

      lastScrollY = currentScrollY;
    };

    update();
    window.addEventListener("scroll", update, { passive: true });

    return () => window.removeEventListener("scroll", update);
  }, []);

  return (
    <>
      <header
        className={cn(
          "fixed left-0 right-0 top-0 z-40 transition duration-300",
          isHidden ? "-translate-y-full" : "translate-y-0",
          isScrolled
            ? "border-b border-border bg-white/95 shadow-[0_10px_40px_rgba(17,24,39,0.06)] backdrop-blur-md"
            : "bg-transparent"
        )}
      >
        <div className="mx-auto flex h-20 max-w-content items-center justify-between px-5 md:px-8">
          <a href="#home" className="flex items-center gap-3" aria-label="Samvedna Homeopathy home">
            <Image
              src="/images/samvedna-logo.webp"
              alt="Samvedna Homeopathy"
              width={126}
              height={40}
              priority
            />
          </a>

          <nav className="hidden items-center gap-7 lg:flex" aria-label="Primary navigation">
            {navItems.map((item) => (
              <div key={item.label} className="group relative">
                <a
                  href={item.href}
                  className="flex items-center gap-1 text-[13px] font-bold text-text/80 transition hover:text-primary uppercase tracking-wide py-4"
                >
                  {item.label}
                  {item.subItems && (
                    <svg className="w-3.5 h-3.5 text-text/50 group-hover:text-primary transition-transform group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  )}
                </a>
                
                {item.subItems && (
                  <div className="absolute left-0 top-[100%] hidden w-56 flex-col overflow-hidden rounded-xl border border-border bg-white p-2 shadow-premium group-hover:flex">
                    {item.subItems.map((subItem) => (
                      <a
                        key={subItem.label}
                        href={subItem.href}
                        className="rounded-lg px-4 py-2.5 text-sm font-medium text-text/80 transition hover:bg-bg-soft hover:text-primary"
                      >
                        {subItem.label}
                      </a>
                    ))}
                  </div>
                )}
              </div>
            ))}
          </nav>

          <div className="hidden lg:block">
            <Button href="https://autismhomeohelp.com/online-consulting/" size="sm">
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

      <MobileMenu isOpen={isMenuOpen} onClose={() => setIsMenuOpen(false)} />
    </>
  );
}
