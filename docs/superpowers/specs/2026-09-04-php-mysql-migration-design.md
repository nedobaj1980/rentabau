# Rent-A-Bau PHP-/MySQL-Migration – Designspezifikation

## Ziel

Die bestehende Rent-A-Bau-Website wird schrittweise von statischen HTML-Seiten und der parallelen Vite-/Cloudflare-Fassung auf eine eigenständig unter XAMPP laufende PHP-/MySQL-Anwendung umgestellt. Das bestehende Erscheinungsbild, Logo, Bilder und die funktionierende Reservationsstrecke bleiben erhalten.

Lokale Zieladresse: `http://localhost/rentabau.ch/`

## Rahmenbedingungen

- Projektpfad: `C:\xampp\htdocs\rentabau.ch`
- MySQL-Server: `127.0.0.1`
- Datenbank: `rentabau`
- Benutzer: `root`
- Lokales Passwort: leer
- Bestehende Projektdateien werden nicht ungefragt gelöscht.
- Vor dieser Migrationsphase wird eine neue datierte Sicherung erstellt und geprüft.
- Die vorhandene Cloudflare-/React-Fassung bleibt als Referenz erhalten und wird nicht mehr als lokale Laufzeit benötigt; produktive HTML-Seiten werden nicht parallel gepflegt.

## Gewählter Ansatz

Die Anwendung verwendet serverseitige PHP-Seiten mit gemeinsamen Templates und MySQL als dauerhafte Datenquelle. JavaScript bleibt gezielt für Kalender, Filter, Preisvorschau und andere unmittelbar interaktive Elemente im Browser bestehen.

PHP übernimmt Routing, Sitzungen, Berechtigungen, Datenvalidierung, Datenbankzugriff und das Rendern der fachlichen Inhalte. Dadurch bleibt das bestehende Bediengefühl erhalten, während sicherheitsrelevante Entscheidungen ausschließlich auf dem Server getroffen werden.

## Seitenstruktur

Folgende PHP-Seiten ersetzen die gleichnamigen HTML-Seiten:

- `index.php`: Startseite
- `maschinen.php`: Maschinenkatalog
- `kategorie.php`: gefilterte Kategorieansicht
- `maschine-detail.php`: Maschinendetails, Verfügbarkeit und Mietauswahl
- `checkout.php`: Kundendaten, Zahlungspräferenz und Reservationsabschluss
- `kontakt.php`: Kontaktformular
- `konto.php`: Registrierung, Login und persönliches Kundenkonto
- `admin.php`: geschützter Administrationsbereich
- fachliche Kategorie-Einstiege wie `bagger.php`, `fahrzeuge.php`, `kleinmaschinen.php` und `abbruch.php`

Gemeinsame Bestandteile werden in fokussierten PHP-Templates geführt:

- Dokumentkopf und Metadaten
- Header und Navigation
- Footer
- Flash- und Fehlermeldungen
- CSRF-Felder

Die bisherigen `.html`-Adressen bleiben als Kompatibilitätseinstiege über Apache-Regeln bestehen und leiten mit HTTP 301 auf die entsprechenden `.php`-Adressen weiter. Query-Strings wie Maschinen- oder Kategorieparameter bleiben erhalten. Die produktiven HTML-Dateien werden nach erfolgreicher PHP-Konvertierung entfernt.

## Datenmodell

MySQL wird zur alleinigen Live-Datenquelle. Das Schema wird ausschließlich durch nicht-destruktive Migrationen erweitert.

### Benutzer

Die Tabelle `users` enthält mindestens:

- numerische ID
- Rolle `customer` oder `admin`
- Vor- und Nachname
- normalisierte, eindeutige E-Mail-Adresse
- Passwort-Hash aus `password_hash()`
- optional Firma, Telefon und Rechnungsadresse
- Aktivstatus
- Erstellungs- und Änderungszeitpunkt

Es werden keine Klartextpasswörter gespeichert. Ein initialer Administrator wird nicht mit einem allgemein bekannten Standardpasswort ausgeliefert. Die erstmalige Einrichtung erfolgt über einen lokal auszuführenden, nach erfolgreicher Verwendung gesperrten Einrichtungsweg.

### Reservationen

Die bestehende Tabelle `reservations` und ihre Daten bleiben erhalten. Sie wird mindestens ergänzt um:

