<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kasse & Verbindliche Buchung (Schritt 2/2) | Rent A Bau</title>
  <meta name="description" content="Kasse für Baumaschinenmiete bei Rent A Bau. Kontaktdaten eingeben, Zahlungsart wählen (TWINT, Mastercard, IBAN, Barzahlung) und verbindlich bestellen.">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="icon" href="favicon.svg">
  <style>
    .checkout-progress {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 16px;
      margin-bottom: 28px;
      padding: 14px;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 12px;
    }
    .checkout-step {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      font-weight: 800;
      color: var(--muted);
    }
    .checkout-step.completed {
      color: #059669;
    }
    .checkout-step.active {
      color: var(--orange-dark);
    }
    .step-badge {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: var(--paper);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      font-size: 13px;
    }
    .checkout-step.completed .step-badge {
      background: #d1fae5;
      color: #047857;
    }
    .checkout-step.active .step-badge {
      background: var(--orange);
      color: #fff;
    }

    .checkout-grid {
      display: grid;
      grid-template-columns: 1.4fr 1fr;
      gap: 24px;
    }

    /* Payment Option Card Grid Styles */
    .payment-options-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
      margin-top: 10px;
    }
    .payment-option-card {
      border: 2px solid var(--line);
      border-radius: 8px;
      padding: 12px;
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
      top: 10px;
      right: 10px;
    }
    .payment-title {
      font-size: 14px;
      font-weight: 800;
      color: var(--black);
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .payment-sub {
      font-size: 11px;
      color: var(--muted);
      line-height: 1.3;
    }
    .payment-notice-tag {
      font-size: 10px;
      font-weight: 800;
      background: #fef3c7;
      color: #92400e;
      padding: 2px 6px;
      border-radius: 4px;
      display: inline-block;
      margin-top: 4px;
    }

    .summary-card {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.03);
      position: sticky;
      top: 90px;
    }
    .summary-machine-img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid var(--line);
      margin-bottom: 12px;
    }

    @media (max-width: 850px) {
      .checkout-grid { grid-template-columns: 1fr; }
      .payment-options-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/navigation.php'; ?>


  <!-- Main Content -->
  <main class="section shell">
    
    <!-- Step Progress Bar -->
    <div class="checkout-progress">
      <div class="checkout-step completed">
        <span class="step-badge">✓</span>
        <span>1. Termine & Übergabe</span>
      </div>
      <div style="color: var(--line); font-size: 18px;">➔</div>
      <div class="checkout-step active">
        <span class="step-badge">2</span>
        <span>2. Kontaktdaten & Bezahlung (Schritt 2/2)</span>
      </div>
    </div>

    <div class="checkout-grid">
      
      <!-- Left Column: Customer Form & Payment Method Selector -->
      <div>
        <form id="checkout-form" onsubmit="handleCheckoutSubmit(event)">
          
          <!-- 1. Customer Information Box -->
          <div class="portal-card" style="margin-bottom: 20px;">
            <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 20px; border-bottom: 2px solid var(--paper); padding-bottom: 8px;">
              👤 1. Kontaktdaten & Rechnungsadresse
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
              <div>
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Vor- und Nachname *</label>
                <input type="text" id="cust-name" placeholder="z. B. Hans Muster" required style="width: 100%; padding: 10px; font-weight: 700;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Firma (Optional)</label>
                <input type="text" id="cust-company" placeholder="z. B. Muster Bau AG" style="width: 100%; padding: 10px;">
              </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
              <div>
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">E-Mail-Adresse *</label>
                <input type="email" id="cust-email" placeholder="hans.muster@beispiel.ch" required style="width: 100%; padding: 10px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Telefonnummer *</label>
                <input type="tel" id="cust-phone" placeholder="+41 79 123 45 67" required style="width: 100%; padding: 10px; font-weight: 700;">
              </div>
            </div>

            <div style="margin-bottom: 12px;">
              <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Strasse & Hausnummer *</label>
              <input type="text" id="cust-address" placeholder="z. B. Hafenstrasse 12" required style="width: 100%; padding: 10px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px;">
              <div>
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">PLZ *</label>
                <input type="text" id="cust-zip" placeholder="8590" required style="width: 100%; padding: 10px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Ort *</label>
                <input type="text" id="cust-city" placeholder="Romanshorn" required style="width: 100%; padding: 10px;">
              </div>
            </div>
          </div>

          <!-- 2. Swiss Payment Methods Manager Box -->
          <div class="portal-card" style="margin-bottom: 24px;">
            <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 20px; border-bottom: 2px solid var(--paper); padding-bottom: 8px;">
              💳 2. Gewünschte Zahlungsart wählen
            </h3>

            <div class="payment-options-grid">
              
              <div class="payment-option-card selected" id="pay-card-twint" onclick="selectPaymentMethod('twint')">
                <input type="radio" name="paymentMethod" value="twint" id="pay-radio-twint" checked>
                <div class="payment-title">📲 TWINT Pay</div>
                <div class="payment-sub">Schweizer Mobile Pay (QR-Code / App)</div>
              </div>

              <div class="payment-option-card" id="pay-card-bank" onclick="selectPaymentMethod('bank')">
                <input type="radio" name="paymentMethod" value="bank" id="pay-radio-bank">
                <div class="payment-title">🏦 Bankverbindung</div>
                <div class="payment-sub">QR-Rechnung & Überweisung (IBAN)</div>
              </div>

              <div class="payment-option-card" id="pay-card-mastercard" onclick="selectPaymentMethod('mastercard')">
                <input type="radio" name="paymentMethod" value="mastercard" id="pay-radio-mastercard">
                <div class="payment-title">💳 Mastercard / Visa</div>
                <div class="payment-sub">Kreditkarte Online</div>
              </div>

              <div class="payment-option-card" id="pay-card-cash" onclick="selectPaymentMethod('cash')">
                <input type="radio" name="paymentMethod" value="cash" id="pay-radio-cash">
                <div class="payment-title">💵 Barzahlung</div>
                <div class="payment-sub">Bei Abholung / Übergabe vor Ort</div>
                <span id="short-notice-badge" class="payment-notice-tag" style="display: none;">⚡ Empfohlen bei kurzfristiger Miete</span>
              </div>

            </div>

            <!-- Dynamic Payment Instruction Details Box -->
            <div id="payment-instruction-box" style="margin-top: 14px; background: #fff8f5; border: 1px solid #ffd4c0; border-radius: 8px; padding: 12px 14px; font-size: 13px; color: #6b4a39;">
              📲 <strong>TWINT Zahlung:</strong> Du erhältst nach dem Abschluss den QR-Code / Betrag direkt zur Bezahlung.
            </div>
          </div>

          <!-- Final Binding Submit Button -->
          <button type="submit" class="button" style="width: 100%; padding: 16px; font-size: 18px; font-weight: 900; background: var(--orange-dark); color: #fff; text-align: center; border-radius: 8px;">
            🔒 Jetzt zahlungspflichtig bestellen & verbindlich buchen (Schritt 2/2)
          </button>
          
          <p style="text-align: center; font-size: 12px; color: var(--muted); margin-top: 10px;">
            🛡️ Deine Daten werden sicher verschlüsselt übertragen. Preise exkl. MWST.
          </p>

        </form>
      </div>

      <!-- Right Column: Machine & Price Breakdown Summary -->
      <div>
        <div class="summary-card">
          <a id="back-to-machine-link" href="maschinen.php" style="font-size: 12px; font-weight: 800; color: var(--muted); display: block; margin-bottom: 12px;">← Angaben bearbeiten (Schritt 1)</a>

          <img id="summary-img" src="brand/rent-a-bau-hero.png" alt="Maschine" class="summary-machine-img">
          
          <h3 id="summary-title" style="margin: 0 0 4px; font-size: 20px;">Minibagger 1.8 t</h3>
          <p id="summary-category" style="color: var(--orange-dark); font-weight: 800; font-size: 12px; margin: 0 0 16px;">BAGGER</p>

          <div style="background: var(--paper); padding: 12px; border-radius: 8px; border: 1px solid var(--line); margin-bottom: 16px; font-size: 13px;">
            <div style="margin-bottom: 6px;">
              <span style="color: var(--muted);">📅 Mietzeitraum:</span><br>
              <strong id="summary-dates">08.09.2026 bis 11.09.2026</strong>
            </div>
            <div style="margin-bottom: 6px;">
              <span style="color: var(--muted);">⏱️ Verrechnungsdauer:</span><br>
              <strong id="summary-duration">4 Verrechnungstag(e)</strong>
            </div>
            <div>
              <span style="color: var(--muted);">🚚 Übergabeart:</span><br>
              <strong id="summary-handover">Selbstabholung in Romanshorn (TG)</strong>
            </div>
          </div>

          <h4 style="font-size: 14px; margin-bottom: 8px; border-bottom: 1px solid var(--line); padding-bottom: 4px;">Kostenaufstellung</h4>
          
          <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 6px;">
            <span>Maschinen-Mietpreis:</span>
            <span id="summary-gross-price">CHF 0</span>
          </div>

          <div id="summary-discount-row" style="display: flex; justify-content: space-between; font-size: 13px; color: var(--orange-dark); margin-bottom: 6px; display: none;">
            <span id="summary-discount-label">Staffelrabatt:</span>
            <strong id="summary-discount-val">-CHF 0</strong>
          </div>

          <div id="summary-acc-row" style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; display: none;">
            <span>Zubehör / Optionen:</span>
            <span id="summary-acc-price">+CHF 0</span>
          </div>

          <div id="summary-delivery-row" style="display: flex; justify-content: space-between; font-size: 13px; color: #1e40af; margin-bottom: 6px; display: none;">
            <span id="summary-delivery-label">Lieferung ab Romanshorn:</span>
            <strong id="summary-delivery-price">+CHF 0</strong>
          </div>

          <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--muted); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--line);">
            <span>Depot / Kaution:</span>
            <span id="summary-deposit">CHF 0</span>
          </div>

          <div style="display: flex; justify-content: space-between; font-size: 22px; font-weight: 900; color: var(--black);">
            <span>Gesamttotal:</span>
            <span id="summary-total" style="color: var(--orange-dark);">CHF 0</span>
          </div>

        </div>
      </div>

    </div>

  </main>


  <script src="assets/js/app.js"></script>
  <script>
    let checkoutData = {
      machine: null,
      rentalType: "tag",
      startDate: "",
      endDate: "",
      hoursPerDay: 5,
      handover: "abholung",
      deliveryKm: 0,
      accessories: [],
      pricing: null,
      deliveryRes: null,
      selectedPayment: "twint"
    };

    function selectPaymentMethod(payKey) {
      checkoutData.selectedPayment = payKey;
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

    function formatDateDE(isoStr) {
      if (!isoStr) return "";
      const normalized = parseToIsoDateString(isoStr);
      const parts = normalized.split("-");
      if (parts.length === 3) return `${parts[2]}.${parts[1]}.${parts[0]}`;
      return isoStr;
    }

    async function initCheckout() {
      await fetchSettings();

      const rawMId = (params.get("id") || "minibagger-18").trim();
      const cleanMId = rawMId.replace(/-+$/, "");
      
      const machine = MACHINES.find(m => m.id === rawMId) 
                   || MACHINES.find(m => m.id === cleanMId)
                   || MACHINES.find(m => m.id.replace(/-+$/, "") === cleanMId)
                   || MACHINES.find(m => m.id.toLowerCase() === rawMId.toLowerCase())
                   || MACHINES[0];
      checkoutData.machine = machine;

      checkoutData.rentalType = params.get("type") || "tag";
      
      const today = new Date();
      const tomorrow = new Date();
      tomorrow.setDate(today.getDate() + 1);

      checkoutData.startDate = parseToIsoDateString(params.get("start_date")) || parseToIsoDateString(today);
      checkoutData.endDate = parseToIsoDateString(params.get("end_date")) || parseToIsoDateString(tomorrow);
      checkoutData.hoursPerDay = parseInt(params.get("hours")) || 5;
      checkoutData.handover = params.get("handover") || "abholung";
      checkoutData.deliveryKm = parseFloat(params.get("delivery_km")) || 0;

      // Parse accessories indices
      const accStr = params.get("acc");
      if (accStr && machine.accessories) {
        const accIndices = accStr.split(",").map(n => parseInt(n)).filter(n => !isNaN(n));
        checkoutData.accessories = accIndices.map(idx => machine.accessories[idx]).filter(Boolean);
      }

      // Back to machine edit link
      document.getElementById("back-to-machine-link").href = `maschine-detail.php?id=${machine.id}`;

      // Display machine summary
      document.getElementById("summary-img").src = machine.image || "brand/rent-a-bau-hero.png";
      document.getElementById("summary-title").innerText = machine.name;
      document.getElementById("summary-category").innerText = machine.category.toUpperCase();

      document.getElementById("summary-dates").innerText = `${formatDateDE(checkoutData.startDate)} bis ${formatDateDE(checkoutData.endDate)}`;

      // Calculate pricing
      const pricing = calculateRentalPrice(machine, {
        type: checkoutData.rentalType,
        startDate: checkoutData.startDate,
        endDate: checkoutData.endDate,
        hoursPerDay: checkoutData.hoursPerDay
      });
      checkoutData.pricing = pricing;

      document.getElementById("summary-duration").innerText = `${pricing.billableDays} Verrechnungstag(e) (${pricing.freeWeekendDays} WE-Tage frei)`;
      document.getElementById("summary-gross-price").innerText = formatCHF(pricing.grossTotal);

      if (pricing.discountPercent > 0) {
        document.getElementById("summary-discount-row").style.display = "flex";
        document.getElementById("summary-discount-label").innerText = `Staffelrabatt (${pricing.discountPercent}%):`;
        document.getElementById("summary-discount-val").innerText = `-${formatCHF(pricing.discountAmount)}`;
      }

      const accTotal = checkoutData.accessories.reduce((sum, a) => sum + (parseFloat(a.price) || 0), 0);
      if (accTotal > 0) {
        document.getElementById("summary-acc-row").style.display = "flex";
        document.getElementById("summary-acc-price").innerText = `+${formatCHF(accTotal)}`;
      }

      let deliveryFee = 0;
      if (checkoutData.handover === "lieferung") {
        const delRes = calculateDeliveryCost(machine, checkoutData.deliveryKm);
        deliveryFee = delRes.totalDelivery;
        checkoutData.deliveryRes = delRes;

        document.getElementById("summary-handover").innerText = `🚚 Lieferung an Baustelle (${checkoutData.deliveryKm} km ab Romanshorn)`;
        document.getElementById("summary-delivery-row").style.display = "flex";
        document.getElementById("summary-delivery-label").innerText = `Lieferung ab Romanshorn (${checkoutData.deliveryKm} km):`;
        document.getElementById("summary-delivery-price").innerText = `+${formatCHF(deliveryFee)}`;
      } else {
        document.getElementById("summary-handover").innerText = `🏬 Selbstabholung in Romanshorn (TG)`;
      }

      document.getElementById("summary-deposit").innerText = formatCHF(machine.deposit);

      const finalTotal = pricing.netTotal + accTotal + deliveryFee;
      document.getElementById("summary-total").innerText = formatCHF(finalTotal);

      // Short Notice badge check
      const isShortNotice = isShortNoticeRental(checkoutData.startDate, 3);
      const shortBadge = document.getElementById("short-notice-badge");
      if (shortBadge) {
        shortBadge.style.display = isShortNotice ? "inline-block" : "none";
      }

      selectPaymentMethod("twint");
    }

    async function handleCheckoutSubmit(e) {
      e.preventDefault();

      const name = document.getElementById("cust-name").value;
      const company = document.getElementById("cust-company").value;
      const email = document.getElementById("cust-email").value;
      const phone = document.getElementById("cust-phone").value;
      const address = document.getElementById("cust-address").value;
      const zip = document.getElementById("cust-zip").value;
      const city = document.getElementById("cust-city").value;

      const accTotal = checkoutData.accessories.reduce((sum, a) => sum + (parseFloat(a.price) || 0), 0);
      const deliveryFee = checkoutData.deliveryRes ? checkoutData.deliveryRes.totalDelivery : 0;
      const total = checkoutData.pricing.netTotal + accTotal + deliveryFee;

      const payLabels = {
        twint: "📲 TWINT Mobile Pay",
        bank: "🏦 Banküberweisung / QR-Rechnung",
        mastercard: "💳 Mastercard / Kreditkarte",
        cash: "💵 Barzahlung bei Abholung / Übergabe"
      };

      const reservationPayload = {
        machineId: checkoutData.machine.id,
        machineName: checkoutData.machine.name,
        rentalType: checkoutData.rentalType,
        startDate: checkoutData.startDate,
        endDate: checkoutData.endDate,
        days: checkoutData.pricing.billableDays,
        hoursPerDay: checkoutData.hoursPerDay,
        handover: checkoutData.handover,
        deliveryKm: checkoutData.deliveryKm,
        deliveryFee: deliveryFee,
        paymentMethod: checkoutData.selectedPayment,
        paymentLabel: payLabels[checkoutData.selectedPayment] || checkoutData.selectedPayment,
        customerName: name,
        company: company,
        email: email,
        phone: phone,
        address: `${address}, ${zip} ${city}`,
        discountPercent: checkoutData.pricing.discountPercent,
        totalPrice: total,
        status: "gebucht"
      };

      await saveReservation(reservationPayload);
      alert(`🎉 Vielen Dank, ${name}!\n\nDeine verbindliche Buchung für den "${checkoutData.machine.name}" mit Zahlungsart "${payLabels[checkoutData.selectedPayment]}" wurde erfolgreich abgeschlossen. Wir senden dir deine Buchungsbestätigung und Rechnung an ${email}.`);
      window.location.href = "konto.php";
    }

    document.addEventListener("DOMContentLoaded", initCheckout);
  </script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
