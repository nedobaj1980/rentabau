<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kundenkonto | Rent A Bau</title>
  <meta name="description" content="Kundenportal für Rent A Bau. Verwalte deine Miethistorie, sieh aktive Baumaschinen-Reservationen und Zahlungsdetails (TWINT, IBAN, Barzahlung) ein.">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="icon" href="favicon.svg">
  <style>
    .pay-badge {
      font-size: 11px;
      font-weight: 800;
      padding: 3px 8px;
      border-radius: 12px;
      display: inline-block;
    }
    .pay-badge.twint { background: #dcfce7; color: #15803d; }
    .pay-badge.bank { background: #dbeafe; color: #1e40af; }
    .pay-badge.mastercard { background: #fef3c7; color: #b45309; }
    .pay-badge.cash { background: #f3e8ff; color: #6b21a8; }
    .reservation-card {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    .reservation-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--paper);
      margin-bottom: 14px;
    }
  </style>
</head>
<body class="portal-page">
<?php include __DIR__ . '/../includes/navigation.php'; ?>


  <!-- Content -->
  <main class="portal-shell shell">
    <div class="portal-heading">
      <div>
        <span class="eyebrow">Kundenportal</span>
        <h1>Meine Miethistorie & Zahlungen</h1>
        <p>Deine Reservationen, Mietvereinbarungen und Zahlungsdaten auf einen Blick.</p>
      </div>
      <div>
        <?php $accountUser = currentUser(); ?>
        <span class="status-pill">Angemeldet als <?= e($accountUser['name'] ?? 'Kunde') ?></span>
      </div>
    </div>

    <div class="portal-grid">
      <section class="portal-card portal-main">
        <h2>Aktuelle & Vergangene Buchungen</h2>
        <div id="customer-reservations-list">
          <div class="empty-state">
            <span>⌛</span>
            <h3>Reservationen werden geladen…</h3>
          </div>
        </div>
      </section>

      <aside class="portal-card">
        <h2>Zahlungs-Hinweise</h2>
        <div style="background: #fff8f5; border: 1px solid #ffd4c0; border-radius: 8px; padding: 14px; font-size: 13px; margin-bottom: 16px;">
          <strong style="color: var(--orange-dark); display: block; margin-bottom: 6px;">📲 TWINT & QR-Banküberweisung:</strong>
          <span>Nach der Bestätigung erhältst du per E-Mail deine Rechnung mit QR-Code oder TWINT-Zahlungslink.</span>
        </div>

        <div style="background: #f3e8ff; border: 1px solid #d8b4fe; border-radius: 8px; padding: 14px; font-size: 13px; margin-bottom: 16px;">
          <strong style="color: #6b21a8; display: block; margin-bottom: 6px;">💵 Barzahlung vor Ort:</strong>
          <span>Bei kurzfristigen Buchungen oder Selbstabholung kannst du den Mietbetrag & Kaution bequem in bar bei der Übergabe bezahlen.</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
          <a href="maschinen.php" class="button" style="text-align: center;">🚜 Neue Baumaschine mieten</a>
          <a href="kontakt.php" class="button button-outline" style="text-align: center;">💬 Kundendienst kontaktieren</a>
        </div>
      </aside>
    </div>
  </main>


  <script src="assets/js/app.js"></script>
  <script>
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    async function loadCustomerPortal() {
      await fetchSettings();
      const container = document.getElementById("customer-reservations-list");
      const reservations = await fetchReservations();

      if (!reservations || reservations.length === 0) {
        container.innerHTML = `
          <div class="empty-state">
            <span>🚜</span>
            <h3>Noch keine Reservationen vorhanden</h3>
            <p>Du hast bisher noch keine Baumaschine angefragt. Wähle eine Maschine aus dem Mietkatalog aus!</p>
          </div>
        `;
        return;
      }

      container.innerHTML = reservations.map(r => {
        const payKey = r.paymentPreference || r.paymentMethod || 'twint';
        const payLbl = r.paymentLabel || ({twint: 'TWINT', bank: 'Banküberweisung', mastercard: 'Karte', cash: 'Barzahlung'}[payKey] || payKey);
        let payBadgeClass = 'twint';
        if (payKey === 'bank') payBadgeClass = 'bank';
        if (payKey === 'mastercard') payBadgeClass = 'mastercard';
        if (payKey === 'cash') payBadgeClass = 'cash';

        const statusPills = {
          angefragt: '<span class="status-pill warning">⌛ Angefragt</span>',
          bestätigt: '<span class="status-pill">🟢 Bestätigt</span>',
          abgeschlossen: '<span class="status-pill">✓ Abgeschlossen</span>',
          storniert: '<span class="status-pill danger">✕ Storniert</span>'
        };

        const deliveryStr = r.handover === 'lieferung' ? 
          `🚚 Lieferung an Baustelle (${r.deliveryKm || 0} km ab Romanshorn)` : 
          `🏬 Selbstabholung in Romanshorn`;

        return `
          <div class="reservation-card">
            <div class="reservation-header">
              <div>
                <strong style="font-size: 18px; color: var(--black);">${escapeHtml(r.machineName)}</strong>
                <small style="color: var(--muted); display: block; margin-top: 2px;">Reservations-ID #${escapeHtml(r.id)}</small>
              </div>
              <div>
                ${statusPills[r.status] || statusPills.angefragt}
              </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; font-size: 14px; margin-bottom: 12px;">
              <div>
                <span style="color: var(--muted); font-size: 12px; display: block;">MIETZEITRAUM</span>
                    <strong>📅 ${escapeHtml(r.startDate)} bis ${escapeHtml(r.endDate)}</strong><br>
                    <small style="color: var(--orange-dark); font-weight: 800;">${escapeHtml(r.days)} Verrechnungstag(e)</small>
              </div>
              <div>
                <span style="color: var(--muted); font-size: 12px; display: block;">ZAHLUNGSART</span>
                <span class="pay-badge ${payBadgeClass}">${escapeHtml(payLbl)}</span>
              </div>
              <div>
                <span style="color: var(--muted); font-size: 12px; display: block;">ÜBERGABE</span>
                <span>${escapeHtml(deliveryStr)}</span>
              </div>
              <div>
                <span style="color: var(--muted); font-size: 12px; display: block;">GESAMTPREIS</span>
                <strong style="font-size: 18px; color: var(--orange-dark);">${escapeHtml(formatCHF(r.totalPrice))}</strong>
              </div>
            </div>
          </div>
        `;
      }).join("");
    }

    document.addEventListener("DOMContentLoaded", loadCustomerPortal);
  </script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
