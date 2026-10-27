<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title id="page-title">Maschine Details | Rent A Bau</title>
  <meta name="description" content="Baumaschine mieten bei Rent A Bau. Detaillierte Spezifikationen, interaktiver Belegungskalender mit Tagespreisen, Zahlungsarten (TWINT, Mastercard, IBAN, Barzahlung) und Sofort-Reservierung.">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="icon" href="favicon.svg">
  <style>
    .gallery-main-img {
      width: 100%;
      height: 440px;
      object-fit: cover;
      object-position: center;
      border-radius: 10px;
      border: 1px solid var(--line);
      box-shadow: 0 4px 16px rgba(0,0,0,0.06);
      background: #f8fafc;
      transition: all 0.25s ease;
    }
    @media (max-width: 768px) {
      .gallery-main-img {
        height: 290px;
      }
    }
    .gallery-thumbs {
      display: flex;
      gap: 10px;
      margin-top: 12px;
      overflow-x: auto;
      padding-bottom: 6px;
    }
    .thumb-img {
      width: 76px;
      height: 56px;
      object-fit: cover;
      border-radius: 6px;
      border: 2px solid var(--line);
      cursor: pointer;
      opacity: 0.75;
      transition: all 0.2s ease;
      background: #f8fafc;
    }
    .thumb-img.active, .thumb-img:hover {
      opacity: 1;
      border-color: var(--orange-dark);
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(255, 85, 0, 0.25);
    }
    .spec-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
      margin: 16px 0;
    }
    .spec-item {
      background: var(--paper);
      padding: 10px 14px;
      border-radius: 6px;
      border: 1px solid var(--line);
    }
    .spec-item strong {
      display: block;
      font-size: 11px;
      color: var(--muted);
      text-transform: uppercase;
    }
    .spec-item span {
      font-size: 15px;
      font-weight: 800;
      color: var(--black);
    }
    .accessory-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 14px;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 6px;
      margin-bottom: 8px;
      cursor: pointer;
    }
    .accessory-item:hover {
      border-color: var(--orange);
    }
    .delivery-calc-box {
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      border-radius: 6px;
      padding: 12px;
      margin-top: 10px;
    }

    /* Payment Option Card Grid Styles */
    .payment-options-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 8px;
      margin-top: 6px;
    }
    .payment-option-card {
      border: 2px solid var(--line);
      border-radius: 8px;
      padding: 10px;
      background: #fff;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      gap: 4px;
      transition: all 0.2s ease;
      position: relative;
    }
    .payment-option-card:hover {
      border-color: var(--orange);
    }
    .payment-option-card.selected {
      border-color: var(--orange-dark);
      background: #fff7ed;
      box-shadow: 0 0 10px rgba(249, 115, 22, 0.2);
    }
    .payment-option-card input[type="radio"] {
      position: absolute;
      top: 8px;
      right: 8px;
    }
    .payment-title {
      font-size: 13px;
      font-weight: 800;
      color: var(--black);
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .payment-sub {
      font-size: 11px;
      color: var(--muted);
      line-height: 1.2;
    }
    .payment-notice-tag {
      font-size: 10px;
      font-weight: 800;
      background: #fef3c7;
      color: #92400e;
      padding: 2px 6px;
      border-radius: 4px;
      display: inline-block;
      margin-top: 2px;
    }

    /* Public Interactive Availability & Pricing Calendar */
    .public-cal-wrapper {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 24px;
      margin-bottom: 28px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
    .public-cal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 18px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .public-cal-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 8px;
    }
    .public-cal-day-name {
      text-align: center;
      font-weight: 900;
      font-size: 13px;
      color: var(--muted);
      padding: 8px 0;
      background: var(--paper);
      border-radius: 6px;
    }
    .public-cal-cell {
      min-height: 95px;
      border: 2px solid #a7f3d0;
      border-radius: 8px;
      padding: 8px;
      background: #ecfdf5;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
    }
    .public-cal-cell:hover {
      border-color: #059669;
      box-shadow: 0 6px 16px rgba(16, 185, 129, 0.25);
      transform: translateY(-2px);
    }
    .public-cal-cell.empty {
      background: transparent;
      border: none;
      cursor: default;
    }
    .public-cal-cell.is-selected {
      background: #fff7ed !important;
      border: 2px solid #fdba74 !important;
      box-shadow: 0 0 10px rgba(249, 115, 22, 0.2) !important;
    }
    .public-cal-cell.is-start-date {
      background: #f97316 !important;
      border-color: #c2410c !important;
      color: #fff !important;
    }
    .public-cal-cell.is-start-date .public-cal-cell-num,
    .public-cal-cell.is-start-date .public-cal-cell-price {
      color: #fff !important;
    }
    .public-cal-cell.is-end-date {
      background: #ea580c !important;
      border-color: #9a3412 !important;
      color: #fff !important;
    }
    .public-cal-cell.is-end-date .public-cal-cell-num,
    .public-cal-cell.is-end-date .public-cal-cell-price {
      color: #fff !important;
    }
    .public-cal-cell.is-reserved, .public-cal-cell.is-pending {
      background: #fef3c7 !important;
      border-color: #fde68a !important;
      cursor: not-allowed;
    }
    .public-cal-cell.is-blocked {
      background: #fef2f2 !important;
      border-color: #fca5a5 !important;
      cursor: not-allowed;
      opacity: 0.8;
    }
    .public-cal-cell-num {
      font-weight: 900;
      font-size: 16px;
      color: var(--black);
      line-height: 1.1;
    }
    .public-cal-cell-price {
      font-size: 12px;
      font-weight: 900;
      color: var(--orange-dark);
      margin: 4px 0;
      line-height: 1.2;
    }
    .public-cal-badge {
      font-size: 10px;
      font-weight: 800;
      padding: 3px 6px;
      border-radius: 12px;
      line-height: 1;
      display: inline-block;
    }
    .public-cal-badge.free {
      background: #d1fae5;
      color: #047857;
    }
    .public-cal-badge.weekend {
      background: #e2e8f0;
      color: #334155;
    }
    .public-cal-badge.reserved {
      background: #ffedd5;
      color: #c2410c;
    }
    .public-cal-badge.blocked {
      background: #fee2e2;
      color: #991b1b;
    }
    .public-cal-badge.selected-start {
      background: #fff;
      color: #c2410c;
      font-weight: 900;
    }
    .public-cal-badge.selected-end {
      background: #fff;
      color: #9a3412;
      font-weight: 900;
    }
    @media (max-width: 600px) {
      .public-cal-cell { min-height: 75px; padding: 4px; }
      .public-cal-cell-price { font-size: 10px; }
      .payment-options-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/navigation.php'; ?>


  <!-- Main Content -->
  <main class="section shell">
    
    <div style="margin-bottom: 16px;">
      <a href="maschinen.php" style="color: var(--muted); font-weight: 700; font-size: 14px;">← Zurück zum Mietkatalog</a>
    </div>

    <div class="portal-grid">
      
      <!-- Left Column: Image Gallery, Specs, Interactive Price Calendar, Accessories -->
      <div>
        
        <div style="margin-bottom: 20px;">
          <div class="gallery-main-wrapper" style="position: relative; cursor: zoom-in;" onclick="openCurrentMachineLightbox()" title="Klicken für Vollbild-Ansicht">
            <img id="main-product-img" src="brand/rent-a-bau-hero.png" alt="Maschine" class="gallery-main-img has-lightbox-zoom">
            <span class="zoom-hint-tag">🔍 Vollbild</span>
          </div>
          <div id="gallery-thumbs-container" class="gallery-thumbs">
            <!-- Populated dynamically -->
          </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
          <span id="product-category" class="category" style="position: static;">Bagger</span>
          <span id="product-badge" class="badge" style="position: static; display: none;">Beliebt</span>
        </div>
        <h1 id="product-title" style="margin: 0 0 10px; font-size: 32px;">Minibagger 1.8 t</h1>
        <p id="product-desc" style="color: var(--muted); font-size: 16px; line-height: 1.5; margin-bottom: 24px;"></p>

        <!-- Technical Specs -->
        <h3 style="font-size: 18px; margin-bottom: 12px; border-bottom: 2px solid var(--paper); padding-bottom: 6px;">⚙️ Technische Spezifikationen</h3>
        <div id="product-specs-container" class="spec-grid">
          <!-- Populated dynamically -->
        </div>

        <!-- Beautiful Public Availability & Pricing Calendar -->
        <div class="public-cal-wrapper">
          <div class="public-cal-header">
            <div>
              <h3 style="margin: 0 0 4px; font-size: 20px;">📅 Belegungskalender & Tagespreise</h3>
              <p style="color: var(--muted); margin: 0; font-size: 14px;">Klicke auf ein freies Datum im Kalender, um deinen Mietzeitraum festzulegen.</p>
            </div>
            <div style="display: flex; gap: 6px; align-items: center;">
              <button type="button" class="button button-small button-outline" onclick="changePublicCalMonth(-1)">‹ Vorheriger</button>
              <span id="public-cal-month-name" style="font-weight: 900; font-size: 16px; min-width: 140px; text-align: center; color: var(--black);">August 2026</span>
              <button type="button" class="button button-small button-outline" onclick="changePublicCalMonth(1)">Nächster ›</button>
            </div>
          </div>

          <div class="calendar-legend" style="margin-bottom: 16px;">
            <span><span class="legend-dot free" style="background:#d1fae5; border:1px solid #10b981;"></span> Frei (Grün)</span>
            <span><span class="legend-dot free" style="background:#e2e8f0; border:1px solid #94a3b8;"></span> Wochenende (Kostenlos)</span>
            <span><span class="legend-dot reserved" style="background:#ffedd5; border:1px solid #f97316;"></span> Ausgewählt (Orange)</span>
            <span><span class="legend-dot reserved" style="background:#fee2e2; border:1px solid #ef4444;"></span> Gesperrt (Rot)</span>
          </div>

          <div id="calendar-selection-notice" style="display: none; background: #fff7ed; border: 1px solid #fdba74; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; font-size: 13px; color: #c2410c; font-weight: 800;">
          </div>

          <div id="public-calendar-container">
            <!-- Calendar grid populated by JS -->
          </div>
        </div>

        <!-- Passendes Zubehör & Cross-Selling -->
        <div style="margin-top: 20px;">
          <h3 style="font-size: 18px; margin-bottom: 12px; border-bottom: 2px solid var(--paper); padding-bottom: 6px;">💡 Passendes Zubehör & Zusatzoptionen</h3>
          <div id="product-accessories-container">
            <!-- Populated dynamically -->
          </div>
        </div>

      </div>

      <!-- Right Column: Interactive Booking & Delivery Summary Card (Step 1) -->
      <aside class="portal-card" style="align-self: flex-start; position: sticky; top: 90px;">
        <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 22px;">🛒 Mietzeitraum & Optionen (Schritt 1/2)</h3>

        <form id="booking-form" onsubmit="handleStep1Proceed(event)">
          
          <div style="margin-bottom: 14px;">
            <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Miettyp *</label>
            <select id="rental-type" onchange="updatePriceCalculation()" style="width: 100%; padding: 10px; font-weight: 800; border-radius: 6px; border: 1px solid var(--line);">
              <option value="tag">📅 Tagesmiete (Tagesdeckel = 5 Std.)</option>
              <option value="stunde">⏱️ Stundenweise Abrechnung</option>
            </select>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
            <div>
              <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Mietbeginn *</label>
              <input type="date" id="start-date" required onchange="onManualDateInputChange()" style="width: 100%; padding: 8px; font-weight: 800;">
            </div>
            <div>
              <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Mietende *</label>
              <input type="date" id="end-date" required onchange="onManualDateInputChange()" style="width: 100%; padding: 8px; font-weight: 800;">
            </div>
          </div>

          <div id="hours-per-day-row" style="margin-bottom: 14px; display: none;">
            <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Arbeitsstunden pro Tag: <strong id="hours-display">3 h</strong></label>
            <input type="range" id="hours-slider" min="1" max="10" value="3" oninput="document.getElementById('hours-display').innerText = this.value + ' h'; updatePriceCalculation();" style="width: 100%;">
          </div>

          <!-- Handover & Delivery Calculator -->
          <div style="margin-bottom: 16px;">
            <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Übergabeart *</label>
            <select id="handover-type" onchange="toggleDeliveryInputs()" style="width: 100%; padding: 10px; font-weight: 800; border-radius: 6px; border: 1px solid var(--line);">
              <option value="abholung">🏬 Selbstabholung in Romanshorn (Kostenlos)</option>
              <option value="lieferung">🚚 Lieferung an Baustelle (berechnet ab Romanshorn)</option>
            </select>

            <div id="delivery-inputs-container" class="delivery-calc-box" style="display: none;">
              <div style="font-size: 11px; font-weight: 900; color: #1e40af; margin-bottom: 4px;">
                📍 Anfahrt wird ab Standort Romanshorn (TG) berechnet:
              </div>
              <label style="font-size: 11px; font-weight: 800; display: block; margin-bottom: 4px; color: #1e3a8a;">Entfernung in KM ab Romanshorn *</label>
              <input type="number" id="delivery-km-input" value="15" min="1" max="500" placeholder="z. B. 25 km ab Romanshorn" oninput="updatePriceCalculation()" style="width: 100%; padding: 8px; font-weight: 800;">
              <small id="delivery-calc-details" style="color: #1e40af; font-size: 11px; display: block; margin-top: 4px; line-height: 1.3;">
                Geringerer KM-Tarif bei längeren Strecken.
              </small>
            </div>
          </div>

          <!-- Pricing Summary Box -->
          <div style="background: var(--paper); padding: 16px; border-radius: 8px; border: 1px solid var(--line); margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 6px;">
              <span>Mietdauer:</span>
              <strong id="calc-duration">1 Tag (0 SA/SO)</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 6px;">
              <span>Mietpreis Maschinen:</span>
              <span id="calc-rent-price">CHF 0</span>
            </div>
            <div id="calc-discount-row" style="display: flex; justify-content: space-between; font-size: 13px; color: var(--orange-dark); margin-bottom: 6px; display: none;">
              <span id="calc-discount-label">Staffelrabatt:</span>
              <strong id="calc-discount-val">-CHF 0</strong>
            </div>
            <div id="calc-accessories-row" style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; display: none;">
              <span>Zubehör / Option(en):</span>
              <span id="calc-acc-price">+CHF 0</span>
            </div>
            <div id="calc-delivery-row" style="display: flex; justify-content: space-between; font-size: 13px; color: #1e40af; margin-bottom: 6px; display: none;">
              <span id="calc-delivery-label">Lieferung ab Romanshorn:</span>
              <strong id="calc-delivery-price">+CHF 0</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--muted); margin-bottom: 8px;">
              <span>Depot / Kaution:</span>
              <span id="calc-deposit">CHF 0</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 900; color: var(--black); padding-top: 10px; border-top: 2px solid var(--line);">
              <span>Gesamttotal:</span>
              <span id="calc-total" style="color: var(--orange-dark);">CHF 0</span>
            </div>
          </div>

          <button type="submit" class="button" style="width: 100%; padding: 16px; font-size: 17px; text-align: center; font-weight: 900;">
            🛒 Weiter zur Kasse (Schritt 1/2)
          </button>
        </form>

      </aside>

    </div>

  </main>


  <script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/../assets/js/app.js') ?>"></script>
  <script>
    let currentMachine = null;
    let selectedAccessories = [];
    let publicCalDate = new Date();
    let isPickingEndDate = false;
    let selectedPayment = "twint";

    function selectPaymentMethod(payKey) {
      selectedPayment = payKey;
      document.querySelectorAll(".payment-option-card").forEach(c => c.classList.remove("selected"));
      
      const card = document.getElementById(`pay-card-${payKey}`);
      const radio = document.getElementById(`pay-radio-${payKey}`);
      if (card) card.classList.add("selected");
      if (radio) radio.checked = true;

      const instBox = document.getElementById("payment-instruction-box");
      const bankConf = (SYSTEM_SETTINGS.paymentConfig && SYSTEM_SETTINGS.paymentConfig.bank) ? SYSTEM_SETTINGS.paymentConfig.bank : DEFAULT_PAYMENT_CONFIG.bank;
      const twintConf = (SYSTEM_SETTINGS.paymentConfig && SYSTEM_SETTINGS.paymentConfig.twint) ? SYSTEM_SETTINGS.paymentConfig.twint : DEFAULT_PAYMENT_CONFIG.twint;

      if (payKey === "twint") {
        instBox.innerHTML = `📲 <strong>TWINT Pay (${twintConf.phone || '+41 79 123 45 67'}):</strong> Schnelle Schweizer Mobile-Zahlung. Du erhältst den QR-Code / Betrag direkt in der Bestätigung.`;
      } else if (payKey === "bank") {
        instBox.innerHTML = `🏦 <strong>Bankverbindung (Vorkasse / QR-Rechnung):</strong> Überweise bequem an <strong>${bankConf.bankName || 'TKB'}</strong> (IBAN: <code>${bankConf.iban || 'CH93 0078 ...'}</code>).`;
      } else if (payKey === "mastercard") {
        instBox.innerHTML = `💳 <strong>Mastercard / Visa:</strong> Gesicherte Kartenzahlung. Die Karten-Abrechnung erfolgt nach Bestätigung deiner Mietanfrage.`;
      } else if (payKey === "cash") {
        instBox.innerHTML = `💵 <strong>Barzahlung bei Abholung / Übergabe:</strong> Zahle den Mietbetrag inklusive Depot einfach direkt vor Ort in bar bei der Übergabe. Ideal bei kurzfristigen Terminen!`;
      }
    }

    function changePublicCalMonth(delta) {
      publicCalDate.setMonth(publicCalDate.getMonth() + delta);
      renderPublicCalendar();
    }

    function onManualDateInputChange() {
      isPickingEndDate = false;
      const notice = document.getElementById("calendar-selection-notice");
      if (notice) notice.style.display = "none";
      updatePriceCalculation();
      renderPublicCalendar();
    }

    async function renderPublicCalendar() {
      try {
        const container = document.getElementById("public-calendar-container");
        if (!container) return;
        if (!currentMachine && MACHINES && MACHINES.length > 0) {
          currentMachine = MACHINES[0];
        }
        if (!currentMachine) return;

        let reservations = [];
        try {
          reservations = await fetchReservations();
        } catch (e) {
          console.warn("fetchReservations calendar fallback", e);
        }
        const blockedDates = SYSTEM_SETTINGS.blockedDates || [];

        const year = publicCalDate.getFullYear();
        const month = publicCalDate.getMonth();
        const monthNames = ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"];

        const monthNameEl = document.getElementById("public-cal-month-name");
        if (monthNameEl) monthNameEl.innerText = `${monthNames[month]} ${year}`;

        const firstDayIndex = new Date(year, month, 1).getDay();
        const mondayOffset = (firstDayIndex + 6) % 7;
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const rawStart = document.getElementById("start-date")?.value || "";
        const rawEnd = document.getElementById("end-date")?.value || "";
        const currentStart = parseToIsoDateString(rawStart);
        const currentEnd = parseToIsoDateString(rawEnd);

        let html = `
          <div class="public-cal-grid">
            <div class="public-cal-day-name">Mo</div>
            <div class="public-cal-day-name">Di</div>
            <div class="public-cal-day-name">Mi</div>
            <div class="public-cal-day-name">Do</div>
            <div class="public-cal-day-name" style="color: var(--orange-dark);">Fr</div>
            <div class="public-cal-day-name">Sa</div>
            <div class="public-cal-day-name">So</div>
        `;

        for (let i = 0; i < mondayOffset; i++) {
          html += `<div class="public-cal-cell empty"></div>`;
        }

        const maxHrs = parseInt(currentMachine.maxHoursPerDay) || 5;
        const hrPrice = parseFloat(currentMachine.hourlyPrice) || 45;
        const dayFlatPrice = hrPrice * maxHrs;

        for (let day = 1; day <= daysInMonth; day++) {
          const mStr = String(month + 1).padStart(2, '0');
          const dStr = String(day).padStart(2, '0');
          const dateStr = `${year}-${mStr}-${dStr}`;
          const dateObj = new Date(year, month, day);
          const dayOfWeek = dateObj.getDay();
          const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

          const isBlocked = blockedDates.some(b => 
            b.machineId === currentMachine.id && dateStr >= parseToIsoDateString(b.startDate) && dateStr <= parseToIsoDateString(b.endDate)
          );

          const isReserved = reservations.some(r => 
            r.status !== 'storniert' && r.machineId === currentMachine.id && dateStr >= parseToIsoDateString(r.startDate) && dateStr <= parseToIsoDateString(r.endDate)
          );

          const isStart = currentStart && dateStr === currentStart;
          const isEnd = currentEnd && dateStr === currentEnd;
          const isInRange = currentStart && currentEnd && dateStr >= currentStart && dateStr <= currentEnd;

          let cellClass = "";
          let badgeHtml = "";

          if (isBlocked) {
            cellClass = "is-blocked";
            badgeHtml = `<span class="public-cal-badge blocked">🔴 Gesperrt</span>`;
          } else if (isReserved) {
            cellClass = "is-reserved";
            badgeHtml = `<span class="public-cal-badge reserved">🟠 Reserviert</span>`;
          } else if (isStart) {
            cellClass = "is-start-date";
            badgeHtml = `<span class="public-cal-badge selected-start">📅 Mietbeginn</span>`;
          } else if (isEnd && currentStart !== currentEnd) {
            cellClass = "is-end-date";
            badgeHtml = `<span class="public-cal-badge selected-end">🏁 Mietende</span>`;
          } else if (isInRange) {
            cellClass = "is-selected";
            badgeHtml = `<span class="public-cal-badge reserved">Ausgewählt</span>`;
          } else if (isWeekend) {
            badgeHtml = `<span class="public-cal-badge weekend">🟢 Frei</span>`;
          } else {
            badgeHtml = `<span class="public-cal-badge free">🟢 Frei</span>`;
          }

          const priceDisplay = isWeekend ? 'Gratis (SA/SO)' : formatCHF(dayFlatPrice);

          html += `
            <div class="public-cal-cell ${cellClass}" onclick="handlePublicCalDateClick('${dateStr}', ${isBlocked || isReserved})">
              <span class="public-cal-cell-num">${day}</span>
              <span class="public-cal-cell-price">${priceDisplay}</span>
              ${badgeHtml}
            </div>
          `;
        }

        html += `</div>`;
        container.innerHTML = html;
      } catch (err) {
        console.error("renderPublicCalendar error", err);
      }
    }

    function handlePublicCalDateClick(dateStr, isDisabled) {
      if (isDisabled) {
        alert(`Der ${dateStr} ist an diesem Tag nicht verfügbar.`);
        return;
      }

      const startInput = document.getElementById("start-date");
      const endInput = document.getElementById("end-date");
      const notice = document.getElementById("calendar-selection-notice");

      const currStart = parseToIsoDateString(startInput.value);
      const currEnd = parseToIsoDateString(endInput.value);

      // Selection Step Logic:
      if (!isPickingEndDate || !currStart || dateStr < currStart) {
        // Step 1: Set Start Date
        startInput.value = dateStr;
        endInput.value = dateStr;
        isPickingEndDate = true;
        
        if (notice) {
          notice.style.display = "block";
          notice.innerHTML = `📍 <strong>Mietbeginn gewählt:</strong> ${formatDateDE(dateStr)}. Klicke jetzt auf das gewünschte <strong>Mietende</strong> im Kalender!`;
        }
      } else {
        // Step 2: Set End Date
        endInput.value = dateStr;
        isPickingEndDate = false;

        if (notice) {
          notice.style.display = "block";
          notice.innerHTML = `✅ <strong>Mietzeitraum gewählt:</strong> ${formatDateDE(currStart)} bis ${formatDateDE(dateStr)}!`;
        }
      }

      updatePriceCalculation();
      renderPublicCalendar();
    }

    function formatDateDE(isoStr) {
      if (!isoStr) return "";
      const normalized = parseToIsoDateString(isoStr);
      const parts = normalized.split("-");
      if (parts.length === 3) return `${parts[2]}.${parts[1]}.${parts[0]}`;
      return isoStr;
    }

    function toggleDeliveryInputs() {
      const type = document.getElementById("handover-type").value;
      document.getElementById("delivery-inputs-container").style.display = type === "lieferung" ? "block" : "none";
      updatePriceCalculation();
    }

    function toggleAccessorySelection(idx, price) {
      const checkbox = document.getElementById(`acc-check-${idx}`);
      if (!checkbox) return;
      checkbox.checked = !checkbox.checked;

      if (checkbox.checked) {
        selectedAccessories.push({ idx, price });
      } else {
        selectedAccessories = selectedAccessories.filter(a => a.idx !== idx);
      }
      updatePriceCalculation();
    }

    function updatePriceCalculation() {
      try {
        if (!currentMachine && MACHINES && MACHINES.length > 0) {
          currentMachine = MACHINES[0];
        }
        if (!currentMachine) return;

        const type = document.getElementById("rental-type").value;
        const rawStart = document.getElementById("start-date").value;
        const rawEnd = document.getElementById("end-date").value;
        const startDate = parseToIsoDateString(rawStart);
        const endDate = parseToIsoDateString(rawEnd);
        
        const hoursPerDay = parseInt(document.getElementById("hours-slider").value) || 3;

        document.getElementById("hours-per-day-row").style.display = type === "stunde" ? "block" : "none";

        // Short Notice Rental Detection
        const isShortNotice = isShortNoticeRental(startDate, 3);
        const shortBadge = document.getElementById("short-notice-badge");
        if (shortBadge) {
          shortBadge.style.display = isShortNotice ? "inline-block" : "none";
        }

        const pricing = calculateRentalPrice(currentMachine, { type, startDate, endDate, hoursPerDay });

        document.getElementById("calc-duration").innerText = `${pricing.billableDays} Verrechnungstag(e) (${pricing.freeWeekendDays} WE-Tage frei)`;
        document.getElementById("calc-rent-price").innerText = formatCHF(pricing.grossTotal);

        if (pricing.discountPercent > 0) {
          document.getElementById("calc-discount-row").style.display = "flex";
          document.getElementById("calc-discount-label").innerText = `Staffelrabatt (${pricing.discountPercent}%):`;
          document.getElementById("calc-discount-val").innerText = `-${formatCHF(pricing.discountAmount)}`;
        } else {
          document.getElementById("calc-discount-row").style.display = "none";
        }

        const accTotal = selectedAccessories.reduce((sum, a) => sum + (parseFloat(a.price) || 0), 0);
        if (accTotal > 0) {
          document.getElementById("calc-accessories-row").style.display = "flex";
          document.getElementById("calc-acc-price").innerText = `+${formatCHF(accTotal)}`;
        } else {
          document.getElementById("calc-accessories-row").style.display = "none";
        }

        const handoverType = document.getElementById("handover-type").value;
        let deliveryFee = 0;
        let deliveryKm = 0;

        if (handoverType === "lieferung") {
          deliveryKm = parseFloat(document.getElementById("delivery-km-input").value || 0);
          const delRes = calculateDeliveryCost(currentMachine, deliveryKm);
          deliveryFee = delRes.totalDelivery;

          document.getElementById("calc-delivery-row").style.display = "flex";
          document.getElementById("calc-delivery-label").innerText = `Lieferung ab Romanshorn (${deliveryKm} km):`;
          document.getElementById("calc-delivery-price").innerText = `+${formatCHF(deliveryFee)}`;

          const detailsEl = document.getElementById("delivery-calc-details");
          if (detailsEl) {
            detailsEl.innerText = `Aufladepauschale CHF ${delRes.loadUnloadFee} + Anfahrt ab Romanshorn (CHF ${delRes.kmAndBaseSubtotal})`;
          }
        } else {
          document.getElementById("calc-delivery-row").style.display = "none";
        }

        const depositVal = parseFloat(currentMachine.deposit) || 500;
        document.getElementById("calc-deposit").innerText = formatCHF(depositVal);

        const finalTotal = pricing.netTotal + accTotal + deliveryFee;
        document.getElementById("calc-total").innerText = formatCHF(finalTotal);
      } catch (err) {
        console.error("updatePriceCalculation error", err);
      }
    }

    function handleStep1Proceed(e) {
      e.preventDefault();
      if (!currentMachine) return;

      const type = document.getElementById("rental-type").value;
      const startDate = parseToIsoDateString(document.getElementById("start-date").value);
      const endDate = parseToIsoDateString(document.getElementById("end-date").value);
      const hoursPerDay = parseInt(document.getElementById("hours-slider").value) || 3;
      const handover = document.getElementById("handover-type").value;
      const deliveryKm = parseFloat(document.getElementById("delivery-km-input")?.value || 0);

      const accIndices = selectedAccessories.map(a => a.idx).join(",");

      const checkoutUrl = `checkout.php?id=${encodeURIComponent(currentMachine.id)}&type=${encodeURIComponent(type)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&hours=${encodeURIComponent(hoursPerDay)}&handover=${encodeURIComponent(handover)}&delivery_km=${encodeURIComponent(deliveryKm)}&acc=${encodeURIComponent(accIndices)}`;

      window.location.href = checkoutUrl;
    }

    function openCurrentMachineLightbox(startIndex = null) {
      const images = (currentMachine && currentMachine.images && currentMachine.images.length > 0) 
        ? currentMachine.images 
        : [((currentMachine && currentMachine.image) ? currentMachine.image : (document.getElementById("main-product-img")?.getAttribute("src") || document.getElementById("main-product-img")?.src || "brand/rent-a-bau-hero.png"))];
      
      let idx = startIndex;
      if (idx === null || idx === undefined) {
        const currentSrc = document.getElementById("main-product-img")?.getAttribute("src") || document.getElementById("main-product-img")?.src;
        const found = images.findIndex(img => currentSrc && (currentSrc.includes(img) || img.includes(currentSrc)));
        idx = found !== -1 ? found : 0;
      }
      const title = (currentMachine && currentMachine.name) ? currentMachine.name : (document.getElementById("product-title")?.innerText || "");
      openImageLightbox(images, idx, title);
    }

    function renderMachineDetails(machine) {
      if (!machine) return;

      document.title = `${machine.name} mieten | Rent A Bau`;
      const titleEl = document.getElementById("product-title");
      if (titleEl) titleEl.innerText = machine.name;

      const catEl = document.getElementById("product-category");
      if (catEl) catEl.innerText = machine.category;

      const descEl = document.getElementById("product-desc");
      if (descEl) descEl.innerText = machine.description || "";

      const badgeEl = document.getElementById("product-badge");
      if (badgeEl) {
        if (machine.badge) {
          badgeEl.innerText = machine.badge;
          badgeEl.style.display = "inline-block";
        } else {
          badgeEl.style.display = "none";
        }
      }

      const images = (machine.images && machine.images.length > 0) ? machine.images : [machine.image || "brand/rent-a-bau-hero.png"];
      const coverImg = machine.image || images[0];

      const mainImg = document.getElementById("main-product-img");
      if (mainImg) {
        mainImg.src = coverImg;
        mainImg.alt = machine.name;
      }

      const thumbsContainer = document.getElementById("gallery-thumbs-container");
      if (thumbsContainer) {
        thumbsContainer.innerHTML = images.map((img, idx) => `
          <img src="${img}" alt="Galerie ${idx}" class="thumb-img has-lightbox-zoom ${img === coverImg ? 'active' : ''}" onclick="document.getElementById('main-product-img').src='${img}'; document.querySelectorAll('.thumb-img').forEach(t=>t.classList.remove('active')); this.classList.add('active');" ondblclick="openCurrentMachineLightbox(${idx})" title="Klicken: Auswählen · Doppelklick: Vollbild">
        `).join("");
      }

      const specsContainer = document.getElementById("product-specs-container");
      if (specsContainer) {
        if (machine.specs && Object.keys(machine.specs).length > 0) {
          specsContainer.innerHTML = Object.entries(machine.specs).map(([k, v]) => `
            <div class="spec-item">
              <strong>${k.toUpperCase()}</strong>
              <span>${v}</span>
            </div>
          `).join("");
        } else {
          specsContainer.innerHTML = `<p style="color: var(--muted); font-size: 14px;">Keine spezifischen Kriterien hinterlegt.</p>`;
        }
      }

      const accessoriesContainer = document.getElementById("product-accessories-container");
      if (accessoriesContainer) {
        const accList = machine.accessories || [];
        if (accList.length > 0) {
          accessoriesContainer.innerHTML = accList.map((acc, idx) => `
            <div class="accessory-item" onclick="toggleAccessorySelection(${idx}, ${acc.price})">
              <div style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" id="acc-check-${idx}" onclick="event.stopPropagation(); toggleAccessorySelection(${idx}, ${acc.price});">
                <strong>${acc.name}</strong>
              </div>
              <span style="color: var(--orange-dark); font-weight: 800;">${acc.price === 0 ? 'Inklusive' : '+CHF ' + acc.price}</span>
            </div>
          `).join("");
        } else {
          accessoriesContainer.innerHTML = `<p style="color: var(--muted); font-size: 14px;">Keine spezifischen Zubehöroptionen für diese Maschine.</p>`;
        }
      }
    }

    document.addEventListener("DOMContentLoaded", async () => {
      const params = new URLSearchParams(window.location.search);
      const rawMId = (params.get("id") || "minibagger-18").trim();
      const cleanMId = rawMId.replace(/-+$/, "");

      const findMachine = () => {
        return MACHINES.find(m => m.id === rawMId) 
            || MACHINES.find(m => m.id === cleanMId)
            || MACHINES.find(m => m.id.replace(/-+$/, "") === cleanMId)
            || MACHINES.find(m => m.id.toLowerCase() === rawMId.toLowerCase())
            || MACHINES[0];
      };

      currentMachine = findMachine();
      if (currentMachine) {
        renderMachineDetails(currentMachine);
      }

      const today = new Date();
      const tomorrow = new Date();
      tomorrow.setDate(today.getDate() + 1);

      const startIso = parseToIsoDateString(today);
      const endIso = parseToIsoDateString(tomorrow);

      document.getElementById("start-date").value = startIso;
      document.getElementById("end-date").value = endIso;

      renderPublicCalendar();
      updatePriceCalculation();

      // Dynamische Daten vom Server holen und ALLES (Details, Galerie, Kalender, Preis) aktualisieren!
      await fetchSettings();
      currentMachine = findMachine();
      if (currentMachine) {
        renderMachineDetails(currentMachine);
      }
      renderPublicCalendar();
      updatePriceCalculation();
    });
  </script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
