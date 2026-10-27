# Vollständige PHP-Umstellung der Rent-A-Bau-Seite – Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Die vorhandene Rent-A-Bau-Website läuft vollständig lokal über PHP/MySQL, behält ihr Design und ihre URLs bei und bietet sichere Reservationen, Kundenkonten sowie einen geschützten Adminbereich.

**Architecture:** Gemeinsame PHP-Templates rendern die Seiten serverseitig. PDO kapselt den MySQL-Zugriff; JavaScript bleibt nur für Kalender, Preisvorschau und UI-Interaktionen. Alte `.html`-Adressen leiten per 301 auf `.php` weiter.

**Tech Stack:** PHP 8.2+, Apache/XAMPP, MariaDB/MySQL, PDO, PHP Sessions, HTML/CSS, bestehendes Vanilla-JavaScript.

**Spec:** `docs/superpowers/specs/2026-09-04-php-mysql-migration-design.md`

## Global Constraints

- Projektpfad: `C:\xampp\htdocs\rentabau.ch`
- Lokale Zieladresse: `http://localhost/rentabau.ch/`
- MySQL-Server: `127.0.0.1`, Datenbank: `rentabau`, Benutzer: `root`, Passwort: leer
- Bestehende Projektdateien werden nicht ungefragt gelöscht.
- Bestehendes Design, Logo und Bilder bleiben erhalten.
- Zahlungen werden nur als Zahlungspräferenz gespeichert; es erfolgt keine Abbuchung.
- Jede neue Funktion erhält zuerst einen fehlschlagenden Test.
- Jede Etappe wird gegen Apache und die lokale MySQL-Datenbank verifiziert.

---

### Task 1: Sicherung, PHP-Grundgerüst und gemeinsame Templates

**Files:**
- Create: `backups/rentabau.ch-before-full-php-YYYYMMDD-HHMMSS.zip` (actual timestamp in filename)
- Create: `includes/bootstrap.php`
- Create: `includes/auth.php`
- Create: `includes/csrf.php`
- Create: `includes/header.php`
- Create: `includes/footer.php`
- Create: `tests/php-bootstrap.ps1`

**Interfaces:**
- Produces `requireBootstrap()`, `currentUser()`, `requireLogin()`, `requireAdmin()`, `csrfToken()`, `verifyCsrfToken()` and reusable header/footer rendering.

- [ ] **Step 1: Write the failing test**

  `tests/php-bootstrap.ps1` ruft eine geschützte Test-Hilfsroute auf und erwartet HTTP 500/403 ohne gültige Session sowie einen HTML-Dokumentkopf bei einer öffentlichen Route.

- [ ] **Step 2: Run test to verify it fails**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\php-bootstrap.ps1`
  Expected: FAIL, weil die gemeinsamen Bootstrap-Dateien und PHP-Seiten noch fehlen.

- [ ] **Step 3: Implement minimal bootstrap**

  `includes/bootstrap.php` lädt `config/database.php`, setzt UTF-8, sichere Session-Cookie-Parameter und zentrale Ausgabe-/Redirect-Helfer. Header und Footer übernehmen die vorhandene Navigation und Assets mit korrekten relativen Pfaden.

- [ ] **Step 4: Run test to verify it passes**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\php-bootstrap.ps1`
  Expected: PASS.

- [ ] **Step 5: Create a checkpoint**

  Sicherungsarchiv prüfen und bei vorhandenem Git `git add`/`git commit -m "chore: add php application foundation"` ausführen; in diesem Projekt ohne Git den Dateistatus dokumentieren.

### Task 2: Nicht-destruktive MySQL-Migration und Datenzugriff

**Files:**
- Create: `database/migrations/002_full_php_application.sql`
- Create: `includes/repositories/MachineRepository.php`
- Create: `includes/repositories/ReservationRepository.php`
- Create: `includes/repositories/UserRepository.php`
- Create: `includes/repositories/ContactRepository.php`
- Test: `tests/mysql-migration.ps1`

**Interfaces:**
- Repositories expose `allMachines()`, `findMachine(string $id)`, `createReservation(array $data)`, `reservationsForUser(int $userId)`, `createUser(array $data)`, `findUserByEmail(string $email)` and `createContactRequest(array $data)`.

- [ ] **Step 1: Write the failing test**

  Prüft, dass Tabellen `users`, `machines`, `categories` und `contact_requests` existieren, dass die vier bisherigen Reservationen erhalten bleiben und dass doppelte Migrationen keinen Datenverlust verursachen.

