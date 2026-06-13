"use client";

import { motion, useReducedMotion } from "framer-motion";
import { Globe2, MapPin } from "lucide-react";
import Image from "next/image";

/* ─── Planets representing countries ─── */
const planets = [
  // Orbit 1: Radius 140
  { name: "India",     code: "in", radius: 140, angle: 0,   speed: 30, direction: 1  },
  { name: "UAE",       code: "ae", radius: 140, angle: 180, speed: 30, direction: 1  },
  // Orbit 2: Radius 210
  { name: "UK",        code: "gb", radius: 210, angle: 60,  speed: 40, direction: -1 },
  { name: "Singapore", code: "sg", radius: 210, angle: 180, speed: 40, direction: -1 },
  { name: "USA",       code: "us", radius: 210, angle: 300, speed: 40, direction: -1 },
  // Orbit 3: Radius 280
  { name: "Canada",    code: "ca", radius: 280, angle: 0,   speed: 55, direction: 1  },
  { name: "Australia", code: "au", radius: 280, angle: 120, speed: 55, direction: 1  },
  { name: "Germany",   code: "de", radius: 280, angle: 240, speed: 55, direction: 1  },
] as const;

const orbits = [140, 210, 280];

/* ─── Individual Planet node ─── */
function PlanetNode({ p }: { p: (typeof planets)[number] }) {
  const prefersReducedMotion = useReducedMotion();
  const orbitDuration = prefersReducedMotion ? 0 : p.speed;

  // We rotate the outer div to orbit the center.
  // We counter-rotate the inner div to keep the text perfectly upright.
  return (
    <motion.div
      className="absolute top-1/2 left-1/2 z-20"
      style={{
        width: 0,
        height: 0,
      }}
      initial={{ rotate: p.angle }}
      animate={
        prefersReducedMotion
          ? undefined
          : { rotate: p.angle + 360 * p.direction }
      }
      transition={{
        duration: orbitDuration,
        repeat: Infinity,
        ease: "linear",
      }}
    >
      <div
        className="absolute top-1/2 left-1/2"
        style={{
          transform: `translate(-50%, -50%) translateX(${p.radius}px)`,
        }}
      >
        <motion.div
          initial={{ rotate: -p.angle }}
          animate={
            prefersReducedMotion
              ? undefined
              : { rotate: -(p.angle + 360 * p.direction) }
          }
          transition={{
            duration: orbitDuration,
            repeat: Infinity,
            ease: "linear",
          }}
        >
          <div className="group relative flex items-center gap-2 rounded-full border border-white/80 bg-white/90 py-1.5 pl-2 pr-3 text-[13px] leading-none shadow-[0_4px_20px_rgba(37,99,235,0.08)] backdrop-blur-md transition-all duration-300 hover:shadow-[0_8px_32px_rgba(37,99,235,0.16)] hover:-translate-y-0.5 hover:bg-white cursor-default">
            {/* Country flag */}
            <div className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 overflow-hidden relative border border-black/5">
              <img
                src={`https://flagcdn.com/${p.code}.svg`}
                alt={`${p.name} flag`}
                className="absolute inset-0 h-full w-full object-cover"
              />
            </div>
            <span className="font-semibold text-slate-800 whitespace-nowrap">
              {p.name}
            </span>
          </div>
        </motion.div>
      </div>
    </motion.div>
  );
}

/* ─── Main Orbital WorldMap component ─── */
export default function WorldMap() {
  const prefersReducedMotion = useReducedMotion();

  return (
    <div className="relative mx-auto w-full max-w-[820px]">
      {/* ── Desktop / tablet view ── */}
      <div className="hidden h-[640px] overflow-hidden rounded-[2.5rem] bg-white sm:block relative border border-primary/5 shadow-sm">
        {/* Abstract space/stars background pattern */}
        <div
          className="absolute inset-0 opacity-[0.02] pointer-events-none"
          style={{
            backgroundImage: `radial-gradient(circle at 2px 2px, #2563EB 1.5px, transparent 0)`,
            backgroundSize: `36px 36px`,
          }}
        />

        {/* Soft background glow meshes */}
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(37,99,235,0.04),transparent_60%)]" />

        {/* Orbit Rings */}
        {orbits.map((radius) => (
          <div
            key={radius}
            className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 rounded-full border border-dashed border-primary/20 pointer-events-none"
            style={{
              width: radius * 2,
              height: radius * 2,
            }}
          />
        ))}

        {/* Central Sun (Swan Logo) */}
        <div className="absolute top-1/2 left-1/2 z-30 -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
          <div className="relative flex h-[110px] w-[110px] items-center justify-center rounded-full bg-white shadow-[0_0_60px_rgba(37,99,235,0.15)] border border-primary/10">
            {/* Sun glow effect */}
            <motion.div
              className="absolute inset-0 rounded-full border border-primary/30"
              animate={
                prefersReducedMotion
                  ? undefined
                  : { scale: [1, 1.3, 1], opacity: [0.5, 0, 0.5] }
              }
              transition={{
                duration: 4,
                repeat: Infinity,
                ease: "easeInOut",
              }}
            />
            
            <Image
              src="/images/samvedna-logo-swan.png"
              alt="Samvedna Swan Logo"
              width={64}
              height={64}
              className="object-contain drop-shadow-sm"
              priority
            />
          </div>
        </div>

        {/* Orbiting Planets */}
        {planets.map((p) => (
          <PlanetNode key={p.name} p={p} />
        ))}
      </div>

      {/* ── Mobile view ── */}
      <div className="sm:hidden">
        <div className="flex items-center gap-4 rounded-2xl bg-gradient-to-br from-primary/[0.04] to-transparent p-5 border border-primary/10">
          <div className="flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-sm ring-2 ring-primary/10">
             <Image
              src="/images/samvedna-logo-swan.png"
              alt="Samvedna Swan Logo"
              width={32}
              height={32}
              className="object-contain"
            />
          </div>
          <div>
            <p className="font-display text-lg font-bold text-slate-900">
              Worldwide access
            </p>
            <p className="text-sm text-slate-500 font-medium">
              Global online care
            </p>
          </div>
        </div>

        <div className="mt-6 grid grid-cols-1 gap-3">
          {planets.map((d) => (
            <div
              key={d.name}
              className="flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white p-3 shadow-sm transition-colors hover:border-primary/20"
            >
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-50 overflow-hidden relative border border-slate-200">
                <img
                  src={`https://flagcdn.com/${d.code}.svg`}
                  alt={`${d.name} flag`}
                  className="absolute inset-0 h-full w-full object-cover"
                />
              </div>
              <div className="flex-1">
                <span className="font-semibold text-slate-900">{d.name}</span>
              </div>
              <div className="flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                <MapPin className="h-3 w-3" strokeWidth={2} />
                <span>Active</span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
