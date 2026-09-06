"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { motion } from "framer-motion";
import {
  ArrowRight,
  ShieldCheck,
  ClipboardList,
  Package,
  CalendarClock,
  ClipboardCheck,
  TriangleAlert,
  TrendingUp,
} from "lucide-react";
import { useSession } from "@/lib/session";
import { ApiError } from "@/lib/api";
import { Button } from "@/components/ui/primitives";
import { Field, Input } from "@/components/ui/form";
import { EASE_ENTRANCE, stagger, staggerItem } from "@/lib/motion";

/**
 * `/login` runs its own visual language: a dark #0c0e1a artwork panel beside a
 * white form. It is chrome-free — no rail, no bar — so the two halves own the
 * whole viewport.
 */

const FLOW_STEPS = [
  { icon: ClipboardList, label: "Patient intake with a mandatory baseline" },
  { icon: Package, label: "Care plan selection and role assignment" },
  { icon: CalendarClock, label: "Auto-generated follow-up schedule" },
  { icon: ClipboardCheck, label: "Bi-monthly review on a forced template" },
  { icon: TriangleAlert, label: "Escalation chain with a 7-day SLA" },
  { icon: TrendingUp, label: "Auto-generated parent progress dashboard" },
];

export default function LoginPage() {
  const { user, loading, meta, signIn } = useSession();
  const router = useRouter();

  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [fields, setFields] = useState<Record<string, string>>({});
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!loading && user) {
      router.replace("/dashboard");
    }
  }, [loading, user, router]);

  async function onSubmit(event: React.FormEvent) {
    event.preventDefault();
    setSubmitting(true);
    setError("");
    setFields({});

    try {
      await signIn(username.trim(), password);
      router.replace("/dashboard");
    } catch (caught) {
      if (caught instanceof ApiError) {
        setError(caught.message);
        setFields(caught.fields);
      } else {
        setError("Something went wrong. Please try again.");
      }
      setSubmitting(false);
    }
  }

  /** Demo convenience: fill the form from one of the seeded accounts. */
  function applyDemoAccount(name: string, pass: string) {
    setUsername(name);
    setPassword(pass);
    setError("");
  }

  return (
    <div className="grid min-h-screen bg-white lg:grid-cols-[1.05fr_1fr]">
      {/* ------------------------------------------------ artwork panel -- */}
      <div className="relative hidden flex-col justify-between overflow-hidden bg-[#0c0e1a] p-10 text-white lg:flex xl:p-14">
        {/* Ambient orbs — transform/opacity only, frozen under reduced motion. */}
        <div
          className="pointer-events-none absolute -right-24 -top-28 size-[28rem] rounded-full bg-indigo-600/25 blur-[110px] motion-safe:animate-orb-drift"
          aria-hidden
        />
        <div
          className="pointer-events-none absolute -bottom-32 -left-24 size-[26rem] rounded-full bg-violet-600/20 blur-[110px] motion-safe:animate-orb-drift-slow"
          aria-hidden
        />
        {/* Hairline grid — texture without noise. */}
        <div
          className="pointer-events-none absolute inset-0 opacity-[0.06]"
          style={{
            backgroundImage:
              "linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px)",
            backgroundSize: "56px 56px",
          }}
          aria-hidden
        />

        <div className="relative flex items-center gap-3">
          <span className="grid size-10 place-items-center rounded-[13px] bg-gradient-to-br from-indigo-500 to-indigo-700 text-base font-black text-white ring-1 ring-white/15">
            S
          </span>
          <span className="leading-tight">
            <span className="block text-[15px] font-bold tracking-tight">Samvedna CMS</span>
            <span className="block text-[11px] font-medium text-white/40">
              Clinical case management
            </span>
          </span>
        </div>

        <motion.div variants={stagger} initial="hidden" animate="show" className="relative max-w-lg">
          <motion.span
            variants={staggerItem}
            className="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-indigo-300 backdrop-blur-md"
          >
            <ShieldCheck className="size-3" /> Samvedna Homeopathy
          </motion.span>

          <motion.h1
            variants={staggerItem}
            className="mt-5 text-[clamp(1.9rem,3.4vw,2.6rem)] font-black leading-[1.1] tracking-[-0.02em]"
          >
            Long care plans fail quietly.
            <br />
            <span className="bg-gradient-to-r from-indigo-300 to-violet-300 bg-clip-text text-transparent">
              This system makes that impossible.
            </span>
          </motion.h1>

          <motion.p
            variants={staggerItem}
            className="mt-4 max-w-md text-[13.5px] font-medium leading-relaxed text-white/50"
          >
            Every case runs the same six steps, and each one stays locked until the step before it
            is genuinely complete.
          </motion.p>

          <motion.ol variants={stagger} className="mt-8 grid gap-2.5 sm:grid-cols-2">
            {FLOW_STEPS.map((step, index) => (
              <motion.li
                key={step.label}
                variants={staggerItem}
                className="flex items-start gap-3 rounded-2xl border border-white/8 bg-white/[0.04] p-3 backdrop-blur-md"
              >
                <span className="grid size-8 shrink-0 place-items-center rounded-xl bg-white/[0.06] text-indigo-300 ring-1 ring-inset ring-white/10">
                  <step.icon className="size-4" strokeWidth={1.9} />
                </span>
                <span className="min-w-0 pt-0.5">
                  <span className="block text-[10px] font-black uppercase tracking-[0.18em] text-white/30">
                    Step {index + 1}
                  </span>
                  <span className="mt-0.5 block text-[12px] font-semibold leading-snug text-white/75">
                    {step.label}
                  </span>
                </span>
              </motion.li>
            ))}
          </motion.ol>
        </motion.div>

        <p className="relative text-[11px] font-medium text-white/25">
          Autism, ADHD and developmental care · demo build
        </p>
      </div>

      {/* --------------------------------------------------- form panel -- */}
      <div className="flex items-center justify-center bg-background px-5 py-12 sm:px-8">
        <motion.div
          initial={{ opacity: 0, y: 14 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.32, ease: EASE_ENTRANCE }}
          className="w-full max-w-[26rem]"
        >
          <div className="mb-7 lg:hidden">
            <span className="grid size-10 place-items-center rounded-[13px] bg-gradient-to-br from-indigo-500 to-indigo-700 text-base font-black text-white">
              S
            </span>
          </div>

          <div className="rounded-3xl border border-slate-200/70 bg-white p-7 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_12px_40px_-16px_rgba(15,23,42,0.18)] sm:p-8">
            <span className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
              Welcome back
            </span>
            <h2 className="mt-2 text-2xl font-black tracking-tight text-navy">Sign in</h2>
            <p className="mt-1 text-[13px] font-medium text-slate-500">
              Use your Samvedna clinical account.
            </p>

            <form onSubmit={onSubmit} className="mt-7 flex flex-col gap-4">
              <Field label="Username" error={fields.username}>
                <Input
                  value={username}
                  onChange={(event) => setUsername(event.target.value)}
                  autoComplete="username"
                  autoFocus
                  invalid={Boolean(fields.username)}
                  placeholder="casedoctor"
                />
              </Field>

              <Field label="Password" error={fields.password}>
                <Input
                  type="password"
                  value={password}
                  onChange={(event) => setPassword(event.target.value)}
                  autoComplete="current-password"
                  invalid={Boolean(fields.password)}
                  placeholder="••••••••"
                />
              </Field>

              {error && (
                <p
                  role="alert"
                  className="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-3 text-[12.5px] font-semibold text-rose-700"
                >
                  {error}
                </p>
              )}

              <Button type="submit" size="lg" loading={submitting} className="mt-1 w-full">
                Sign in <ArrowRight className="size-4" />
              </Button>
            </form>
          </div>

          {meta?.demo && meta.demoAccounts.length > 0 && (
            <div className="mt-5 rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm">
              <p className="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                <ShieldCheck className="size-3.5 text-indigo-500" /> Demo accounts
              </p>
              <p className="mt-1.5 text-[11.5px] font-medium text-slate-500">
                Each role sees a different slice of the system. Tap one to fill the form.
              </p>
              <div className="mt-3.5 grid grid-cols-2 gap-2">
                {meta.demoAccounts.map((account) => (
                  <button
                    key={account.username}
                    type="button"
                    onClick={() => applyDemoAccount(account.username, account.password)}
                    className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-left transition-colors hover:border-indigo-300 hover:bg-indigo-50"
                  >
                    <span className="block text-[12.5px] font-bold text-navy">{account.role}</span>
                    <span className="block text-[10.5px] font-medium text-slate-400">
                      {account.username}
                    </span>
                  </button>
                ))}
              </div>
              <p className="mt-3 text-[10.5px] font-medium text-slate-400">
                Password for all demo accounts:{" "}
                <span className="font-bold text-slate-600">samvedna123</span>
              </p>
            </div>
          )}
        </motion.div>
      </div>
    </div>
  );
}
