# Audit- und Deployment-Protokoll – Design

## Ziel

Mehrere Administratoren sollen nachvollziehen können, wer welche fachliche Änderung im Adminbereich durchgeführt hat. Zusätzlich werden dokumentierte Code-/Deployment-Stände getrennt von fachlichen Datenänderungen erfasst.

## Fachliches Audit

Neue Tabelle `admin_audit_log` mit:

- `id`, `user_id`, `user_name`, `user_email`
- `action`, `area`, `entity_type`, `entity_id`
- `summary`
- `before_json`, `after_json`
- `ip_address`, `user_agent`, `created_at`

Audit-Einträge werden nur serverseitig erzeugt, sind nicht editierbar und für Admins lesbar. Passwörter, Passwort-Hashes, SMTP-Passwörter, Datenbankpasswörter und vollständige Zahlungsgeheimnisse werden vor der Speicherung entfernt oder maskiert.

Zu protokollierende Bereiche umfassen zunächst Reservationen, Benutzer, Maschinen, Einstellungen und Uploads. Die spätere Rechnungs- und Gutscheinlogik verwendet dieselbe Funktion.

## Code-/Deployment-Protokoll

Neue Tabelle `deployment_log` mit Release-Nummer, Beschreibung, Bearbeiter, geänderten Dateien, optionalem Commit-/Upload-Referenzwert und Prüfsumme. Ein Deployment-Eintrag wird bewusst durch einen Admin oder eine spätere Upload-/CI-Integration erstellt. Direkte FTP-Änderungen können ohne Hoststar-Serverprotokoll nicht zuverlässig automatisch einem Benutzer zugeordnet werden.

## Adminoberfläche

Der Adminbereich erhält einen Tab „Aktivitätsprotokoll“ mit:

- kombinierbaren Filtern für Admin, Bereich, Aktion und Zeitraum
- sortierter Liste, neueste Ereignisse zuerst
- Detailansicht mit Vorher-/Nachher-Werten
- separatem Abschnitt für Deployments
- klarer Kennzeichnung von fachlicher Änderung und Code-/Release-Eintrag

## Sicherheit und Fehlerverhalten

- Alle Schreibvorgänge passieren innerhalb derselben Datenbankverbindung wie die Fachänderung.
- Ein Audit-Fehler wird protokolliert; die Fachänderung darf nicht stillschweigend ohne Hinweis fortgesetzt werden.
- Nur Admins dürfen Protokolle lesen.
- Ausgabe erfolgt escaped; JSON wird nicht als HTML interpretiert.
- IP-Adressen und User-Agents werden nur für Nachvollziehbarkeit gespeichert und nicht öffentlich angezeigt.

## Erfolgskriterien

1. Eine Admin-Änderung an einer Reservation erzeugt einen Audit-Eintrag mit Vorher-/Nachher-Werten.
2. Filter und Detailansicht funktionieren nur im geschützten Adminbereich.
3. Passwörter und Geheimnisse erscheinen niemals im Log.
4. Ein Deployment kann mit Release, Bearbeiter, Beschreibung und Dateiliste dokumentiert werden.
5. Bestehende Reservationen, Login- und Wartungsmodus-Funktionen bleiben unverändert.
