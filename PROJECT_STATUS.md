# Rent A Bau – Projektstatus

**Stand:** 08.09.2026  
**Projektordner:** `C:\xampp\htdocs\rentabau.ch`  
**Lokale Adresse:** `http://localhost/rentabau.ch/` bzw. `http://127.0.0.1/rentabau.ch/`

## 1. Aktueller Gesamtstand

Die aktive lokale Anwendung läuft über PHP, Apache und MySQL (XAMPP). Die frühere Vite-/Cloudflare-Struktur bleibt als Referenz im Projekt erhalten. Öffentliche Seiten, Kundenkonten, Adminbereich und Reservationsverwaltung wurden auf PHP/MySQL umgestellt.

Der **Online-Aufbau-Modus ist aktuell aktiviert**. Nicht angemeldete Besucher werden auf `maintenance.php` umgeleitet und sehen nur Logo, Aufbauhinweis und das direkte Loginformular. Bereits angemeldete Benutzer dürfen die Anwendung öffnen.

## 2. Bisherige Änderungen

### PHP-/MySQL-Migration

- Öffentliche HTML-Seiten auf PHP-Einstiegspunkte umgestellt.
- Zentrale Navigation in `includes/navigation.php`.
- Gemeinsamer Header/Footer in `includes/header.php` und `includes/footer.php`.
- PDO-/MySQL-Verbindung in `config/database.php`.
- Authentifizierung mit Sessions, Kundenrolle und Adminrolle.
- Weiterleitungen alter `.html`-Adressen über `.htaccess`.
- Schutz interner Ordner (`database`, `docs`, `tests`, `backups`, `config`, `logs` usw.) über `.htaccess`.

### Öffentliche Website

- Bestehendes Design, Logo und Bilder erhalten.
- Mobile Navigation und responsive Layouts ergänzt.
- Footer überarbeitet und transparentes Footer-Logo verwendet.
- Seite `so-funktionierts.php` ergänzt.
- Kontaktseite modernisiert.
- Abholadresse: Kreuzlingerstrasse 21, 8590 Romanshorn.
- Google-Maps-Ausschnitt auf der Kontaktseite.

### Kunden und Admin

- Registrierung, Login, Logout und geschütztes Kundenkonto vorhanden.
- Adminbereich rollenbasiert geschützt.
- Adminmenü direkt unter der Hauptnavigation.
- Maschinen-, Kategorien-, Liefer-, Zahlungs- und Rabattverwaltung im Adminbereich vorhanden.
- Admin-Setup über `admin-setup.php` mit Setup-Token vorbereitet.
- Eigenes Admin-Menü `Aktivitätsprotokoll` mit Filterung nach Admin, Bereich und Zeitraum.
- Admin-Änderungen an Reservationen, zentralen Einstellungen/Maschinen, Kategorien, Medienuploads und Homepage-Slider werden mit Benutzer, Zeit, Aktion sowie Vorher-/Nachher-Werten protokolliert.
- Code-/Deployment-Änderungen können im Adminbereich mit Release-Name, Beschreibung und Serverdateien dokumentiert werden.

### Reservationen

- Reservationen werden in MySQL in `reservations` gespeichert.
- Admin kann Reservationen bestätigen, abschliessen oder stornieren.
- Zahlungsstatus: `offen`, `teilweise`, `bezahlt`, `zurueckerstattet`.
- Felder für `confirmed_at`, `paid_at` und interne `admin_note` vorhanden.
- Reservationsfilter nach Kunde, Maschine, Zeitraum, Status und Zahlungsart.
- Sortierung nach Datum, Kunde, Maschine, Preis und Zahlungsstatus.
- Detailansicht pro Reservation mit Kunden-, Miet-, Maschinen- und Zahlungsdaten.
- Gastkonto kann keine Reservationen erstellen.

### Wartungs-/Aufbaumodus

- `config/maintenance.php` steuert den Modus über `RENTABAU_MAINTENANCE_MODE`.
- Der Online-Aufbaumodus wird zentral in `config/maintenance.php` gesteuert. Eine Hoststar-Domain wird automatisch als Online-Umgebung erkannt. Lokal hält `config/maintenance.enabled` den Schutz aktiv.

  Optional kann `RENTABAU_MAINTENANCE_MODE=1` über die Server-Umgebung gesetzt werden.

- `maintenance.php` enthält ein direktes Loginformular.
- `login.php` blendet die Registrierung während des Aufbaumodus aus.
- Nicht authentifizierte API-Aufrufe erhalten im geschützten Modus HTTP 503.

