import mysql, { type Pool } from "mysql2/promise";

// Reuse a single pool across hot reloads / serverless invocations.
const globalForDb = globalThis as unknown as { __mysqlPool?: Pool };

export function getPool(): Pool {
  if (!globalForDb.__mysqlPool) {
    globalForDb.__mysqlPool = mysql.createPool({
      host: process.env.DB_HOST || "localhost",
      port: Number(process.env.DB_PORT) || 3306,
      database: process.env.DB_NAME || "samvedna_homeopathy",
      user: process.env.DB_USER || "root",
      password: process.env.DB_PASS || "",
      waitForConnections: true,
      connectionLimit: 5,
      queueLimit: 0,
      enableKeepAlive: true
    });
  }

  return globalForDb.__mysqlPool;
}
