/**
 * Care-plan assessment questionnaire — single source of truth.
 *
 * Rendered step by step by components/consultation/AssessmentWizard.tsx. Step 0
 * is the general-information step (`general: true`): its fields map to real
 * columns on the `consultations` table and are the only required ones. Every
 * later step is clinical intake — those answers are packed into the `answers`
 * JSON blob, grouped by step title, for the admin detail view.
 *
 * Kept deliberately short: the intake used to run to sixteen steps and parents
 * dropped out midway, so closely related topics (behaviour, sensory, fears) are
 * now grouped into one step each and low-yield questions were removed. The
 * remaining detail is collected by the doctor during the first consultation.
 */

/**
 * Payment QR shown on the final step.
 *
 * PAYMENT_QR_SRC is the bank's static merchant QR, cropped out of the SmartHub
 * card (public/images/qr.jpeg) with its pixels otherwise untouched. It carries
 * no amount, so the payer would have to type it in.
 *
 * PAYMENT_QR_BY_PLAN holds a QR per plan with the amount already filled in
 * (EMVCo tag 54), re-encoded from that same merchant payload by
 * scripts/make-payment-qr.py. RE-RUN THAT SCRIPT whenever a rupee price changes
 * in constants/plans.ts, or the QR will keep charging the old amount — it is a
 * silent failure, nothing in the build catches it.
 *
 * UPI settles in INR only, so these encode the rupee price whichever currency
 * the visitor is viewing. Deleting the generated files is a complete rollback:
 * the payment step falls back to the static QR with no code change.
 */
export const PAYMENT_QR_SRC = "/images/payment-qr.png";

/** Amount-embedded QR per plan slug. Falls back to the static QR when absent. */
export const PAYMENT_QR_BY_PLAN: Record<string, string> = {
  starter: "/images/payment-qr-starter.png",
  standard: "/images/payment-qr-standard.png",
  premium: "/images/payment-qr-premium.png",
};

/** WhatsApp number the payment receipt should be sent to (digits only). */
export const PAYMENT_WHATSAPP = "917874876777";
export const PAYMENT_WHATSAPP_DISPLAY = "+91-78748-76777";

export type Question = {
  id: string;
  label: string;
  type: "text" | "tel" | "email" | "number" | "textarea" | "radio" | "checkbox";
  /** Choices for radio/checkbox questions. */
  options?: string[];
  placeholder?: string;
  /** Unit shown attached to the right of a text/number input, e.g. "Yrs.". */
  suffix?: string;
  required?: boolean;
  /** Span both columns in the general-information grid. */
  wide?: boolean;
};

export type Step = {
  id: string;
  title: string;
  /** True only for the contact step, which uses a two-column grid layout. */
  general?: boolean;
  questions: Question[];
};

