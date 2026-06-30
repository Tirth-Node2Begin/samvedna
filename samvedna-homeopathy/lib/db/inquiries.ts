import type { ConsultationInput } from "@/lib/schemas/consultation";
import { getPool } from "@/lib/db/mysql";

// `condition` is a reserved word in MySQL, so the column is `condition_type`.
const CREATE_TABLE_SQL = `
  CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_name VARCHAR(255) NOT NULL,
    child_age INT NOT NULL,
    condition_type VARCHAR(255) NOT NULL,
    country VARCHAR(255) NOT NULL,
    phone VARCHAR(32) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NULL,
    preferred_time VARCHAR(32) NOT NULL,
    source VARCHAR(32) NOT NULL DEFAULT 'website',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
`;

// Ensure the table exists exactly once per pool lifetime.
let schemaReady: Promise<void> | null = null;

function ensureSchema(): Promise<void> {
  if (!schemaReady) {
    schemaReady = getPool()
      .query(CREATE_TABLE_SQL)
      .then(() => undefined)
      .catch((error) => {
        // Reset so a later request can retry if this attempt failed.
        schemaReady = null;
        throw error;
      });
  }

  return schemaReady;
}

export async function saveInquiry(
  input: ConsultationInput,
  source = "website"
): Promise<void> {
  await ensureSchema();

  await getPool().execute(
    `INSERT INTO inquiries
      (parent_name, child_age, condition_type, country, phone, email, message, preferred_time, source)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [
      input.parentName,
      input.childAge,
      input.condition,
      input.country,
      input.phone,
      input.email,
      input.message ?? null,
      input.preferredTime,
      source
    ]
  );
}
