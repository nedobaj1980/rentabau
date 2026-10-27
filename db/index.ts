import { drizzle } from "drizzle-orm/d1";
import * as schema from "./schema";

declare global {
  // Populated by the Worker entry before Vinext handles each request.
  var __RENTABAU_DB__: D1Database | undefined;
}

export function getDb() {
  const database = globalThis.__RENTABAU_DB__;
  if (!database) {
    throw new Error(
      "Cloudflare D1 binding `DB` is unavailable. Set the `d1` field in .openai/hosting.json to `DB` or let your control plane inject the real binding values before using the database."
    );
  }

  return drizzle(database, { schema });
}
