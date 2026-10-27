<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'So funktioniert die Miete | Rent A Bau';
include __DIR__ . '/includes/header.php';
?>
<main class="how-page">
  <section class="how-hero">
    <div class="shell">
      <span class="eyebrow">Einfach mieten</span>
      <h1><span class="how-title-prefix">So funktioniert’s bei</span> <span class="brand-word brand-rent">Rent</span> <span class="brand-word brand-a">A</span> <span class="brand-word brand-bau">Bau</span></h1>
      <p>Von der passenden Maschine bis zur Rückgabe: In wenigen Schritten bist du startklar auf deiner Baustelle.</p>
      <a class="button" href="maschinen.php">Maschinen entdecken</a>
    </div>
  </section>
  <section class="shell how-steps" aria-labelledby="how-steps-title">
    <div class="section-head"><div><span class="eyebrow">Der Ablauf</span><h2 id="how-steps-title">In drei Schritten zur Maschine</h2></div></div>
    <div class="steps how-step-grid">
      <article><span>01</span><h3>Maschine auswählen</h3><p>Vergleiche Maschinen, Tagespreise und technische Daten. Wähle deinen gewünschten Mietzeitraum direkt auf der Detailseite.</p></article>
      <article><span>02</span><h3>Reservation anfragen</h3><p>Gib deine Kontaktdaten und Übergabeart an. Wir prüfen die Verfügbarkeit und bestätigen dir Preis, Zubehör und Termin.</p></article>
      <article><span>03</span><h3>Abholen oder liefern lassen</h3><p>Du erhältst alle Übergabeinformationen. Auf Wunsch liefern wir die Maschine termingerecht auf deine Baustelle.</p></article>
    </div>
  </section>
  <section class="dark-section how-details">
    <div class="shell">
      <div class="section-head inverse"><div><span class="eyebrow">Gut zu wissen</span><h2>Transparent von Anfang an</h2></div></div>
      <div class="how-info-grid">
        <article><h3>Preise</h3><p>Die angezeigten Preise sind Tagesdeckel und verstehen sich exklusive MWST und Treibstoff. Mehrtagesrabatte werden transparent ausgewiesen.</p></article>
        <article><h3>Übergabe</h3><p>Bei der Abholung erhältst du eine persönliche Einweisung. Lieferung und Rückgabe stimmen wir direkt mit dir ab.</p></article>
        <article><h3>Zahlung</h3><p>Die gewünschte Zahlungsart wählst du bei der Anfrage. Die eigentliche Zahlung erfolgt erst nach unserer Bestätigung.</p></article>
      </div>
    </div>
  </section>
  <section class="shell how-cta"><h2>Noch Fragen?</h2><p>Wir beraten dich gerne persönlich zur passenden Maschine und Lieferung.</p><a class="button button-dark" href="kontakt.php">Kontakt aufnehmen</a></section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