export const STEPS: Step[] = [
  {
    id: "general",
    title: "General Information",
    general: true,
    questions: [
      { id: "patientName", label: "Patient Name", type: "text", required: true },
      { id: "childAge", label: "Age of your child", type: "number", suffix: "Yrs." },
      { id: "fatherName", label: "Parent / Guardian Name", type: "text" },
      { id: "mobile", label: "Mobile", type: "tel", required: true, placeholder: "+917874876777" },
      { id: "email", label: "Email", type: "email", placeholder: "parent@example.com" },
      { id: "city", label: "City", type: "text" },
      {
        id: "remarks",
        label: "Anything specific you would like us to know?",
        type: "textarea",
        wide: true,
      },
    ],
  },
  {
    id: "developmental-growth",
    title: "Developmental Growth",
    questions: [
      {
        id: "nameResponse",
        label: "Is your child having name response?",
        type: "radio",
        options: ["Good", "Poor", "Absent"],
      },
      {
        id: "eyeContact",
        label: "Is your child giving eye contact?",
        type: "radio",
        options: ["Good", "Poor", "Absent"],
      },
      {
        id: "followingCommands",
        label: "Is your child following commands?",
        type: "radio",
        options: [
          "Follows commands well.",
          "Follows commands partially.",
          "Doesn't understand commands.",
        ],
      },
      {
        id: "language",
        label: "How much is your child's language developed?",
        type: "radio",
        options: [
          "No speech.",
          "Speaks single words.",
          "Speaks small sentences.",
          "Echolalia.",
        ],
      },
      {
        id: "intellectual",
        label: "How is the intellectual ability of your child?",
        type: "radio",
        options: [
          "Visual learning.",
          "Auditory learning.",
          "Difficulty in paying attention.",
          "Solves puzzles well.",
        ],
      },
      {
        id: "toiletTrained",
        label: "Is your child toilet/urine trained?",
        type: "radio",
        options: [
          "No toilet urine sense.",
          "Explains through gestures.",
          "Well trained.",
        ],
      },
      {
        id: "socialize",
        label: "Does your child socialize?",
        type: "radio",
        options: ["No social interaction.", "Involves with known kids."],
      },
    ],
  },
  {
    id: "behaviour-sensory",
    title: "Behaviour & Sensory",
    questions: [
      {
        id: "repetitivePatterns",
        label: "Repetitive patterns you notice",
        type: "checkbox",
        options: [
          "Hand flapping.",
          "Whirling (moves round and round) in circles.",
          "Shadow playing",
          "Jumping with laughter.",
          "Toe walking.",
          "Lining up toys.",
          "Thumb/Finger sucking.",
          "Puts everything in mouth.",
          "Observes spinning objects.",
        ],
      },
      {
        id: "emotionalReactions",
        label: "Emotional reactions",
        type: "checkbox",
        options: [
          "Lack of emotions.",
          "Remains aloof.",
          "Mild, sensitive.",
          "Cheerful, vivacious.",
          "Shy, silent, timid.",
          "Hugs, cuddles or kisses.",
          "Stubborn or obstinate.",
          "Curious & inquisitive.",
          "Fearless, audacious.",
          "Fearful.",
          "Cranky, vexed, irritable.",
        ],
      },
      {
        id: "sensitiveTo",
        label: "Is your child sensitive to -?",
        type: "checkbox",
        options: [
          "Haircutting.",
          "Nail cutting.",
          "Brushing.",
          "Light.",
          "Loud noise.",
          "Soft/rough surface.",
        ],
      },
      {
        id: "fears",
        label: "Is your child having fear of -?",
        type: "checkbox",
        options: [
          "Height.",
          "Water.",
          "Light.",
          "Darkness.",
          "Being alone.",
          "Crowded places.",
          "Animals.",
        ],
      },
      {
        id: "hyperactive",
        label: "Is your child hyperactive?",
        type: "radio",
        options: [
          "No",
          "Yes",
          "Extremely hyperactive, cannot sit at a place",
          "Constantly jumping and running",
          "Lack of attention",
        ],
      },
      {
        id: "unusualBehaviour",
        label: "Any unusual behaviour?",
        type: "checkbox",
        options: [
          "Meaningless laughing",
          "Meaningless crying",
          "Staring at a place",
          "Lost in his own world",
        ],
      },
    ],
  },
  {
    id: "generals",
    title: "Appetite, Sleep & Generals",
    questions: [
      {
        id: "appetite",
        label: "Appetite",
        type: "radio",
        options: ["Regular.", "Excessive hunger.", "Lost."],
      },
      {
        id: "foodDesire",
        label: "Food desire",
        type: "checkbox",
        options: [
          "Sweet.",
          "Spicy.",
          "Sour.",
          "Fruits.",
          "Bread.",
          "Curd.",
          "Milk.",
          "Chalk, clay, pica.",
          "Chocolates.",
        ],
      },
      {
        id: "thirst",
        label: "Thirst",
        type: "radio",
        options: [
          "For chilled water.",
          "Profuse, in large quantity.",
          "Scanty, in sips.",
          "Regular.",
        ],
      },
      {
        id: "stool",
        label: "Stool",
        type: "radio",
        options: ["Constipation.", "Regular."],
      },
      {
        id: "thermal",
        label: "Thermal",
        type: "radio",
        options: ["Ambithermal (tolerates both weather well).", "Chilly", "Hot."],
      },
      {
        id: "perspiration",
        label: "Perspiration",
        type: "radio",
        options: [
          "Profuse.",
          "Scanty",
          "On Face.",
          "On Scalp.",
          "In palms and soles.",
        ],
      },
      {
        id: "sleepType",
        label: "Type of sleep",
        type: "radio",
        options: ["Sound.", "Comatose.", "Startles.", "Wakeful and crying."],
      },
    ],
  },
  {
    id: "medical-history",
    title: "Medical & Family History",
    questions: [
      {
        id: "teething",
        label: "How was teething of your child?",
        type: "radio",
        options: ["Early.", "Delayed.", "Decayed."],
      },
      {
        id: "respiratoryComplaints",
        label: "Any respiratory complaints?",
        type: "radio",
        options: ["Frequent cold, cough & coryza.", "H/o pneumonia."],
      },
      {
        id: "convulsion",
        label: "Any history of convulsion?",
        type: "radio",
        options: [
          "Jerks during convulsion.",
          "Falling on either side.",
          "One sided convulsion.",
          "Convulsions in full body.",
          "Febrile (fever) convulsion.",
        ],
      },
      {
        id: "familyHistory",
        label: "Family history",
        type: "checkbox",
        options: [
          "H/o neurological disorder.",
          "H/o Tuberculosis.",
          "H/o Cancer.",
          "H/o Genetic disorder.",
          "H/o Psychological disorder.",
        ],
      },
      {
        id: "threatsDuringPregnancy",
        label: "Concerns during pregnancy",
        type: "checkbox",
        options: [
          "Any sort of infection or taken antibiotics during pregnancy.",
          "Bleeding.",
          "Lack of proper nutrition.",
          "Excessive nausea or vomiting.",
          "Low lying placenta.",
          "Breech presentation.",
          "Mental or Physical stress.",
          "High blood pressure.",
          "Diabetes.",
          "Hypothyroidism.",
        ],
      },
      {
        id: "threatsAfterDelivery",
        label: "Concerns at or after delivery",
        type: "checkbox",
        options: [
          "Premature delivery- low birth weight.",
          "Asphyxia- delayed birth cry.",
          "Epilepsy/Convulsion.",
          "Neonatal jaundice.",
          "Poly/oligohydramnios (excess or loss of amniotic fluid).",
          "Nuchal cord- umbilical cord around the neck.",
          "Passed meconium (stool) in womb.",
        ],
      },
    ],
  },
];