- optionale `user_id` für Gast- und Kundenreservationen
- Firmen- und Rechnungsadressfelder
- Zahlungspräferenz
- Preisbestandteile, Übergabe- und Lieferangaben
- Erstellungs- und Änderungszeitpunkt

Alte Reservationen ohne Benutzerzuordnung bleiben gültig. Neue Reservationen werden dem angemeldeten Kunden zugeordnet; Gastreservationen bleiben möglich, solange dies nicht später anders entschieden wird.

### Katalog und Einstellungen

Maschinen, Kategorien, technische Eigenschaften, Bilder, Zubehör, Preisregeln, Lieferregeln, Sperrzeiten und öffentliche Einstellungen werden aus den bisherigen JavaScript-/JSON-Strukturen in normalisierte MySQL-Tabellen überführt. Bestehende Bilder bleiben als Dateien erhalten; MySQL speichert nur geprüfte relative Pfade und Metadaten.

### Kontaktanfragen

Das Kontaktformular schreibt Anfragen in eine Tabelle `contact_requests`. Eine E-Mail-Zustellung ist nicht Bestandteil dieser Phase, kann später ergänzt werden.

## Authentifizierung und Berechtigungen

- Registrierung und Login laufen über serverseitig validierte POST-Anfragen.
- Passwörter werden mit `password_hash()` gespeichert und mit `password_verify()` geprüft.
- Nach einem Login wird die Session-ID erneuert.
- Session-Cookies verwenden `HttpOnly`, `SameSite=Lax` und bei HTTPS zusätzlich `Secure`.
- Zustandsändernde Formulare benötigen ein CSRF-Token.
- Der Kundenbereich erfordert eine Anmeldung und filtert serverseitig nach der angemeldeten Benutzer-ID.
- Der Adminbereich erfordert zusätzlich die Rolle `admin`.
- Direkte Aufrufe geschützter PHP-Endpunkte ohne Berechtigung liefern Weiterleitung oder HTTP 403, abhängig vom Anfrageformat.
- Ausgabe aus Datenbank oder Formularen wird kontextgerecht escaped.
- SQL-Zugriffe erfolgen ausschließlich mit vorbereiteten PDO-Anweisungen.

## Reservationsablauf

1. Der Kunde wählt Maschine, Mietzeitraum und Übergabeart.
2. PHP beziehungsweise ein schreibgeschützter API-Aufruf prüft die Verfügbarkeit anhand von Reservationen und Sperrzeiten.
3. Im Checkout werden Kundendaten und eine Zahlungspräferenz erfasst.
4. Der Server validiert Pflichtfelder, E-Mail, Datumsbereich, Maschine, Preise und Verfügbarkeit erneut.
5. Der Server berechnet den verbindlich gespeicherten Preis aus den aktuellen Datenbankwerten; vom Browser gelieferte Preise werden nicht vertraut.
6. Die Reservation wird atomar in MySQL gespeichert.
7. Der Kunde erhält eine Reservationsbestätigung mit Referenznummer. Die Formulierung behauptet keine erfolgte Zahlung.

Überschneidende Reservationen dürfen nicht allein durch Browserlogik verhindert werden. Die serverseitige Prüfung und Speicherung erfolgen in einer Transaktion, sodass parallele Anfragen nicht denselben Zeitraum doppelt belegen.

## Zahlungen

Zahlungen werden nur vorbereitet:

- Der Kunde kann eine Zahlungspräferenz auswählen.
- Die Präferenz wird zusammen mit der Reservation gespeichert.
- Es werden keine Karten-, Konto- oder Zugangsdaten erfasst.
- Es erfolgt keine Abbuchung, Zahlungsautorisierung oder Kommunikation mit einem Zahlungsanbieter.
- Checkout und Bestätigung verwenden klare Texte wie „Reservationsanfrage senden“ und „gewählte Zahlungsart“.
- Die spätere Zahlungsintegration erhält eine abgegrenzte Service-Schnittstelle, ohne bereits einen konkreten Anbieter vorzugeben.

## Administration

Der geschützte Adminbereich unterstützt schrittweise:

- Reservationen ansehen und Status ändern
- Maschinen und Kategorien verwalten
- Bilder und Zubehör verwalten
- Preis-, Rabatt- und Lieferregeln verwalten
- Sperrzeiten pflegen
- Kontaktanfragen ansehen