## 3. Lokale Datenbank

### Verbindung

```text
Host:       127.0.0.1
Port:       3306
Datenbank:  rentabau
Benutzer:   root
Passwort:   leer
```

### Tabellen

- `users` – Kunden, Admins und Gast-/Vorschauzugang
- `reservations` – Reservationsdaten, Preise, Zeiträume, Status und Zahlungsstatus
- `contact_requests` – Kontaktanfragen
- `admin_audit_log` – nachvollziehbare Admin-Aktionen inkl. Vorher-/Nachher-Werten
- `deployment_log` – manuell dokumentierte Code-/Deployment-Änderungen

### Wichtige Rollen

- `customer` – normales Kundenkonto
- `admin` – vollständiger Adminzugriff
- `guest` – schreibgeschützter Vorschauzugang

### Migrationen

- `database/migrations/001_phase1_reservations.sql` – Basisstruktur und Reservationen
- `database/migrations/002_full_php_application.sql` – vollständige PHP-Anwendung
- `database/migrations/003_reservation_workflow.sql` – Zahlungs-/Workflowfelder
- `database/migrations/004_guest_preview.sql` – Rolle `guest`
- `database/migrations/005_audit_and_deployment_log.sql` – Aktivitäts- und Deployment-Protokoll
  - Die Migration enthält bewusst kein `USE` und wird nach Auswahl der Ziel-Datenbank importiert (lokal `rentabau`, online `ch369984_rentabau`).

### Lokale Konten

- Ein Admin-Konto ist eingerichtet (konkrete E-Mail-Adresse bleibt außerhalb des Repositories).
- Ein Gastkonto ist eingerichtet; nur Vorschau, keine Reservationen.
- Passwörter werden nicht in dieser Statusdatei dokumentiert.

## 4. Hosting-Konfiguration Hoststar

Die Online-Datenbank ist vorbereitet, aber noch nicht vollständig konfiguriert.

```text
Datenbankname: ch369984_rentabau
Host:          localhost (Hoststar-Angabe prüfen)
Port:          3306
Benutzer:      noch einzutragen
Passwort:      noch einzutragen
```

Dateien:

- `config/database.hoststar.php`
- Vorlage: `config/database.hoststar.php.example`

Die Datei enthält aktuell den Datenbanknamen, aber absichtlich noch keinen Hoststar-Benutzer und kein Passwort. Diese Zugangsdaten müssen aus dem Hoststar-Panel übernommen werden. Alternativ können die Variablen `RENTABAU_DB_HOST`, `RENTABAU_DB_PORT`, `RENTABAU_DB_NAME`, `RENTABAU_DB_USER` und `RENTABAU_DB_PASSWORD` gesetzt werden.

### Hosting noch offen

- PHP-Dateien und Datenbank noch nicht auf Hoststar hochgeladen.
- FTP-/FTPS-Zugangsdaten und Zielordner fehlen.
- Hoststar-Datenbank muss mit dem lokalen Schema migriert werden.
- Remote-Admin- und Gastkonto müssen nach der Migration angelegt werden.
- SMTP-Zugang für die Geschäftsadresse ist noch nicht in der Anwendung hinterlegt.

## 5. Rechnungen und Zahlungen

Noch nicht produktiv umgesetzt:

- eigene QR-Rechnungs-/QR-Code-Erzeugung
- Rechnungsnummern und Rechnungstabellen
- PDF-Rechnungen mit TKB-QR-IBAN
- automatische E-Mail-Zustellung von Rechnungen
- Zahlungsimport über TKB `camt.054` oder IMAP-Zahlungsbenachrichtigungen
- Teilzahlungsberechnung und automatische Fehlbetragsmeldung
- verbindliche Freigabe einer Reservation erst nach vollständigem Zahlungseingang
- Buchhaltungsexport und Rechnungsarchiv
- Gutscheine und Stammkundenrabatte

Die manuell erzeugte Test-PDF liegt unter:

- `Buchhaltung/qr-bill-CH4400784298989542002-260908-114412.pdf`

Für die automatische QR-Rechnung wird noch die TKB-QR-IBAN benötigt. Die normale IBAN allein reicht für eine strukturierte QR-Referenz nicht aus.

## 6. Bekannte Fehler und Einschränkungen

