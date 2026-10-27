"use client";
import { FormEvent, useState } from "react";
import type { Machine } from "../data";
import { formatCHF } from "../data";

export default function ReservationModal({ machine, onClose }: { machine: Machine; onClose: () => void }) {
  const [state, setState] = useState<"idle" | "loading" | "success" | "error">("idle");
  const [message, setMessage] = useState("");
  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); setState("loading");
    const payload = Object.fromEntries(new FormData(event.currentTarget).entries());
    try {
      const response = await fetch("/api/reservations", { method: "POST", headers: { "content-type": "application/json" }, body: JSON.stringify({ ...payload, machineId: machine.id, machineName: machine.name }) });
      const data = (await response.json()) as { error?: string };
      if (!response.ok) throw new Error(data.error || "Die Anfrage konnte nicht gesendet werden.");
      setState("success");
    } catch (error) { setMessage(error instanceof Error ? error.message : "Unbekannter Fehler"); setState("error"); }
  }
  return <div className="modal-backdrop" role="presentation" onMouseDown={onClose}><div className="modal" role="dialog" aria-modal="true" aria-labelledby="reservation-title" onMouseDown={(e) => e.stopPropagation()}>
    <button className="modal-close" onClick={onClose} aria-label="Fenster schliessen">×</button>
    {state === "success" ? <div className="success-state"><span className="success-icon">✓</span><h2>Anfrage erhalten</h2><p>Wir prüfen den Termin und melden uns mit der definitiven Bestätigung.</p><button className="button" onClick={onClose}>Schliessen</button></div> : <><span className="eyebrow">Reservationsanfrage</span><h2 id="reservation-title">{machine.name}</h2><p className="modal-price">ab {formatCHF(machine.dayPrice)} pro Tag · Depot {formatCHF(machine.deposit)}</p><form onSubmit={submit} className="reservation-form">
      <div className="form-grid"><label>Von<input name="startDate" type="date" required /></label><label>Bis<input name="endDate" type="date" required /></label></div>
      <div className="form-grid"><label>Vor- und Nachname<input name="customerName" required autoComplete="name" /></label><label>Telefon<input name="phone" required autoComplete="tel" /></label></div>
      <label>E-Mail<input name="email" type="email" required autoComplete="email" /></label>
      <label>Übergabe<select name="handover" defaultValue="abholung"><option value="abholung">Abholung</option><option value="lieferung">Lieferung an Baustelle</option></select></label>
      <label>Bemerkung<textarea name="notes" rows={3} placeholder="Einsatzort, Zubehör oder besondere Wünsche" /></label>
      {state === "error" && <p className="form-error">{message}</p>}<button className="button button-wide" disabled={state === "loading"}>{state === "loading" ? "Wird gesendet…" : "Unverbindlich reservieren"}</button><small>Die Reservation wird erst nach unserer Bestätigung verbindlich.</small>
    </form></>}
  </div></div>;
}
