# Rent A Bau Optimierung Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** PHP/MySQL als robuste, wartbare und lokal testbare Produktivfassung für Rent A Bau etablieren.

**Architecture:** Gemeinsame PHP-Includes und rollenbasierte Zugriffslogik; MySQL als Live-Datenquelle. React/Vite/Cloudflare bleibt nur Referenz.

**Tech Stack:** PHP 8.2, Apache/XAMPP, MySQL/MariaDB, PDO, HTML/CSS/Vanilla JS, PowerShell-Smoke-Tests.

**Spec:** `docs/superpowers/specs/2026-09-06-rentabau-optimization-design.md`

## Global Constraints

- Projektpfad: `C:\xampp\htdocs\rentabau.ch`
- URL: `http://localhost/rentabau.ch/`
- Datenbank: `rentabau` auf `127.0.0.1`, Benutzer `root`, leeres Passwort
- Bestehendes Design, Logo und Bilder erhalten
- Keine bestehenden Dateien ungefragt löschen
- Zahlung bleibt deaktiviert

### Task 1: Sicherheits- und Authentifizierungsbasis

Dateien: `login.php`, `register.php`, `includes/auth.php`, `includes/bootstrap.php`, `includes/csrf.php`

- Einheitliche Mindestpasswortlänge von 10 Zeichen und CSRF-Schutz für alle POST-Formulare.
- Session-Cookies mit HttpOnly, SameSite und sicherem Sessionwechsel beibehalten.
- Eingaben validieren und generische Loginfehler verwenden.

### Task 2: Reservations- und Kontodaten schützen

Dateien: `api/reservations.php`, `templates/konto.php`, Tests unter `tests/`

- GET liefert Kunden nur eigene Reservationen und Admins alle.
- Nicht angemeldete Nutzer dürfen weiterhin Reservationen anfragen, aber keine Liste lesen.
- Bestehende Statusänderungen bleiben Admin-only.

### Task 3: Admin-Arbeitsablauf verbessern

Dateien: `templates/admin.php`, `admin.php`, gegebenenfalls `api/contact-requests.php`

- Übersichtlichere Tabellen, Statusaktionen und Kontaktanfragen.
- POST/PATCH-Aktionen validieren und CSRF schützen.

### Task 4: Gemeinsame Darstellung und Formulare

Dateien: `includes/header.php`, `includes/navigation.php`, `includes/footer.php`, `assets/css/style.css`, betroffene Templates

- Doppelte Header/Footer-Strukturen reduzieren.
- Fokuszustände, Fehlermeldungen, mobile Navigation und konsistente Formularabstände ergänzen.

### Task 5: Verifikation und Dokumentation

- `php -l` für alle PHP-Dateien.
- vorhandene PowerShell-Integrationstests plus neue Rechte-/Formulartests.
- Apache-Smoke-Test für öffentliche Seiten, Konto, Admin und API.
- `README_LOCALHOST.md` um Wartung, Backup und Testbefehle ergänzen.
