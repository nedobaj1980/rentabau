<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kontakt & Standort | Rent A Bau</title>
  <meta name="description" content="Baumaschinenvermietung in der Ostschweiz. Direkter Kontakt, Telefonnummer und Standort.">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="icon" href="favicon.svg">
</head>
<body>
<?php include __DIR__ . '/../includes/navigation.php'; ?>


  <!-- Content -->
  <main class="section shell">
    <div class="section-head">
      <div>
        <span class="eyebrow">Persönlich für dich da</span>
        <h2>Kontakt & Mietstation Ostschweiz</h2>
      </div>
      <p>Hast du Fragen zu unseren Maschinen, benötigst ein Zubehör oder wünschst eine Beratung für dein Bauprojekt?</p>
    </div>

    <div class="detail-layout">
      <!-- Contact Info Box -->
      <div>
        <div style="padding: 32px; border: 1px solid var(--line); border-radius: 8px; background: #fff;">
          <h3 style="font-size: 24px; margin-top: 0;">Rent A Bau Ostschweiz</h3>
          <p style="color: var(--muted); line-height: 1.6;">
            Dein zuverlässiger Partner für Baumaschinen im Kanton St. Gallen, Thurgau, Appenzell und Umgebung.
          </p>

          <hr style="border: 0; border-top: 1px solid var(--line); margin: 24px 0;">

          <dl>
            <dt>Telefon & Whatsapp</dt>
            <dd><a href="tel:+41710000000" style="color: var(--orange); font-size: 20px;">+41 71 000 00 00</a></dd>

            <dt>E-Mail</dt>
            <dd><a href="mailto:info@rentabau.ch">info@rentabau.ch</a></dd>

            <dt>Öffnungszeiten & Abholung</dt>
            <dd>Montag – Freitag: 07:00 – 18:30 Uhr<br>Samstag: 07:30 – 16:00 Uhr</dd>

            <dt>Lieferdienst</dt>
            <dd>Termingerechte Lieferung direkt auf die Baustelle in der ganzen Ostschweiz.</dd>
          </dl>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="booking-card">
        <h3>Nachricht senden</h3>
        <p class="subtitle">Schreibe uns deine Anfrage – wir antworten innert kurzer Zeit.</p>

        <form onsubmit="event.preventDefault(); alert('Vielen Dank für deine Nachricht! Wir melden uns in Kürze.'); this.reset();" class="reservation-form">
          <label>
            Name / Firma
            <input type="text" placeholder="Max Muster" required>
          </label>

          <div class="form-grid">
            <label>
              E-Mail
              <input type="email" placeholder="max@beispiel.ch" required>
            </label>
            <label>
              Telefon
              <input type="tel" placeholder="079 123 45 67">
            </label>
          </div>

          <label>
            Betreff
            <select>
              <option>Allgemeine Anfrage</option>
              <option>Transport & Lieferkosten</option>
              <option>Langzeitmiete / Spezialpreis</option>
            </select>
          </label>

          <label>
            Deine Nachricht
            <textarea rows="4" placeholder="Wie können wir dir helfen?" required></textarea>
          </label>

          <button type="submit" class="button button-wide">Nachricht absenden →</button>
        </form>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer>
    <div class="shell footer-grid">
      <img src="brand/rent-a-bau-logo.png" alt="Rent A Bau Logo">
      <div>
        <strong>Rent A Bau</strong>
        <span>Baumaschinenvermietung in der Ostschweiz</span>
      </div>
      <div>
        <a href="konto.php">Kundenkonto</a>
        <a href="admin.php">Admin Dashboard</a>
      </div>
    </div>
    <div class="shell footer-bottom">
      <span>© 2026 Rent A Bau</span>
      <span>rentabau.ch · Preise exkl. MWST</span>
    </div>
  </footer>

  <script src="assets/js/app.js"></script>
</body>
</html>