- [ ] **Step 2: Run test to verify it fails**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\mysql-migration.ps1`
  Expected: FAIL, weil die neuen Tabellen und Repository-Verträge fehlen.

- [ ] **Step 3: Implement migration and repositories**

  Migrationen verwenden `CREATE TABLE IF NOT EXISTS` und `ADD COLUMN IF NOT EXISTS`. Alle Schreibzugriffe nutzen PDO-Prepared-Statements; bestehende JSON-Daten werden mit einem idempotenten Importskript einmalig übernommen.

- [ ] **Step 4: Run test to verify it passes**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\mysql-migration.ps1`
  Expected: PASS with unchanged reservation count.

- [ ] **Step 5: Create a checkpoint**

  SQL-Migration, Importprotokoll und Datenbankzählung als Checkpoint sichern.

### Task 3: Öffentliche PHP-Seiten und Katalog

**Files:**
- Create: `index.php`, `maschinen.php`, `kategorie.php`, `maschine-detail.php`
- Create: `bagger.php`, `fahrzeuge.php`, `kleinmaschinen.php`, `abbruch.php`
- Modify: `assets/css/style.css`, `assets/js/app.js`, `.htaccess`
- Test: `tests/public-pages.ps1`

**Interfaces:**
- PHP-Seiten lesen Maschinen/Kategorien ausschließlich aus den Repositories und übergeben Kalenderdaten als escaped JSON an bestehendes JavaScript.

- [ ] **Step 1: Write the failing test**

  Ruft jede neue PHP-Seite auf, erwartet HTTP 200, den vorhandenen Markennamen und mindestens eine Maschinenkarte; eine ungültige Maschinen-ID muss HTTP 404 liefern.

- [ ] **Step 2: Run test to verify it fails**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\public-pages.ps1`
  Expected: FAIL, weil die PHP-Routen fehlen.

- [ ] **Step 3: Implement server-rendered pages**

  Bestehendes Markup wird in PHP übernommen, dynamische Daten werden escaped ausgegeben, Bildpfade bleiben unter `brand/` und `public/brand/` gültig. Mobile Navigation, `max-width`-Layouts und fehlendes Favicon werden korrigiert.

- [ ] **Step 4: Run test to verify it passes**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\public-pages.ps1`
  Expected: PASS for all public routes and assets.

- [ ] **Step 5: Create a checkpoint**

  Desktop- und Mobil-Screenshots der Startseite, des Katalogs und der Detailseite erzeugen und prüfen.

### Task 4: Registrierung, Login und Kundenkonto

**Files:**
- Create: `register.php`, `login.php`, `logout.php`
- Modify: `konto.php`, `includes/auth.php`, `includes/csrf.php`
- Test: `tests/authentication.ps1`

**Interfaces:**
- POST-Handler akzeptieren E-Mail/Passwort, geben Validierungsfehler zurück und setzen nach erfolgreichem Login eine regenerierte Session.

- [ ] **Step 1: Write the failing test**

  Testet Registrierung, falsches Passwort, korrektes Login, Logout, Session-ID-Erneuerung und dass `konto.php` ohne Login nicht die Reservationsliste liefert.

- [ ] **Step 2: Run test to verify it fails**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\authentication.ps1`
  Expected: FAIL, weil die Session- und User-Flows fehlen.

- [ ] **Step 3: Implement password/session flow**

  `password_hash()`/`password_verify()`, normalisierte eindeutige E-Mail, CSRF-Token, `session_regenerate_id(true)`, `HttpOnly`/`SameSite=Lax` und serverseitige Filterung nach `user_id` verwenden.

- [ ] **Step 4: Run test to verify it passes**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\authentication.ps1`
  Expected: PASS; Testbenutzer danach gezielt entfernen.

- [ ] **Step 5: Create a checkpoint**

  Konto-Screenshot nach Login und ohne Login prüfen.

### Task 5: Geschützter Adminbereich

**Files:**
- Create: `admin.php`
- Create: `admin-setup.php`
- Modify: `includes/auth.php`, `admin.html` (301-Kompatibilität)
- Test: `tests/admin-authorization.ps1`

**Interfaces:**
- `requireAdmin()` blockiert nicht angemeldete oder nicht-administrative Benutzer; Admin-POSTs prüfen Session, Rolle und CSRF.

- [ ] **Step 1: Write the failing test**

  Ruft `admin.php` anonym und als Customer auf und erwartet HTTP 403/Weiterleitung; ein Admin darf Reservationen lesen und einen Status ändern.

