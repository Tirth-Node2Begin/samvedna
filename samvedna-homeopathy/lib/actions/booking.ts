"use server";

import {
  consultationSchema,
  type ConsultationInput
} from "@/lib/schemas/consultation";
import { saveInquiry } from "@/lib/db/inquiries";

type FieldErrors = Partial<Record<keyof ConsultationInput, string[]>>;

export type BookingActionResult =
  | {
      status: "success";
      message: string;
      submittedAt: string;
    }
  | {
      status: "error";
      message: string;
      fieldErrors?: FieldErrors;
    };

function formValue(formData: FormData, key: string): FormDataEntryValue | null {
  return formData.get(key);
}

const ALLOWED_SOURCES = new Set(["website", "popup"]);

function readSource(formData: FormData): string {
  const raw = formData.get("source");
  return typeof raw === "string" && ALLOWED_SOURCES.has(raw) ? raw : "website";
}

export async function submitBooking(
  formData: FormData
): Promise<BookingActionResult> {
  const parsed = consultationSchema.safeParse({
    parentName: formValue(formData, "parentName"),
    childAge: formValue(formData, "childAge"),
    condition: formValue(formData, "condition"),
    country: formValue(formData, "country"),
    phone: formValue(formData, "phone"),
    email: formValue(formData, "email"),
    message: formValue(formData, "message") || undefined,
    preferredTime: formValue(formData, "preferredTime")
  });

  if (!parsed.success) {
    return {
      status: "error",
      message: "Please correct the highlighted fields.",
      fieldErrors: parsed.error.flatten().fieldErrors as FieldErrors
    };
  }

  try {
    await saveInquiry(parsed.data, readSource(formData));

    return {
      status: "success",
      message:
        "Thank you. The Samvedna care desk has received your request and will contact you shortly.",
      submittedAt: new Date().toISOString()
    };
  } catch (error) {
    console.error("Booking submission error:", error);
    return {
      status: "error",
      message: "Unable to save your request right now. Please try again or call us."
    };
  }
}
