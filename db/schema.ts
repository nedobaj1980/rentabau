import { integer, sqliteTable, text } from "drizzle-orm/sqlite-core";
export const reservations = sqliteTable("reservations", {
  id: integer("id").primaryKey({ autoIncrement: true }), machineId: text("machine_id").notNull(), machineName: text("machine_name").notNull(), customerName: text("customer_name").notNull(), email: text("email").notNull(), phone: text("phone").notNull(), startDate: text("start_date").notNull(), endDate: text("end_date").notNull(), handover: text("handover").notNull().default("abholung"), notes: text("notes").notNull().default(""), status: text("status").notNull().default("angefragt"), createdAt: integer("created_at", { mode: "timestamp" }).notNull().$defaultFn(() => new Date()),
});
