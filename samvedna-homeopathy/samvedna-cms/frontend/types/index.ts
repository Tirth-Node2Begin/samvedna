/**
 * API shapes. These mirror the `shape()` methods in the PHP models — keep the
 * two in step when a column is added.
 */

export type Role = "founder" | "senior_doctor" | "case_doctor" | "coordinator";

export interface User {
  id: number;
  username: string;
  name: string;
  email: string;
  phone: string;
  title: string;
  role: Role;
  roleLabel: string;
  status: string;
}

export interface TeamMember extends User {
  caseload: number;
  openEscalations: number;
  pendingSignoffs: number;
  scorecard: Scorecard;
}

export interface Scorecard {
  reviewsClosed: number;
  completeness: number;
  onTimeRate: number;
  satisfaction: number;
}

export interface Plan {
  id: number;
  code: string;
  name: string;
  tagline: string;
  price: number;
  currency: string;
  priceLabel: string;
  durationMonths: number;
  durationLabel: string;
  reviewCount: number;
  reviewIntervalDays: number;
  adherenceIntervalDays: number;
  rescheduleWindowDays: number;
  requiresSenior: boolean;
  requiresFounder: boolean;
  founderIntervalDays: number;
  clinicalCover: string;
  features: string[];
  highlight: boolean;
  sortOrder: number;
  status: string;
  cadenceLabel: string;
  activeCases?: number;
  cycles?: number;
}

export type PatientStatus = "draft" | "active" | "on_hold" | "completed" | "discharged";

export interface Patient {
  id: number;
  code: string;
  childName: string;
  dob: string | null;
  age: number | null;
  gender: string;
  guardianName: string;
  guardianRelation: string;
  phone: string;
  altPhone: string;
  email: string;
  city: string;
  state: string;
  country: string;
  referralSource: string;
  notes: string;
  status: PatientStatus;
  createdAt?: string | null;
  /* Present on the register listing only. */
  condition?: string;
  severity?: string;
  baselineComplete?: boolean;
  caseId?: number | null;
  caseCode?: string;
  caseStatus?: string;
  planName?: string;
  planCode?: string;
  caseDoctorName?: string;
  startDate?: string | null;
  endDate?: string | null;
  cycleLabel?: string;
}

export interface Marker {
  key: string;
  label: string;
  rating: string;
  score: number;
}

export interface Baseline {
  conditionType: string;
  severity: string;
  diagnosisAge: string;
  medicalHistory: string;
  therapyInvolvement: string;
  concerns: string[];
  markers: Marker[];
  isComplete: boolean;
  completedAt: string | null;
}

export interface BaselineVerdict {
  complete: boolean;
  missing: Record<string, string>;
  percent: number;
  ratedCount: number;
  totalCount: number;
}

export interface CareCase {
  id: number;
  code: string;
  patientId: number;
  planId: number;
  plan: Plan | null;
  caseDoctorId: number | null;
  seniorDoctorId: number | null;
  founderId: number | null;
  caseDoctorName: string;
  seniorDoctorName: string;
  founderName: string;
  startDate: string;
  endDate: string;
  totalCycles: number;
  currentCycle: number;
  cycleLabel: string;
  status: string;
  activatedAt: string | null;
  elapsedPercent: number;
  daysRemaining: number;
}

export type EventType = "activation" | "adherence" | "review" | "founder_review";
export type EventStatus = "scheduled" | "upcoming" | "done" | "missed" | "rescheduled";

export interface ScheduleEvent {
  id: number;
  caseId: number;
  cycle: number;
  type: EventType;
  typeLabel: string;
  isConsult: boolean;
  title: string;
  dueDate: string;
  originalDueDate: string;
  status: EventStatus;
  ownerId: number | null;
  notes: string;
  completedAt: string | null;
  daysAway: number;
  isOverdue: boolean;
  /* Feed-only fields. */
  caseCode?: string;
  patientId?: number;
  patientName?: string;
  patientCode?: string;
  ownerName?: string;
}

export interface CycleSummary {
  cycle: number;
  start: string;
  end: string;
  label: string;
  events: number;
  done: number;
  percent: number;
  isCurrent: boolean;
}

