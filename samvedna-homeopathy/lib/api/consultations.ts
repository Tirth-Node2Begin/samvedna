/**
 * Browser-side submit path for the multi-step care-plan assessment.
 *
 * Like lib/api/leads.ts, the site is a static export on PHP-only hosting, so the
 * wizard posts directly to a same-origin PHP endpoint — no server action, no
 * CORS. General-information fields go as top-level keys; every clinical answer
 * is packed into `answers` (grouped by step) for the admin detail view.
 */

/** One rendered answer for the admin view: the exact question and its value. */
export type AnswerItem = { label: string; value: string };
export type AnswerSection = { title: string; items: AnswerItem[] };

export type ConsultationPayload = {
  plan: string;
  planName?: string;
  amount?: string;
  patientName: string;
  fatherName?: string;
  mobile: string;
  altPhone?: string;
  address?: string;
  city?: string;
  state?: string;
  zip?: string;
  email?: string;
  remarks?: string;
  childAge?: string;
  answers: AnswerSection[];
  /** Honeypot — never a real input. */
  company?: string;
};

/** Fields the server can complain about (mirrors api/consultations.php). */
export type ConsultationField = "patientName" | "mobile" | "email";
type FieldErrors = Partial<Record<ConsultationField, string[]>>;

export type ConsultationResult =
  | { status: "success"; message: string; submittedAt: string }
  | { status: "error"; message: string; fieldErrors?: FieldErrors };

const ENDPOINT = "/api/consultations.php";

const SUCCESS_MESSAGE =
  "Thank you. Your assessment has been received. Please complete the payment below.";
const GENERIC_ERROR =
  "Unable to save your submission right now. Please try again or call us.";

type ApiResponse = {
  ok?: boolean;
  message?: string;
  fieldErrors?: FieldErrors;
};

export async function submitConsultation(
  payload: ConsultationPayload,
  source: "website" | "popup" = "website"
): Promise<ConsultationResult> {
  try {
    const res = await fetch(ENDPOINT, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ...payload, source }),
    });

    // A misconfigured server (PHP not wired up) returns HTML, not JSON — treat
    // that as a generic failure rather than throwing an unhandled parse error.
    let data: ApiResponse = {};
    try {
      data = (await res.json()) as ApiResponse;
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