Jede administrative Änderung wird serverseitig autorisiert und validiert. Löschvorgänge benötigen eine eindeutige Bestätigung und sollen fachlich nach Möglichkeit als Deaktivierung statt als physische Löschung erfolgen.

## Fehlerbehandlung

- Öffentliche Seiten zeigen verständliche Meldungen ohne interne Pfade, SQL oder Stacktraces.
- Technische Details werden über PHP `error_log` protokolliert.
- API-Antworten verwenden konsistente JSON-Strukturen und passende HTTP-Statuscodes.
- Formulare behalten bei Validierungsfehlern unkritische Eingaben bei.
- Datenbankausfälle erzeugen keine JSON- oder Dateisystem-Fallbacks, die zwei konkurrierende Datenbestände schaffen würden.

## Darstellung und Barrierefreiheit

- Das bestehende visuelle Design, Logo, Hero-Bild, Farben und Maschinenbilder bleiben erhalten.
- Der globale Header erhält eine funktionierende mobile Navigation.
- Horizontales Überlaufen wird auf allen Hauptseiten beseitigt.
- Formulare besitzen programmatisch zugeordnete Labels, sichtbare Fokuszustände und verständliche Validierungsmeldungen.
- Bedienelemente bleiben per Tastatur erreichbar.
- Tabellen im Adminbereich erhalten auf kleinen Bildschirmen eine kontrollierte, nutzbare Darstellung.
- Das fehlende Favicon wird unter dem von Apache ausgelieferten Pfad bereitgestellt.

## Migration und Kompatibilität

- Datenmigrationen sind wiederholbar und nicht-destruktiv.
- Bestehende Reservationen werden vor und nach jeder Migration gezählt und stichprobenartig verglichen.
- Maschinen- und Einstellungsdaten werden erst nach erfolgreicher Übernahme von MySQL gelesen.
- Die bisherigen JSON-Dateien bleiben als gesicherte Referenz erhalten, werden danach aber nicht mehr als Live-Speicher beschrieben.
- Alte HTML-Adressen werden erst nach erfolgreichem Test ihrer PHP-Ziele auf permanente Weiterleitungen umgestellt.

## Teststrategie

Die Umsetzung erfolgt testgetrieben und in kleinen Migrationsschritten:

- PHP-Syntaxprüfungen für alle PHP-Dateien
- Integrationstests gegen Apache und die lokale MySQL-Datenbank
- Tests für Registrierung, Login, Logout, Session-Erneuerung, Rollen und CSRF-Schutz
- Tests, dass Kunden ausschließlich eigene Reservationen sehen
- Tests für Reservationsvalidierung, Preisberechnung und Überschneidungsschutz
- Tests für Kontaktanfragen und administrative Statusänderungen
- Linkprüfung für alle internen Ziele und Weiterleitungen
- Browserprüfung in Chrome auf Desktop- und Mobilgrößen
- Prüfung der Browser-Konsole und fehlender Ressourcen
- Screenshots der zentralen öffentlichen Seiten, des Checkouts, Kundenkontos und Adminbereichs

Tests erzeugen ausschließlich eindeutig markierte Datensätze und entfernen nur diese eigenen Testdaten.

## Umsetzungsetappen

1. Neue Sicherung, Testgrundlage und gemeinsames PHP-Grundgerüst
2. Öffentliche Seiten und gemeinsame Templates
3. Katalog- und Einstellungsdaten nach MySQL migrieren
4. Registrierung, Login, Logout und Kundenkonto
5. Rollenbasierter Adminschutz und administrative Schreibvorgänge
6. Vollständiger serverseitiger Reservations- und Checkoutablauf
7. Kontaktanfragen und vorbereitete Zahlungspräferenz
8. HTML-Weiterleitungen, mobile Korrekturen und abschließender Browser-/Sicherheitstest

Jede Etappe muss eigenständig lauffähig und geprüft sein, bevor die nächste beginnt.

## Nicht Bestandteil dieser Migration

- echte Zahlungsabwicklung
- Speicherung von Karten- oder Bankzugangsdaten
- E-Mail-Versand
- Passwort-zurücksetzen per E-Mail
- Entfernung der alten Cloudflare-/React-Quelldateien
- Veröffentlichung außerhalb des lokalen XAMPP-Systems