export interface AdherenceRecord {
  id: number;
  eventId: number;
  caseId: number;
  cycle: number;
  compliance: "full" | "partial" | "none";
  refill: string;
  parentConcern: boolean;
  parentNote: string;
  recordedAt: string;
}

export type ReviewStatus = "draft" | "awaiting_signoff" | "closed";

export interface Goal {
  id?: number;
  caseId?: number;
  cycle?: number;
  title: string;
  metric: string;
  status: "pending" | "progressing" | "achieved" | "missed";
}

export interface Signoff {
  id: number;
  role: string;
  roleLabel: string;
  userId: number | null;
  userName: string;
  status: "pending" | "signed" | "rejected";
  comment: string;
  signedAt: string | null;
}

export interface Review {
  id: number;
  caseId: number;
  eventId: number | null;
  cycle: number;
  reviewDate: string;
  improvements: string;
  stagnation: string;
  protocolDecision: string;
  protocolRationale: string;
  therapyNotes: string;
  adherencePercent: number;
  escalate: boolean;
  escalateNote: string;
  nextReviewDate: string | null;
  status: ReviewStatus;
  completeness: number;
  closedOnTime: boolean | null;
  parentSatisfaction: number | null;
  closedAt: string | null;
  createdBy: number | null;
  createdAt?: string;
  /* Feed / detail extras. */
  caseCode?: string;
  totalCycles?: number;
  patientId?: number;
  patientName?: string;
  patientCode?: string;
  planName?: string;
  requiresSenior?: boolean;
  authorName?: string;
  signoffs?: Signoff[];
  goals?: Goal[];
}

export interface EscalationStep {
  id: number;
  role: string;
  roleLabel: string;
  userName: string;
  status: "standby" | "reviewing" | "approved" | "resolved";
  note: string;
  actedAt: string | null;
}

export interface Escalation {
  id: number;
  code: string;
  caseId: number;
  reviewId: number | null;
  raisedBy: number | null;
  reasons: string[];
  reasonLabels: string[];
  notes: string;
  level: "senior" | "founder";
  status: "open" | "in_progress" | "resolved" | "closed";
  raisedAt: string;
  dueDate: string;
  slaDays: number;
  daysLeft: number;
  isOverdue: boolean;
  resolutionNotes: string;
  resolvedAt: string | null;
  caseCode?: string;
  patientId?: number;
  patientName?: string;
  patientCode?: string;
  raisedByName?: string;
}

export interface ProgressArea {
  key: string;
  label: string;
  baseline: number;
  score: number;
  trend: "improving" | "stable" | "declining";
}

export interface ProgressReport {
  id: number;
  caseId: number;
  reviewId: number | null;
  cycle: number;
  periodStart: string;
  periodEnd: string;
  periodLabel: string;
  overallTrend: "improving" | "stable" | "declining";
  goalsTotal: number;
  goalsProgressing: number;
  adherencePercent: number;
  areas: ProgressArea[];
  objectives: { title: string; metric: string; done: boolean }[];
  scorecard: {
    doctor?: string;
    completeness?: number;
    closure?: string;
    onTime?: boolean;
    satisfaction?: number;
  };
  sharedAt: string | null;
  sharedChannel: string;
  createdAt: string;
}

export interface ActivityEntry {
  id: number;
  actorName: string;
  entityType?: string;
  entityId?: number | null;
  action: string;
  summary: string;
  createdAt: string;
}

export interface Meta {
  demo: boolean;
  conditions: string[];
  severities: { key: string; label: string }[];
  markers: { key: string; label: string }[];
  markerRatings: { key: string; label: string; score: number }[];
  protocolDecisions: { key: string; label: string }[];
  escalationReasons: { key: string; label: string }[];
  roles: { key: Role; label: string }[];
  patientStatuses: string[];
  demoAccounts: { username: string; role: string; password: string }[];
}

/* ------------------------------------------------------ Medicine supply -- */

export type SupplyState = "due_today" | "overdue" | "upcoming";

