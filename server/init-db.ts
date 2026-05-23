import { readFileSync } from "fs";
import { join, dirname } from "path";
import { fileURLToPath } from "url";
import { pool } from "./db.js";

const __dirname = dirname(fileURLToPath(import.meta.url));

export async function initDb() {
  const schema = readFileSync(join(__dirname, "../shared/schema.sql"), "utf-8");
  const client = await pool.connect();
  try {
    await client.query(schema);
    console.log("[DB] Schema initialized");
  } catch (err) {
    console.error("[DB] Schema init error:", err);
    throw err;
  } finally {
    client.release();
  }
}

if (process.argv[1] === fileURLToPath(import.meta.url)) {
  initDb()
    .then(() => { console.log("[DB] Done"); process.exit(0); })
    .catch((err) => { console.error(err); process.exit(1); });
}