- Hoststar-Benutzername und Hoststar-Passwort fehlen noch; die Online-Datenbankverbindung kann deshalb noch nicht getestet werden.
- Der PHP-CLI-Build auf diesem Rechner meldet beim direkten CLI-Test teilweise fehlenden `pdo_mysql`; die Apache-PHP-Umgebung mit XAMPP funktioniert für die Webtests.
- Während des Aufbaumodus liefern normale öffentliche Seiten nach der Weiterleitung die Wartungsseite statt der eigentlichen Inhalte. Die Tests berücksichtigen diesen Zustand.
- Die Reservationsdetailansicht zeigt bei Rechnungen momentan noch einen Hinweis, weil das Rechnungsmodul noch nicht implementiert ist.
- Bankzahlungen werden derzeit noch nicht automatisch erkannt oder freigegeben.
- Die Zahlungsmethoden werden aktuell dokumentiert/gespeichert, aber es findet noch keine Online-Zahlung statt.
- Das Audit-Protokoll erfasst alle derzeit vorhandenen schreibenden Admin-Aktionen. Sobald Kundenverwaltung oder Rechnungsbearbeitung als Adminmodule ergänzt werden, müssen deren Schreibvorgänge ebenfalls über `auditLogSafe()` geführt werden.
- Codeänderungen werden nicht automatisch aus dem Dateisystem erkannt; sie werden über das Deployment-Formular im Adminbereich dokumentiert.
- Vor der öffentlichen Freischaltung müssen HTTPS, SMTP, remote DB, Backups und Berechtigungen auf Hoststar getestet werden.

## 7. Tests

Erfolgreich getestet wurden unter anderem:

- PHP-Syntax der geänderten Dateien
- öffentliche PHP-Seiten im Aufbaumodus
- Registrierung und Login lokal
- Admin-Berechtigung
- Gastlogin und Schreibschutz
- Reservations-API und Reservationsworkflow
- Reservationsfilter und Detailansicht
- Audit-/Deployment-Protokoll inkl. Adminfilter und funktionalem Reservationseintrag
- mobile Navigation
- Wartungsmodus und Weiterleitung auf `maintenance.php`

Beispiele:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tests\maintenance-mode.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tests\guest-preview.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tests\reservation-details-api.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File tests\reservation-details-ui.ps1
```

## 8. Sicherungen

Es wurden vor den grösseren Änderungen Sicherungen im Ordner `backups\` angelegt. Wichtige aktuelle Sicherungen:

- `before-hoststar-db-config-20260908-124303`
- `before-hoststar-db-name-20260908-124558`
- `before-guest-preview-20260908-122413`
- `before-maintenance-mode-20260908-121336.zip`
- `before-reservation-details-qr-20260908-115417.zip`
- `users-before-admin-20260908-123221.sql`

## 9. Nächste Schritte

1. Hoststar-Datenbankbenutzer und Passwort im Panel ermitteln und nur auf dem Server in `config/database.hoststar.php` eintragen.
2. Projekt per FTP/FTPS in den richtigen Hoststar-Webordner hochladen.
3. Datenbankmigrationen auf `ch369984_rentabau` ausführen.
4. Remote-Admin- und Gastkonto anlegen.
5. Hoststar-PHP-Version, PDO-MySQL, HTTPS und Session-Cookies prüfen.
6. Eigenen QR-Rechnungsgenerator nach TKB-/SIX-Standard implementieren.
7. TKB-QR-IBAN eintragen und Testrechnung erzeugen.
8. SMTP über Hoststar konfigurieren und Testmails versenden.
9. Zahlungsabgleich über `camt.054` oder sichere manuelle Importfunktion entwickeln.
10. Rechnungsarchiv, Teilzahlungen, Buchhaltungsexport und Gutscheine ergänzen.
11. Wartungsmodus erst deaktivieren, wenn Login, Datenbank, Mail und Backups online erfolgreich getestet wurden.

## Stand 10.09.2026 – Finanzen & Belege
Lokales PHP-Modul eingerichtet, Migrationen 006/007 angewendet. Vier vorhandene Reservationen erhalten, keine Testrechnungen in rentabau. Rechnungen, Zahlungen/Erstattungen, Kundenhistorie, Ausgaben, geschützte PDFs, mobile Übergabe-/Abnahmeprotokolle und Treuhand-ZIP implementiert. Bedienung und Grenzen: docs/finance.md. Absender-Vorschlag Sdd GmbH; Adresse/E-Mail und Mailtransport noch zu konfigurieren. Domain-, Datenbank-, HTTP-, Browser-, PDF-/ZIP- und Sliderprüfungen durchgeführt. Keine Veröffentlichung und kein E-Mail-Versand ausgeführt.