/** One case's 15-day medicine cycle. `nextDueOn` is the fact everything hangs on. */
export interface MedicineSupply {
  id: number;
  caseId: number;
  patientId: number;
  intervalDays: number;
  lastDeliveredOn: string | null;
  nextDueOn: string;
  daysUntilDue: number;
  state: SupplyState;
  stateLabel: string;
  status: "active" | "paused";
  /** Out of stock defers the delivery; it does not complete it. */
  stockStatus: "in_stock" | "out_of_stock";
  isOutOfStock: boolean;
  outOfStockSince: string | null;
  stockNote: string;
  notes: string;
  /* Present on the board listing. */
  caseCode?: string | null;
  patientName?: string | null;
  patientCode?: string | null;
  guardianName?: string | null;
  phone?: string | null;
  city?: string | null;
  caseDoctorName?: string | null;
}

export interface MedicineDelivery {
  id: number;
  caseId: number;
  patientId: number;
  /** A real delivery, or an out-of-stock deferral on the same timeline. */
  kind: "delivered" | "deferred_out_of_stock";
  isDeferral: boolean;
  kindLabel: string;
  deliveredOn: string;
  nextDueOn: string;
  mode: "courier" | "hand" | "pickup" | "other";
  modeLabel: string;
  reference: string;
  notes: string;
  deliveredByName: string;
  createdAt: string;
  patientName?: string;
  patientCode?: string;
}

export interface MedicineBoard {
  today: string;
  dueToday: MedicineSupply[];
  overdue: MedicineSupply[];
  upcoming: MedicineSupply[];
  later: MedicineSupply[];
  recent: MedicineDelivery[];
  counts: {
    dueToday: number;
    overdue: number;
    upcoming: number;
    outOfStock: number;
    deliveredThisMonth: number;
  };
  /** Server-side default for an out-of-stock deferral, in days. */
  defaultDeferDays: number;
}

/* --------------------------------------------------------- Notifications -- */

export type NotificationStatus = "unread" | "read" | "done" | "dismissed";

export interface AppNotification {
  id: number;
  type: string;
  caseId: number | null;
  patientId: number | null;
  patientName: string | null;
  patientCode: string | null;
  title: string;
  body: string;
  href: string | null;
  dueDate: string | null;
  status: NotificationStatus;
  isMedicine: boolean;
  /** Only a "deliver today" notice offers the Mark-delivered shortcut. */
  isActionable: boolean;
  isOutOfStock: boolean;
  createdAt: string;
  readAt: string | null;
  doneAt: string | null;
}

export interface NotificationCounts {
  unread: number;
  read: number;
  done: number;
  dismissed: number;
  open: number;
}

export interface NotificationsPayload {
  notifications: AppNotification[];
  counts: NotificationCounts;
  medicine: { dueToday: number; overdue: number };
  today: string;
}

/** The full case workspace payload from GET /api/patients/{id}. */
export interface PatientWorkspace {
  patient: Patient;
  baseline: Baseline;
  verdict: BaselineVerdict;
  case: CareCase | null;
  schedule: ScheduleEvent[];
  cycles: CycleSummary[];
  reviews: Review[];
  escalations: Escalation[];
  progress: ProgressReport[];
  adherence: AdherenceRecord[];
  medicine: { supply: MedicineSupply | null; history: MedicineDelivery[] };
}

export interface DashboardPayload {
  metrics: {
    activeCases: number;
    draftIntakes: number;
    reviewsDue: number;
    awaitingSignoff: number;
    pendingForMe: number;
    openEscalations: number;
    overdueEscalations: number;
    overdueTouchpoints: number;
    adherence: number;
    cmsCompleteness: number;
    medicineDueToday: number;
    medicineOverdue: number;
    unreadNotifications: number;
  };
  medicine: { dueToday: MedicineSupply[]; overdue: MedicineSupply[] };
  trends: { improving: number; stable: number; declining: number };
  upcoming: ScheduleEvent[];
  reviewQueue: Review[];
  escalations: Escalation[];
  activity: ActivityEntry[];
  scorecard: Scorecard;
}
