<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
requireAdmin();
require_once __DIR__ . '/includes/slider.php';
$error = '';
$sliderConfig = sliderLoad();
$beforeSliderConfig = $sliderConfig;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!is_string($submittedToken) || $submittedToken === '' || empty($_SESSION['csrf_token'])) {
        http_response_code(419); exit('Ungültige Formularsitzung. Bitte Seite neu laden.');
    }
    verifyCsrfToken($submittedToken);
    if (($_POST['action'] ?? '') === 'upload') {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $file = $_FILES['image'] ?? [];
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 8 * 1024 * 1024) throw new InvalidArgumentException('Bitte ein Bild bis 8 MB auswählen.');
            $info = @getimagesize($file['tmp_name']);
            $extension = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$info['mime'] ?? ''] ?? null;
            if (!$extension || ($info[0] * $info[1]) > 40000000) throw new InvalidArgumentException('Erlaubt sind JPG, PNG und WebP bis 40 Megapixel.');
            $imagePath = 'brand/slider-' . bin2hex(random_bytes(12)) . '.' . $extension;
            if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $imagePath)) throw new RuntimeException('Bild konnte nicht gespeichert werden.');
            auditLogSafe('create', 'slider', 'image', $imagePath, 'Slider-Bild hochgeladen', null, ['path' => $imagePath]);
            echo json_encode(['image'=>$imagePath]);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            http_response_code(400); echo json_encode(['error'=>$exception->getMessage()]);
        }
        exit;
    }
    try {
        $input = json_decode((string)($_POST['config'] ?? ''), true, 64, JSON_THROW_ON_ERROR);
        if (!is_array($input)) throw new InvalidArgumentException('Ungültige Einstellungen.');
        $sliderConfig = sliderValidate($input);
        sliderSave($sliderConfig);
        auditLogSafe('update', 'slider', 'slider_config', null, 'Homepage-Slider aktualisiert', $beforeSliderConfig ?? null, $sliderConfig);
        redirectTo('admin-slider.php?saved=1');
    } catch (JsonException|InvalidArgumentException|RuntimeException $exception) { $error = $exception->getMessage(); }
}
$sliderImages = [];
foreach (glob(__DIR__ . '/brand/*') ?: [] as $file) {
    $relative = 'brand/' . basename($file);
    if (sliderImageValid($relative) && !str_contains(basename($file), 'logo')) $sliderImages[] = $relative;
}
function sliderField(string $key, string $label, string $type = 'text', array $options = []): void {
    global $sliderConfig;
    echo '<label class="slider-field"><span>' . e($label) . '</span>';
    if ($type === 'select') {
        echo '<select data-config="' . e($key) . '">';
        foreach ($options as $value => $name) echo '<option value="' . e($value) . '"' . ((string)$sliderConfig[$key] === (string)$value ? ' selected' : '') . '>' . e($name) . '</option>';
        echo '</select>';
    } elseif ($type === 'textarea') echo '<textarea rows="3" data-config="' . e($key) . '">' . e($sliderConfig[$key]) . '</textarea>';
    elseif ($type === 'checkbox') echo '<input type="checkbox" data-config="' . e($key) . '"' . ($sliderConfig[$key] ? ' checked' : '') . '>';
    else {
        echo '<input type="' . e($type) . '" data-config="' . e($key) . '" value="' . e($sliderConfig[$key]) . '"';
        foreach ($options as $attr => $value) echo ' ' . e($attr) . '="' . e($value) . '"';
        echo '>';
    }
    echo '</label>';
}
?>
<!DOCTYPE html>
<html lang="de"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Homepage-Slider verwalten | Rent A Bau</title>
<link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
<link rel="stylesheet" href="assets/css/slider.css?v=<?= filemtime(__DIR__ . '/assets/css/slider.css') ?>">
<link rel="stylesheet" href="assets/css/slider-admin.css?v=<?= filemtime(__DIR__ . '/assets/css/slider-admin.css') ?>">
</head><body>
<?php include __DIR__ . '/includes/navigation.php'; ?>
<main class="shell slider-admin">
  <a class="text-link" href="admin.php">← Zur Verwaltung</a>
  <div class="slider-admin-heading"><div><span class="eyebrow">Website gestalten</span><h1>Homepage-Slider</h1><p>Bilder wechseln, der Text bleibt stehen. Dein aktueller Look ist als Vorlage hinterlegt.</p></div><a class="button button-outline" href="index.php" target="_blank" rel="noopener">Homepage ansehen ↗</a></div>
  <?php if (isset($_GET['saved'])): ?><p class="slider-notice" role="status">Gespeichert. Die Änderungen sind auf der Homepage sichtbar.</p><?php endif; ?>
  <?php if ($error): ?><p class="slider-error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <form id="slider-editor" method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="config" id="slider-config">
    <div class="slider-editor-grid"><div class="slider-editor-fields">
      <details open><summary>Bilder & Reihenfolge</summary><p>Bis zu 12 Bilder. Verschiebe sie mit den Pfeilen und passe den Bildausschnitt je Bild an.</p><div id="slider-slide-list"></div><button type="button" class="button button-outline button-small" id="slider-add">+ Bild hinzufügen</button><p class="slider-help">Die zwei ergänzenden Standardmotive sind KI-generierte Illustrationen. Du kannst sie durch eigene Maschinenfotos ersetzen.</p></details>
      <details><summary>Texte & Schaltflächen</summary>
        <?php sliderField('eyebrow','Kleine Überschrift'); sliderField('title','Hauptüberschrift'); sliderField('accent','Hervorgehobene zweite Zeile'); sliderField('description','Beschreibung','textarea'); sliderField('facts','Vorteile – ein Eintrag pro Zeile','textarea'); sliderField('primaryLabel','Erster Button – Text'); sliderField('primaryUrl','Erster Button – Link'); sliderField('secondaryLabel','Zweiter Button – Text'); sliderField('secondaryUrl','Zweiter Button – Link'); ?>
        <p class="slider-help">Leerer Button-Text blendet den Button aus. Links: lokale PHP-Seite, https://, tel: oder mailto:.</p>
      </details>
      <details><summary>Schrift & Farben</summary><div class="slider-field-grid">
        <?php sliderField('fontFamily','Schriftfamilie','select',['system'=>'Aktueller Stil (Systemschrift)','arial'=>'Arial','georgia'=>'Georgia','verdana'=>'Verdana']); sliderField('fontWeight','Schriftstärke','select',['400'=>'Normal','600'=>'Halbfett','700'=>'Fett','800'=>'Extra fett','900'=>'Sehr kräftig (aktuell)']); sliderField('fontStyle','Schriftstil','select',['normal'=>'Aufrecht','italic'=>'Kursiv']); sliderField('titleSize','Überschrift Desktop (px)','number',['min'=>28,'max'=>120]); sliderField('mobileTitleSize','Überschrift Mobil (px)','number',['min'=>24,'max'=>64]); sliderField('bodySize','Beschreibung (px)','number',['min'=>14,'max'=>30]); sliderField('eyebrowSize','Kleine Überschrift (px)','number',['min'=>10,'max'=>24]); sliderField('titleColor','Überschrift','color'); sliderField('accentColor','Akzent & Bildpunkte','color'); sliderField('bodyColor','Beschreibung & Vorteile','color'); sliderField('buttonColor','Button-Hintergrund','color'); sliderField('buttonTextColor','Button-Schrift','color'); ?>
      </div></details>
      <details><summary>Layout & Bildwirkung</summary>
        <?php sliderField('alignment','Textausrichtung','select',['left'=>'Links (aktuell)','center'=>'Zentriert','right'=>'Rechts']); sliderField('contentWidth','Maximale Textbreite (px)','number',['min'=>320,'max'=>1100]); sliderField('minHeight','Mindesthöhe Desktop (px)','number',['min'=>400,'max'=>900]); sliderField('overlay','Abdunklung (%)','number',['min'=>0,'max'=>100]); ?>
        <p class="slider-help">Auf Mobilgeräten passt sich die Höhe dem Text an. Genügend Abdunklung hält die Schrift lesbar.</p>
      </details>
      <details><summary>Bedienung & Animation</summary>
        <?php sliderField('showArrows','Pfeile anzeigen','checkbox'); sliderField('showDots','Bildpunkte anzeigen','checkbox'); sliderField('autoplay','Automatischer Bildwechsel','checkbox'); sliderField('interval','Wechselintervall (Sekunden)','number',['min'=>3,'max'=>30]); sliderField('transition','Überblendung (Millisekunden)','number',['min'=>0,'max'=>1500]); ?>
        <p class="slider-help">Standard: manuell. Automatik pausiert bei Mauszeiger, Tastaturfokus und in inaktiven Tabs. Bei reduzierter Bewegung bleibt sie aus.</p>
      </details>
    </div><aside class="slider-preview-panel"><div class="slider-preview-heading"><strong>Live-Vorschau</strong><label>Ansicht <select id="slider-preview-size"><option value="desktop">Desktop</option><option value="mobile">Mobil</option></select></label></div><p class="slider-help">Änderungen erscheinen erst nach dem Speichern auf der Website.</p><label class="slider-field">Bild bearbeiten<select id="slider-preview-image"></select></label>
