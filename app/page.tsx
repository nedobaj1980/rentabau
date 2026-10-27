import Header from "./components/Header";
import MachineCard from "./components/MachineCard";
import { machines } from "./data";

export default function Home() {
  return <main><Header />
    <section className="hero"><img className="hero-bg" src="/brand/rent-a-bau-hero.png" alt="Minibagger und Verdichtungsmaschine vor Schweizer Berglandschaft" /><div className="hero-shade" /><div className="shell hero-content">
      <span className="eyebrow light">Baumaschinenvermietung · Ganze Ostschweiz</span><h1>Die richtige Maschine.<br /><em>Genau dann, wenn du sie brauchst.</em></h1>
      <p>Professionelle Baumaschinen für Private und Unternehmen – einfach reservieren, selbst abholen oder direkt auf die Baustelle liefern lassen.</p>
      <div className="hero-actions"><a className="button" href="#maschinen">Maschinen entdecken</a><a className="button button-ghost" href="tel:+41710000000">Beratung anrufen</a></div>
      <div className="hero-facts"><span>✓ Faire Tagespreise</span><span>✓ Flexible Mietdauer</span><span>✓ Lieferung möglich</span></div>
    </div></section>
    <section className="trust-strip"><div className="shell trust-grid"><div><strong>Ostschweiz</strong><span>regional verfügbar</span></div><div><strong>Privat & Gewerbe</strong><span>für jedes Projekt</span></div><div><strong>Abholung & Lieferung</strong><span>du entscheidest</span></div><div><strong>Direkter Kontakt</strong><span>schnell und unkompliziert</span></div></div></section>
    <section className="section shell" id="maschinen"><div className="section-head"><div><span className="eyebrow">Maschinenpark</span><h2>Starkes Gerät für dein Projekt</h2></div><p>Der Maschinenpark wird laufend erweitert. Alle Preise verstehen sich exklusive MWST und Treibstoff.</p></div><div className="machine-grid">{machines.map((machine) => <MachineCard key={machine.id} machine={machine} />)}</div></section>
    <section className="dark-section" id="ablauf"><div className="shell section"><div className="section-head inverse"><div><span className="eyebrow">Einfach mieten</span><h2>In drei Schritten zur Maschine</h2></div></div><div className="steps"><article><span>01</span><h3>Maschine wählen</h3><p>Passendes Gerät auswählen und gewünschten Zeitraum angeben.</p></article><article><span>02</span><h3>Bestätigung erhalten</h3><p>Wir prüfen Verfügbarkeit, Zubehör und den genauen Preis.</p></article><article><span>03</span><h3>Loslegen</h3><p>Maschine abholen oder termingerecht liefern lassen.</p></article></div></div></section>
    <section className="section shell split" id="lieferung"><div className="split-image"><img src="/brand/rent-a-bau-hero.png" alt="Baumaschinenlieferung in der Ostschweiz" /></div><div className="split-copy"><span className="eyebrow">Flexibel übergeben</span><h2>Abholen oder liefern lassen</h2><p>Du kannst deine Maschine selbst übernehmen oder bequem auf die Baustelle liefern lassen. Die Lieferkosten berechnen wir transparent nach Distanz und Maschine.</p><ul><li>Persönliche Einweisung bei Übergabe</li><li>Passendes Zubehör auf Anfrage</li><li>Flexible Rückgabe nach Vereinbarung</li></ul><a className="button button-dark" href="#maschinen">Jetzt Maschine wählen</a></div></section>
    <footer><div className="shell footer-grid"><img src="/brand/rent-a-bau-logo.png" alt="Rent A Bau" /><div><strong>Rent A Bau</strong><span>Baumaschinenvermietung in der Ostschweiz</span></div><div><a href="/konto">Kundenkonto</a><a href="/admin">Admin</a></div></div><div className="shell footer-bottom"><span>© 2026 Rent A Bau</span><span>rentabau.ch · Preise exkl. MWST</span></div></footer>
  </main>;
}
