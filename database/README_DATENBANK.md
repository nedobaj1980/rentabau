# Datenbank-Export

Die veröffentlichte Rent-A-Bau-Seite verwendet eine Cloudflare-D1-Datenbank auf SQLite-Basis.

Enthalten sind:

- `rentabau_d1_sqlite.sql`: Originalformat für D1/SQLite
- `rentabau_mysql.sql`: Importdatei für MySQL/MariaDB und XAMPP/phpMyAdmin
- `reservations.csv`: Tabellenexport mit Spaltenüberschriften

Zum Zeitpunkt des Exports am 24.08.2026 waren noch keine Reservationen gespeichert.

## Import in XAMPP/phpMyAdmin

1. Apache und MySQL im XAMPP Control Panel starten.
2. `http://localhost/phpmyadmin` öffnen.
3. Oben auf **Importieren** klicken.
4. Datei `database/rentabau_mysql.sql` auswählen.
5. Import starten.

Hinweis: Der aktuelle TypeScript-Code verwendet D1. Damit die Anwendung direkt mit MySQL arbeitet, muss die Datenbank-Anbindung im Programm noch auf MySQL umgestellt werden.
