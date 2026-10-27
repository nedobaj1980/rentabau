# Homepage-Slider

Öffnen: `admin.php` → **Homepage-Slider** oder `admin-slider.php` (Administrator-Anmeldung erforderlich).

- 1–12 Bilder hinzufügen, sortieren, entfernen oder aus der vorhandenen Bildauswahl übernehmen.
- Eigene JPG-, PNG- und WebP-Bilder bis 8 MB und 40 Megapixel hochladen. Die Dateien werden auf Bildinhalt geprüft und mit einem zufälligen Namen abgelegt.
- Je Bild Beschreibung und horizontalen/vertikalen Bildausschnitt einstellen.
- Überschriften, Beschreibung, Vorteile und zwei Buttons bearbeiten. Leerer Buttontext blendet den jeweiligen Button aus.
- Schriftfamilie, Schriftstärke, Kursivstil, Desktop-/Mobilgrösse, Beschreibung, kleine Überschrift, Text- und Buttonfarben einstellen.
- Textausrichtung, Textbreite, Mindesthöhe, Abdunklung, Pfeile, Bildpunkte, Überblendung und optionalen automatischen Wechsel einstellen.
- Die Live-Vorschau zeigt Änderungen vor dem Speichern. Desktop wird verkleinert dargestellt, Mobil mit einer Breite von 390 px.
- **Aktuellen Originalstil laden** setzt Layout und Darstellungsoptionen zurück, behält aber Bilder, Texte und Links. Erst **Änderungen speichern** übernimmt die Vorschau auf die Homepage.

Die Bilder wechseln standardmässig manuell mit Pfeilen, Bildpunkten, Pfeiltasten oder horizontalem Wischen. Optionaler automatischer Wechsel pausiert bei Mauszeiger, Tastaturfokus und in ausgeblendeten Tabs. Die Einstellung „reduzierte Bewegung“ deaktiviert automatische Wechsel und Überblendungen.

Einstellungen: `database/slider.json`, getrennt von den Katalogeinstellungen. Fehlende/ungültige Konfiguration fällt auf die ursprünglichen Standardwerte zurück. Änderungen benötigen Adminrechte und ein gültiges CSRF-Token. Bilder aus dem Slider zu entfernen löscht die Bilddatei nicht aus der Bibliothek.

## Prüfung

`C:/php/php.exe tests/slider-config.php`

`node --test tests/slider-behavior.test.cjs`

`powershell.exe -NoProfile -ExecutionPolicy Bypass -File tests/public-pages.ps1`

In Chrome zusätzlich Pfeil-/Tastaturwechsel, mobile Darstellung und Admin-Speicherung mit geänderter Schriftgrösse sowie Wiederherstellung des Originalstils geprüft.

## Bildmotive

Das vorhandene Titelbild bleibt erhalten. `brand/slider-transport.webp` und `brand/slider-verdichtung.webp` wurden mit dem integrierten ImageGen-Werkzeug erzeugt und als WebP optimiert. Es sind illustrative Motive, keine dokumentarischen Fotos des tatsächlichen Mietbestands. Eigene Fotos können sie im Editor ersetzen.

### Bildausschnitt direkt in der Vorschau

Rechts unter Live-Vorschau ein Bild auswählen oder links auf Bearbeiten klicken. Der orange Rahmen begrenzt den sichtbaren Slider. Die vier Pfeile verschieben das Bild; Horizontal und Vertikal stellen den Ausschnitt ein. Plus/Minus und der Zoomregler vergrössern das Bild zwischen 100 und 300 Prozent. Bei 100 Prozent kann eine vollständig passende Bildachse nicht weiter verschoben werden; zum Verschieben auf beiden Achsen den Zoom erhöhen. Ausschnitt zurücksetzen stellt die Bildmitte und 100 Prozent wieder her.

Die Vorschau bleibt beim ausgewählten Bild stehen. Desktop und Mobil speichern Bildposition und Zoom unabhängig. Die Auswahl Ansicht schaltet Vorschau, Pfeile, Positionsregler und Zoom auf die jeweilige Ansicht um. Auch Zurücksetzen betrifft nur diese Ansicht. Vorhandene Ausschnitte werden beim ersten Bearbeiten als Startwert für beide Ansichten übernommen. Auf der Homepage gelten die mobilen Werte bis 700 Pixel Bildschirmbreite. Erst Änderungen speichern übernimmt die Einstellungen auf die Homepage. Alte Konfigurationen ohne Zoom bleiben mit 100 Prozent kompatibel.
