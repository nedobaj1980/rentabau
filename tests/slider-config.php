<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/slider.php';
function check(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
$defaults = sliderDefaults();
check($defaults['autoplay'] === false, 'Manual playback must be the default.');
check(count($defaults['slides']) === 3, 'Three starting slides required.');
check(sliderValidate($defaults) === $defaults, 'Defaults must validate unchanged.');
foreach ([['titleSize' => 900], ['titleColor' => 'red;display:none'], ['primaryUrl' => 'javascript:alert(1)'], ['slides' => []], ['fontFamily' => 'url(evil)'], ['slides' => [['image' => '../config/database.php', 'alt' => '', 'positionX' => 50, 'positionY' => 50]]]] as $invalid) {
    try { sliderValidate(array_replace($defaults, $invalid)); throw new RuntimeException('Invalid config accepted: ' . json_encode($invalid)); }
    catch (InvalidArgumentException $expected) {}
}
$path = sys_get_temp_dir() . '/rentabau-slider-' . bin2hex(random_bytes(6)) . '.json';
try {
    $custom = array_replace($defaults, ['title' => '<script>alert(1)</script>', 'titleSize' => 64, 'autoplay' => true]);
    sliderSave($custom, $path);
    check(sliderLoad($path) === $custom, 'Settings must survive a save/reload.');
    check(sliderDefaults()['titleSize'] === 80, 'Saving must not mutate defaults.');
} finally { if (is_file($path)) unlink($path); }
function e(string|int|float|null $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
$sliderConfig = array_replace($defaults, ['title'=>'<script>alert(1)</script>']);
ob_start(); include __DIR__ . '/../includes/hero-slider.php'; $html=ob_get_clean();
check(!str_contains($html, '<script>alert(1)</script>'), 'Text must be HTML escaped.');
check(str_contains($html, '&lt;script&gt;'), 'Escaped text must remain visible.');
echo "PASS: slider defaults, validation and persistence.\n";
