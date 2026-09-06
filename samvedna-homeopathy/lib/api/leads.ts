import type { ConsultationInput } from "@/lib/schemas/consultation";

/**
 * Browser-side submit path for the consultation form.
 *
 * Replaces the old `submitBooking` server action: the site is a static export on
 * PHP-only hosting, so there is no Node runtime to execute server actions. The
 * PHP endpoint lives in the same docroot, so this is a same-origin request and
 * needs no CORS or absolute URL.
 */

type FieldErrors = Partial<Record<keyof ConsultationInput, string[]>>;

export type BookingActionResult =
  | { status: "success"; message: string; submittedAt: string }
  | { status: "error"; message: string; fieldErrors?: FieldErrors };

const ENDPOINT = "/api/leads.php";

const SUCCESS_MESSAGE =
  "Thank you. The Samvedna care desk has received your request and will contact you shortly.";
const GENERIC_ERROR =
  "Unable to save your request right now. Please try again or call us.";

type LeadResponse = {
  ok?: boolean;
  message?: string;
  fieldErrors?: FieldErrors;
};

/** Form values plus the honeypot field, which is never a real input. */
export type BookingPayload = ConsultationInput & { company?: string };

export async function submitBooking(
  values: BookingPayload,
  source: "website" | "popup" = "website"
): Promise<BookingActionResult> {
  try {
    const res = await fetch(ENDPOINT, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ...values, source }),
    });

    // A misconfigured server (PHP not wired up) returns HTML, not JSON — treat
    // that as a generic failure rather than throwing an unhandled parse error.
    let data: LeadResponse = {};
    try {
      data = (await res.json()) as LeadResponse;
    } catch {
      return { status: "error", message: GENERIC_ERROR };
    }

    if (!res.ok || !data.ok) {
      return {
        status: "error",
        message: data.message || GENERIC_ERROR,
        fieldErrors: data.fieldErrors,
      };
    }

    return {
      status: "success",
      message: SUCCESS_MESSAGE,
      submittedAt: new Date().toISOString(),
    };
  } catch {
    // Network failure / offline.
    return { status: "error", message: GENERIC_ERROR };
  }
}
