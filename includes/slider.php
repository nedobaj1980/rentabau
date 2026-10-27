<?php
declare(strict_types=1);

function sliderDefaults(): array
{
    return [
        'eyebrow' => 'Baumaschinenvermietung · Ganze Ostschweiz',
        'title' => 'Die richtige Maschine.', 'accent' => 'Genau dann, wenn du sie brauchst.',
        'description' => 'Professionelle Baumaschinen für Private und Unternehmen – einfach reservieren, selbst abholen oder direkt auf die Baustelle liefern lassen.',
        'facts' => "Faire Tagespreise\nFlexible Mietdauer\nLieferung möglich",
        'primaryLabel' => 'Maschinen entdecken', 'primaryUrl' => 'maschinen.php',
        'secondaryLabel' => 'Beratung anrufen', 'secondaryUrl' => 'tel:+41710000000',
        'titleSize' => 80, 'mobileTitleSize' => 44, 'bodySize' => 19, 'eyebrowSize' => 12,
        'titleColor' => '#ffffff', 'accentColor' => '#ff4e00', 'bodyColor' => '#d9d9d9',
        'buttonColor' => '#ff4e00', 'buttonTextColor' => '#ffffff',
        'fontFamily' => 'system', 'fontWeight' => '900', 'fontStyle' => 'normal',
        'alignment' => 'left', 'contentWidth' => 800, 'minHeight' => 600, 'overlay' => 92,
        'autoplay' => false, 'interval' => 6, 'transition' => 500, 'showArrows' => true, 'showDots' => true,
        'slides' => [
            ['image' => 'brand/rent-a-bau-hero.png', 'alt' => 'Minibagger und Verdichtungsmaschine vor Schweizer Berglandschaft', 'positionX' => 50, 'positionY' => 50, 'zoom' => 100],
            ['image' => 'brand/slider-transport.webp', 'alt' => 'Illustratives Motiv: orangefarbener Raddumper auf einem Kiesplatz', 'positionX' => 50, 'positionY' => 50, 'zoom' => 100],
            ['image' => 'brand/slider-verdichtung.webp', 'alt' => 'Illustratives Motiv: Rüttelplatte auf einer vorbereiteten Kiesfläche', 'positionX' => 50, 'positionY' => 50, 'zoom' => 100],
        ],
    ];
}

function sliderImageValid(string $path): bool
{
    return preg_match('~^brand/[a-zA-Z0-9_-]+\.(?:png|jpe?g|webp|gif)$~D', $path) === 1 && is_file(__DIR__ . '/../' . $path);
}

