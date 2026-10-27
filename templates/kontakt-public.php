<?php
$pageTitle = 'Kontakt – Rent A Bau';
include __DIR__ . '/../includes/header.php';
?>
<main class="shell contact-page">
  <div class="contact-layout">
    <section class="contact-intro">
      <p class="eyebrow">Kontakt</p>
      <h1>Wie können wir dir helfen?</h1>
      <p class="contact-lead">Fragen zu Maschinen, Lieferung oder einem passenden Mietangebot? Schreib uns – wir melden uns schnellstmöglich.</p>
      <div class="contact-details" aria-label="Kontaktinformationen">
        <a href="https://www.google.com/maps/search/?api=1&query=Kreuzlingerstrasse+21%2C+8590+Romanshorn" target="_blank" rel="noopener"><strong>Abholadresse</strong><span>Kreuzlingerstrasse 21<br>8590 Romanshorn <small>↗ Karte öffnen</small></span></a>
        <a href="tel:+41710000000"><strong>Telefon &amp; WhatsApp</strong><span>+41 71 000 00 00</span></a>
        <a href="mailto:info@rentabau.ch"><strong>E-Mail</strong><span>info@rentabau.ch</span></a>
        <div><strong>Abholung &amp; Öffnungszeiten</strong><span>Mo–Fr 07:00–18:30 Uhr<br>Sa 07:30–16:00 Uhr</span></div>
      </div>
    </section>
    <section class="contact-card">
      <h2>Nachricht senden</h2>
      <p class="form-hint">Pflichtfelder sind mit * markiert.</p>
      <?php if ($message): ?><p class="form-success" role="status"><?= e($message) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endif; ?>
      <form method="post" class="reservation-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <label for="name">Name / Firma *</label><input id="name" name="name" required autocomplete="name" value="<?= e($_POST['name'] ?? '') ?>">
        <div class="form-grid"><div><label for="email">E-Mail *</label><input id="email" name="email" type="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>"></div><div><label for="phone">Telefon</label><input id="phone" name="phone" type="tel" autocomplete="tel" value="<?= e($_POST['phone'] ?? '') ?>"></div></div>
        <label for="topic">Thema</label><select id="topic" name="topic"><option value="Reservation">Reservation</option><option value="Lieferung">Lieferung</option><option value="Allgemeine Anfrage">Allgemeine Anfrage</option></select>
        <label for="message">Nachricht *</label><textarea id="message" name="message" rows="6" required><?= e($_POST['message'] ?? '') ?></textarea>
        <button class="button button-wide" type="submit">Nachricht senden</button>
      </form>
    </section>
  </div>
  <section class="contact-map-section" aria-labelledby="contact-map-title">
    <div class="contact-map-header">
      <p class="eyebrow">Mietstation</p>
      <h2 id="contact-map-title">So findest du uns</h2>
      <p>Kreuzlingerstrasse 21, 8590 Romanshorn · Direkte Abholung und Rückgabe vor Ort</p>
    </div>
    <div class="contact-map"><iframe title="Karte zur Mietstation an der Kreuzlingerstrasse 21 in Romanshorn" src="https://www.google.com/maps?q=Kreuzlingerstrasse+21%2C+8590+Romanshorn&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
  </section>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