<p class="slider-help">Der Rahmen zeigt den sichtbaren Ausschnitt. Wähle ein Bild und verschiebe es mit den Pfeilen oder Reglern. Position und Zoom werden getrennt für Desktop und Mobil gespeichert. Die Auswahl „Ansicht“ bestimmt, welche Einstellungen du bearbeitest.</p>
<div class="slider-preview-frame"><div class="slider-preview-scroll"><div id="slider-preview"><?php include __DIR__ . '/includes/hero-slider.php'; ?></div></div></div>
<div id="slider-crop-controls" class="slider-crop-controls">
<strong id="slider-crop-mode" aria-live="polite">Bildausschnitt für Desktop</strong>
<div class="slider-position-buttons" role="group" aria-label="Bild verschieben">
<button type="button" data-crop-move="up" aria-label="Bild nach oben verschieben">↑</button>
<button type="button" data-crop-move="left" aria-label="Bild nach links verschieben">←</button>
<button type="button" data-crop-move="down" aria-label="Bild nach unten verschieben">↓</button>
<button type="button" data-crop-move="right" aria-label="Bild nach rechts verschieben">→</button>
</div>
<label class="slider-field">Horizontal <input id="slider-crop-x" type="range" min="0" max="100" value="50"></label>
<label class="slider-field">Vertikal <input id="slider-crop-y" type="range" min="0" max="100" value="50"></label>
<div class="slider-zoom-controls"><button type="button" id="slider-zoom-out" aria-label="Bild verkleinern">−</button><label class="slider-field">Bildzoom <input id="slider-crop-zoom" type="range" min="100" max="300" step="5" value="100"></label><button type="button" id="slider-zoom-in" aria-label="Bild vergrössern">+</button><output id="slider-zoom-value">100 %</output></div>
<button type="button" id="slider-crop-reset">Ausschnitt zurücksetzen</button>
</div></aside></div>
    <div class="slider-save-bar"><span id="slider-editor-status" role="status">Alle Änderungen gespeichert.</span><button type="button" class="button button-outline button-small" id="slider-reset-style">Aktuellen Originalstil laden</button><button type="submit" class="button" id="slider-save">Änderungen speichern</button></div>
  </form>
</main>
<script type="application/json" id="slider-initial"><?= json_encode(['config'=>$sliderConfig,'defaults'=>sliderDefaults(),'images'=>$sliderImages], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
<script src="assets/js/slider.js?v=<?= filemtime(__DIR__ . '/assets/js/slider.js') ?>"></script><script src="assets/js/slider-admin.js?v=<?= filemtime(__DIR__ . '/assets/js/slider-admin.js') ?>"></script>
</body></html>
