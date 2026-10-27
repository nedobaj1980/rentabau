export type Machine = {
  id: string; name: string; category: string; description: string;
  dayPrice: number; weekendPrice: number; deposit: number; image: string; badge?: string;
};

export const machines: Machine[] = [
  { id: "minibagger-18", name: "Minibagger 1.8 t", category: "Bagger", description: "Kompakt, kräftig und ideal für Gartenbau, Leitungsgräben und enge Zufahrten.", dayPrice: 245, weekendPrice: 430, deposit: 800, image: "/brand/rent-a-bau-hero.png", badge: "Beliebt" },
  { id: "raddumper-15", name: "Raddumper 1.5 t", category: "Transport", description: "Für Aushub, Kies und Materialtransport auf kleinen und mittleren Baustellen.", dayPrice: 195, weekendPrice: 340, deposit: 600, image: "/brand/rent-a-bau-hero.png" },
  { id: "ruettelplatte-500", name: "Rüttelplatte 500 kg", category: "Verdichtung", description: "Leistungsstarke reversierbare Platte für Fundationen, Plätze und Zufahrten.", dayPrice: 145, weekendPrice: 250, deposit: 400, image: "/brand/rent-a-bau-hero.png" },
];

export const formatCHF = (value: number) => new Intl.NumberFormat("de-CH", { style: "currency", currency: "CHF", maximumFractionDigits: 0 }).format(value);