- [ ] **Step 2: Run test to verify it fails**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\admin-authorization.ps1`
  Expected: FAIL, weil `admin.html` bisher öffentlich ist.

- [ ] **Step 3: Implement role gate and setup**

  Ein lokaler Setup-Weg erstellt den ersten Admin nur mit explizitem Einmal-Token bzw. CLI-Aufruf und deaktiviert sich danach. Schreibaktionen werden auf zulässige Werte und bekannte IDs begrenzt.

- [ ] **Step 4: Run test to verify it passes**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\admin-authorization.ps1`
  Expected: PASS.

- [ ] **Step 5: Create a checkpoint**

  Admin- und Nicht-Admin-Screenshots sowie 403-Antwort protokollieren.

### Task 6: Reservation, Checkout und Kontakt vollständig serverseitig

**Files:**
- Create: `checkout.php`, `kontakt.php`
- Modify: `api/reservations.php`, `assets/js/app.js`
- Test: `tests/reservation-flow.ps1`

**Interfaces:**
- Reservation POST validiert Maschine, Zeitraum, Preis, Kundendaten, Zahlungspräferenz und Verfügbarkeit serverseitig und speichert atomar.

- [ ] **Step 1: Write the failing test**

  Testet gültige Reservation, Überschneidung, ungültiges Datum, CSRF-Fehler, Kontaktanfrage und Speicherung der Zahlungspräferenz ohne Zahlungsanbieteraufruf.

- [ ] **Step 2: Run test to verify it fails**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\reservation-flow.ps1`
  Expected: FAIL für PHP-Checkout und Kontaktpersistenz.

- [ ] **Step 3: Implement transaction and neutral payment copy**

  Preis wird aus MySQL-Maschinendaten berechnet; Zahlungsart wird nur als Präferenz gespeichert. Bestätigungen verwenden „Reservationsanfrage“ und behaupten keine Zahlung.

- [ ] **Step 4: Run test to verify it passes**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\reservation-flow.ps1`
  Expected: PASS; ausschließlich markierte Testdaten löschen.

- [ ] **Step 5: Create a checkpoint**

  Checkout- und Kontakt-Screenshots sowie Datenbankzeilen prüfen.

### Task 7: HTML-Kompatibilitätsweiterleitungen

**Files:**
- Modify: `.htaccess`
- Create: `legacy/*.html` redirect stubs only where Apache rules cannot preserve query strings
- Test: `tests/redirects.ps1`

**Interfaces:**
- Jede bisherige `.html`-Adresse antwortet mit HTTP 301 und erhält Pfad, Query-String und Kategorie-/Maschinenparameter.

- [ ] **Step 1: Write the failing test**

  Ruft alle bisherigen HTML-Adressen mit `curl.exe -I` auf und erwartet 301 plus korrektes `.php`-Ziel.

- [ ] **Step 2: Run test to verify it fails**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\redirects.ps1`
  Expected: FAIL, weil Apache derzeit die HTML-Dateien direkt ausliefert.

- [ ] **Step 3: Implement redirects**

  `.htaccess` nutzt explizite, nicht-rekursive Regeln für jede alte Seite; API-Pfade, Assets und `index.php` bleiben ausgenommen.

- [ ] **Step 4: Run test to verify it passes**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\redirects.ps1`
  Expected: PASS for all legacy routes.

- [ ] **Step 5: Create a checkpoint**

  Linkcrawler erneut ausführen und nur 200/301 für interne Ziele zulassen.

### Task 8: Vollständige Verifikation und Übergabe

**Files:**
- Modify: `README_LOCALHOST.md`
- Create: `qa-screenshots/full-php/`
- Test: `tests/full-php-smoke.ps1`

**Interfaces:**
- Smoke-Test liefert einen reproduzierbaren Bericht über Seiten, Assets, APIs, Sessions, Rollen, Datenbankzählungen und Redirects.

- [ ] **Step 1: Write the failing test**

  Smoke-Test prüft Homepage, Katalog, Detailseite, Checkout, Kontakt, Login, Konto, Admin, Favicon, Bilder, API und Datenbankzustand.

- [ ] **Step 2: Run test to verify it fails**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\full-php-smoke.ps1`
  Expected: FAIL, solange einzelne HTML-Routen oder ungeschützte Bereiche bestehen.

- [ ] **Step 3: Implement only remaining root-cause fixes**

  Fehler anhand des Smoke-Berichts einzeln beheben; keine parallelen ungetesteten Refactorings durchführen.

- [ ] **Step 4: Run test to verify it passes**

  Run: `powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\full-php-smoke.ps1`
  Expected: PASS with zero broken links, zero uncaught browser errors and preserved reservation count.

- [ ] **Step 5: Create a checkpoint**

  Abschluss-Sicherung, Screenshots und genaue lokale Adresse dokumentieren. Ein Git-Commit ist nur möglich, wenn zuvor ein Repository eingerichtet wurde.
