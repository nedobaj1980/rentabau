# Reservations-Detailansicht und Filter – Design

## Ziel

Der geschützte Adminbereich erhält eine eigene Detailansicht für einzelne Reservationen. Administratoren können Reservationen gezielt nach Kunde, Zeitraum und Maschine finden und alle relevanten Angaben inklusive Status, Zahlung und interner Notiz prüfen.

## Umfang Phase 1

- Detailansicht über einen eindeutigen Link oder Button je Reservation.
- Filter nach freier Kundensuche (Name, E-Mail, Telefon), Maschine, Start-/Enddatum und Status.
- Filterung erfolgt serverseitig über die bestehende PHP-API, damit auch grosse Datenmengen nicht vollständig an den Browser übertragen werden müssen.
- Admin-only Zugriff; nicht angemeldete Benutzer werden auf den bestehenden Login-/Admin-Schutz verwiesen.
- Bestehende Reservationen, Datenbankfelder und Statuswerte bleiben kompatibel.
- Keine Rechnungs- oder Zahlungsabwicklung in dieser Phase; die Detailansicht liefert dafür die Grundlage.

## Architektur und Datenfluss

`templates/admin.php` rendert Filterformular und Detailbereich. `assets/js/app.js` lädt die gefilterte Liste über `api/reservations.php` und öffnet Details über `api/reservations.php?id=<id>`. Die API prüft `requireAdmin()`, validiert Parameter und verwendet parametrisierte SQL-Abfragen. Die Detailantwort enthält die vorhandenen Reservationsdaten sowie `paymentStatus`, Zeitstempel und `adminNote`.

Filterparameter: `customer`, `machineId`, `startDate`, `endDate`, `status`. Datumsfilter verwenden inklusive Grenzen: Eine Reservation zählt, wenn ihr Zeitraum den gewählten Zeitraum überschneidet.

## Sicherheit und Fehlerverhalten

- Jeder Listen- und Detailaufruf erfordert die Admin-Rolle.
- IDs und Datumswerte werden strikt validiert; ungültige Werte liefern HTTP 400/422.
- Nicht gefundene Reservationen liefern HTTP 404.
- SQL-Fehler werden serverseitig protokolliert und nach aussen als neutrale Fehlermeldung ausgegeben.
- Ausgabewerte werden im Frontend escaped, um gespeicherte HTML-/Script-Inhalte nicht auszuführen.

## Erfolgskriterien

1. Admin kann Filter kombinieren und erhält nur passende Reservationen.
2. Admin kann aus der Liste eine Reservation öffnen und sämtliche Kundendaten, Maschinendaten, Zeitraum, Übergabe, Preis, Zahlungs- und Bearbeitungsstatus ansehen.
3. Direkter Detailzugriff ohne Admin-Berechtigung ist nicht möglich.
4. Bestehende öffentlichen Reservationen und Admin-Aktionen funktionieren unverändert.
5. PHP-Lint sowie bestehende Auth-, Reservations- und Mobile-Tests bleiben erfolgreich; neue API- und UI-Tests decken Filter, Detailzugriff und Berechtigungen ab.

## Vorbereitung für spätere Rechnungsphase

Die Detailansicht verwendet die Reservations-ID als Referenz. Später werden daran Rechnungen, Rechnungspositionen, PDF-Datei, Versandprotokoll und Buchungs-/Zahlungsereignisse verknüpft, ohne die Reservationstabelle mit Rechnungslogik zu überladen.
