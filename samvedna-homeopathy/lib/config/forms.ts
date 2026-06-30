/**
 * Configuration for the consultation form's post-submit redirect and the
 * auto-show popup. Values are read from public env vars (NEXT_PUBLIC_*) with
 * safe defaults, so the feature works out of the box and is configurable
 * without code changes.
 *
 * Env vars (all optional):
 *   NEXT_PUBLIC_FORM_REDIRECT_MODE      none | thankYou | appointment | whatsapp | custom
 *   NEXT_PUBLIC_FORM_REDIRECT_URL       custom URL (used when mode = custom)
 *   NEXT_PUBLIC_FORM_REDIRECT_DELAY_MS  delay before redirecting (default 2200)
 *   NEXT_PUBLIC_APPOINTMENT_URL         appointment booking URL
 *   NEXT_PUBLIC_WHATSAPP                WhatsApp number (digits only)
 *   NEXT_PUBLIC_FORM_POPUP_DELAY_MS     auto-show popup delay (default 12000)
 */

export type FormRedirectMode =
  | "none"
  | "thankYou"
  | "appointment"
  | "whatsapp"
  | "custom";

export type ResolvedRedirect = {
  url: string | null;
  external: boolean;
  delayMs: number;
};

const APPOINTMENT_FALLBACK = "https://autismhomeohelp.com/online-consulting/";
const WHATSAPP_FALLBACK = "https://wa.me/917874876777";

function toPositiveInt(value: string | undefined, fallback: number): number {
  const parsed = Number(value);
  return Number.isFinite(parsed) && parsed >= 0 ? parsed : fallback;
}

const mode = (process.env.NEXT_PUBLIC_FORM_REDIRECT_MODE as FormRedirectMode) || "thankYou";
const customUrl = process.env.NEXT_PUBLIC_FORM_REDIRECT_URL ?? "";
const appointmentUrl = process.env.NEXT_PUBLIC_APPOINTMENT_URL || APPOINTMENT_FALLBACK;
const whatsappUrl = process.env.NEXT_PUBLIC_WHATSAPP
  ? `https://wa.me/${process.env.NEXT_PUBLIC_WHATSAPP}`
  : WHATSAPP_FALLBACK;
const redirectDelayMs = toPositiveInt(process.env.NEXT_PUBLIC_FORM_REDIRECT_DELAY_MS, 2200);

export function getFormRedirect(): ResolvedRedirect {
  switch (mode) {
    case "none":
      return { url: null, external: false, delayMs: redirectDelayMs };
    case "appointment":
      return { url: appointmentUrl, external: true, delayMs: redirectDelayMs };
    case "whatsapp":
      return { url: whatsappUrl, external: true, delayMs: redirectDelayMs };
    case "custom":
      return { url: customUrl || null, external: true, delayMs: redirectDelayMs };
    case "thankYou":
    default:
      return { url: "/thank-you", external: false, delayMs: redirectDelayMs };
  }
}

export const FORM_POPUP = {
  /** Delay before the consultation popup auto-appears (ms). */
  delayMs: toPositiveInt(process.env.NEXT_PUBLIC_FORM_POPUP_DELAY_MS, 12000),
  /** sessionStorage key — popup shows only once per browser session. */
  sessionKey: "samvedna:consultation-popup-shown"
} as const;
