<?php
require __DIR__ . '/slider-config.php';
$legacy = sliderDefaults();
unset($legacy['slides'][0]['zoom']);
check(sliderValidate($legacy)['slides'][0]['zoom'] === 100, 'Existing slides must default to 100% zoom.');
$custom = sliderDefaults();
$custom['slides'][0]['zoom'] = 175;
$path = sys_get_temp_dir() . '/slider-zoom-' . bin2hex(random_bytes(6)) . '.json';
try {
    sliderSave($custom, $path);
    check(sliderLoad($path)['slides'][0]['zoom'] === 175, 'Zoom must survive saving.');
} finally { if (is_file($path)) unlink($path); }
foreach ([99, 301, 'invalid'] as $zoom) {
    $custom['slides'][0]['zoom'] = $zoom;
    try { sliderValidate($custom); throw new RuntimeException('Invalid zoom accepted.'); }
    catch (InvalidArgumentException $expected) {}
}
$sliderConfig = sliderDefaults();
$sliderConfig['slides'][0]['zoom'] = 175;
ob_start(); include __DIR__ . '/../includes/hero-slider.php'; $html = ob_get_clean();
check(str_contains($html, 'scale(1.75)'), 'Homepage must render saved zoom.');
echo "PASS: zoom compatibility, persistence, validation and rendering.\n";
