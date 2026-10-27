"use client";
import { useState } from "react";
import type { Machine } from "../data";
import { formatCHF } from "../data";
import ReservationModal from "./ReservationModal";

export default function MachineCard({ machine }: { machine: Machine }) {
  const [open, setOpen] = useState(false);
  return <><article className="machine-card">
    <div className="machine-image"><img src={machine.image} alt="Orange Baumaschine in der Ostschweiz" />{machine.badge && <span className="badge">{machine.badge}</span>}<span className="category">{machine.category}</span></div>
    <div className="machine-body"><h3>{machine.name}</h3><p>{machine.description}</p><div className="price-row"><div><strong>{formatCHF(machine.dayPrice)}</strong><span> / Tag</span></div><button className="button button-dark" onClick={() => setOpen(true)}>Verfügbarkeit prüfen</button></div></div>
  </article>{open && <ReservationModal machine={machine} onClose={() => setOpen(false)} />}</>;
}
