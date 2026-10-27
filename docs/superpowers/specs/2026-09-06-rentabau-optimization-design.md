# Rent A Bau Optimierungsdesign

## Ziel

PHP/MySQL wird die einzige produktive Laufzeit für XAMPP. Die vorhandene React/Vite-/Cloudflare-Fassung bleibt unverändert als Referenz. Die PHP-Seite soll sicherer, wartbarer und im Alltag besser administrierbar werden, ohne bestehende Inhalte, Bilder oder Reservationen zu verlieren.

## Architektur

Gemeinsame PHP-Bausteine (`includes/bootstrap.php`, Header, Navigation, Footer, Auth und CSRF) bilden die Präsentationsschicht. MySQL bleibt die einzige Live-Datenquelle für Benutzer, Reservationen und Kontaktanfragen. Öffentliche Reservationen werden über die bestehende API erstellt; Lesen und Statusänderungen werden rollenabhängig geschützt.

## Umfang

- konsistente Session-, CSRF- und Passwortregeln
- rollenbasierte Sichtbarkeit von Kunden- und Admin-Daten
- Admin-Verwaltung für Reservationen, Benutzer und Kontaktanfragen
- zentrale Fehler-/Erfolgsmeldungen und weniger doppelte Markup-/Style-Logik
- responsive und zugängliche Formulare/Navigation
- lokale Tests, Browser-Smoke-Test und Betriebsdokumentation

Zahlungen bleiben bewusst deaktiviert und werden nur als Präferenz gespeichert.
