<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rent A Bau | Baumaschinen mieten in der Ostschweiz</title>
  <meta name="description" content="Baumaschinen für Private und Gewerbe in der Ostschweiz. Einfach reservieren, selbst abholen oder liefern lassen.">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="icon" href="favicon.svg">
  <link rel="stylesheet" href="assets/css/slider.css?v=<?= filemtime(__DIR__ . '/../assets/css/slider.css') ?>">
</head>
<body>
<?php include __DIR__ . '/../includes/navigation.php'; ?>


  <!-- Hero Section -->
  <?php include __DIR__ . '/../includes/hero-slider.php'; ?>
  <script src="assets/js/slider.js?v=<?= filemtime(__DIR__ . '/../assets/js/slider.js') ?>" defer></script>

  <!-- Trust Strip -->
  <section class="trust-strip">
    <div class="shell trust-grid">
      <div><strong>Ostschweiz</strong><span>regional verfügbar</span></div>
      <div><strong>Privat & Gewerbe</strong><span>für jedes Projekt</span></div>
      <div><strong>Abholung & Lieferung</strong><span>du entscheidest</span></div>
      <div><strong>Direkter Kontakt</strong><span>schnell und unkompliziert</span></div>
    </div>
  </section>

  <!-- Featured Machine Park Teaser -->
  <section class="section shell" id="maschinen">
    <div class="section-head">
      <div>
        <span class="eyebrow">Maschinenpark</span>
        <h2>Starkes Gerät für dein Projekt</h2>
      </div>
      <p>Alle Maschinen stehen zur Miete bereit. Alle Preise verstehen sich exklusive MWST und Treibstoff.</p>
    </div>

    <div class="machine-grid" id="home-machine-grid">
      <!-- Dynamically filled by JS -->
    </div>
    
    <div style="text-align: center; margin-top: 40px;">
      <a href="maschinen.php" class="button button-dark">Gesamten Maschinenkatalog anzeigen →</a>
    </div>
  </section>

  <!-- Ablauf / Steps -->
  <section class="dark-section" id="ablauf">
    <div class="shell section">
      <div class="section-head inverse">
        <div>
          <span class="eyebrow">Einfach mieten</span>
          <h2>In drei Schritten zur Maschine</h2>
        </div>
      </div>
      <div class="steps">
        <article>
          <span>01</span>
          <h3>Maschine wählen</h3>
          <p>Passendes Gerät auswählen und gewünschten Zeitraum angeben.</p>
        </article>
        <article>
          <span>02</span>
          <h3>Bestätigung erhalten</h3>
          <p>Wir prüfen Verfügbarkeit, Zubehör und den genauen Preis.</p>
        </article>
        <article>
          <span>03</span>
          <h3>Loslegen</h3>
          <p>Maschine abholen oder termingerecht auf die Baustelle liefern lassen.</p>
        </article>
      </div>
      <div class="how-more"><a class="button button-outline" href="so-funktionierts.php">Detaillierte Erklärung →</a></div>
    </div>
  </section>

  <!-- Split Section Delivery -->
  <section class="section shell split" id="lieferung">
    <div class="split-image">
      <img src="brand/rent-a-bau-hero.png" alt="Baumaschinenlieferung in der Ostschweiz">
    </div>
    <div class="split-copy">
      <span class="eyebrow">Flexibel übergeben</span>
      <h2>Abholen oder liefern lassen</h2>
      <p>Du kannst deine Maschine selbst übernehmen oder bequem auf die Baustelle liefern lassen. Die Lieferkosten berechnen wir transparent nach Distanz und Maschine.</p>
      <ul>
        <li>Persönliche Einweisung bei Übergabe</li>
        <li>Passendes Zubehör auf Anfrage</li>
        <li>Flexible Rückgabe nach Vereinbarung</li>
      </ul>
      <a class="button button-dark" href="maschinen.php">Jetzt Maschine wählen</a>
    </div>
  </section>

  <script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/../assets/js/app.js') ?>"></script>
  <script>
    document.addEventListener("DOMContentLoaded", async () => {
      await fetchSettings();
      const grid = document.getElementById("home-machine-grid");
      if (grid) {
        const visibleMachines = MACHINES.filter(m => m.status === "online");
        grid.innerHTML = visibleMachines.map(m => {
          const dayPrice = (parseFloat(m.hourlyPrice) || 0) * (parseInt(m.maxHoursPerDay) || 5);
          return `
          <article class="machine-card">
            <div class="machine-image has-lightbox-zoom" style="position: relative; cursor: zoom-in;" onclick="openMachineLightboxById('${m.id}')" title="Klicken für Vollbild-Ansicht">
              <img loading="lazy" decoding="async" src="${m.image || 'brand/rent-a-bau-hero.png'}" alt="${m.name}">
              ${m.badge ? `<span class="badge">${m.badge}</span>` : ''}
              <span class="category">${m.category}</span>
              <span class="zoom-hint-tag">🔍 Vollbild</span>
            </div>
            <div class="machine-body">
              <h3><a href="maschine-detail.php?id=${m.id}" style="color: inherit; text-decoration: none;">${m.name}</a></h3>
              <p>${m.description || ''}</p>
              <div class="price-row">
                <div>
                  <strong>${formatCHF(dayPrice)}</strong>
                  <span> / Tag</span>
                </div>
                <a href="maschine-detail.php?id=${m.id}" class="button button-dark">Verfügbarkeit prüfen</a>
              </div>
            </div>
          </article>
        `;
        }).join("");
      }
    });
  </script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
