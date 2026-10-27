# Reservations-Detailansicht und Filter Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Eine geschützte Admin-Detailansicht mit serverseitigen Filtern für Kunde, Datum, Maschine und Status bereitstellen.

**Architecture:** Die bestehende Reservations-API wird um validierte GET-Parameter und einen Einzelabruf erweitert. Das Admin-Template erhält ein Filterformular, eine Ergebnisliste und ein Detailpanel; JavaScript lädt Daten über die API und escaped alle Ausgaben. Rechnungen bleiben als spätere, getrennte Ausbaustufe an die Reservations-ID gekoppelt.

**Tech Stack:** PHP 8+, PDO/MySQL, bestehendes Admin-Template, Vanilla JavaScript, CSS, PowerShell-Tests.

**Spec:** `docs/superpowers/specs/2026-09-07-reservation-details-design.md`

## Global Constraints

- Adminzugriff erfolgt ausschließlich über die bestehende `requireAdmin()`-Prüfung.
- Bestehende Statuswerte bleiben `angefragt`, `bestätigt`, `abgeschlossen`, `storniert`.
- Alle SQL-Abfragen verwenden PDO-Parameter; keine bestehenden Dateien werden gelöscht.
- Datumsfilter verwenden ISO-Format `YYYY-MM-DD` und inklusive Zeitraumüberschneidung.
- Bestehende Reservationen, Authentifizierung und mobile Navigation dürfen nicht regressieren.

---

### Task 1: API-Filter und Detailabruf

**Files:**
- Modify: `api/reservations.php` (GET-Zweig)
- Test: `tests/reservation-details-api.ps1`

**Interfaces:**
- Consumes: GET `/api/reservations.php?customer=&machineId=&startDate=&endDate=&status=` and optional `id`.
- Produces: `{ reservations: [...] }` for filtered lists; `{ reservation: {...} }` for `id`; HTTP 400/422/404 for invalid requests.

- [ ] **Step 1: Write failing API tests**

  Create a PowerShell test that logs in as admin, creates/uses fixture reservations, verifies `customer`, `machineId`, `status`, date-overlap filters and `id`, and asserts unauthenticated access is rejected.

- [ ] **Step 2: Run the test to verify it fails**

  Run `powershell -NoProfile -ExecutionPolicy Bypass -File tests/reservation-details-api.ps1`.
  Expected: FAIL because filter parameters and `id` response are not implemented.

- [ ] **Step 3: Implement the minimal API behavior**

  Parse and validate the six GET parameters, build a parameterized `WHERE` clause, apply date overlap as `start_date <= :endDate AND end_date >= :startDate`, and return either one mapped reservation or the filtered list. Keep the existing anonymous availability response unchanged.

- [ ] **Step 4: Run API tests and existing auth tests**

  Run the new test plus `tests/authentication.ps1`, `tests/admin-authorization.ps1`, and `tests/reservation-admin-workflow.ps1`.
  Expected: PASS.

- [ ] **Step 5: Commit**

  `git add api/reservations.php tests/reservation-details-api.ps1 && git commit -m "feat: add reservation detail and filters api"`

### Task 2: Admin-Filteroberfläche und Detailpanel

**Files:**
- Modify: `templates/admin.php` (Reservations-Tab)
- Modify: `assets/js/app.js` (Admin reservation functions)
- Modify: `assets/css/style.css` (filter/detail responsive styles)
- Test: `tests/reservation-details-ui.ps1`

**Interfaces:**
- Consumes: API interface from Task 1.
- Produces: `loadAdminReservations(filters)`, `openReservationDetail(id)`, `closeReservationDetail()` and accessible filter controls in the reservations tab.

- [ ] **Step 1: Write failing structural UI tests**

  Assert the admin template contains fields `reservation-filter-customer`, `reservation-filter-machine`, `reservation-filter-start`, `reservation-filter-end`, `reservation-filter-status`, a filter submit/reset control, and a detail container. Assert JavaScript defines the three functions and CSS contains responsive detail classes.

- [ ] **Step 2: Run the test to verify it fails**

  Run `powershell -NoProfile -ExecutionPolicy Bypass -File tests/reservation-details-ui.ps1`.
  Expected: FAIL because the filter controls and detail functions do not exist.

- [ ] **Step 3: Implement the UI**

  Add a compact filter form above the reservation table, a “Details” action per row, and a hidden detail panel. Use `fetch` with `URLSearchParams`, show loading/empty/error states, and render escaped text for all reservation values. Preserve existing save/status actions.

- [ ] **Step 4: Add responsive styling**

  Add grid/flex styles for desktop and one-column controls below 700px; ensure the detail panel and table do not overflow horizontally.

- [ ] **Step 5: Run UI, lint and mobile tests**

  Run the new UI test, `php -l templates/admin.php`, `tests/mobile-navigation.ps1`, `tests/public-pages.ps1`, and `tests/reservation-admin-workflow.ps1`.
  Expected: PASS.

- [ ] **Step 6: Commit**

  `git add templates/admin.php assets/js/app.js assets/css/style.css tests/reservation-details-ui.ps1 && git commit -m "feat: add admin reservation details ui"`

### Task 3: End-to-End verification and handoff

**Files:**
- Modify: `README_LOCALHOST.md` (document filter/detail usage)
- Test: existing test suite

- [ ] **Step 1: Document the admin workflow**

  Add the exact URL `http://localhost/rentabau.ch/admin.php#reservations`, required admin login, available filters, and detail behavior to `README_LOCALHOST.md`.

- [ ] **Step 2: Run the complete relevant suite**

  Run all `tests/*.ps1` scripts, PHP lint for changed PHP files, and the JavaScript tests with `npm test` where configured.
  Expected: PASS with no new warnings.

- [ ] **Step 3: Verify browser-facing responses**

  Request the admin page while authenticated, confirm the reservations tab renders, and check browser console for errors. Capture a screenshot of the filtered list and open detail panel.

- [ ] **Step 4: Commit**

  `git add README_LOCALHOST.md && git commit -m "docs: document reservation detail workflow"`
