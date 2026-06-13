import { z } from "zod";
import { conditionList } from "@/constants/conditions";

export const preferredTimes = ["morning", "afternoon", "evening"] as const;

export const consultationSchema = z.object({
  parentName: z.string().trim().min(2, "Enter the parent's full name."),
  childAge: z.coerce
    .number()
    .min(0, "Age cannot be negative.")
    .max(18, "Please enter an age between 0 and 18."),
  condition: z.enum(conditionList, {
    message: "Choose the primary concern."
  }),
  country: z.string().trim().min(2, "Enter your country."),
  phone: z
    .string()
    .trim()
    .regex(/^\+?[0-9]{8,15}$/, "Use 8 to 15 digits, with optional country code."),
  email: z.string().trim().email("Enter a valid email address."),
  message: z.string().trim().max(500, "Keep the message under 500 characters.").optional(),
  preferredTime: z.enum(preferredTimes, {
    message: "Choose a preferred consultation time."
  })
});

export type ConsultationInput = z.infer<typeof consultationSchema>;
export type ConsultationFormValues = z.input<typeof consultationSchema>;
