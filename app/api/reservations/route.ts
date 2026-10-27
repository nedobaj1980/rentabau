import { and, desc, eq } from "drizzle-orm";
import { getDb } from "../../../db";
import { reservations } from "../../../db/schema";
import { getChatGPTUser } from "../../chatgpt-auth";

export async function POST(request: Request) {
  try {
    const data = (await request.json()) as Record<string, string>;
    const required = ["machineId", "machineName", "customerName", "email", "phone", "startDate", "endDate"];
    if (required.some((key) => !data[key]?.trim())) return Response.json({ error: "Bitte alle Pflichtfelder ausfüllen." }, { status: 400 });
    if (data.endDate < data.startDate) return Response.json({ error: "Das Enddatum muss nach dem Startdatum liegen." }, { status: 400 });
    const db = getDb();
    const [reservation] = await db.insert(reservations).values({ machineId: data.machineId.trim(), machineName: data.machineName.trim(), customerName: data.customerName.trim(), email: data.email.trim().toLowerCase(), phone: data.phone.trim(), startDate: data.startDate, endDate: data.endDate, handover: data.handover === "lieferung" ? "lieferung" : "abholung", notes: data.notes?.trim() ?? "" }).returning();
    return Response.json({ reservation }, { status: 201 });
  } catch { return Response.json({ error: "Die Reservation konnte noch nicht gespeichert werden." }, { status: 500 }); }
}
export async function GET(request: Request) {
  const user = await getChatGPTUser(); if (!user) return Response.json({ error: "Anmeldung erforderlich." }, { status: 401 });
  const status = new URL(request.url).searchParams.get("status"); const db = getDb();
  const where = status ? and(eq(reservations.email, user.email.toLowerCase()), eq(reservations.status, status)) : eq(reservations.email, user.email.toLowerCase());
  const rows = await db.select().from(reservations).where(where).orderBy(desc(reservations.createdAt)); return Response.json({ reservations: rows });
}
