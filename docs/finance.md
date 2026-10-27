# Finanzen & Belege

Einstieg: admin.php → Finanzen & Belege (admin-finance.php). Nur Administratoren haben Zugriff.

## Bedienung
1. Unter Einstellungen Firmenadresse und Firmen-E-Mail ergänzen. Der vorgeschlagene Firmenname ist Sdd GmbH. IBAN und MWST-Nummer bei Bedarf eintragen.
2. Unter Aufträge eine Rechnung mit Rechnungsadresse und Positionen erstellen. Positionen verwenden Nettopreise und einen ausdrücklich gewählten MWST-Satz. Ausgestellte PDFs werden mit den damaligen Angaben gespeichert.
3. Tatsächliche Zahlungen mit Datum, Zahlungsart und Referenz erfassen. Teilzahlungen, Überzahlungsschutz, vollständige Gutschriften und Rückzahlungen sind unterstützt. Eine Gutschrift führt keine Banküberweisung aus.
4. Beim Auftrag Übergabe / Rücknahme öffnen. Entwurf ausfüllen, JPG/PNG-Fotos hinzufügen und beide Personen unterschreiben lassen. Änderungen an Angaben löschen die noch nicht gespeicherten Unterschriften. Abschluss archiviert das PDF. Spätere Änderungen über ein verknüpftes Korrekturprotokoll erfassen; Fotos und Unterschriften müssen dafür neu hinzugefügt werden.
5. Kunden zeigt die Mietgeschichte sowie fakturierte Beträge und Zahlungseingänge im Zeitraum. Offene Posten zeigen den aktuellen Stand über alle Zeiträume.
6. Ausgaben mit optionalem PDF/JPG/PNG-Beleg erfassen. Treuhand exportiert CSV und archivierte Dokumente für alle Kunden im gewählten Zeitraum. Geldspalten mit Rappen im Titel sind ganze Rappen. ZIP maximal 24 MB; für grössere Archive kürzere Zeiträume wählen.
7. Rechnungskopien können heruntergeladen werden. Kopie senden verwendet nach Einrichtung den Mailtransport des Servers und protokolliert die Übergabe an den Mailserver, keine garantierte Zustellung.

## Einrichtung und Betrieb
Die additiven Migrationen database/migrations/006_finance.sql und 007_finance_protocols.sql werden durch financeSetup() oder die Einrichtungsschaltfläche ausgeführt. Bestehende Reservationen bleiben erhalten. Alte manuelle Zahlungsstatus erzeugen keine fiktiven Zahlungseingänge. Sobald eine Rechnung besteht, werden Zahlungen über Finanzen erfasst.

PHP benötigt PDO MySQL, mbstring, iconv, GD und fileinfo. Upload-Limits passend setzen (upload_max_filesize mindestens 10M, post_max_size mindestens 16M). Apache muss .htaccess auswerten; darin sind Tests, Datenbankdateien, Belegaltordner und vendor gesperrt. Der PHP-Testserver ist kein Produktivserver.

E-Mail: RENTABAU_FINANCE_MAIL_FROM auf eine gültige Absenderadresse setzen und PHP mail() mit einem funktionierenden Mailtransport konfigurieren. Anschliessend einen ausdrücklich freigegebenen Testversand durchführen. Während der Entwicklung wurden keine E-Mails versendet.

Datenbankbackups müssen alle fin_* Tabellen einschliessen: PDFs, Fotos, Unterschriften und Ausgabenbelege werden als Datenbank-BLOB gespeichert. Die Anwendung verhindert Bearbeitung abgeschlossener Dokumente; dies ersetzt kein externes Backup und kein unveränderbares Langzeitarchiv.

## Umfang
Die Anwendung verbindet Mietaufträge, Rechnungen, Zahlungen, Kundenhistorie, Ausgaben und Übergabe-/Abnahmeprotokolle. Sie enthält keine vollständige doppelte Buchhaltung, Bankanbindung, Bilanz, Lohnbuchhaltung, Steuerdeklaration oder Swiss-QR-Zahlteile. Pro Reservation ist derzeit eine Rechnung plus vollständige Gutschrift möglich; Teilgutschriften und eine neue Ersatzrechnung auf derselben Reservation sind noch nicht implementiert. Unterschriften sind handschriftliche Bildaufzeichnungen, keine qualifizierte elektronische Signatur.

## Prüfung
Tests mit isolierten Datenbanken: finance-domain.php, finance-integration.php, finance-http.cjs, finance-browser.cjs und finance-protocol-integration.php. Browserfixture und Testserver siehe Testdateien. Browserprüfung mit Edge bei Desktop- und Mobilbreite, inklusive Zeichnen beider Unterschriften nach Grössenänderung; kein physisches iPad getestet. PDF-Seiten gerendert und visuell geprüft, ZIP per CRC geprüft. Slider-Regression: tests/slider-zoom.php.

PDF-Abhängigkeit: FPDF 1.9.0 von https://github.com/Setasign/FPDF/tree/1.9.0, Lizenz unter vendor/fpdf/license.txt. Helvetica-Kernfonts, Zeichen ausserhalb Windows-1252 werden transliteriert.