function sliderValidate(array $input): array
{
    $data = sliderDefaults();
    foreach ($data as $key => $default) {
        if (array_key_exists($key, $input)) $data[$key] = $input[$key];
    }
    foreach (['eyebrow' => 180, 'title' => 180, 'accent' => 240, 'description' => 900, 'facts' => 600, 'primaryLabel' => 80, 'secondaryLabel' => 80, 'primaryUrl' => 300, 'secondaryUrl' => 300] as $key => $max) {
        if (!is_string($data[$key]) || strlen($data[$key]) > $max * 4) throw new InvalidArgumentException('Text zu lang oder ungültig: ' . $key);
    }
    if (trim($data['title']) === '') throw new InvalidArgumentException('Bitte eine Überschrift eingeben.');
    foreach (['primaryUrl', 'secondaryUrl'] as $key) {
        if (!preg_match('~^(?:[a-zA-Z0-9_-]+\.php(?:\?[a-zA-Z0-9_=&%.-]*)?|\#[a-zA-Z0-9_-]+|tel:\+?[0-9 ()-]+|mailto:[a-zA-Z0-9._+@-]+|https://[a-zA-Z0-9][^\s<>"\x27]*)$~D', $data[$key])) throw new InvalidArgumentException('Bitte einen gültigen Link eingeben: ' . $key);
    }
    foreach (['titleSize' => [28,120], 'mobileTitleSize' => [24,64], 'bodySize' => [14,30], 'eyebrowSize' => [10,24], 'contentWidth' => [320,1100], 'minHeight' => [400,900], 'overlay' => [0,100], 'interval' => [3,30], 'transition' => [0,1500]] as $key => [$min,$max]) {
        if (!is_numeric($data[$key]) || (float)$data[$key] < $min || (float)$data[$key] > $max) throw new InvalidArgumentException('Wert ausserhalb des erlaubten Bereichs: ' . $key);
        $data[$key] = (int)$data[$key];
    }
    foreach (['titleColor', 'accentColor', 'bodyColor', 'buttonColor', 'buttonTextColor'] as $key) {
        if (!is_string($data[$key]) || !preg_match('/^#[0-9a-fA-F]{6}$/D', $data[$key])) throw new InvalidArgumentException('Ungültige Farbe: ' . $key);
    }
    foreach (['fontFamily' => ['system','arial','georgia','verdana'], 'fontWeight' => ['400','600','700','800','900'], 'fontStyle' => ['normal','italic'], 'alignment' => ['left','center','right']] as $key => $allowed) {
        if (!in_array($data[$key], $allowed, true)) throw new InvalidArgumentException('Ungültiger Stil: ' . $key);
    }
    foreach (['autoplay','showArrows','showDots'] as $key) {
        if (!is_bool($data[$key])) throw new InvalidArgumentException('Ungültiger Schalter: ' . $key);
    }
    if (!is_array($data['slides']) || count($data['slides']) < 1 || count($data['slides']) > 12) throw new InvalidArgumentException('Bitte 1 bis 12 Bilder verwenden.');
    $slides = [];
    foreach ($data['slides'] as $slide) {
        if (!is_array($slide) || !is_string($slide['image'] ?? null) || !sliderImageValid($slide['image'])) throw new InvalidArgumentException('Bitte ein vorhandenes Slider-Bild auswählen.');
        if (!is_string($slide['alt'] ?? null) || strlen($slide['alt']) > 1000) throw new InvalidArgumentException('Ungültige Bildbeschreibung.');
        foreach (['positionX', 'positionY'] as $key) {
            if (!is_numeric($slide[$key] ?? null) || $slide[$key] < 0 || $slide[$key] > 100) throw new InvalidArgumentException('Bildausschnitt muss zwischen 0 und 100 liegen.');
        }
        $zoom = $slide['zoom'] ?? 100;
        if (!is_numeric($zoom) || !is_finite((float)$zoom) || $zoom < 100 || $zoom > 300) throw new InvalidArgumentException('Bildzoom muss zwischen 100 und 300 liegen.');
        $mobile = null;
        if (array_key_exists('mobile', $slide)) {
            if (!is_array($slide['mobile'])) throw new InvalidArgumentException('Ungültiger mobiler Bildausschnitt.');
            $mobile = [];
            foreach (['positionX' => [0,100], 'positionY' => [0,100], 'zoom' => [100,300]] as $key => [$min,$max]) {
                $value = $slide['mobile'][$key] ?? null;
                if (!is_numeric($value) || !is_finite((float)$value) || $value < $min || $value > $max) throw new InvalidArgumentException('Ungültiger mobiler Bildausschnitt: ' . $key);
                $mobile[$key] = (int)$value;
            }
        }
        $slides[] = ['image' => $slide['image'], 'alt' => $slide['alt'], 'positionX' => (int)$slide['positionX'], 'positionY' => (int)$slide['positionY'], 'zoom' => (int)$zoom];
        if ($mobile !== null) $slides[array_key_last($slides)]['mobile'] = $mobile;
    }
    $data['slides'] = $slides;
    return $data;
}

function sliderLoad(?string $path = null): array
{
    $path ??= __DIR__ . '/../database/slider.json';
    if (!is_file($path)) return sliderDefaults();
    try {
        $data = json_decode((string)file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
        return is_array($data) ? sliderValidate($data) : sliderDefaults();
    } catch (JsonException|InvalidArgumentException $error) { return sliderDefaults(); }
}

function sliderSave(array $data, ?string $path = null): void
{
    $data = sliderValidate($data);
    $path ??= __DIR__ . '/../database/slider.json';
    $temporary = tempnam(dirname($path), 'slider-');
    if ($temporary === false) throw new RuntimeException('Einstellungen konnten nicht gespeichert werden.');
    try {
        if (file_put_contents($temporary, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), LOCK_EX) === false || !rename($temporary, $path)) throw new RuntimeException('Einstellungen konnten nicht gespeichert werden.');
    } finally { if (is_file($temporary)) unlink($temporary); }
}

function sliderCss(array $config): string
{
    $rules = [];
    foreach (['titleSize','mobileTitleSize','bodySize','eyebrowSize','contentWidth','minHeight'] as $key) $rules[] = '--slider-' . $key . ':' . $config[$key] . 'px';
    foreach (['titleColor','accentColor','bodyColor','buttonColor','buttonTextColor','fontWeight','fontStyle'] as $key) $rules[] = '--slider-' . $key . ':' . $config[$key];
    $rules[] = '--slider-overlay:' . ($config['overlay'] / 100);
    $rules[] = '--slider-transition:' . $config['transition'] . 'ms';
    $rules[] = '--slider-font:' . ['system' => '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif','arial' => 'Arial,sans-serif','georgia' => 'Georgia,serif','verdana' => 'Verdana,sans-serif'][$config['fontFamily']];
    return implode(';', $rules);
}
