<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard & Zahlungsarten | Rent A Bau</title>
  <meta name="description" content="Kategorien-Verwaltung, Zahlungsarten (TWINT, Mastercard, IBAN, Barzahlung), Produktspezifische Lieferkosten & Staffelpreise, Belegungskalender, Produkte & Tarife.">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="stylesheet" href="assets/css/category-templates.css?v=<?= filemtime(__DIR__ . '/../assets/css/category-templates.css') ?>">
  <meta name="category-csrf" content="<?= e(csrfToken()) ?>">
  <link rel="icon" href="favicon.svg">
  <style>
    .admin-menu {
      position: sticky;
      top: 0;
      z-index: 20;
      display: flex;
      flex-wrap: nowrap;
      align-items: center;
      gap: 6px;
      margin: 0 0 28px;
      padding: 10px;
      background: rgba(255, 255, 255, .97);
      border: 1px solid var(--line);
      border-radius: 10px;
      box-shadow: 0 8px 24px rgba(0,0,0,.07);
      overflow-x: auto;
      scrollbar-width: thin;
      -webkit-overflow-scrolling: touch;
    }
    .admin-menu::-webkit-scrollbar {
      height: 5px;
    }
    .admin-menu::-webkit-scrollbar-track {
      background: transparent;
    }
    .admin-menu::-webkit-scrollbar-thumb {
      background: var(--line);
      border-radius: 4px;
    }
    .admin-menu-title { padding: 0 10px 0 4px; color: var(--muted); font-size: 11px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; white-space: nowrap; }
    .admin-menu .admin-tab-btn, .admin-menu-link { flex: 0 0 auto; min-height: 38px; padding: 8px 12px; border: 1px solid transparent; border-radius: 7px; background: transparent; color: var(--black); font-size: 13px; font-weight: 800; text-decoration: none; white-space: nowrap; cursor: pointer; }
    .admin-menu .admin-tab-btn:hover, .admin-menu-link:hover { background: var(--paper); border-color: var(--line); }
    .admin-menu .admin-tab-btn.active { background: var(--black); color: #fff; border-color: var(--black); }
    .admin-menu-spacer { flex: 1 0 12px; }
    .admin-menu-link { color: var(--orange-dark); }
    @media (max-width: 700px) { .admin-menu { position: sticky; top: 0; margin-inline: -8px; border-radius: 0; } .admin-menu-title, .admin-menu-spacer { display: none; } }
    .admin-nav-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 24px;
      border-bottom: 2px solid var(--line);
      padding-bottom: 12px;
      flex-wrap: wrap;
    }
    .admin-tab-btn {
      padding: 10px 18px;
      border: 1px solid var(--line);
      border-radius: 6px;
      background: var(--paper);
      font-weight: 800;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .admin-tab-btn.active {
      background: var(--black);
      color: #fff;
      border-color: var(--black);
    }
    .product-detail-card {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 24px;
      margin-bottom: 28px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
    .product-detail-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 14px;
      margin-bottom: 20px;
      border-bottom: 2px solid var(--paper);
    }
    .form-section-title {
      font-size: 14px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--orange-dark);
      margin: 16px 0 10px;
      padding-bottom: 4px;
      border-bottom: 1px dashed var(--line);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .grid-4 {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
    }
    .grid-3 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
    }
    .grid-2 {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 14px;
    }
    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 14px;
      margin-top: 10px;
    }
    .gallery-card {
      border: 2px solid var(--line);
      border-radius: 8px;
      padding: 8px;
      background: var(--paper);
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
    }
    .gallery-card.is-cover {
      border-color: #f59e0b;
      background: #fffdf5;
      box-shadow: 0 0 10px rgba(245, 158, 11, 0.2);
    }
    .gallery-card img {
      width: 100%;
      height: 100px;
      object-fit: cover;
      border-radius: 4px;
    }
    .cover-badge {
      position: absolute;
      top: 12px;
      left: 12px;
      background: #f59e0b;
      color: #fff;
      font-size: 10px;
      font-weight: 900;
      padding: 2px 6px;
      border-radius: 4px;
    }
    .admin-calendar-wrapper {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 20px;
    }
    .admin-cal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
      flex-wrap: wrap;
      gap: 16px;
    }
    .admin-machine-preview-card {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 10px 16px;
      background: #fff8f5;
      border: 1px solid #ffd4c0;
      border-radius: 8px;
    }
    .admin-machine-preview-img {
      width: 60px;
      height: 45px;
      border-radius: 6px;
      object-fit: cover;
      border: 1px solid var(--line);
    }
    .admin-cal-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 8px;
    }
    .admin-cal-day-name {
      text-align: center;
      font-weight: 900;
      font-size: 12px;
      color: var(--muted);
      padding: 8px 0;
      background: var(--paper);
      border-radius: 4px;
    }
    .admin-cal-cell {
      min-height: 95px;
      border: 1px solid var(--line);
      border-radius: 6px;
      padding: 8px;
      background: #fff;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
    }
    .admin-cal-cell:hover {
      border-color: var(--orange);
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .admin-cal-cell.empty {
      background: transparent;
      border: none;
      cursor: default;
    }
    .admin-cal-cell.is-blocked {
      background: #fef2f2;
      border-color: #fca5a5;
    }
    .admin-cal-cell.is-reserved {
      background: #eff6ff;
      border-color: #93c5fd;
    }
    .admin-cal-cell.is-pending {
      background: #fffbebfb;
      border-color: #fde68a;
    }
    .admin-cal-cell-num {
      font-weight: 800;
      font-size: 14px;
    }
    .admin-cal-cell-price {
      font-size: 11px;
      font-weight: 900;
      color: var(--orange-dark);
      margin-top: 2px;
    }
    .admin-cal-cell-badge {
      font-size: 10px;
      font-weight: 800;
      padding: 2px 4px;
      border-radius: 3px;
      line-height: 1.2;
      word-break: break-word;
    }
    .admin-cal-cell-badge.blocked {
      background: #fee2e2;
      color: #991b1b;
    }
    .admin-cal-cell-badge.reserved {
      background: #dbeafe;
      color: #1e40af;
    }
    .admin-cal-cell-badge.pending {
      background: #fef3c7;
      color: #92400e;
    }
    .admin-cal-cell-badge.free {
      background: #ecfdf5;
      color: #047857;
    }
    .category-card {
      display: flex;
      flex-direction: column;
      gap: 10px;
      padding: 20px;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.02);
      transition: all 0.2s ease;
    }
    .category-card:hover {
      border-color: var(--orange);
      box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    }
    .spec-tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #ffedd5;
      color: #c2410c;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 800;
    }
    .url-badge-pill {
      font-size: 11px;
      font-weight: 800;
      background: #eff6ff;
      color: #1d4ed8;
      padding: 4px 10px;
      border-radius: 20px;
      border: 1px solid #bfdbfe;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
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
    @media (max-width: 800px) {
      .grid-4, .grid-3, .grid-2 { grid-template-columns: 1fr; }
      .admin-cal-cell { min-height: 70px; padding: 4px; }
    }
  </style>
</head>
<body class="portal-page">
<?php include __DIR__ . '/../includes/navigation.php'; ?>

  <!-- Admin-Navigation direkt unter der Hauptnavigation -->
  <div class="shell admin-menu-top">
    <nav class="admin-menu" id="admin-main-nav-tabs" aria-label="Admin-Bereiche">
      <span class="admin-menu-title">Verwaltung</span>
      <button class="admin-tab-btn active" id="btn-tab-calendar" onclick="showAdminTab('calendar')"><span aria-hidden="true">📅</span> Kalender</button>
      <button class="admin-tab-btn" id="btn-tab-reservations" onclick="showAdminTab('reservations')"><span aria-hidden="true">📋</span> Reservationen</button>
      <button class="admin-tab-btn" id="btn-tab-products" onclick="showAdminTab('products', 'all')"><span aria-hidden="true">🛠️</span> Produkte</button>
      <button class="admin-tab-btn" id="btn-tab-categories" onclick="showAdminTab('categories')"><span aria-hidden="true">📂</span> Kategorien</button>
      <button class="admin-tab-btn" id="btn-tab-delivery" onclick="showAdminTab('delivery')"><span aria-hidden="true">🚚</span> Transport</button>
      <button class="admin-tab-btn" id="btn-tab-payments" onclick="showAdminTab('payments')"><span aria-hidden="true">💳</span> Zahlungen</button>
      <button class="admin-tab-btn" id="btn-tab-discounts" onclick="showAdminTab('discounts')"><span aria-hidden="true">🏷️</span> Rabatte</button>
      <button class="admin-tab-btn" id="btn-tab-audit" onclick="showAdminTab('audit')"><span aria-hidden="true">🧾</span> Aktivitätsprotokoll</button>
      <span class="admin-menu-spacer"></span>
      <a class="admin-menu-link" href="admin-finance.php">Finanzen &amp; Belege</a>
      <a class="admin-menu-link" href="admin-slider.php">Homepage-Slider</a>
      <a class="admin-menu-link" href="index.php" target="_blank" rel="noopener">Website ↗</a>
    </nav>
  </div>

  <!-- Content -->
  <main class="portal-shell shell">
    <div class="portal-heading">
      <div>
        <span class="eyebrow">Administration</span>
        <h1>Rent A Bau Verwaltung</h1>
        <p>Zahlungsarten (TWINT, Mastercard, IBAN, Barzahlung), Transport & Belegungskalender.</p>
      </div>
      <div style="display: flex; gap: 10px; align-items: center;">
        <span id="current-url-display-pill" class="url-badge-pill">🔗 Link: admin.php#calendar</span>
        <span class="status-pill warning">Admin-Modus aktiv</span>
      </div>
    </div>

    <!-- Live Statistics -->
    <div class="stats">
      <article>
        <span>Offene Anfragen</span>
        <strong id="stat-pending">0</strong>
      </article>
      <article>
        <span>Bestätigte Vermietungen</span>
        <strong id="stat-active">0</strong>
      </article>
      <article>
        <span>Maschinen Online</span>
        <strong id="stat-online-count">0</strong>
      </article>
      <article>
        <span>Geschätzter Umsatz</span>
        <strong id="stat-revenue">CHF 0</strong>
      </article>
    </div>

    <!-- Tab 1: Master Interactive Admin Calendar -->
    <div id="tab-sec-calendar">
      <div class="product-detail-card">
        <div class="admin-cal-header">
          <div>
            <h2 style="margin: 0 0 4px;">📅 Master Belegungskalender</h2>
            <p style="color: var(--muted); margin: 0; font-size: 14px;">Visuelle Übersicht aller Belegungen mit Tagespreisen und Produktbildern. Klicke auf einen Tag zum Sperren.</p>
          </div>

          <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
            <div>
              <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Baumaschine auswählen:</label>
              <select id="admin-cal-machine-select" onchange="renderAdminMasterCalendar()" style="padding: 10px 14px; font-weight: 800; border-radius: 6px; border: 1px solid var(--line); font-size: 14px;">
              </select>
            </div>

            <div id="admin-machine-preview-card" class="admin-machine-preview-card">
              <img id="admin-preview-img" src="brand/rent-a-bau-hero.png" alt="Maschinen Cover" class="admin-machine-preview-img">
              <div>
                <strong id="admin-preview-title" style="font-size: 15px; color: var(--black); display: block;">Minibagger 1.8 t</strong>
                <small id="admin-preview-price" style="color: var(--orange-dark); font-weight: 800; font-size: 13px;">CHF 225 / Tag (CHF 45/h)</small>
              </div>
            </div>

            <div style="display: flex; gap: 4px;">
              <button type="button" class="button button-small button-outline" onclick="changeAdminCalMonth(-1)">‹ Vorheriger</button>
              <button type="button" class="button button-small button-outline" onclick="changeAdminCalMonth(1)">Nächster ›</button>
            </div>
          </div>
        </div>

        <div class="calendar-legend" style="margin-bottom: 16px;">
          <span><span class="legend-dot free"></span> Frei (inkl. Tagespreis)</span>
          <span><span class="legend-dot reserved" style="background:#3b82f6;"></span> Bestätigte Buchung</span>
          <span><span class="legend-dot reserved" style="background:#f59e0b;"></span> Offene Anfrage</span>
          <span><span class="legend-dot reserved" style="background:#ef4444;"></span> Admin-Sperre / Wartung</span>
        </div>

        <div id="admin-master-calendar-container" class="admin-calendar-wrapper">
        </div>
      </div>
    </div>

    <!-- Tab 2: Dynamic Categories Overview -->
    <div id="tab-sec-categories" style="display: none;">
      <div id="categories-overview-view">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
          <div>
            <h2 style="margin: 0 0 4px;">📂 Kategorien-Übersicht & Detail-Seiten</h2>
            <p style="color: var(--muted); margin: 0; font-size: 14px;">Wähle eine Kategorie aus, um deren eigene Admin-Seite zu öffnen und Kriterien anzupassen.</p>
          </div>
          <button type="button" class="button button-small" onclick="createNewCategoryPrompt()">＋ Neue Kategorie anlegen</button>
        </div>

        <div id="categories-grid-cards-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        </div>
      </div>

      <div id="category-dedicated-editor-view" style="display: none;">
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
          <button class="button button-outline" onclick="closeCategoryDedicatedEditor()">← Zurück zur Kategorien-Übersicht</button>
          <span style="font-size: 14px; color: var(--orange-dark); font-weight: 800;" id="dedicated-cat-badge">⚙️ Kategorie-Einstellungen</span>
        </div>

        <div id="category-dedicated-editor-container">
        </div>
      </div>
    </div>

    <!-- Tab 3: Products -->
    <div id="tab-sec-products" style="display: none;">
      <div id="admin-products-list-view">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; gap: 16px; flex-wrap: wrap;">
          <div>
            <h2 id="admin-cat-section-title" style="margin: 0 0 6px;">Produkt-Katalog & Zubehör-Verwaltung</h2>
            <p id="admin-cat-section-subtitle" style="color: var(--muted); margin: 0;">Lade Bilder hoch, füge passendes Zubehör hinzu und verknüpfe ähnliche Baumaschinen.</p>
          </div>
          <div style="display: flex; gap: 10px; align-items: center;">
            <input type="text" id="admin-product-search" class="search-input" placeholder="🔍 Maschine suchen…" oninput="renderAdminProductsTable()" style="width: 220px; padding: 8px 14px;">
            <button class="button" onclick="showCreateMachineForm()" style="background: var(--orange-dark);">＋ Neues Produkt anlegen</button>
          </div>
        </div>

        <div id="create-machine-card" class="product-detail-card" style="display: none; background: #f0fdf4; border: 2px solid #4ade80;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin: 0; color: #166534; font-size: 20px;">✨ Neue Baumaschine mit Zubehör anlegen</h3>
            <button type="button" onclick="document.getElementById('create-machine-card').style.display='none'" style="border:0; background:transparent; font-size: 20px; cursor:pointer;">✕</button>
          </div>

          <form onsubmit="handleCreateNewMachine(event)">
            <div class="form-section-title" style="color: #166534;">1. Produkt-Stammdaten & Transport</div>
            <div class="grid-4" style="margin-bottom: 14px;">
              <div>
                <label style="font-size: 12px; font-weight: 800;">Maschinen-Name *</label>
                <input type="text" id="new-m-name" placeholder="z. B. Abbruchhammer 2.5 t" required style="width: 100%; padding: 8px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Kategorie *</label>
                <select id="new-m-cat" onchange="onNewMachineCategoryChange(this.value)" required style="width: 100%; padding: 8px; font-weight: 800;">
                </select>
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Aufladepauschale (CHF) *</label>
                <input type="number" id="new-m-loadfee" value="40" min="0" required style="width: 100%; padding: 8px; font-weight: 800;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Transport-Klasse *</label>
                <select id="new-m-transclass" required style="width: 100%; padding: 8px; font-weight: 800;">
                  <option value="leicht">Leichttransport (Anhänger / Bus - 1.0x)</option>
                  <option value="mittel" selected>Mitteltransport (3.5t Anhänger - 1.4x)</option>
                  <option value="schwer">Schwertransport (LKW / Tieflader - 2.2x)</option>
                </select>
              </div>
            </div>

            <div class="grid-2" style="margin-bottom: 14px;">
              <div>
                <label style="font-size: 12px; font-weight: 800;">Badge / Kennzeichnung (optional)</label>
                <input type="text" id="new-m-badge" placeholder="z. B. Neu, Beliebt, Bestseller (oder leer lassen)" style="width: 100%; padding: 8px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Status *</label>
                <select id="new-m-status" required style="width: 100%; padding: 8px; font-weight: 800;">
                  <option value="online" selected>🟢 Online & Buchbar</option>
                  <option value="maintenance">🟡 In Wartung / Gesperrt</option>
                </select>
              </div>
            </div>

            <div style="margin-bottom: 16px; padding: 16px; background: #fff; border: 1px solid #bbf7d0; border-radius: 6px;">
              <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 6px;">Mehrere Produktbilder hochladen</label>
              <input type="file" id="new-m-files-input" accept="image/*" multiple onchange="uploadMultipleImagesForNewMachine(this)" style="margin-bottom: 8px;">
              <div id="new-m-gallery-preview" class="gallery-grid">
                <div class="gallery-card is-cover">
                  <span class="cover-badge">⭐ COVER</span>
                  <img src="brand/rent-a-bau-hero.png" alt="Hero Standard">
                </div>
              </div>
            </div>

            <div style="margin-bottom: 16px;">
              <label style="font-size: 12px; font-weight: 800;">Beschreibungstext</label>
              <textarea id="new-m-desc" rows="2" placeholder="Kurze Beschreibung..." style="width: 100%; padding: 8px;"></textarea>
            </div>

            <div class="form-section-title" style="color: #166534;">2. Tarife & Kaution</div>
            <div class="grid-4" style="margin-bottom: 16px;">
              <div>
                <label style="font-size: 12px; font-weight: 800;">Stundentarif (CHF/Std.) *</label>
                <input type="number" id="new-m-price" placeholder="65" required style="width: 100%; padding: 8px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Tagesdeckel (Max. Std./Tag)</label>
                <input type="number" id="new-m-cap" value="5" min="1" max="12" required style="width: 100%; padding: 8px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Wochenendtarif (CHF)</label>
                <input type="number" id="new-m-weekend" placeholder="580" style="width: 100%; padding: 8px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Kaution / Depot (CHF) *</label>
                <input type="number" id="new-m-depot" placeholder="1000" required style="width: 100%; padding: 8px;">
              </div>
            </div>

            <div class="form-section-title" style="color: #166534;">
              <span>3. Technische Spezifikationen & Kriterien</span>
              <button type="button" class="button button-small button-outline" onclick="addNewSpecRowToNewMachineForm()">＋ Weiteres Kriterium</button>
            </div>

            <div id="new-m-specs-dynamic-container" class="grid-4" style="margin-bottom: 20px;">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 14px; border-top: 1px dashed #bbf7d0;">
              <button type="button" class="button button-outline" onclick="document.getElementById('create-machine-card').style.display='none'">Abbrechen</button>
              <button type="submit" class="button" style="background: #166534;">🚀 Neues Produkt im Mietkatalog veröffentlichen</button>
            </div>
          </form>
        </div>

        <div class="product-detail-card">
          <div id="admin-products-table-container">
          </div>
        </div>
      </div>

      <div id="admin-single-product-editor-view" style="display: none;">
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
          <button class="button button-outline" onclick="closeSingleProductEditor()">← Zurück zur Produktübersicht</button>
          <span style="font-size: 14px; color: var(--orange-dark); font-weight: 800;">🛠️ Produkt, Zubehör & Galerie bearbeiten</span>
        </div>

        <div id="single-product-editor-container">
        </div>
      </div>
    </div>

    <!-- Tab 4: Delivery -->
    <div id="tab-sec-delivery" style="display: none;">
      <div class="product-detail-card">
        <div class="location-notice-box">
          <span style="font-size: 24px;">📍</span>
          <div>
            <strong style="color: #1e40af; font-size: 16px;">Standort-Hinweis für Transport & Lieferung:</strong>
            <p style="margin: 2px 0 0; color: #1e3a8a; font-size: 14px;">Alle Kilometer-Berechnungen und Anfahrts-Routen erfolgen exakt ab Standort <strong>Romanshorn (TG)</strong>.</p>
          </div>
        </div>

        <div class="product-detail-header">
          <div>
            <h2 style="margin: 0 0 4px;">🚚 Transport-Tool: Lieferkosten, Aufladepauschalen & KM-Staffel</h2>
            <p style="color: var(--muted); margin: 0; font-size: 14px;">Stelle Grundpauschalen, KM-Staffeln ab Romanshorn und individuelle Aufladepauschalen pro Produkt ein.</p>
          </div>
        </div>

        <form id="delivery-config-form" onsubmit="handleSaveDeliveryConfig(event)">
          <div class="form-section-title">1. Standort & Anfahrts-Grundpauschale</div>
          <div class="grid-2" style="margin-bottom: 20px;">
            <div>
              <label style="font-size: 12px; font-weight: 800;">Standort für Kilometer-Berechnung</label>
              <input type="text" id="del-base-location" value="Romanshorn (TG)" required style="width: 100%; padding: 10px; font-size: 15px; font-weight: 800; background: var(--paper);">
            </div>
            <div>
              <label style="font-size: 12px; font-weight: 800;">Fixe Anfahrts-Grundpauschale (CHF) *</label>
              <input type="number" id="del-base-fee" min="0" value="40" required style="width: 100%; padding: 10px; font-size: 15px; font-weight: 800; border: 1px solid var(--line); border-radius: 6px;">
            </div>
          </div>

          <div class="form-section-title">
            <span>2. Degressive KM-Staffelpreise ab Romanshorn</span>
            <button type="button" class="button button-small button-outline" onclick="addDeliveryTierRow()">＋ KM-Staffel hinzufügen</button>
          </div>

          <div id="delivery-tiers-container" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px;">
          </div>

          <div class="form-section-title">3. Transport-Klassen & Multiplikatoren</div>
          <div class="grid-3" style="margin-bottom: 24px;">
            <div style="background: #f0fdf4; padding: 16px; border-radius: 8px; border: 1px solid #bbf7d0;">
              <strong style="color: #166534; font-size: 15px; display: block; margin-bottom: 6px;">🟢 Leichttransport</strong>
              <label style="font-size: 11px; font-weight: 800;">Multiplikator</label>
              <input type="number" step="0.1" id="del-mult-leicht" value="1.0" min="0.5" max="5.0" required style="width: 100%; padding: 8px; font-weight: 800;">
            </div>
            <div style="background: #eff6ff; padding: 16px; border-radius: 8px; border: 1px solid #bfdbfe;">
              <strong style="color: #1e40af; font-size: 15px; display: block; margin-bottom: 6px;">🔵 Mitteltransport</strong>
              <label style="font-size: 11px; font-weight: 800;">Multiplikator</label>
              <input type="number" step="0.1" id="del-mult-mittel" value="1.4" min="0.5" max="5.0" required style="width: 100%; padding: 8px; font-weight: 800;">
            </div>
            <div style="background: #fff7ed; padding: 16px; border-radius: 8px; border: 1px solid #fed7aa;">
              <strong style="color: #c2410c; font-size: 15px; display: block; margin-bottom: 6px;">🟠 Schwertransport</strong>
              <label style="font-size: 11px; font-weight: 800;">Multiplikator</label>
              <input type="number" step="0.1" id="del-mult-schwer" value="2.2" min="0.5" max="5.0" required style="width: 100%; padding: 8px; font-weight: 800;">
            </div>
          </div>

          <div class="form-section-title">4. Produktspezifische Auflad- & Abladepauschalen</div>
          <div id="product-load-fees-manager" style="margin-bottom: 24px; background: #fffdf5; padding: 16px; border-radius: 8px; border: 1px solid #fde68a;">
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 2px solid var(--paper);">
            <button type="submit" class="button">🚚 Transport-Tarife & Pauschalen speichern ✓</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tab 5: Payment Manager (TWINT, Mastercard, IBAN Bankverbindung, Barzahlung) -->
    <div id="tab-sec-payments" style="display: none;">
      <div class="product-detail-card" style="max-width: 900px;">
        <div class="product-detail-header">
          <div>
            <h2 style="margin: 0 0 4px;">💳 Schweizer Zahlungsarten-Manager</h2>
            <p style="color: var(--muted); margin: 0; font-size: 14px;">Verwalte TWINT, Banküberweisung / QR-Rechnung, Mastercard & Barzahlung bei Abholung.</p>
          </div>
        </div>

        <form id="payment-settings-form" onsubmit="handleSavePaymentSettings(event)">
          
          <!-- 1. TWINT -->
          <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 18px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <strong style="color: #166534; font-size: 16px;">📲 TWINT Mobile Payment (Schweiz)</strong>
              <label style="font-size: 12px; font-weight: 800;">
                <input type="checkbox" id="pay-twint-enable" checked> Aktivieren
              </label>
            </div>
            <div class="grid-2">
              <div>
                <label style="font-size: 12px; font-weight: 800;">TWINT Mobiltelefonnummer *</label>
                <input type="text" id="pay-twint-phone" value="+41 79 123 45 67" required style="width: 100%; padding: 8px; font-weight: 800;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Empfänger-Bezeichnung *</label>
                <input type="text" id="pay-twint-label" value="Rent A Bau TWINT Pay" required style="width: 100%; padding: 8px;">
              </div>
            </div>
          </div>

          <!-- 2. Banküberweisung / QR-Rechnung -->
          <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 18px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <strong style="color: #1e40af; font-size: 16px;">🏦 Bankverbindung & Schweizer QR-Rechnung</strong>
              <label style="font-size: 12px; font-weight: 800;">
                <input type="checkbox" id="pay-bank-enable" checked> Aktivieren
              </label>
            </div>
            <div class="grid-2" style="margin-bottom: 10px;">
              <div>
                <label style="font-size: 12px; font-weight: 800;">Bankinstitut Name *</label>
                <input type="text" id="pay-bank-name" value="Thurgauer Kantonalbank (TKB)" required style="width: 100%; padding: 8px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Kontoinhaber *</label>
                <input type="text" id="pay-bank-holder" value="Rent A Bau AG" required style="width: 100%; padding: 8px;">
              </div>
            </div>
            <div class="grid-2">
              <div>
                <label style="font-size: 12px; font-weight: 800;">Standard IBAN *</label>
                <input type="text" id="pay-bank-iban" value="CH93 0078 2000 1234 5678 9" required style="width: 100%; padding: 8px; font-weight: 800;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Schweizer QR-IBAN</label>
                <input type="text" id="pay-bank-qriban" value="CH44 3199 9123 4567 8901 2" style="width: 100%; padding: 8px; font-weight: 800;">
              </div>
            </div>
          </div>

          <!-- 3. Mastercard / Kreditkarte -->
          <div style="background: #fffdf5; border: 1px solid #fde68a; border-radius: 8px; padding: 18px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <strong style="color: #b45309; font-size: 16px;">💳 Mastercard / Visa / Kreditkarte</strong>
              <label style="font-size: 12px; font-weight: 800;">
                <input type="checkbox" id="pay-mastercard-enable" checked> Aktivieren
              </label>
            </div>
            <div>
              <label style="font-size: 12px; font-weight: 800;">Bezeichnung im Checkout</label>
              <input type="text" id="pay-mastercard-label" value="Mastercard / Visa / Kreditkarte" style="width: 100%; padding: 8px;">
            </div>
          </div>

          <!-- 4. Barzahlung bei Abholung / Übergabe -->
          <div style="background: #f3e8ff; border: 1px solid #d8b4fe; border-radius: 8px; padding: 18px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <strong style="color: #6b21a8; font-size: 16px;">💵 Barzahlung bei Abholung / Übergabe</strong>
              <label style="font-size: 12px; font-weight: 800;">
                <input type="checkbox" id="pay-cash-enable" checked> Aktivieren
              </label>
            </div>
            <div class="grid-2">
              <div>
                <label style="font-size: 12px; font-weight: 800;">Bezeichnung im Checkout</label>
                <input type="text" id="pay-cash-label" value="Barzahlung bei Abholung / Übergabe vor Ort" style="width: 100%; padding: 8px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Kurzfristig-Grenze in Tagen (Barzahlung Empfehlung)</label>
                <input type="number" id="pay-cash-days" value="3" min="1" max="14" style="width: 100%; padding: 8px; font-weight: 800;">
              </div>
            </div>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 2px solid var(--paper);">
            <small style="color: var(--muted);">Änderungen gelten sofort für das Kundenformular und Bestätigungen.</small>
            <button type="submit" class="button">💳 Zahlungsarten-Einstellungen speichern ✓</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tab 6: Reservations -->
    <div id="tab-sec-reservations" style="display: none;">
      <div class="portal-grid">
        <section class="portal-card portal-main">
          <div class="reservation-toolbar">
            <div>
              <h2>Eingegangene Reservationsanfragen & Zahlungsarten</h2>
              <p class="reservation-toolbar-hint">Reservationsstatus, Zahlung und interne Notizen direkt in der Tabelle verwalten.</p>
            </div>
            <div class="reservation-quick-actions" aria-label="Schnellaktionen">
              <span class="reservation-quick-title">Schnellaktionen</span>
              <a href="maschinen.php" class="button button-outline">＋ Katalog einsehen</a>
              <button type="button" class="button button-outline" onclick="loadAdminDashboard()">↻ Anfragen aktualisieren</button>
            </div>
          </div>
          <form id="reservation-filter-form" class="reservation-filter-form" onsubmit="handleReservationFilterSubmit(event)">
            <label>
              <span>Kunde</span>
              <input id="reservation-filter-customer" type="search" placeholder="Name, E-Mail oder Telefon">
            </label>
            <label>
              <span>Maschine</span>
              <select id="reservation-filter-machine"><option value="">Alle Maschinen</option></select>
            </label>
            <label>
              <span>Von</span>
              <input id="reservation-filter-start" type="date">
            </label>
            <label>
              <span>Bis</span>
              <input id="reservation-filter-end" type="date">
            </label>
            <label>
              <span>Reservationsstatus</span>
              <select id="reservation-filter-status">
                <option value="">Alle Status</option>
                <option value="angefragt">Angefragt</option>
                <option value="bestätigt">Bestätigt</option>
                <option value="abgeschlossen">Abgeschlossen</option>
                <option value="storniert">Storniert</option>
              </select>
            </label>
            <label>
              <span>Zahlungsart</span>
              <select id="reservation-filter-payment"><option value="">Alle Zahlungsarten</option></select>
            </label>
            <label>
              <span>Sortierung</span>
              <select id="reservation-filter-sort">
                <option value="createdAt:desc">Neueste zuerst</option>
                <option value="startDate:asc">Mietbeginn aufsteigend</option>
                <option value="startDate:desc">Mietbeginn absteigend</option>
                <option value="customer:asc">Kunde A–Z</option>
                <option value="machine:asc">Maschine A–Z</option>
                <option value="totalPrice:desc">Preis absteigend</option>
                <option value="paymentStatus:asc">Zahlungsstatus</option>
              </select>
            </label>
            <div class="reservation-filter-actions">
              <button type="submit" class="button button-small">Filtern</button>
              <button type="button" class="button button-small button-outline" onclick="resetReservationFilters()">Zurücksetzen</button>
            </div>
          </form>
          <div id="reservation-detail-panel" class="reservation-detail-panel" hidden></div>
          <div id="admin-reservations-list">
          </div>
        </section>
      </div>
    </div>

    <!-- Tab 7: Discounts -->
    <div id="tab-sec-discounts" style="display: none;">
      <div class="product-detail-card" style="max-width: 700px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <div>
            <h2 style="margin: 0 0 4px;">Staffelrabatte (Flexibel verwalten)</h2>
            <p style="color: var(--muted); margin: 0; font-size: 14px;">Erstelle oder passe beliebige Rabatt-Stufen an.</p>
          </div>
          <button type="button" class="button button-small" onclick="addDiscountTierRow()">＋ Neue Rabatt-Stufe</button>
        </div>

        <form id="dynamic-discounts-form" onsubmit="handleSaveDynamicDiscounts(event)">
          <div id="discount-tiers-container" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid var(--line);">
            <small style="color: var(--muted);">Der höchste passende Rabatt wird angewendet.</small>
            <button type="submit" class="button">Staffelrabatte speichern ✓</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tab 8: Audit and deployments -->
    <div id="tab-sec-audit" style="display: none;">
      <section class="portal-card audit-card">
        <div class="reservation-toolbar">
          <div><h2>Aktivitätsprotokoll</h2><p class="reservation-toolbar-hint">Nachvollziehbar: Wer hat wann welche Änderung vorgenommen?</p></div>
          <button type="button" class="button button-outline" onclick="loadAuditLog()">↻ Protokoll aktualisieren</button>
        </div>
        <form class="audit-filter-form" onsubmit="handleAuditFilterSubmit(event)">
          <label><span>Admin</span><input id="audit-filter-admin" type="search" placeholder="Name oder E-Mail"></label>
          <label><span>Bereich</span><select id="audit-filter-area"><option value="">Alle Bereiche</option><option value="reservations">Reservationen</option><option value="users">Benutzer</option><option value="machines">Maschinen</option><option value="settings">Einstellungen</option><option value="deployments">Deployments</option></select></label>
          <label><span>Von</span><input id="audit-filter-from" type="date"></label>
          <label><span>Bis</span><input id="audit-filter-to" type="date"></label>
          <div class="reservation-filter-actions"><button type="submit" class="button button-small">Filtern</button><button type="button" class="button button-small button-outline" onclick="resetAuditFilters()">Zurücksetzen</button></div>
        </form>
        <form class="deployment-form" onsubmit="handleDeploymentSubmit(event)">
          <h3>Code-/Deployment-Änderung dokumentieren</h3>
          <p class="reservation-toolbar-hint">Trage nach jedem Upload den Release-Namen und die betroffenen Serverdateien ein. Eine Datei pro Zeile.</p>
          <div class="deployment-form-grid">
            <label><span>Release / Änderung *</span><input id="deployment-release" type="text" maxlength="120" required placeholder="z. B. Audit-Log 2026-09-08"></label>
            <label><span>Referenz</span><input id="deployment-reference" type="text" maxlength="160" placeholder="z. B. Backup-Ordner oder Ticket"></label>
            <label class="deployment-form-wide"><span>Beschreibung</span><input id="deployment-description" type="text" maxlength="255" placeholder="Kurz beschreiben, was geändert wurde"></label>
            <label class="deployment-form-wide"><span>Geänderte Dateien / Pfade</span><textarea id="deployment-files" rows="3" placeholder="api/audit.php\ntemplates/admin.php\nassets/css/style.css"></textarea></label>
          </div>
          <button type="submit" class="button button-small">＋ Änderung protokollieren</button>
        </form>
        <div id="admin-audit-list"><p class="reservation-detail-loading">Aktivitätsprotokoll wird geladen …</p></div>
      </section>
    </div>
  </main>


  <script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/../assets/js/app.js') ?>"></script>
  <script>
    let currentEditingMachineId = null;
    let activeEditingCategoryName = null;
    let selectedCategoryFilter = "all";
    let adminCalDate = new Date();
    let newMachineUploadedImages = ["brand/rent-a-bau-hero.png"];
    let newMachineCoverImage = "brand/rent-a-bau-hero.png";

    let editingMachineImages = [];
    let editingMachineCover = "";
    let editingMachineAccessories = [];

    function updateUrlState(newHash) {
      if (history.replaceState) {
        history.replaceState(null, null, '#' + newHash);
      } else {
        window.location.hash = newHash;
      }
      const pill = document.getElementById("current-url-display-pill");
      if (pill) pill.innerText = `🔗 Link: admin.php#${newHash}`;
    }

    function showAdminTab(tabName, categoryFilter = null) {
      document.querySelectorAll(".admin-tab-btn").forEach(btn => btn.classList.remove("active"));
      
      const targetBtn = document.getElementById(`btn-tab-${tabName}`);
      if (targetBtn) targetBtn.classList.add("active");

      document.getElementById("tab-sec-calendar").style.display = tabName === "calendar" ? "block" : "none";
      document.getElementById("tab-sec-categories").style.display = tabName === "categories" ? "block" : "none";
      document.getElementById("tab-sec-products").style.display = (tabName === "products" || tabName.startsWith("cat-")) ? "block" : "none";
      document.getElementById("tab-sec-delivery").style.display = tabName === "delivery" ? "block" : "none";
      document.getElementById("tab-sec-payments").style.display = tabName === "payments" ? "block" : "none";
      document.getElementById("tab-sec-reservations").style.display = tabName === "reservations" ? "block" : "none";
      document.getElementById("tab-sec-discounts").style.display = tabName === "discounts" ? "block" : "none";
      document.getElementById("tab-sec-audit").style.display = tabName === "audit" ? "block" : "none";
      if (tabName === "audit") loadAuditLog();

      if (categoryFilter !== null) {
        selectedCategoryFilter = categoryFilter;
        const titleEl = document.getElementById("admin-cat-section-title");
        const subtitleEl = document.getElementById("admin-cat-section-subtitle");
        
        if (categoryFilter === "all") {
          titleEl.innerText = "Alle Produkte & Zubehör-Verwaltung";
          subtitleEl.innerText = "Gesamtübersicht aller Baumaschinen in allen Kategorien.";
          updateUrlState("products");
        } else {
          titleEl.innerText = `📂 ${categoryFilter} Produkte`;
          subtitleEl.innerText = `Verwalte alle Baumaschinen der Kategorie "${categoryFilter}".`;
          updateUrlState(`category-${encodeURIComponent(categoryFilter)}`);
        }
        renderAdminProductsTable();
      } else {
        updateUrlState(tabName);
      }

      if (tabName === "calendar") renderAdminMasterCalendar();
      if (tabName === "categories") renderCategoriesManager();
      if (tabName === "delivery") renderDeliveryConfigManager();
      if (tabName === "payments") renderPaymentSettingsManager();
    }

    function renderPaymentSettingsManager() {
      const conf = (SYSTEM_SETTINGS.paymentConfig && typeof SYSTEM_SETTINGS.paymentConfig === "object") ? 
        SYSTEM_SETTINGS.paymentConfig : DEFAULT_PAYMENT_CONFIG;

      if (conf.twint) {
        document.getElementById("pay-twint-enable").checked = conf.twint.enabled !== false;
        document.getElementById("pay-twint-phone").value = conf.twint.phone || "+41 79 123 45 67";
        document.getElementById("pay-twint-label").value = conf.twint.label || "Rent A Bau TWINT Pay";
      }
      if (conf.bank) {
        document.getElementById("pay-bank-enable").checked = conf.bank.enabled !== false;
        document.getElementById("pay-bank-name").value = conf.bank.bankName || "Thurgauer Kantonalbank (TKB)";
        document.getElementById("pay-bank-holder").value = conf.bank.accountHolder || "Rent A Bau AG";
        document.getElementById("pay-bank-iban").value = conf.bank.iban || "CH93 0078 2000 1234 5678 9";
        document.getElementById("pay-bank-qriban").value = conf.bank.qrIban || "CH44 3199 9123 4567 8901 2";
      }
      if (conf.mastercard) {
        document.getElementById("pay-mastercard-enable").checked = conf.mastercard.enabled !== false;
        document.getElementById("pay-mastercard-label").value = conf.mastercard.label || "Mastercard / Visa / Kreditkarte";
      }
      if (conf.cash) {
        document.getElementById("pay-cash-enable").checked = conf.cash.enabled !== false;
        document.getElementById("pay-cash-label").value = conf.cash.label || "Barzahlung bei Abholung / Übergabe vor Ort";
        document.getElementById("pay-cash-days").value = conf.cash.allowShortNoticeDays || 3;
      }
    }

    async function handleSavePaymentSettings(e) {
      e.preventDefault();
      
      const newPayConfig = {
        twint: {
          enabled: document.getElementById("pay-twint-enable").checked,
          phone: document.getElementById("pay-twint-phone").value.trim(),
          label: document.getElementById("pay-twint-label").value.trim()
        },
        bank: {
          enabled: document.getElementById("pay-bank-enable").checked,
          bankName: document.getElementById("pay-bank-name").value.trim(),
          accountHolder: document.getElementById("pay-bank-holder").value.trim(),
          iban: document.getElementById("pay-bank-iban").value.trim(),
          qrIban: document.getElementById("pay-bank-qriban").value.trim()
        },
        mastercard: {
          enabled: document.getElementById("pay-mastercard-enable").checked,
          label: document.getElementById("pay-mastercard-label").value.trim()
        },
        cash: {
          enabled: document.getElementById("pay-cash-enable").checked,
          label: document.getElementById("pay-cash-label").value.trim(),
          allowShortNoticeDays: parseInt(document.getElementById("pay-cash-days").value) || 3
        }
      };

      await saveSettings({ paymentConfig: newPayConfig });
      alert("🎉 Schweizer Zahlungsarten-Einstellungen (TWINT, Bank/QR, Mastercard, Barzahlung) gespeichert!");
      renderPaymentSettingsManager();
    }

    function renderDeliveryConfigManager() {
      const config = SYSTEM_SETTINGS.deliveryConfig || DEFAULT_DELIVERY_CONFIG;
      document.getElementById("del-base-location").value = config.baseLocation || "Romanshorn (TG)";
      document.getElementById("del-base-fee").value = config.baseFee || 40;

      const tClasses = config.transportClasses || DEFAULT_DELIVERY_CONFIG.transportClasses;
      if (tClasses.leicht) document.getElementById("del-mult-leicht").value = tClasses.leicht.multiplier || 1.0;
      if (tClasses.mittel) document.getElementById("del-mult-mittel").value = tClasses.mittel.multiplier || 1.4;
      if (tClasses.schwer) document.getElementById("del-mult-schwer").value = tClasses.schwer.multiplier || 2.2;

      renderDeliveryTiersList(config.tiers || DEFAULT_DELIVERY_CONFIG.tiers);
      renderProductLoadFeesManager();

      const simSelect = document.getElementById("sim-machine-select");
      if (simSelect) {
        simSelect.innerHTML = MACHINES.map(m => `<option value="${m.id}">${m.name} (Pauschale: CHF ${m.loadUnloadFee || 35})</option>`).join("");
        runDeliverySimulation();
      }
    }

    function renderProductLoadFeesManager() {
      const container = document.getElementById("product-load-fees-manager");
      if (!container) return;

      container.innerHTML = `
        <label style="font-weight: 800; font-size: 13px; display: block; margin-bottom: 10px; color: var(--black);">
          💡 Bestimme die individuelle Auflad- & Abladepauschale (Aufwand Handhabung & Sichern) für jedes Produkt:
        </label>
        <div class="grid-3">
          ${MACHINES.map(m => `
            <div style="background: #fff; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--line);">
              <strong style="font-size: 13px; color: var(--black); display: block; margin-bottom: 4px;">${m.name}</strong>
              <small style="color: var(--muted); display: block; margin-bottom: 6px;">${m.category} · ${m.transportClass || 'mittel'}</small>
              <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-weight: 800; font-size: 12px;">CHF</span>
                <input type="number" value="${m.loadUnloadFee !== undefined ? m.loadUnloadFee : 35}" min="0" class="prod-load-fee-input" data-mid="${m.id}" style="width: 100%; padding: 6px; font-weight: 800;">
              </div>
            </div>
          `).join("")}
        </div>
      `;
    }

    function renderDeliveryTiersList(tiers) {
      const container = document.getElementById("delivery-tiers-container");
      if (!container) return;

      container.innerHTML = tiers.map((t, idx) => `
        <div style="display: flex; gap: 12px; align-items: center; background: var(--paper); padding: 12px; border-radius: 6px; border: 1px solid var(--line);" id="del-tier-row-${idx}">
          <span style="font-weight: 900; color: var(--orange-dark); font-size: 14px; width: 30px;">#${idx + 1}</span>
          <div style="flex: 1;">
            <label style="font-size: 11px; font-weight: 800; display: block;">Bis Entfernung ab Romanshorn (Max. KM)</label>
            <input type="number" value="${t.maxKm}" min="1" max="999" class="del-tier-maxkm" required style="width: 100%; padding: 8px;">
          </div>
          <div style="flex: 1;">
            <label style="font-size: 11px; font-weight: 800; display: block;">Preis pro KM (CHF / km)</label>
            <input type="number" step="0.1" value="${t.ratePerKm}" min="0.1" max="20" class="del-tier-rate" required style="width: 100%; padding: 8px; font-weight: 800; color: var(--orange-dark);">
          </div>
          <button type="button" style="color: red; border: 0; background: transparent; font-size: 18px; font-weight: 900; cursor: pointer; margin-top: 14px;" onclick="document.getElementById('del-tier-row-${idx}').remove()" title="Staffel löschen">✕</button>
        </div>
      `).join("");
    }

    function addDeliveryTierRow() {
      const container = document.getElementById("delivery-tiers-container");
      if (!container) return;
      const idx = Date.now();
      const count = container.children.length + 1;
      const div = document.createElement("div");
      div.id = `del-tier-row-${idx}`;
      div.style.display = "flex";
      div.style.gap = "12px";
      div.style.alignItems = "center";
      div.style.background = "var(--paper)";
      div.style.padding = "12px";
      div.style.borderRadius = "6px";
      div.style.border = "1px solid var(--line)";

      div.innerHTML = `
        <span style="font-weight: 900; color: var(--orange-dark); font-size: 14px; width: 30px;">#${count}</span>
        <div style="flex: 1;">
          <label style="font-size: 11px; font-weight: 800; display: block;">Bis Entfernung ab Romanshorn (Max. KM)</label>
          <input type="number" value="50" min="1" max="999" class="del-tier-maxkm" required style="width: 100%; padding: 8px;">
        </div>
        <div style="flex: 1;">
          <label style="font-size: 11px; font-weight: 800; display: block;">Preis pro KM (CHF / km)</label>
          <input type="number" step="0.1" value="2.50" min="0.1" max="20" class="del-tier-rate" required style="width: 100%; padding: 8px; font-weight: 800; color: var(--orange-dark);">
        </div>
        <button type="button" style="color: red; border: 0; background: transparent; font-size: 18px; font-weight: 900; cursor: pointer; margin-top: 14px;" onclick="this.parentElement.remove()" title="Staffel löschen">✕</button>
      `;
      container.appendChild(div);
    }

    async function handleSaveDeliveryConfig(e) {
      e.preventDefault();
      const baseLocation = document.getElementById("del-base-location").value.trim() || "Romanshorn (TG)";
      const baseFee = parseFloat(document.getElementById("del-base-fee").value) || 40;
      
      const maxKmInputs = document.querySelectorAll(".del-tier-maxkm");
      const rateInputs = document.querySelectorAll(".del-tier-rate");
      const newTiers = [];

      maxKmInputs.forEach((el, i) => {
        const maxKm = parseInt(el.value);
        const ratePerKm = parseFloat(rateInputs[i] ? rateInputs[i].value : 0);
        if (!isNaN(maxKm) && !isNaN(ratePerKm)) {
          newTiers.push({ maxKm, ratePerKm });
        }
      });
      newTiers.sort((a, b) => a.maxKm - b.maxKm);

      const multLeicht = parseFloat(document.getElementById("del-mult-leicht").value) || 1.0;
      const multMittel = parseFloat(document.getElementById("del-mult-mittel").value) || 1.4;
      const multSchwer = parseFloat(document.getElementById("del-mult-schwer").value) || 2.2;

      const loadFeeInputs = document.querySelectorAll(".prod-load-fee-input");
      const updatedMachines = SYSTEM_SETTINGS.machines || {};

      loadFeeInputs.forEach(input => {
        const mId = input.getAttribute("data-mid");
        const val = parseFloat(input.value) || 0;
        if (mId && updatedMachines[mId]) {
          updatedMachines[mId].loadUnloadFee = val;
        }
      });

      const newConfig = {
        baseLocation: baseLocation,
        baseFee: baseFee,
        tiers: newTiers,
        transportClasses: {
          leicht: { name: "Leichttransport (Anhänger / Bus)", multiplier: multLeicht },
          mittel: { name: "Mitteltransport (3.5t Anhänger)", multiplier: multMittel },
          schwer: { name: "Schwertransport (LKW / Tieflader)", multiplier: multSchwer }
        }
      };

      await saveSettings({ deliveryConfig: newConfig, machines: updatedMachines });
      alert("🎉 Transport-Tarife, Aufladepauschalen & Standort (Romanshorn) erfolgreich gespeichert!");
      renderDeliveryConfigManager();
    }

    function runDeliverySimulation() {
      const mId = document.getElementById("sim-machine-select")?.value;
      const km = parseFloat(document.getElementById("sim-km-input")?.value || 0);
      const machine = MACHINES.find(m => m.id === mId);

      const res = calculateDeliveryCost(machine, km);

      document.getElementById("sim-total-result").innerText = formatCHF(res.totalDelivery);

      const box = document.getElementById("sim-breakdown-box");
      if (box) {
        box.innerHTML = `
          <strong>Berechnungs-Details (ab ${res.baseLocation}):</strong><br>
          • Auflad- & Abladepauschale (${machine ? machine.name : 'Produkt'}): <strong>CHF ${res.loadUnloadFee}</strong><br>
          • Anfahrts-Grundpauschale: <strong>CHF ${res.baseFee}</strong><br>
          • KM-Kosten ab ${res.baseLocation} (Degressiv): <strong>CHF ${res.rawKmCost.toFixed(2)}</strong> (${res.breakdown.map(b => `${b.kmCount}km @ CHF ${b.ratePerKm.toFixed(2)}`).join(" + ")})<br>
          • Zwischensumme vor Faktor: CHF ${(res.baseFee + res.rawKmCost).toFixed(2)} x ${res.multiplier} (${res.className}) = CHF ${res.kmAndBaseSubtotal}<br>
          <span style="color: var(--orange-dark); font-weight: 800;">➔ Gesamte Lieferkosten (Pauschale + Anfahrt): CHF ${res.totalDelivery}</span>
        `;
      }
    }

    function renderCategoriesManager() {
      const container = document.getElementById("categories-grid-cards-container");
      if (!container) return;

      container.innerHTML = CATEGORIES.map((cat, idx) => {
        const specs = CATEGORY_SPECS[cat] || ["gewicht", "breite", "laenge", "leistung"];
        const productsCount = MACHINES.filter(m => m.category === cat).length;
        const catHashUrl = `admin.php#category-editor-${encodeURIComponent(cat)}`;

        return `
          <div class="category-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <div>
                <span style="font-size: 11px; font-weight: 900; color: var(--orange-dark); text-transform: uppercase;">Kategorie #${idx + 1}</span>
                <h3 style="margin: 4px 0; font-size: 22px; color: var(--black);">${cat}</h3>
                <small style="color: var(--muted); font-weight: 700;">${productsCount} Baumaschine(n) zugeordnet</small>
              </div>
              <button type="button" style="color: red; border: 0; background: transparent; font-weight: 900; font-size: 16px; cursor: pointer;" onclick="handleDeleteCategory('${cat}')" title="Kategorie löschen">✕</button>
            </div>

            <div style="margin: 10px 0;">
              <small style="font-size: 11px; font-weight: 800; color: var(--black); display: block; margin-bottom: 6px;">⚙️ Erfasste Kriterien (${specs.length}):</small>
              <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                ${specs.slice(0, 4).map(s => `<span class="spec-tag" style="font-size: 11px; padding: 2px 8px;">✓ ${s}</span>`).join("")}
                ${specs.length > 4 ? `<span style="font-size: 11px; color: var(--muted); font-weight: 800;">+${specs.length - 4} weitere</span>` : ''}
              </div>
            </div>

            <div style="display: flex; gap: 8px; margin-top: 10px;">
              <button type="button" class="button button-small" onclick="openDedicatedCategoryEditor('${cat}')" style="flex: 1; text-align: center;">⚙️ ${cat} Details & Kriterien bearbeiten →</button>
            </div>
            <small style="color: var(--muted); font-size: 10px; word-break: break-all;">Link: ${catHashUrl}</small>
          </div>
        `;
      }).join("");
    }

    function openDedicatedCategoryEditor(catName) {
      activeEditingCategoryName = catName;
      showAdminTab('categories');
      document.getElementById("categories-overview-view").style.display = "none";
      document.getElementById("category-dedicated-editor-view").style.display = "block";
      document.getElementById("dedicated-cat-badge").innerText = `⚙️ Kriterien-Seite: ${catName}`;

      updateUrlState(`category-editor-${encodeURIComponent(catName)}`);
      renderDedicatedCategoryEditorPage(catName);
    }

    function closeCategoryDedicatedEditor() {
      document.getElementById("category-dedicated-editor-view").style.display = "none";
      document.getElementById("categories-overview-view").style.display = "block";
      activeEditingCategoryName = null;
      updateUrlState("categories");
      renderCategoriesManager();
    }

    function renderDedicatedCategoryEditorPage(catName) {
      const container = document.getElementById("category-dedicated-editor-container");
      const specs = CATEGORY_SPECS[catName] || ["gewicht", "breite", "laenge", "leistung"];
      const categoryMachines = MACHINES.filter(m => m.category === catName);

      container.innerHTML = `
        <div class="product-detail-card">
          <div class="product-detail-header">
            <div>
              <span class="eyebrow" style="color: var(--orange-dark);">Eigene Kategorie-Seite</span>
              <h2 style="margin: 0;">⚙️ Einstellungen & Kriterien für "${catName}"</h2>
              <small style="color: var(--muted);">Direkt-URL: <strong>admin.php#category-editor-${encodeURIComponent(catName)}</strong></small>
            </div>
            <button type="button" class="button button-small button-outline" onclick="closeCategoryDedicatedEditor()">← Zurück zur Übersicht</button>
          </div>

          <form onsubmit="handleSaveDedicatedCategorySpecs(event, '${catName}')">
            <div class="form-section-title">1. Kategorie-Stammdaten</div>
            <div class="grid-2" style="margin-bottom: 16px;">
              <div>
                <label style="font-size: 12px; font-weight: 800;">Kategorie Name *</label>
                <input type="text" id="dedicated-cat-name-input" value="${catName}" required style="width: 100%; padding: 8px; font-weight: 800;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Anzahl Produkte in ${catName}</label>
                <input type="text" value="${categoryMachines.length} Maschine(n) zugeordnet" disabled style="width: 100%; padding: 8px; background: var(--paper); color: var(--muted);">
              </div>
            </div>

            <div class="form-section-title">
              <span>2. Erfasste Kriterien & Maße (Gewicht/Kilo, Grabtiefe, Länge, Breite, etc.)</span>
              <button type="button" class="button button-small button-outline" onclick="addDedicatedCategorySpecRow()">＋ Kriterium hinzufügen</button>
            </div>

            <div id="dedicated-specs-list-container" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px;">
              ${specs.map((s, idx) => `
                <div style="display: flex; gap: 10px; align-items: center; background: var(--paper); padding: 10px; border-radius: 6px; border: 1px solid var(--line);" id="ded-spec-row-${idx}">
                  <span style="font-weight: 900; color: var(--orange-dark); font-size: 14px; width: 30px;">#${idx + 1}</span>
                  <input type="text" value="${s}" class="ded-spec-key-input" placeholder="Kriterium Name (z. B. gewicht, grabtiefe, breite)" required style="flex: 1; padding: 8px; font-weight: 800;">
                  <button type="button" style="color: red; border: 0; background: transparent; font-weight: 900; font-size: 18px; cursor: pointer;" onclick="document.getElementById('ded-spec-row-${idx}').remove()">✕ Löschen</button>
                </div>
              `).join("")}
            </div>

            <div class="form-section-title">3. Baumaschinen in dieser Kategorie</div>
            <div style="margin-bottom: 24px;">
              ${categoryMachines.length > 0 ? `
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                  ${categoryMachines.map(m => `
                    <span style="padding: 8px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 20px; font-weight: 800; font-size: 13px; color: #166534; cursor: pointer;" onclick="openSingleProductEditor('${m.id}')">
                      🚜 ${m.name} (✏️ Bearbeiten)
                    </span>
                  `).join("")}
                </div>
              ` : `<p style="color: var(--muted); font-size: 14px;">Noch keine Maschinen in dieser Kategorie.</p>`}
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 2px solid var(--paper);">
              <button type="button" class="button button-outline" onclick="closeCategoryDedicatedEditor()">Abbrechen</button>
              <button type="submit" class="button">
                💾 Kriterien für "${catName}" speichern ✓
              </button>
            </div>
          </form>
        </div>
      `;
    }

    function addDedicatedCategorySpecRow() {
      const container = document.getElementById("dedicated-specs-list-container");
      if (!container) return;
      const idx = Date.now();
      const count = container.children.length + 1;
      const div = document.createElement("div");
      div.id = `ded-spec-row-${idx}`;
      div.style.display = "flex";
      div.style.gap = "10px";
      div.style.alignItems = "center";
      div.style.background = "var(--paper)";
      div.style.padding = "10px";
      div.style.borderRadius = "6px";
      div.style.border = "1px solid var(--line)";

      div.innerHTML = `
        <span style="font-weight: 900; color: var(--orange-dark); font-size: 14px; width: 30px;">#${count}</span>
        <input type="text" placeholder="Neues Kriterium (z. B. laenge, hoehe, gewicht)" class="ded-spec-key-input" required style="flex: 1; padding: 8px; font-weight: 800;">
        <button type="button" style="color: red; border: 0; background: transparent; font-weight: 900; font-size: 18px; cursor: pointer;" onclick="this.parentElement.remove()">✕ Löschen</button>
      `;
      container.appendChild(div);
    }

    async function handleSaveDedicatedCategorySpecs(e, oldCatName) {
      e.preventDefault();
      const newCatName = document.getElementById("dedicated-cat-name-input").value.trim();
      const specInputs = document.querySelectorAll(".ded-spec-key-input");
      
      const newSpecs = [];
      specInputs.forEach(input => {
        const val = input.value.trim().toLowerCase();
        if (val && !newSpecs.includes(val)) {
          newSpecs.push(val);
        }
      });

      const updatedCategories = CATEGORIES.map(c => c === oldCatName ? newCatName : c);
      const updatedCategorySpecs = { ...CATEGORY_SPECS };
      
      if (oldCatName !== newCatName) {
        delete updatedCategorySpecs[oldCatName];
      }
      updatedCategorySpecs[newCatName] = newSpecs;

      const updatedMachines = SYSTEM_SETTINGS.machines || {};
      if (oldCatName !== newCatName) {
        Object.keys(updatedMachines).forEach(mId => {
          if (updatedMachines[mId].category === oldCatName) {
            updatedMachines[mId].category = newCatName;
          }
        });
      }

      await saveSettings({
        categories: updatedCategories,
        categorySpecs: updatedCategorySpecs,
        machines: updatedMachines
      });

      alert(`🎉 Kriterien für "${newCatName}" wurden erfolgreich gespeichert!`);
      closeCategoryDedicatedEditor();
      await loadAdminDashboard();
    }

    async function createNewCategoryPrompt() {
      const catName = prompt("Name der neuen Kategorie eingeben (z. B. Spezialbaustellen):");
      if (!catName || !catName.trim()) return;

      const trimmed = catName.trim();
      if (CATEGORIES.includes(trimmed)) {
        alert("Diese Kategorie existiert bereits.");
        return;
      }

      const updatedCats = [...CATEGORIES, trimmed];
      const updatedCategorySpecs = { ...CATEGORY_SPECS };
      updatedCategorySpecs[trimmed] = ["gewicht", "breite", "laenge", "leistung"];

      await saveSettings({ categories: updatedCats, categorySpecs: updatedCategorySpecs });
      alert(`🎉 Kategorie "${trimmed}" angelegt!`);
      await loadAdminDashboard();
      openDedicatedCategoryEditor(trimmed);
    }

    async function handleDeleteCategory(catName) {
      if (!confirm(`Kategorie "${catName}" wirklich löschen?`)) return;
      const updatedCats = CATEGORIES.filter(c => c !== catName);
      const updatedCategorySpecs = { ...CATEGORY_SPECS };
      delete updatedCategorySpecs[catName];

      await saveSettings({ categories: updatedCats, categorySpecs: updatedCategorySpecs });
      alert(`Kategorie "${catName}" gelöscht.`);
      loadAdminDashboard();
    }

    function showCreateMachineForm() {
      document.getElementById("create-machine-card").style.display = "block";
      newMachineUploadedImages = ["brand/rent-a-bau-hero.png"];
      newMachineCoverImage = "brand/rent-a-bau-hero.png";
      const fileInput = document.getElementById("new-m-files-input");
      if (fileInput) fileInput.value = "";
      renderNewMachineGalleryPreview();

      const catSelect = document.getElementById("new-m-cat");
      if (catSelect) {
        catSelect.innerHTML = CATEGORIES.map(c => `<option value="${c}">${c}</option>`).join("");
        if (selectedCategoryFilter !== "all" && CATEGORIES.includes(selectedCategoryFilter)) {
          catSelect.value = selectedCategoryFilter;
        }
        onNewMachineCategoryChange(catSelect.value || CATEGORIES[0]);
      }
      document.getElementById("new-m-name").focus();
    }

    function onNewMachineCategoryChange(catName) {
      const container = document.getElementById("new-m-specs-dynamic-container");
      if (!container) return;

      const specFields = CATEGORY_SPECS[catName] || ["gewicht", "grabtiefe", "breite", "leistung"];
      container.innerHTML = specFields.map((f, i) => `
        <div id="new-m-spec-row-${i}">
          <label style="font-size: 11px; font-weight: 800; text-transform: uppercase;">${f}</label>
          <input type="text" class="new-m-spec-val" data-key="${f}" placeholder="z. B. 1'800 kg oder 2.4 m" style="width: 100%; padding: 8px;">
        </div>
      `).join("");
    }

    function addNewSpecRowToNewMachineForm() {
      const container = document.getElementById("new-m-specs-dynamic-container");
      if (!container) return;
      const keyName = prompt("Kriterium Name eingeben:", "laenge");
      if (!keyName) return;

      const idx = Date.now();
      const div = document.createElement("div");
      div.id = `new-m-spec-row-${idx}`;
      div.innerHTML = `
        <label style="font-size: 11px; font-weight: 800; text-transform: uppercase;">${keyName}</label>
        <input type="text" class="new-m-spec-val" data-key="${keyName}" placeholder="Wert eingeben" style="width: 100%; padding: 8px;">
      `;
      container.appendChild(div);
    }

    function changeAdminCalMonth(delta) {
      adminCalDate.setMonth(adminCalDate.getMonth() + delta);
      renderAdminMasterCalendar();
    }

    async function renderAdminMasterCalendar() {
      const container = document.getElementById("admin-master-calendar-container");
      const selectEl = document.getElementById("admin-cal-machine-select");
      if (!container || !selectEl) return;

      if (selectEl.children.length === 0) {
        selectEl.innerHTML = `<option value="all">🌟 Alle Maschinen (Gesamtübersicht)</option>` + 
          MACHINES.map(m => `<option value="${m.id}">${m.name}</option>`).join("");
      }

      const selectedMachineId = selectEl.value;
      const selectedMachine = MACHINES.find(m => m.id === selectedMachineId);

      const previewCard = document.getElementById("admin-machine-preview-card");
      const previewImg = document.getElementById("admin-preview-img");
      const previewTitle = document.getElementById("admin-preview-title");
      const previewPrice = document.getElementById("admin-preview-price");

      if (selectedMachine) {
        const flatPrice = selectedMachine.hourlyPrice * (selectedMachine.maxHoursPerDay || 5);
        previewImg.src = selectedMachine.image || "brand/rent-a-bau-hero.png";
        previewTitle.innerText = selectedMachine.name;
        previewPrice.innerText = `${formatCHF(flatPrice)} / Tag (${formatCHF(selectedMachine.hourlyPrice)}/h)`;
        previewCard.style.display = "flex";
      } else {
        previewImg.src = "brand/rent-a-bau-logo.png";
        previewTitle.innerText = "Alle Maschinen Übersicht";
        previewPrice.innerText = `${MACHINES.length} Maschinen Online`;
        previewCard.style.display = "flex";
      }

      const reservations = await fetchReservations();
      const blockedDates = SYSTEM_SETTINGS.blockedDates || [];

      const year = adminCalDate.getFullYear();
      const month = adminCalDate.getMonth();
      const monthNames = ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"];

      const firstDayIndex = new Date(year, month, 1).getDay();
      const mondayOffset = (firstDayIndex + 6) % 7;
      const daysInMonth = new Date(year, month + 1, 0).getDate();

      let html = `
        <div style="font-size: 18px; font-weight: 900; margin-bottom: 12px; text-align: center; color: var(--black);">
          ${monthNames[month]} ${year}
        </div>
        <div class="admin-cal-grid">
          <div class="admin-cal-day-name">Mo</div>
          <div class="admin-cal-day-name">Di</div>
          <div class="admin-cal-day-name">Mi</div>
          <div class="admin-cal-day-name">Do</div>
          <div class="admin-cal-day-name" style="color: var(--orange-dark);">Fr</div>
          <div class="admin-cal-day-name">Sa</div>
          <div class="admin-cal-day-name">So</div>
      `;

      for (let i = 0; i < mondayOffset; i++) {
        html += `<div class="admin-cal-cell empty"></div>`;
      }

      for (let day = 1; day <= daysInMonth; day++) {
        const mStr = String(month + 1).padStart(2, '0');
        const dStr = String(day).padStart(2, '0');
        const dateStr = `${year}-${mStr}-${dStr}`;
        const dateObj = new Date(year, month, day);
        const dayOfWeek = dateObj.getDay();
        const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

        const matchingBlocked = blockedDates.filter(b => 
          (selectedMachineId === 'all' || b.machineId === selectedMachineId) &&
          dateStr >= b.startDate && dateStr <= b.endDate
        );

        const matchingRes = reservations.filter(r => 
          r.status !== 'storniert' &&
          (selectedMachineId === 'all' || r.machineId === selectedMachineId) &&
          dateStr >= r.startDate && dateStr <= r.endDate
        );

        let cellClass = "";
        let badgesHtml = "";

        if (matchingBlocked.length > 0) {
          cellClass = "is-blocked";
          badgesHtml += matchingBlocked.map(b => `<span class="admin-cal-cell-badge blocked">🔒 ${b.reason || 'Sperre'}</span>`).join("");
        }

        if (matchingRes.length > 0) {
          matchingRes.forEach(r => {
            const isConfirmed = r.status === 'bestätigt';
            const bClass = isConfirmed ? 'reserved' : 'pending';
            cellClass = isConfirmed ? 'is-reserved' : 'is-pending';
            badgesHtml += `<span class="admin-cal-cell-badge ${bClass}">${isConfirmed ? '✓' : '⌛'} ${r.customerName}</span>`;
          });
        }

        if (!cellClass) {
          badgesHtml = `<span class="admin-cal-cell-badge free">${isWeekend ? 'Wochenende' : 'Frei'}</span>`;
        }

        let priceHtml = "";
        if (selectedMachine && !isWeekend) {
          const dayPrice = selectedMachine.hourlyPrice * (selectedMachine.maxHoursPerDay || 5);
          priceHtml = `<span class="admin-cal-cell-price">${formatCHF(dayPrice)}</span>`;
        } else if (selectedMachine && isWeekend) {
          priceHtml = `<span class="admin-cal-cell-price" style="color:#64748b;">(Kein Preis)</span>`;
        }

        html += `
          <div class="admin-cal-cell ${cellClass}" onclick="handleAdminCalCellClick('${dateStr}', '${selectedMachineId}')">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <span class="admin-cal-cell-num">${day}</span>
              ${priceHtml}
            </div>
            <div style="display: flex; flex-direction: column; gap: 2px; margin-top: 4px;">
              ${badgesHtml}
            </div>
          </div>
        `;
      }

      html += `</div>`;
      container.innerHTML = html;
    }

    async function handleAdminCalCellClick(dateStr, machineId) {
      if (machineId === 'all') {
        alert(`Datum gewählt: ${dateStr}\nBitte wähle eine spezifische Maschine im Dropdown aus, um diesen Tag zu sperren.`);
        return;
      }

      const m = MACHINES.find(item => item.id === machineId);
      const mName = m ? m.name : machineId;

      const reason = prompt(`Maschine "${mName}" am ${dateStr} sperren?\n\nGib den Grund der Sperre ein:`, "Wartung & Service");
      if (reason) {
        const current = SYSTEM_SETTINGS.blockedDates || [];
        current.push({ machineId, startDate: dateStr, endDate: dateStr, reason });
        await saveSettings({ blockedDates: current });
        alert(`🔒 "${mName}" wurde für den ${dateStr} gesperrt!`);
        renderAdminMasterCalendar();
      }
    }

    function escapeAdminHtml(value) {
      return String(value ?? '').replace(/[&<>'\"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','\"':'&quot;'}[char]));
    }

    function getReservationFilterParams() {
      const sortValue = document.getElementById('reservation-filter-sort')?.value || 'createdAt:desc';
      const [sort, direction] = sortValue.split(':');
      return {
        customer: document.getElementById('reservation-filter-customer')?.value || '',
        machineId: document.getElementById('reservation-filter-machine')?.value || '',
        startDate: document.getElementById('reservation-filter-start')?.value || '',
        endDate: document.getElementById('reservation-filter-end')?.value || '',
        status: document.getElementById('reservation-filter-status')?.value || '',
        paymentPreference: document.getElementById('reservation-filter-payment')?.value || '',
        sort,
        direction
      };
    }

    function populateReservationFilterOptions() {
      const machineSelect = document.getElementById('reservation-filter-machine');
      if (machineSelect && machineSelect.options.length <= 1) {
        machineSelect.innerHTML = '<option value="">Alle Maschinen</option>' + MACHINES.map(m => `<option value="${escapeAdminHtml(m.id)}">${escapeAdminHtml(m.name)}</option>`).join('');
      }
      const paymentSelect = document.getElementById('reservation-filter-payment');
      if (paymentSelect && paymentSelect.options.length <= 1) {
        paymentSelect.innerHTML = '<option value="">Alle Zahlungsarten</option><option value="bank">Banküberweisung</option><option value="twint">TWINT</option><option value="mastercard">Mastercard</option><option value="cash">Barzahlung</option>';
      }
    }

    function handleReservationFilterSubmit(event) {
      event.preventDefault();
      loadAdminDashboard();
    }

    function resetReservationFilters() {
      document.getElementById('reservation-filter-form')?.reset();
      loadAdminDashboard();
    }

    async function openReservationDetail(id) {
      const panel = document.getElementById('reservation-detail-panel');
      if (!panel) return;
      panel.hidden = false;
      panel.innerHTML = '<p class="reservation-detail-loading">Reservation wird geladen …</p>';
      try {
        const response = await fetch(`api/reservations.php?id=${encodeURIComponent(id)}`, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (!response.ok || !data.reservation) throw new Error(data.error || 'Reservation konnte nicht geladen werden.');
        const r = data.reservation;
        const machine = MACHINES.find(item => String(item.id) === String(r.machineId));
        const machineSpecs = machine?.specs && typeof machine.specs === 'object' ? Object.entries(machine.specs).map(([key, value]) => `<dt>${escapeAdminHtml(key)}</dt><dd>${escapeAdminHtml(value)}</dd>`).join('') : '<dd>Keine Maschinendaten hinterlegt.</dd>';
        panel.innerHTML = `
          <div class="reservation-detail-header"><div><span class="eyebrow">Reservation #${escapeAdminHtml(r.id)}</span><h3>${escapeAdminHtml(r.machineName || 'Maschine')}</h3></div><button type="button" class="button button-small button-outline" onclick="closeReservationDetail()">Schliessen</button></div>
          <div class="reservation-detail-grid">
            <section><h4>Kundendaten</h4><dl><dt>Name</dt><dd>${escapeAdminHtml(r.customerName)}</dd><dt>E-Mail</dt><dd>${escapeAdminHtml(r.email)}</dd><dt>Telefon</dt><dd>${escapeAdminHtml(r.phone || '-')}</dd></dl></section>
            <section><h4>Mietdaten</h4><dl><dt>Zeitraum</dt><dd>${escapeAdminHtml(r.startDate)} bis ${escapeAdminHtml(r.endDate)}</dd><dt>Tage</dt><dd>${escapeAdminHtml(r.days)}</dd><dt>Übergabe</dt><dd>${escapeAdminHtml(r.handover || '-')}</dd><dt>Preis total</dt><dd>${formatCHF(r.totalPrice)}</dd></dl></section>
            <section><h4>Maschinendaten</h4><dl><dt>Maschine</dt><dd>${escapeAdminHtml(r.machineName)}</dd><dt>Maschinen-ID</dt><dd>${escapeAdminHtml(r.machineId)}</dd>${machineSpecs}</dl></section>
            <section><h4>Zahlung & Dokumente</h4><dl><dt>Zahlungsart</dt><dd>${escapeAdminHtml(r.paymentPreference || '-')}</dd><dt>Zahlungsstatus</dt><dd>${escapeAdminHtml(r.paymentStatus || 'offen')}</dd><dt>Bestätigt am</dt><dd>${escapeAdminHtml(r.confirmedAt || '-')}</dd><dt>Rechnung</dt><dd class="muted"><a href="admin-finance.php?reservation_id=${Number(r.id)}">Rechnungen &amp; Zahlungen öffnen</a> · <a href="admin-protocol.php?reservation_id=${Number(r.id)}">Übergabe / Abnahme</a></dd></dl></section>
          </div>
          <section class="reservation-detail-notes"><h4>Notizen</h4><p>${escapeAdminHtml(r.notes || 'Keine Kundennotiz')}</p><p><strong>Interne Notiz:</strong> ${escapeAdminHtml(r.adminNote || 'Keine interne Notiz')}</p></section>`;
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } catch (error) {
        panel.innerHTML = `<p class="error-message">${escapeAdminHtml(error.message)}</p>`;
      }
    }

    function closeReservationDetail() {
      const panel = document.getElementById('reservation-detail-panel');
      if (panel) { panel.hidden = true; panel.innerHTML = ''; }
    }

    function getAuditFilterParams() {
      return {
        admin: document.getElementById('audit-filter-admin')?.value || '',
        area: document.getElementById('audit-filter-area')?.value || '',
        from: document.getElementById('audit-filter-from')?.value || '',
        to: document.getElementById('audit-filter-to')?.value || ''
      };
    }

    function handleAuditFilterSubmit(event) { event.preventDefault(); loadAuditLog(); }
    function resetAuditFilters() { document.querySelector('.audit-filter-form')?.reset(); loadAuditLog(); }

    async function handleDeploymentSubmit(event) {
      event.preventDefault();
      const payload = {
        releaseName: document.getElementById('deployment-release')?.value.trim() || '',
        reference: document.getElementById('deployment-reference')?.value.trim() || '',
        description: document.getElementById('deployment-description')?.value.trim() || '',
        changedFiles: (document.getElementById('deployment-files')?.value || '').split(/\r?\n/).map(value => value.trim()).filter(Boolean)
      };
      try {
        const response = await fetch('api/audit.php', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify(payload) });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Deployment konnte nicht protokolliert werden.');
        document.querySelector('.deployment-form')?.reset();
        await loadAuditLog();
        alert('✅ Änderung wurde im Protokoll gespeichert.');
      } catch (error) { alert(error.message); }
    }

    function renderAuditJson(value) {
      if (!value) return '<span class="muted">–</span>';
      return `<pre class="audit-json">${escapeAdminHtml(JSON.stringify(value, null, 2))}</pre>`;
    }

    async function loadAuditLog() {
      const container = document.getElementById('admin-audit-list');
      if (!container) return;
      const params = new URLSearchParams(getAuditFilterParams());
      try {
        const response = await fetch(`api/audit.php?${params.toString()}`, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Protokoll konnte nicht geladen werden.');
        const audit = Array.isArray(data.audit) ? data.audit : [];
        const deployments = Array.isArray(data.deployments) ? data.deployments : [];
        const auditRows = audit.map(entry => `<tr><td>${escapeAdminHtml(entry.created_at)}</td><td><strong>${escapeAdminHtml(entry.user_name || entry.user_email || 'System')}</strong><br><small>${escapeAdminHtml(entry.user_email || '')}</small></td><td>${escapeAdminHtml(entry.area)}</td><td>${escapeAdminHtml(entry.summary || entry.action)}</td><td><details><summary>Details</summary><div class="audit-detail"><p><strong>${escapeAdminHtml(entry.entity_type)} #${escapeAdminHtml(entry.entity_id)}</strong></p>${renderAuditJson(entry.before)}${renderAuditJson(entry.after)}</div></details></td></tr>`).join('');
        const deploymentRows = deployments.map(entry => `<tr><td>${escapeAdminHtml(entry.created_at)}</td><td><strong>${escapeAdminHtml(entry.user_name || entry.user_email || 'System')}</strong></td><td>${escapeAdminHtml(entry.release_name)}</td><td>${escapeAdminHtml(entry.description)}</td><td>${escapeAdminHtml((entry.changedFiles || []).join(', '))}</td></tr>`).join('');
        container.innerHTML = `<h3>Admin-Aktivitäten</h3>${auditRows ? `<div class="audit-table-wrap"><table class="reservation-table audit-table"><thead><tr><th>Datum</th><th>Admin</th><th>Bereich</th><th>Änderung</th><th>Werte</th></tr></thead><tbody>${auditRows}</tbody></table></div>` : '<p class="empty-state">Noch keine Admin-Aktivitäten protokolliert.</p>'}<h3 class="audit-deployment-heading">Code-/Deployment-Protokoll</h3>${deploymentRows ? `<div class="audit-table-wrap"><table class="reservation-table audit-table"><thead><tr><th>Datum</th><th>Bearbeiter</th><th>Release</th><th>Beschreibung</th><th>Dateien</th></tr></thead><tbody>${deploymentRows}</tbody></table></div>` : '<p class="empty-state">Noch keine Deployments dokumentiert.</p>'}`;
      } catch (error) {
        container.innerHTML = `<p class="error-message">${escapeAdminHtml(error.message)}</p>`;
      }
    }

    async function loadAdminDashboard() {
      await fetchSettings();

      const container = document.getElementById("admin-reservations-list");
      populateReservationFilterOptions();
      const reservations = await fetchReservations(getReservationFilterParams());
      window.ADMIN_RESERVATIONS = reservations;

      const pendingCount = reservations.filter(r => !r.status || r.status === "angefragt").length;
      const activeCount = reservations.filter(r => r.status === "bestätigt").length;
      const onlineCount = MACHINES.filter(m => m.status === "online").length;
      const totalRev = reservations.reduce((sum, r) => sum + (parseFloat(r.totalPrice) || 0), 0);

      document.getElementById("stat-pending").innerText = pendingCount;
      document.getElementById("stat-active").innerText = activeCount;
      document.getElementById("stat-online-count").innerText = onlineCount;
      document.getElementById("stat-revenue").innerText = formatCHF(totalRev);

      if (!reservations || reservations.length === 0) {
        container.innerHTML = `
          <div class="empty-state">
            <span>A</span>
            <h3>Noch keine Reservationsanfragen</h3>
            <p>Eingegangene Kundenanfragen von der Webseite erscheinen hier in Echtzeit.</p>
          </div>
        `;
      } else {
        container.innerHTML = `
          <table class="reservation-table">
            <thead>
              <tr>
                <th>Kunde</th>
                <th>Maschine</th>
                <th>Miettyp & Zeitraum</th>
                <th>Zahlungsart</th>
                <th>Lieferung / Abholung</th>
                <th>Preis Total</th>
                <th>Reservationsstatus</th>
                      <th>Zahlungsstatus</th>
                      <th>Notiz & Speichern</th>
              </tr>
            </thead>
            <tbody>
              ${reservations.map(r => {
                const isStunde = r.rentalType === 'stunde';
                const durationStr = isStunde ? 
                  `⏱️ ${r.startDate || '-'} bis ${r.endDate || '-'} (${r.days || 1} Tage · ${r.hoursPerDay || 3}h/Tag)` : 
                  `📅 ${r.startDate || '-'} bis ${r.endDate || '-'} (${r.days || 1} Tage · Ganztag)`;
                const discountStr = r.discountPercent > 0 ? `<br><small style="color:var(--orange-dark); font-weight:800;">${r.discountPercent}% Rabatt angewendet</small>` : '';

                const deliveryInfo = r.handover === 'lieferung' ? 
                  `🚚 <strong>Lieferung ab Romanshorn (${r.deliveryKm || 0} km)</strong><br><small style="color:var(--orange-dark); font-weight:800;">+${formatCHF(r.deliveryFee || 0)}</small>` : 
                  `🏬 Selbstabholung`;

                const payKey = r.paymentMethod || 'twint';
                const payLbl = r.paymentLabel || 'TWINT';
                let payBadgeClass = 'twint';
                if (payKey === 'bank') payBadgeClass = 'bank';
                if (payKey === 'mastercard') payBadgeClass = 'mastercard';
                if (payKey === 'cash') payBadgeClass = 'cash';

                return `
                  <tr>
                    <td>
                      <strong>${escapeAdminHtml(r.customerName)}</strong><br>
                      <small style="color:var(--muted);">${escapeAdminHtml(r.email)} · ${escapeAdminHtml(r.phone || '-')}</small>
                    </td>
                    <td><strong>${escapeAdminHtml(r.machineName)}</strong></td>
                    <td>${durationStr}${discountStr}</td>
                    <td><span class="pay-badge ${payBadgeClass}">${payLbl}</span></td>
                    <td>${deliveryInfo}</td>
                    <td><strong>${r.totalPrice ? formatCHF(r.totalPrice) : '-'}</strong></td>
                    <td>
                      <select id="reservation-status-${r.id}" class="action-select">
                        <option value="angefragt" ${r.status === 'angefragt' ? 'selected' : ''}>Angefragt</option>
                        <option value="bestätigt" ${r.status === 'bestätigt' ? 'selected' : ''}>Bestätigt</option>
                        <option value="abgeschlossen" ${r.status === 'abgeschlossen' ? 'selected' : ''}>Abgeschlossen</option>
                        <option value="storniert" ${r.status === 'storniert' ? 'selected' : ''}>Storniert</option>
                      </select>
                    </td>
                    <td>
                      <select id="reservation-payment-${r.id}" class="action-select">
                        <option value="offen" ${r.paymentStatus === 'offen' || !r.paymentStatus ? 'selected' : ''}>Offen</option>
                        <option value="teilweise" ${r.paymentStatus === 'teilweise' ? 'selected' : ''}>Teilweise</option>
                        <option value="bezahlt" ${r.paymentStatus === 'bezahlt' ? 'selected' : ''}>Bezahlt</option>
                        <option value="zurueckerstattet" ${r.paymentStatus === 'zurueckerstattet' ? 'selected' : ''}>Rückerstattet</option>
                      </select>
                    </td>
                    <td class="reservation-admin-actions">
                      <button type="button" class="button button-small button-outline" onclick="openReservationDetail(${Number(r.id)})">Details</button>
                      <input id="reservation-note-${r.id}" type="text" placeholder="Interne Notiz" value="${escapeAdminHtml(r.adminNote || '')}">
                      <button type="button" class="button button-small" onclick="handleReservationSave(${r.id})">Speichern</button>
                      <button type="button" class="button button-small button-outline" onclick="quickReservationAction(${r.id}, 'confirm')">Reservation bestätigen</button>
                      <button type="button" class="button button-small button-outline" onclick="quickReservationAction(${r.id}, 'paid')">Zahlung erhalten</button>
                    </td>
                  </tr>
                `;
              }).join("")}
            </tbody>
          </table>
        `;
      }

      renderAdminMasterCalendar();
      renderCategoriesManager();
      renderAdminProductsTable();
      renderDeliveryConfigManager();
      renderPaymentSettingsManager();
      renderDiscountTiersEditor();

      handleAdminUrlDeepLinking();
    }

    function handleAdminUrlDeepLinking() {
      const hash = window.location.hash.replace('#', '').trim();
      if (!hash) {
        showAdminTab('calendar');
        return;
      }

      if (hash.startsWith('category-editor-')) {
        const catName = decodeURIComponent(hash.replace('category-editor-', ''));
        openDedicatedCategoryEditor(catName);
      } else if (hash.startsWith('category-')) {
        const catName = decodeURIComponent(hash.replace('category-', ''));
        showAdminTab('products', catName);
      } else if (hash.startsWith('product-editor-')) {
        const mId = decodeURIComponent(hash.replace('product-editor-', ''));
        openSingleProductEditor(mId);
      } else if (['calendar', 'categories', 'products', 'delivery', 'payments', 'reservations', 'discounts', 'audit'].includes(hash)) {
        showAdminTab(hash);
      } else {
        showAdminTab('calendar');
      }
    }

    function renderAdminProductsTable() {
      const container = document.getElementById("admin-products-table-container");
      if (!container) return;

      const searchVal = (document.getElementById("admin-product-search")?.value || "").toLowerCase();
      const filtered = MACHINES.filter(m => {
        const matchesCategory = selectedCategoryFilter === "all" || m.category === selectedCategoryFilter;
        const matchesSearch = m.name.toLowerCase().includes(searchVal) || 
                              m.category.toLowerCase().includes(searchVal) || 
                              m.id.toLowerCase().includes(searchVal);
        return matchesCategory && matchesSearch;
      });

      if (filtered.length === 0) {
        container.innerHTML = `
          <div class="empty-state">
            <span>🔍</span>
            <h3>Keine Produkte in dieser Kategorie</h3>
            <p>Aktuell sind keine Baumaschinen für den Filter "${selectedCategoryFilter}" vorhanden.</p>
          </div>
        `;
        return;
      }

      container.innerHTML = `
        <table class="reservation-table">
          <thead>
            <tr>
              <th>Cover</th>
              <th>Maschine</th>
              <th>Kategorie</th>
              <th>Aufladepauschale</th>
              <th>Transport-Klasse</th>
              <th>Bilder</th>
              <th>Stundentarif</th>
              <th>Status</th>
              <th>Aktion</th>
            </tr>
          </thead>
          <tbody>
            ${filtered.map(m => {
              const dayFlat = m.hourlyPrice * (m.maxHoursPerDay || 5);
              const imgCount = m.images ? m.images.length : 1;
              const tClass = m.transportClass || "mittel";
              const loadFee = m.loadUnloadFee !== undefined ? m.loadUnloadFee : 35;
              
              let tBadge = '<span class="badge" style="background:#eff6ff; color:#1e40af;">🔵 Mittel (1.4x)</span>';
              if (tClass === 'leicht') tBadge = '<span class="badge" style="background:#f0fdf4; color:#166534;">🟢 Leicht (1.0x)</span>';
              if (tClass === 'schwer') tBadge = '<span class="badge" style="background:#fff7ed; color:#c2410c;">🟠 Schwer (2.2x)</span>';

              return `
                <tr>
                  <td>
                    <div style="width: 50px; height: 35px; border-radius: 4px; overflow: hidden; border: 1px solid var(--line);">
                      <img src="${m.image || 'brand/rent-a-bau-hero.png'}" alt="${m.name}" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                  </td>
                  <td>
                    <strong>${m.name}</strong><br>
                    <small style="color:var(--muted);">ID: ${m.id}</small>
                  </td>
                  <td><span class="category">${m.category}</span></td>
                  <td><strong style="color:var(--orange-dark);">CHF ${loadFee}</strong></td>
                  <td>${tBadge}</td>
                  <td><span class="badge" style="background:#e0f2fe; color:#0369a1;">📷 ${imgCount}</span></td>
                  <td><strong>${formatCHF(m.hourlyPrice)} / Std.</strong></td>
                  <td>
                    <span class="status-pill ${m.status === 'online' ? '' : 'warning'}">
                      ${m.status === 'online' ? '🟢 Online' : '🟡 In Wartung'}
                    </span>
                  </td>
                  <td>
                    <div style="display: flex; gap: 6px;">
                      <button class="button button-small" onclick="openSingleProductEditor('${m.id}')">✏️ Bearbeiten & Details</button>
                      <button class="button button-small button-outline" onclick="handleDeleteMachine('${m.id}', '${m.name}')" style="color: #b91c1c; border-color: #fecaca;">✕</button>
                    </div>
                  </td>
                </tr>
              `;
            }).join("")}
          </tbody>
        </table>
      `;
    }

    function renderDiscountTiersEditor() {
      const container = document.getElementById("discount-tiers-container");
      if (!container) return;

      let tiers = SYSTEM_SETTINGS.discounts || [
        { minDays: 3, percent: 10 },
        { minDays: 7, percent: 15 },
        { minDays: 14, percent: 25 }
      ];

      if (!Array.isArray(tiers)) {
        tiers = [
          { minDays: 3, percent: tiers.tier3 || 10 },
          { minDays: 7, percent: tiers.tier7 || 15 },
          { minDays: 14, percent: tiers.tier14 || 25 }
        ];
      }

      container.innerHTML = tiers.map((t, idx) => `
        <div style="display: flex; gap: 12px; align-items: center; background: var(--paper); padding: 12px; border-radius: 6px; border: 1px solid var(--line);" id="discount-row-${idx}">
          <div style="flex: 1;">
            <label style="font-size: 11px; font-weight: 800; display: block;">Ab Miettagen (Mindestdauer)</label>
            <input type="number" value="${t.minDays}" min="1" max="365" class="disc-days-input" required style="width: 100%; padding: 8px;">
          </div>
          <div style="flex: 1;">
            <label style="font-size: 11px; font-weight: 800; display: block;">Rabatt in Prozent (%)</label>
            <input type="number" value="${t.percent}" min="1" max="90" class="disc-pct-input" required style="width: 100%; padding: 8px;">
          </div>
          <button type="button" style="color: red; border: 0; background: transparent; font-size: 18px; font-weight: 900; cursor: pointer; margin-top: 14px;" onclick="document.getElementById('discount-row-${idx}').remove()" title="Stufe löschen">✕</button>
        </div>
      `).join("");
    }

    function addDiscountTierRow() {
      const container = document.getElementById("discount-tiers-container");
      if (!container) return;
      const idx = Date.now();
      const div = document.createElement("div");
      div.id = `discount-row-${idx}`;
      div.style.display = "flex";
      div.style.gap = "12px";
      div.style.alignItems = "center";
      div.style.background = "var(--paper)";
      div.style.padding = "12px";
      div.style.borderRadius = "6px";
      div.style.border = "1px solid var(--line)";

      div.innerHTML = `
        <div style="flex: 1;">
          <label style="font-size: 11px; font-weight: 800; display: block;">Ab Miettagen (Mindestdauer)</label>
          <input type="number" value="5" min="1" max="365" class="disc-days-input" required style="width: 100%; padding: 8px;">
        </div>
        <div style="flex: 1;">
          <label style="font-size: 11px; font-weight: 800; display: block;">Rabatt in Prozent (%)</label>
          <input type="number" value="12" min="1" max="90" class="disc-pct-input" required style="width: 100%; padding: 8px;">
        </div>
        <button type="button" style="color: red; border: 0; background: transparent; font-size: 18px; font-weight: 900; cursor: pointer; margin-top: 14px;" onclick="this.parentElement.remove()" title="Stufe löschen">✕</button>
      `;
      container.appendChild(div);
    }

    async function handleSaveDynamicDiscounts(e) {
      e.preventDefault();
      const daysInputs = document.querySelectorAll(".disc-days-input");
      const pctInputs = document.querySelectorAll(".disc-pct-input");

      const newTiers = [];
      daysInputs.forEach((dEl, i) => {
        const minDays = parseInt(dEl.value);
        const percent = parseInt(pctInputs[i] ? pctInputs[i].value : 0);
        if (!isNaN(minDays) && !isNaN(percent)) {
          newTiers.push({ minDays, percent });
        }
      });

      newTiers.sort((a, b) => a.minDays - b.minDays);

      await saveSettings({ discounts: newTiers });
      alert("🎉 Die neuen Staffelrabatte wurden erfolgreich gespeichert!");
      loadAdminDashboard();
    }

    function openSingleProductEditor(machineId) {
      const machine = MACHINES.find(m => m.id === machineId);
      if (!machine) return;

      currentEditingMachineId = machineId;
      editingMachineImages = machine.images && machine.images.length > 0 ? [...machine.images] : [machine.image || "brand/rent-a-bau-hero.png"];
      editingMachineCover = machine.image || editingMachineImages[0];
      editingMachineAccessories = machine.accessories ? JSON.parse(JSON.stringify(machine.accessories)) : [];

      showAdminTab('products');
      document.getElementById("admin-products-list-view").style.display = "none";
      document.getElementById("admin-single-product-editor-view").style.display = "block";

      updateUrlState(`product-editor-${encodeURIComponent(machineId)}`);
      renderEditingSingleProductForm(machine);
    }

    function closeSingleProductEditor() {
      document.getElementById("admin-single-product-editor-view").style.display = "none";
      document.getElementById("admin-products-list-view").style.display = "block";
      currentEditingMachineId = null;
      updateUrlState("products");
    }

    function renderEditingSingleProductForm(machine) {
      const editorContainer = document.getElementById("single-product-editor-container");
      const dayFlatPrice = machine.hourlyPrice * (machine.maxHoursPerDay || 5);
      const specs = machine.specs || {};
      const tClass = machine.transportClass || "mittel";
      const loadFee = machine.loadUnloadFee !== undefined ? machine.loadUnloadFee : 35;

      editorContainer.innerHTML = `
        <div class="product-detail-card">
          <div class="product-detail-header">
            <div>
              <strong style="font-size: 24px; color: var(--black);">${machine.name}</strong>
              <span class="category" style="margin-left: 10px;">${machine.category}</span>
              ${machine.badge ? `<span class="badge" style="margin-left: 6px;">${machine.badge}</span>` : ''}
              <small style="color: var(--muted); display: block; margin-top: 4px;">Direkt-URL: <strong>admin.php#product-editor-${encodeURIComponent(machine.id)}</strong></small>
            </div>
            <div>
              <span class="status-pill ${machine.status === 'online' ? '' : 'warning'}">
                ${machine.status === 'online' ? '🟢 Online & Buchbar' : '🟡 In Wartung / Gesperrt'}
              </span>
            </div>
          </div>

          <form onsubmit="handleSaveFullMachineDetails(event, '${machine.id}')">
            
            <div class="form-section-title">1. Bilder-Galerie & Cover-Bild bestimmen</div>
            <div style="margin-bottom: 16px; padding: 16px; background: var(--paper); border: 1px solid var(--line); border-radius: 6px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <label style="font-size: 13px; font-weight: 800;">Produktbilder (${editingMachineImages.length})</label>
                <input type="file" accept="image/*" multiple onchange="uploadMultipleImagesForEditing(this)" style="font-size: 12px;">
              </div>

              <div id="editing-gallery-container" class="gallery-grid">
                ${editingMachineImages.map((imgPath, idx) => {
                  const isCover = imgPath === editingMachineCover;
                  return `
                    <div class="gallery-card ${isCover ? 'is-cover' : ''}">
                      ${isCover ? '<span class="cover-badge">⭐ COVER BILD</span>' : ''}
                      <img src="${imgPath}" alt="Galerie ${idx}">
                      <div style="display: flex; flex-direction: column; gap: 4px; width: 100%;">
                        ${!isCover ? `<button type="button" class="button button-small button-outline" onclick="setEditingCover('${imgPath}')">⭐ Als Cover</button>` : '<span style="font-size: 11px; font-weight: 800; color: #f59e0b; text-align: center;">Titelbild</span>'}
                        ${editingMachineImages.length > 1 ? `<button type="button" class="button button-small button-outline" onclick="removeEditingImage(${idx})" style="color: red;">✕ Löschen</button>` : ''}
                      </div>
                    </div>
                  `;
                }).join("")}
              </div>
            </div>

            <div class="form-section-title">2. Passendes Zubehör & Zusatzgeräte (Cross-Selling)</div>
            <div style="margin-bottom: 16px; padding: 16px; background: #fff8f5; border: 1px solid #ffe2d4; border-radius: 6px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 13px; font-weight: 800; color: var(--orange-dark);">💡 Passendes Zubehör für ${machine.name}</span>
                <button type="button" class="button button-small button-outline" onclick="addAccessoryRow()">＋ Zubehör hinzufügen</button>
              </div>

              <div id="editing-accessories-container" style="display: flex; flex-direction: column; gap: 8px;">
                ${editingMachineAccessories.map((acc, idx) => `
                  <div style="display: flex; gap: 10px; align-items: center;" id="acc-row-${idx}">
                    <input type="text" value="${acc.name}" placeholder="Zubehör-Name (z.B. Abbruchhammer)" class="acc-name-input" style="flex: 2; padding: 8px; font-weight: 800;">
                    <input type="number" value="${acc.price}" placeholder="Aufpreis CHF (z.B. 40 oder 0 für Inklusive)" class="acc-price-input" style="flex: 1; padding: 8px;">
                    <button type="button" style="color: red; border: 0; background: transparent; font-weight: 900; font-size: 16px; cursor: pointer;" onclick="document.getElementById('acc-row-${idx}').remove()">✕</button>
                  </div>
                `).join("")}
              </div>
            </div>

            <div class="form-section-title">3. Produkt-Stammdaten, Aufladepauschale & Transport-Klasse</div>
            <div class="grid-4" style="margin-bottom: 14px;">
              <div>
                <label style="font-size: 12px; font-weight: 800;">Maschinen-Name</label>
                <input type="text" id="edit-name" value="${machine.name}" required style="width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Kategorie</label>
                <select id="edit-category" required style="width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px; font-weight: 800;">
                  ${CATEGORIES.map(c => `<option value="${c}" ${machine.category === c ? 'selected' : ''}>${c}</option>`).join("")}
                </select>
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Auflad- & Abladepauschale (CHF) *</label>
                <input type="number" id="edit-loadfee" value="${loadFee}" min="0" required style="width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px; font-weight: 800; background: #fffdf5;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Transport-Klasse (Lieferfaktor) *</label>
                <select id="edit-transclass" required style="width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px; font-weight: 800; background: #eff6ff;">
                  <option value="leicht" ${tClass === 'leicht' ? 'selected' : ''}>🟢 Leichttransport (Anhänger / Bus - 1.0x)</option>
                  <option value="mittel" ${tClass === 'mittel' ? 'selected' : ''}>🔵 Mitteltransport (3.5t Transporter - 1.4x)</option>
                  <option value="schwer" ${tClass === 'schwer' ? 'selected' : ''}>🟠 Schwertransport (LKW / Tieflader - 2.2x)</option>
                </select>
              </div>
            </div>

            <div class="grid-2" style="margin-bottom: 14px;">
              <div>
                <label style="font-size: 12px; font-weight: 800;">Badge / Kennzeichnung (optional)</label>
                <input type="text" id="edit-badge" value="${machine.badge || ''}" placeholder="z. B. Neu, Beliebt, Bestseller (oder leer)" style="width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Status *</label>
                <select id="edit-status" required style="width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px; font-weight: 800;">
                  <option value="online" ${machine.status === 'online' ? 'selected' : ''}>🟢 Online & Buchbar</option>
                  <option value="maintenance" ${machine.status !== 'online' ? 'selected' : ''}>🟡 In Wartung / Gesperrt</option>
                </select>
              </div>
            </div>

            <div style="margin-bottom: 16px;">
              <label style="font-size: 12px; font-weight: 800;">Beschreibungstext</label>
              <input type="text" id="edit-desc" value="${machine.description || ''}" style="width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px;">
            </div>

            <div class="form-section-title">4. Tarife, Tagesdeckel & Kaution</div>
            <div class="grid-4" style="margin-bottom: 16px;">
              <div>
                <label style="font-size: 12px; font-weight: 800;">Stundentarif (CHF / Std.)</label>
                <input type="number" id="edit-price" value="${machine.hourlyPrice}" min="1" required style="width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Tagesdeckel (Max. Std./Tag)</label>
                <input type="number" id="edit-cap" value="${machine.maxHoursPerDay || 5}" min="1" max="12" required style="width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Wochenendtarif (CHF / 2 Tage)</label>
                <input type="number" id="edit-weekend" value="${machine.weekendPrice || (dayFlatPrice * 2)}" min="0" required style="width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 800;">Kaution / Depot (CHF)</label>
                <input type="number" id="edit-depot" value="${machine.deposit}" min="0" step="50" required style="width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px;">
              </div>
            </div>

            <div class="form-section-title">
              <span>5. Technische Spezifikationen & Kriterien</span>
              <button type="button" class="button button-small button-outline" onclick="addSingleSpecRow()">＋ Neues Kriterium hinzufügen</button>
            </div>

            <div id="single-specs-container" class="grid-2" style="margin-bottom: 20px;">
              ${Object.entries(specs).map(([k, val], idx) => `
                <div style="display: flex; gap: 8px; align-items: center;" id="single-spec-row-${idx}">
                  <input type="text" value="${k}" placeholder="Kriterium (z. B. gewicht)" class="single-spec-key" style="width: 45%; padding: 8px; font-weight: 800;">
                  <input type="text" value="${val}" placeholder="Wert (z. B. 1'800 kg)" class="single-spec-val" style="width: 45%; padding: 8px;">
                  <button type="button" style="color: red; border: 0; background: transparent; cursor: pointer; font-weight: 900; font-size: 16px;" onclick="document.getElementById('single-spec-row-${idx}').remove()">✕</button>
                </div>
              `).join("")}
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 2px solid var(--paper);">
              <button type="button" class="button button-outline" onclick="closeSingleProductEditor()">← Abbrechen & Zurück</button>
              <button type="submit" class="button">
                💾 Details für ${machine.name} speichern ✓
              </button>
            </div>
          </form>
        </div>
      `;
    }

    function addAccessoryRow() {
      const container = document.getElementById("editing-accessories-container");
      if (!container) return;
      const idx = Date.now();
      const div = document.createElement("div");
      div.id = `acc-row-${idx}`;
      div.style.display = "flex";
      div.style.gap = "10px";
      div.style.alignItems = "center";
      div.innerHTML = `
        <input type="text" placeholder="Zubehör-Name (z.B. Abbruchhammer)" class="acc-name-input" style="flex: 2; padding: 8px; font-weight: 800;">
        <input type="number" placeholder="Aufpreis CHF (z.B. 40)" class="acc-price-input" style="flex: 1; padding: 8px;">
        <button type="button" style="color: red; border: 0; background: transparent; font-weight: 900; font-size: 16px; cursor: pointer;" onclick="this.parentElement.remove()">✕</button>
      `;
      container.appendChild(div);
    }

    async function uploadMultipleImagesForEditing(fileInput) {
      if (!fileInput.files || fileInput.files.length === 0) return;
      const formData = new FormData();
      for (let i = 0; i < fileInput.files.length; i++) {
        formData.append("images[]", fileInput.files[i]);
      }

      try {
        const res = await fetch("api/upload.php", { method: "POST", body: formData });
        if (res.ok) {
          const data = await res.json();
          if (data.imagePaths && data.imagePaths.length > 0) {
            editingMachineImages = [...editingMachineImages, ...data.imagePaths];
            if (!editingMachineCover) editingMachineCover = editingMachineImages[0];
            
            const machine = MACHINES.find(m => m.id === currentEditingMachineId);
            renderEditingSingleProductForm(machine);
            alert(`🎉 ${data.imagePaths.length} neue(s) Bild(er) hochgeladen!`);
          }
        }
      } catch (err) {
        console.error(err);
      }
    }

    function setEditingCover(imgPath) {
      editingMachineCover = imgPath;
      const machine = MACHINES.find(m => m.id === currentEditingMachineId);
      renderEditingSingleProductForm(machine);
    }

    function removeEditingImage(idx) {
      const removed = editingMachineImages.splice(idx, 1);
      if (editingMachineCover === removed[0]) {
        editingMachineCover = editingMachineImages[0] || "brand/rent-a-bau-hero.png";
      }
      const machine = MACHINES.find(m => m.id === currentEditingMachineId);
      renderEditingSingleProductForm(machine);
    }

    function renderNewMachineGalleryPreview() {
      const container = document.getElementById("new-m-gallery-preview");
      if (!container) return;

      if (!newMachineUploadedImages || newMachineUploadedImages.length === 0) {
        newMachineUploadedImages = ["brand/rent-a-bau-hero.png"];
      }
      if (!newMachineCoverImage || !newMachineUploadedImages.includes(newMachineCoverImage)) {
        newMachineCoverImage = newMachineUploadedImages[0];
      }

      container.innerHTML = newMachineUploadedImages.map((imgPath, idx) => {
        const isCover = imgPath === newMachineCoverImage;
        return `
          <div class="gallery-card ${isCover ? 'is-cover' : ''}">
            ${isCover ? '<span class="cover-badge">⭐ COVER</span>' : ''}
            <img src="${imgPath}" alt="Vorschau ${idx}">
            <div style="display: flex; flex-direction: column; gap: 4px; width: 100%;">
              ${!isCover ? `<button type="button" class="button button-small button-outline" onclick="setNewMachineCover('${imgPath}')">⭐ Als Cover</button>` : '<span style="font-size: 11px; font-weight: 800; color: #f59e0b; text-align: center;">Titelbild</span>'}
              ${newMachineUploadedImages.length > 1 ? `<button type="button" class="button button-small button-outline" onclick="removeNewMachineImage(${idx})" style="color: red;">✕ Löschen</button>` : ''}
            </div>
          </div>
        `;
      }).join("");
    }

    function setNewMachineCover(imgPath) {
      newMachineCoverImage = imgPath;
      renderNewMachineGalleryPreview();
    }

    function removeNewMachineImage(idx) {
      const removed = newMachineUploadedImages.splice(idx, 1);
      if (newMachineCoverImage === removed[0]) {
        newMachineCoverImage = newMachineUploadedImages[0] || "brand/rent-a-bau-hero.png";
      }
      renderNewMachineGalleryPreview();
    }

    async function uploadMultipleImagesForNewMachine(fileInput) {
      if (!fileInput.files || fileInput.files.length === 0) return;
      const formData = new FormData();
      for (let i = 0; i < fileInput.files.length; i++) {
        formData.append("images[]", fileInput.files[i]);
      }

      try {
        const res = await fetch("api/upload.php", { method: "POST", body: formData });
        if (res.ok) {
          const data = await res.json();
          if (data.imagePaths && data.imagePaths.length > 0) {
            if (newMachineUploadedImages.length === 1 && newMachineUploadedImages[0] === "brand/rent-a-bau-hero.png") {
              newMachineUploadedImages = [...data.imagePaths];
              newMachineCoverImage = data.imagePaths[0];
            } else {
              newMachineUploadedImages = [...newMachineUploadedImages, ...data.imagePaths];
            }
            if (!newMachineCoverImage) newMachineCoverImage = newMachineUploadedImages[0];
            renderNewMachineGalleryPreview();
            alert(`🎉 ${data.imagePaths.length} neue(s) Bild(er) erfolgreich hochgeladen!`);
          }
        } else {
          const err = await res.json().catch(() => ({}));
          alert(`Fehler beim Bildupload: ${err.error || 'Serverfehler'}`);
        }
      } catch (err) {
        console.error("Upload error:", err);
        alert("Upload fehlgeschlagen. Bitte prüfe die Verbindung oder Dateigrösse.");
      }
    }

    function addSingleSpecRow() {
      const container = document.getElementById("single-specs-container");
      if (!container) return;
      const idx = Date.now();
      const div = document.createElement("div");
      div.id = `single-spec-row-${idx}`;
      div.style.display = "flex";
      div.style.gap = "8px";
      div.style.alignItems = "center";
      div.innerHTML = `
        <input type="text" placeholder="Neues Kriterium (z. B. laenge)" class="single-spec-key" style="width: 45%; padding: 8px; font-weight: 800;">
        <input type="text" placeholder="Wert (z. B. 2.4 m)" class="single-spec-val" style="width: 45%; padding: 8px;">
        <button type="button" style="color: red; border: 0; background: transparent; cursor: pointer; font-weight: 900; font-size: 16px;" onclick="this.parentElement.remove()">✕</button>
      `;
      container.appendChild(div);
    }

    async function handleCreateNewMachine(e) {
      e.preventDefault();
      const name = (document.getElementById("new-m-name")?.value || "").trim();
      const category = document.getElementById("new-m-cat")?.value || "";
      const loadFee = parseFloat(document.getElementById("new-m-loadfee")?.value) || 40;
      const transClass = document.getElementById("new-m-transclass")?.value || "mittel";
      const badge = document.getElementById("new-m-badge") ? document.getElementById("new-m-badge").value.trim() : "";
      const status = document.getElementById("new-m-status") ? document.getElementById("new-m-status").value : "online";
      const desc = document.getElementById("new-m-desc")?.value || `Starke Baumaschine in der Kategorie ${category}.`;
      
      const hourlyPrice = parseFloat(document.getElementById("new-m-price")?.value) || 0;
      const maxHoursPerDay = parseInt(document.getElementById("new-m-cap")?.value) || 5;
      const weekendPrice = parseFloat(document.getElementById("new-m-weekend")?.value) || (hourlyPrice * maxHoursPerDay * 2 * 0.9);
      const deposit = parseFloat(document.getElementById("new-m-depot")?.value) || 0;

      const newId = name.toLowerCase().replace(/[^a-z0-9]/g, "-").replace(/-+/g, "-");
      if (!newId) {
        alert("Bitte einen gültigen Namen für die Maschine eingeben.");
        return;
      }

      const specs = {};
      const specValInputs = document.querySelectorAll(".new-m-spec-val");
      specValInputs.forEach(input => {
        const k = input.getAttribute("data-key");
        const val = input.value.trim();
        if (k && val) specs[k] = val;
      });

      const newMachineObj = {
        id: newId,
        name: name,
        category: category,
        transportClass: transClass,
        loadUnloadFee: loadFee,
        description: desc,
        badge: badge,
        hourlyPrice: hourlyPrice,
        maxHoursPerDay: maxHoursPerDay,
        weekendPrice: weekendPrice,
        deposit: deposit,
        status: status,
        image: newMachineCoverImage || (newMachineUploadedImages && newMachineUploadedImages[0]) || "brand/rent-a-bau-hero.png",
        images: (newMachineUploadedImages && newMachineUploadedImages.length > 0) ? newMachineUploadedImages : ["brand/rent-a-bau-hero.png"],
        accessories: [],
        specs: specs
      };

      const updatedMachines = SYSTEM_SETTINGS.machines || {};
      updatedMachines[newId] = newMachineObj;

      await saveSettings({ machines: updatedMachines });
      alert(`🎉 Das neue Produkt "${name}" in Kategorie "${category}" wurde angelegt!`);
      document.getElementById("create-machine-card").style.display = "none";
      await loadAdminDashboard();
      openSingleProductEditor(newId);
    }

    async function handleSaveFullMachineDetails(e, machineId) {
      e.preventDefault();

      const name = document.getElementById("edit-name").value;
      const category = document.getElementById("edit-category").value;
      const loadFee = parseFloat(document.getElementById("edit-loadfee").value) || 35;
      const transClass = document.getElementById("edit-transclass").value || "mittel";
      const badge = document.getElementById("edit-badge") ? document.getElementById("edit-badge").value.trim() : "";
      const status = document.getElementById("edit-status") ? document.getElementById("edit-status").value : "online";
      const description = document.getElementById("edit-desc") ? document.getElementById("edit-desc").value : "";

      const hourlyPrice = parseFloat(document.getElementById("edit-price").value);
      const maxHoursPerDay = parseInt(document.getElementById("edit-cap").value);
      const weekendPrice = parseFloat(document.getElementById("edit-weekend").value);
      const deposit = parseFloat(document.getElementById("edit-depot").value);

      const accNames = document.querySelectorAll(".acc-name-input");
      const accPrices = document.querySelectorAll(".acc-price-input");
      const updatedAccessories = [];

      accNames.forEach((nEl, i) => {
        const n = nEl.value.trim();
        const p = parseFloat(accPrices[i] ? accPrices[i].value : 0) || 0;
        if (n) updatedAccessories.push({ name: n, price: p });
      });

      const keys = document.querySelectorAll(".single-spec-key");
      const vals = document.querySelectorAll(".single-spec-val");
      const updatedSpecs = {};

      keys.forEach((keyEl, i) => {
        const k = keyEl.value.trim().toLowerCase();
        const v = vals[i] ? vals[i].value.trim() : "";
        if (k) updatedSpecs[k] = v;
      });

      const updatedMachines = SYSTEM_SETTINGS.machines || {};
      updatedMachines[machineId] = {
        id: machineId,
        name,
        category,
        transportClass: transClass,
        loadUnloadFee: loadFee,
        badge,
        status,
        image: editingMachineCover,
        images: editingMachineImages,
        accessories: updatedAccessories,
        description,
        hourlyPrice,
        maxHoursPerDay,
        weekendPrice,
        deposit,
        specs: updatedSpecs
      };

      await saveSettings({ machines: updatedMachines });
      alert(`✅ Details für "${name}" wurden gespeichert!`);
      await loadAdminDashboard();
      closeSingleProductEditor();
    }

    async function handleDeleteMachine(machineId, name) {
      if (!confirm(`Möchtest du die Maschine "${name}" wirklich aus dem Mietkatalog löschen?`)) return;
      await deleteMachineFromApi(machineId);
      loadAdminDashboard();
    }

    async function handleStatusChange(id, newStatus) {
      try { await updateReservationStatus(id, newStatus); await loadAdminDashboard(); } catch (error) { alert(error.message); }
    }

    async function handleReservationSave(id) {
      const status = document.getElementById(`reservation-status-${id}`)?.value || 'angefragt';
      const paymentStatus = document.getElementById(`reservation-payment-${id}`)?.value || 'offen';
      const adminNote = document.getElementById(`reservation-note-${id}`)?.value || '';
      try {
        await updateReservationDetails(id, { status, paymentStatus, adminNote });
        await loadAdminDashboard();
      } catch (error) { alert(error.message); }
    }

    async function quickReservationAction(id, action) {
      const details = action === 'confirm' ? { status: 'bestätigt' } : { paymentStatus: 'bezahlt' };
      try {
        await updateReservationDetails(id, details);
        await loadAdminDashboard();
      } catch (error) { alert(error.message); }
    }

    window.addEventListener("hashchange", handleAdminUrlDeepLinking);
    document.addEventListener("DOMContentLoaded", loadAdminDashboard);
  </script>
  <script src="assets/js/category-fields.js?v=<?= filemtime(__DIR__ . '/../assets/js/category-fields.js') ?>"></script>
  <script src="assets/js/category-templates.js?v=<?= filemtime(__DIR__ . '/../assets/js/category-templates.js') ?>"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
