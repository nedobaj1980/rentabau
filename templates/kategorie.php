<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title id="page-title">Kategorie | Rent A Bau</title>
  <meta name="description" content="Baumaschinen und Geräte dieser Kategorie mieten. Transparente Tarife, Tagesdeckel (5h) und kostenlose Wochenenden.">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="icon" href="favicon.svg">
  <style>
    .cat-hero-banner {
      background: linear-gradient(135deg, #18181b 0%, #27272a 100%);
      color: #fff;
      padding: 60px 0;
      border-radius: 12px;
      margin-bottom: 36px;
      position: relative;
      overflow: hidden;
    }
    .cat-hero-banner::after {
      content: "";
      position: absolute;
      right: -50px;
      bottom: -50px;
      width: 250px;
      height: 250px;
      background: radial-gradient(circle, rgba(249, 115, 22, 0.25) 0%, transparent 70%);
      pointer-events: none;
    }
    .cat-spec-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 255, 255, 0.1);
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 800;
      border: 1px solid rgba(255, 255, 255, 0.15);
    }
    #cat-products-grid {
      display: grid !important;
      grid-template-columns: repeat(3, 1fr) !important;
      gap: 20px !important;
      width: 100% !important;
    }
    .card-img-compact {
      width: 100%;
      height: 160px;
      object-fit: cover;
    }
    @media (max-width: 900px) {
      #cat-products-grid {
        grid-template-columns: repeat(2, 1fr) !important;
      }
    }
    @media (max-width: 550px) {
      #cat-products-grid {
        grid-template-columns: 1fr !important;
      }
    }
  </style>
