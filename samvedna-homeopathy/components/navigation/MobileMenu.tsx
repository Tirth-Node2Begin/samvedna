"use client";

import { AnimatePresence, motion } from "framer-motion";
import { X } from "lucide-react";
import Image from "next/image";
import { navItems } from "@/constants/site";
import Button from "@/components/ui/Button";
import { fadeUp, staggerContainer } from "@/lib/animations";

type MobileMenuProps = {
  isOpen: boolean;
  onClose: () => void;
};

export default function MobileMenu({ isOpen, onClose }: MobileMenuProps) {
  return (
    <AnimatePresence>
      {isOpen ? (
        <motion.div
          className="fixed inset-0 z-50 bg-white px-5 py-5 lg:hidden"
          initial={{ opacity: 0, x: 32 }}
          animate={{ opacity: 1, x: 0 }}
          exit={{ opacity: 0, x: 32 }}
          transition={{ duration: 0.28, ease: [0.22, 1, 0.36, 1] }}
        >
          <div className="mx-auto flex max-w-content items-center justify-between">
            <a href="#home" onClick={onClose} className="flex items-center gap-3">
              <Image
                src="/images/samvedna-logo.webp"
                alt="Samvedna Homeopathy"
                width={126}
                height={40}
              />
            </a>
            <button
              type="button"
              aria-label="Close menu"
              onClick={onClose}
              suppressHydrationWarning
              className="inline-flex h-11 w-11 items-center justify-center rounded-control border border-border text-text"
            >
              <X className="h-5 w-5" aria-hidden="true" />
            </button>
          </div>

          <motion.nav
            variants={staggerContainer}
            initial="initial"
            animate="animate"
            className="mx-auto mt-16 flex max-w-content flex-col gap-2"
            aria-label="Mobile navigation"
          >
            {navItems.map((item) => (
              <motion.div key={item.label} variants={fadeUp} className="border-b border-border py-4">
                <a
                  href={item.href}
                  onClick={onClose}
                  className="block font-display text-2xl font-semibold text-text uppercase tracking-wide"
                >
                  {item.label}
                </a>
                {item.subItems && (
                  <div className="mt-3 flex flex-col gap-2 pl-4 border-l-2 border-primary/20">
                    {item.subItems.map((subItem) => (
                      <a
                        key={subItem.label}
                        href={subItem.href}
                        onClick={onClose}
                        className="text-lg font-medium text-text/70 hover:text-primary py-1"
                      >
                        {subItem.label}
                      </a>
                    ))}
                  </div>
                )}
              </motion.div>
            ))}
            <motion.div variants={fadeUp} className="mt-8">
              <Button href="https://autismhomeohelp.com/online-consulting/" size="lg" onClick={onClose}>
                Book Consultation
              </Button>
            </motion.div>
          </motion.nav>
        </motion.div>
      ) : null}
    </AnimatePresence>
  );
}
