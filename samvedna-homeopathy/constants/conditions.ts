/**
 * The condition names offered in the enquiry form dropdown and linked from the
 * footer.
 *
 * The homepage "Conditions we support" cards are NOT here any more — they are
 * admin-managed (see core-php/admin/conditions, /api/conditions.php and
 * getConditions() in lib/content.ts) so the client can edit copy and upload an
 * image per condition. This list stays static because it also backs the zod
 * enum in lib/schemas/consultation.ts: submitted values have to validate against
 * a fixed set, and widening it from the database would let a renamed row
 * invalidate the form. Add a name here too if the client introduces a genuinely
 * new area of care.
 */
export const conditionList = [
  "Autism Spectrum Disorder Support",
  "ADHD Support",
  "Learning Disability Support",
  "Speech Delay Support",
  "Developmental Delay Support",
  "Genetic Disorders Support",
  "Neurological Disorders Support"
] as const;
