# Rent A Bau lokal mit XAMPP starten

Die öffentlich aufrufbare Fassung läuft über Apache, PHP und MySQL. Die bisherige Vite-/Cloudflare-Fassung bleibt als Referenz im Projekt erhalten.

## Voraussetzungen

- Apache und MySQL im XAMPP Control Panel starten.
- Projektpfad: `C:\xampp\htdocs\rentabau.ch`
- Aufruf: `http://localhost/rentabau.ch/`

## Datenbank einrichten

Das nicht-destruktive Phase-1-Schema liegt in `database/migrations/001_phase1_reservations.sql`. Es erstellt die Datenbank und Tabelle nur, wenn sie fehlen, und ergänzt fehlende Preisfelder ohne bestehende Reservationen zu löschen.

Standardverbindung:

- Server: `127.0.0.1`
- Datenbank: `rentabau`
- Benutzer: `root`
- Passwort: leer

Lokal verwendet `config/database.php` automatisch diese Werte. Für Hoststar ist die Datenbank `ch369984_rentabau` bereits in `config/database.hoststar.php` eingetragen. Benutzername und Passwort müssen dort noch mit den Angaben aus dem Hoststar-Panel ergänzt werden. Alternativ kann Hoststar über `RENTABAU_DB_HOST`, `RENTABAU_DB_PORT`, `RENTABAU_DB_NAME`, `RENTABAU_DB_USER` und `RENTABAU_DB_PASSWORD` konfiguriert werden.

Die Konfigurations- und Log-Ordner sind über `.htaccess` gegen direkten Webzugriff gesperrt. Die echten Hoststar-Zugangsdaten gehören nur auf den Server und nicht in den öffentlichen Projektordner.

## Online-Aufbau-Modus

Für den Upload auf Hoststar bleibt die Website standardmässig geschützt. `config/maintenance.php` erkennt eine nicht-lokale Domain automatisch als Online-Aufbau-Modus. Optional kann der Modus über eine Umgebungsvariable gesteuert werden:

```apache
RENTABAU_MAINTENANCE_MODE=1
```

Gäste sehen danach `maintenance.php` mit Logo und Loginformular. `login.php`, `logout.php` und bereits angemeldete Sessions bleiben erreichbar. Zum Öffnen der Website wird die Variable auf `0` gesetzt. Lokal hält `config/maintenance.enabled` den Schutz ebenfalls aktiv.

Der Reservations-Arbeitsablauf wird mit `database/migrations/003_reservation_workflow.sql` erweitert. Das Admin-Menü kann damit Reservationsstatus, Zahlungsstatus und interne Buchhaltungsnotizen speichern.

## Phase 1

- `config/database.php`: zentrale PDO-Verbindung mit UTF-8 und Exceptions
- `api/reservations.php`: MySQL-API für Abruf, Erstellung und Statusänderung
- `database/reservations.json`: unveränderter Altbestand; keine Live-Datenquelle mehr
- `tests/phase1-reservations.ps1`: lokaler Integrationstest über Apache und MySQL

## PHP-Migrationsstand

Alle öffentlichen Seiten sind als PHP-Adressen verfügbar (`index.php`, `maschinen.php`, `kategorie.php`, `maschine-detail.php`, `checkout.php`, `kontakt.php` sowie Kategorie-Seiten). Die früheren `.html`-Adressen leiten per HTTP 301 auf PHP weiter; die produktiven HTML-Dateien im Projektstamm wurden entfernt. Die konvertierten Seitentemplates liegen unter `templates/*.php`, die Navigation wird zentral über `includes/navigation.php` eingebunden.

Für Kundenkonten stehen `register.php`, `login.php`, `logout.php` und das geschützte `konto.php` bereit. `admin.php` ist rollenbasiert geschützt. Ein erster Administrator wird ausschließlich über `admin-setup.php` mit einem in der PHP-Umgebung gesetzten `RENTABAU_ADMIN_SETUP_TOKEN` angelegt; der Token muss danach entfernt werden.

Zahlungsarten werden momentan nur als `payment_preference` gespeichert. Es findet keine Zahlung statt.

## Reservations-Detailansicht

Im Adminbereich unter `http://localhost/rentabau.ch/admin.php#reservations` können Reservationen nach Kunde, Maschine, Zeitraum, Reservationsstatus, Zahlungsart und Sortierung gefiltert werden. Über **Details** öffnet sich die Einzelansicht mit Kundendaten, Mietdaten, Maschinendaten, Zahlungsstatus und Notizen.

Die Detailansicht ist geschützt und für nicht angemeldete Benutzer nicht verfügbar. Rechnungs-PDFs und QR-Rechnungen werden in einer nächsten Ausbaustufe an die jeweilige Reservations-ID gekoppelt. Dafür wird die TKB-QR-IBAN benötigt.

Test aus PowerShell starten:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\phase1-reservations.ps1
```

## Wartung und Sicherung

Vor Änderungen wird eine ZIP-Sicherung im Ordner `backups\` angelegt. Die PHP-/MySQL-Fassung ist die aktive lokale Version; React/Vite/Cloudflare bleibt als Referenz erhalten. Für einen Funktionstest können die vorhandenen Skripte einzeln oder gesammelt ausgeführt werden:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\public-pages.ps1
powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\authentication.ps1
powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\admin-authorization.ps1
powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests\reservation-flow.ps1
```

Direkte Zugriffe auf `database`, `tests`, `docs` und `backups` sind über Apache gesperrt. Änderungen an Katalogeinstellungen und Bilduploads sind nur mit Administrator-Sitzung möglich; öffentliche API-Aufrufe enthalten ausschließlich anonymisierte Verfügbarkeitsdaten.
