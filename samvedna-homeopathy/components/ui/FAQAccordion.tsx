"use client";

import * as Accordion from "@radix-ui/react-accordion";
import { ChevronDown } from "lucide-react";
import type { FaqItem } from "@/types";

type FAQAccordionProps = {
  items: FaqItem[];
};

export default function FAQAccordion({ items }: FAQAccordionProps) {
  return (
    <Accordion.Root type="single" collapsible className="flex w-full flex-col gap-4">
      {items.map((item, index) => (
        <Accordion.Item 
          key={item.question} 
          value={`faq-${index}`}
          className="group rounded-2xl border border-border bg-white px-6 transition-all duration-300 hover:border-primary/20 hover:shadow-md data-[state=open]:border-primary/30 data-[state=open]:shadow-md sm:px-8"
        >
          <Accordion.Header>
            <Accordion.Trigger
              className="flex w-full items-center justify-between gap-6 py-6 text-left font-display text-lg font-semibold leading-snug text-text transition hover:text-primary md:text-xl"
              suppressHydrationWarning
            >
              <span>{item.question}</span>
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/5 transition-colors duration-300 group-hover:bg-primary/10 group-data-[state=open]:bg-primary">
                <ChevronDown
                  className="h-5 w-5 text-primary transition-transform duration-300 group-data-[state=open]:rotate-180 group-data-[state=open]:text-white"
                  aria-hidden="true"
                />
              </div>
            </Accordion.Trigger>
          </Accordion.Header>
          <Accordion.Content className="overflow-hidden text-muted data-[state=closed]:animate-none data-[state=open]:animate-none">
            <p className="pb-8 text-base leading-relaxed md:text-lg">{item.answer}</p>
          </Accordion.Content>
        </Accordion.Item>
      ))}
    </Accordion.Root>
  );
}
