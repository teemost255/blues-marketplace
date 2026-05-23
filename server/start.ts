import { initDb } from "./init-db.js";
import { startApiServer } from "./index.js";

async function main() {
  await initDb();
  await startApiServer();
}

main().catch((err) => {
  console.error("[Server] Fatal error:", err);
  process.exit(1);
});