</head>
<body>

  <!-- Header (Clean Standard Public Navigation) -->
  <header class="site-header">
    <div class="shell nav-wrap">
      <a href="index.php" class="brand" aria-label="Rent A Bau Startseite">
        <img src="brand/rent-a-bau-logo.png" alt="Rent A Bau Logo">
      </a>
      <nav>
        <a href="index.php">Startseite</a>
        <a href="maschinen.php" class="active">Maschinen</a>
        <a href="so-funktionierts.php">So funktioniert's</a>
        <a href="kontakt.php">Kontakt & Delivery</a>
      </nav>
      <div class="nav-actions">
        <a href="konto.php" class="text-link">Kundenkonto</a>
        <a href="admin.php" class="button button-small button-dark">Admin</a>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="section shell">

    <div style="margin-bottom: 16px;">
      <a href="maschinen.php" style="color: var(--muted); font-weight: 700; font-size: 14px;">← Zurück zum gesamten Mietkatalog</a>
    </div>

    <!-- Category Dedicated Hero Banner -->
    <div class="cat-hero-banner shell" style="padding-left: 32px; padding-right: 32px;">
      <span class="eyebrow" style="color: var(--orange);">Kategorie-Katalog</span>
      <h1 id="cat-title" style="font-size: clamp(32px, 4vw, 52px); margin: 6px 0 12px; color: #fff;"></h1>
      <p id="cat-subtitle" style="font-size: 18px; color: #d4d4d8; max-width: 650px; margin: 0 0 20px; line-height: 1.5;"></p>
      
      <div id="cat-specs-highlights" style="display: flex; gap: 10px; flex-wrap: wrap;">
        <!-- Dynamically populated spec tags -->
      </div>
    </div>

    <!-- Category Toolbar & Info Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
      <div>
        <h2 style="font-size: 24px; margin: 0;">Verfügbare Mietgeräte (<span id="cat-product-count">0</span>)</h2>
        <p style="color: var(--muted); font-size: 14px; margin: 2px 0 0;">Faire Abrechnung nach Arbeitstagen (Wochenende mietfrei).</p>
      </div>

      <div style="display: flex; gap: 10px; align-items: center;">
        <input type="text" id="cat-search-input" class="search-input" placeholder="🔍 In dieser Kategorie suchen…" oninput="renderCategoryProducts()" style="padding: 8px 14px; width: 220px;">
      </div>
    </div>

    <!-- Category Specific Products Grid (Exakt 3 Produkte nebeneinander) -->
    <div id="cat-products-grid">
      <!-- Dynamically populated cards -->
    </div>

    <!-- Passendes Zubehör & Abbruchwerkzeuge Highlight Banner -->
    <div style="margin-top: 50px; padding: 28px; background: #fff8f5; border: 1px solid #ffd4c0; border-radius: 10px;">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
          <span class="eyebrow" style="color: var(--orange-dark);">Zusatzausrüstung</span>
          <h3 style="font-size: 22px; margin: 4px 0 6px;">🛠️ Passendes Zubehör für diese Kategorie</h3>
          <p style="color: var(--muted); margin: 0; font-size: 14px;">Löffelpakete, Hydraulik-Abbruchhämmer, Transportanhänger & Auffahrrampen direkt mitreservieren.</p>
        </div>
        <a href="kontakt.php" class="button button-outline">Sonderwünsche anfragen →</a>
      </div>
    </div>

  </main>


  <script src="assets/js/app.js"></script>
  <script>
    let currentCategoryName = "Bagger";

    const CATEGORY_INFOS = {
      "Bagger": {
        title: "🏗️ Bagger & Minibagger mieten",
        subtitle: "Kompakte Minibagger und Raupenbagger für Gartenbau, Fundamente, Aushub & Leitungsgräben in der Ostschweiz.",
        specs: ["Gewicht (kg)", "Grabtiefe (m)", "Breite (mm)", "Motorleistung (PS)"]
      },
      "Fahrzeuge": {
        title: "🚜 Fahrzeuge & Dumper mieten",
        subtitle: "Raddumper, Raupendumper & Baustellenfahrzeuge für schnellen Materialtransport von Aushub, Kies und Beton.",
        specs: ["Nutzlast (kg)", "Muldeninhalt (Liter)", "Breite (mm)", "Allrad 4x4"]
      },
      "Kleinmaschinen": {
        title: "⚡ Kleinmaschinen & Verdichtung mieten",
        subtitle: "Rüttelplatten, Stampfer, Schneidetrenner & Verdichtungsgeräte für perfekte Baustellen-Vorbereitung.",
        specs: ["Gewicht (kg)", "Fliehkraft (kN)", "Arbeitsbreite (mm)", "Diesel-Antrieb"]
      },
      "Abbruch": {
        title: "💥 Abbruch & Anbaugeräte mieten",
        subtitle: "Hydraulik-Abbruchhämmer, Abbruchzangen, Sortiergreifer & Meißel für Rückbau und Betonabbruch.",
        specs: ["Einsatzgewicht", "Ölmenge (l/min)", "Arbeitsdruck (bar)", "Trägergerät (t)"]
      },
      "Transport & Anhänger": {
        title: "🚛 Transport & Anhänger mieten",
        subtitle: "Maschinen-Transportanhänger, Alu-Auffahrrampen und Tieflader für den sicheren Transport deiner Baumaschinen.",
        specs: ["Nutzlast (kg)", "Gesamtgewicht (t)", "Ladefläche (m)", "Kupplung"]
      }
    };

    function renderCategoryProducts() {
      const gridContainer = document.getElementById("cat-products-grid");
      if (!gridContainer) return;

      const searchVal = (document.getElementById("cat-search-input")?.value || "").toLowerCase();

      const categoryProducts = MACHINES.filter(m => {
        const isOnline = m.status === "online";
        const matchesCategory = m.category.toLowerCase() === currentCategoryName.toLowerCase();
        const matchesSearch = m.name.toLowerCase().includes(searchVal) || 
                              (m.description && m.description.toLowerCase().includes(searchVal));
        return isOnline && matchesCategory && matchesSearch;
      });

      document.getElementById("cat-product-count").innerText = categoryProducts.length;

      if (categoryProducts.length === 0) {
        gridContainer.innerHTML = `
          <div class="empty-state" style="grid-column: 1 / -1;">
            <span>🚜</span>
            <h3>Keine Produkte in dieser Kategorie</h3>
            <p>Aktuell stehen keine Online-Produkte in der Kategorie "${currentCategoryName}" zur Verfügung.</p>
            <a href="maschinen.php" class="button button-small" style="margin-top: 12px;">Alle Baumaschinen anzeigen</a>
          </div>
        `;
        return;
      }

      gridContainer.innerHTML = categoryProducts.map(m => {
        const dayFlatPrice = m.hourlyPrice * (m.maxHoursPerDay || 5);
        return `
          <article class="card" style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden; background: #fff; display: flex; flex-direction: column;">
            <img src="${m.image || 'brand/rent-a-bau-hero.png'}" alt="${m.name}" class="card-img-compact">
            <div class="card-body" style="padding: 16px; display: flex; flex-direction: column; flex-grow: 1;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span class="category" style="position: static;">${m.category}</span>
                ${m.badge ? `<span class="badge" style="position: static;">${m.badge}</span>` : ''}
              </div>
              <h3 style="margin: 8px 0; font-size: 20px;">${m.name}</h3>
              <p style="color: var(--muted); font-size: 14px; margin-bottom: 16px; line-height: 1.5;">${m.description || ''}</p>

              ${m.specs ? `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; font-size: 12px; background: var(--paper); padding: 8px; border-radius: 4px; margin-bottom: 16px;">
                  ${Object.entries(m.specs).slice(0, 2).map(([k, v]) => `
                    <div><span style="color: var(--muted);">${k.toUpperCase()}:</span> <strong>${v}</strong></div>
                  `).join("")}
                </div>
              ` : ''}

              <div style="display: flex; justify-content: space-between; align-items: flex-end; padding-top: 14px; border-top: 1px solid var(--line); margin-top: auto;">
                <div>
                  <small style="color: var(--muted); display: block;">Tagesdeckel (5h)</small>
                  <strong style="font-size: 19px; color: var(--orange-dark);">${formatCHF(dayFlatPrice)} / Tag</strong>
                  <small style="color: var(--muted); display: block; font-size: 11px;">(${formatCHF(m.hourlyPrice)} / Std.)</small>
                </div>
                <a href="maschine-detail.php?id=${m.id}" class="button button-small">Details & Mieten →</a>
              </div>
            </div>
          </article>
        `;
      }).join("");
    }

    document.addEventListener("DOMContentLoaded", async () => {
      await fetchSettings();

      const params = new URLSearchParams(window.location.search);
      const rawCat = params.get("cat") || "Bagger";
      
      const foundCat = CATEGORIES.find(c => c.toLowerCase() === rawCat.toLowerCase()) || rawCat;
      currentCategoryName = foundCat;

      const info = CATEGORY_INFOS[foundCat] || {
        title: `📂 ${foundCat} mieten`,
        subtitle: `Alle verfügbaren Mietgeräte aus der Kategorie ${foundCat} in der Übersicht.`,
        specs: CATEGORY_SPECS[foundCat] || ["Gewicht", "Leistung", "Abmessungen"]
      };

      document.title = `${info.title} | Rent A Bau`;
      document.getElementById("cat-title").innerText = info.title;
      document.getElementById("cat-subtitle").innerText = info.subtitle;

      const specsContainer = document.getElementById("cat-specs-highlights");
      specsContainer.innerHTML = info.specs.map(s => `
        <span class="cat-spec-pill">✓ ${s}</span>
      `).join("");

      renderCategoryProducts();
    });
  </script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
