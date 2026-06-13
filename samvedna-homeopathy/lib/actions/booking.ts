"use server";

import {
  consultationSchema,
  type ConsultationInput
} from "@/lib/schemas/consultation";

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

function formValue(formData: FormData, key: keyof ConsultationInput): FormDataEntryValue | null {
  return formData.get(key);
}

const PHP_API_URL = process.env.PHP_ADMIN_URL || "http://localhost:8080";

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
    const response = await fetch(`${PHP_API_URL}/api/inquiry.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(parsed.data)
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => null);
      return {
        status: "error",
        message: errorData?.message || "Failed to submit inquiry. Please try again."
      };
    }

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
      message: "Unable to reach the server. Please try again later."
    };
  }
}
