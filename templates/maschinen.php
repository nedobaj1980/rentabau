<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mietkatalog - Baumaschinen & Geräte | Rent A Bau</title>
  <meta name="description" content="Übersicht aller verfügbaren Baumaschinen: Bagger, Fahrzeuge, Kleinmaschinen, Abbruch und Transport. Transparente Tarife und direkte Online-Reservation.">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="icon" href="favicon.svg">
  <style>
    /* Explicit 3-Column Side-by-Side Product Grid */
    #public-catalog-grid, .cards-grid, .machine-grid {
      display: grid !important;
      grid-template-columns: repeat(3, 1fr) !important;
      gap: 20px !important;
      width: 100% !important;
    }

    .card {
      border: 1px solid var(--line);
      border-radius: 8px;
      overflow: hidden;
      background: #fff;
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .category-filter-pills {
      display: flex;
      gap: 10px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }
    .category-pill-btn {
      padding: 10px 20px;
      border: 1px solid var(--line);
      border-radius: 30px;
      background: #fff;
      font-weight: 800;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .category-pill-btn:hover {
      border-color: var(--orange);
      background: #fff8f5;
    }
    .category-pill-btn.active {
      background: var(--black);
      color: #fff;
      border-color: var(--black);
    }
    .card-img-compact {
      width: 100%;
      height: 160px;
      object-fit: cover;
    }

    @media (max-width: 900px) {
      #public-catalog-grid, .cards-grid, .machine-grid {
        grid-template-columns: repeat(2, 1fr) !important;
      }
    }
    @media (max-width: 550px) {
      #public-catalog-grid, .cards-grid, .machine-grid {
        grid-template-columns: 1fr !important;
      }
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/navigation.php'; ?>


  <!-- Main Content -->
  <main class="section shell">
    <div class="section-head" style="margin-bottom: 24px;">
      <div>
        <span class="eyebrow">Schweizer Mietkatalog</span>
        <h1>Baumaschinen & Geräte mieten</h1>
        <p>Bagger, Fahrzeuge, Kleinmaschinen & Abbruch. Tagesdeckel max. 5h pro Tag. Faire Abrechnung nach Arbeitstagen (Wochenende mietfrei).</p>
      </div>
      <div class="search-wrap" style="align-self: flex-end;">
        <input type="text" id="catalog-search-input" class="search-input" placeholder="🔍 Maschinen oder Kriterien suchen…" oninput="renderPublicCatalog()">
      </div>
    </div>

    <!-- Dynamic Category Filter Pills (Avesco Rent Style) -->
    <div id="public-category-filter-pills" class="category-filter-pills">
      <!-- Dynamically populated pills -->
    </div>

    <!-- Machine Catalog Cards Grid (Exakt 3 Produkte nebeneinander) -->
    <div id="public-catalog-grid" class="cards-grid">
      <!-- Dynamically populated cards -->
    </div>
  </main>


  <script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/../assets/js/app.js') ?>"></script>
  <script>
    let activePublicCategory = "all";

    function setPublicCategoryFilter(cat) {
      activePublicCategory = cat;
      renderPublicCategoryPills();
      renderPublicCatalog();
    }

    function renderPublicCategoryPills() {
      const pillsContainer = document.getElementById("public-category-filter-pills");
      if (!pillsContainer) return;

      let html = `
        <button class="category-pill-btn ${activePublicCategory === 'all' ? 'active' : ''}" onclick="setPublicCategoryFilter('all')">
          🌟 Alle Produkte (${MACHINES.length})
        </button>
      `;

      CATEGORIES.forEach(cat => {
        const count = MACHINES.filter(m => m.category === cat && m.status === 'online').length;
        html += `
          <button class="category-pill-btn ${activePublicCategory === cat ? 'active' : ''}" onclick="setPublicCategoryFilter('${cat}')">
            ${cat} (${count})
          </button>
        `;
      });

      pillsContainer.innerHTML = html;
    }

    function renderPublicCatalog() {
      const gridContainer = document.getElementById("public-catalog-grid");
      if (!gridContainer) return;

      const searchVal = (document.getElementById("catalog-search-input")?.value || "").toLowerCase();

      const filtered = MACHINES.filter(m => {
        const isOnline = m.status === "online";
        const matchesCategory = activePublicCategory === "all" || m.category === activePublicCategory;
        const matchesSearch = m.name.toLowerCase().includes(searchVal) || 
                              m.category.toLowerCase().includes(searchVal) || 
                              (m.description && m.description.toLowerCase().includes(searchVal));
        return isOnline && matchesCategory && matchesSearch;
      });

      if (filtered.length === 0) {
        gridContainer.innerHTML = `
          <div class="empty-state" style="grid-column: 1 / -1;">
            <span>🔍</span>
            <h3>Keine Baumaschinen in dieser Kategorie</h3>
            <p>Leider passt aktuell kein Mietgerät zu den gewählten Filtern.</p>
          </div>
        `;
        return;
      }

      gridContainer.innerHTML = filtered.map(m => {
        const dayFlatPrice = m.hourlyPrice * (m.maxHoursPerDay || 5);
        return `
          <article class="card">
            <div style="position: relative; cursor: zoom-in;" onclick="openMachineLightboxById('${m.id}')" title="Klicken für Vollbild-Ansicht">
              <img loading="lazy" decoding="async" src="${m.image || 'brand/rent-a-bau-hero.png'}" alt="${m.name}" class="card-img-compact has-lightbox-zoom">
              <span class="zoom-hint-tag">🔍 Vollbild</span>
            </div>
            <div class="card-body" style="padding: 16px;">
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
      renderPublicCategoryPills();
      renderPublicCatalog();
    });
  </script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
