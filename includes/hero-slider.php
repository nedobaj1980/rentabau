<?php
require_once __DIR__ . '/slider.php';
$sliderConfig ??= sliderLoad();
$sliderCount = count($sliderConfig['slides']);
?>
<section class="hero hero-slider" data-hero-slider data-alignment="<?= e($sliderConfig['alignment']) ?>" data-autoplay="<?= $sliderConfig['autoplay'] ? 'true' : 'false' ?>" data-interval="<?= e($sliderConfig['interval']) ?>" style="<?= e(sliderCss($sliderConfig)) ?>" aria-roledescription="Karussell" aria-label="Unser Maschinenpark" tabindex="0">
  <div class="slider-images">
    <?php foreach ($sliderConfig['slides'] as $i => $slide): $mobileCrop = $slide['mobile'] ?? $slide; ?>
      <img class="hero-bg slider-image<?= $i === 0 ? ' is-active' : '' ?>" src="<?= e($slide['image']) ?>" alt="<?= e($slide['alt']) ?>" style="--slide-position:<?= e($slide['positionX']) ?>% <?= e($slide['positionY']) ?>%;--slide-transform:scale(<?= e(($slide['zoom'] ?? 100) / 100) ?>);--slide-mobile-position:<?= e($mobileCrop['positionX']) ?>% <?= e($mobileCrop['positionY']) ?>%;--slide-mobile-transform:scale(<?= e(($mobileCrop['zoom'] ?? 100) / 100) ?>)" aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
    <?php endforeach; ?>
  </div>
  <div class="hero-shade"></div>
  <div class="shell hero-content">
    <span class="eyebrow light" data-slider-text="eyebrow"><?= e($sliderConfig['eyebrow']) ?></span>
    <h1><span data-slider-text="title"><?= e($sliderConfig['title']) ?></span><br><em data-slider-text="accent"><?= e($sliderConfig['accent']) ?></em></h1>
    <p data-slider-text="description"><?= e($sliderConfig['description']) ?></p>
    <div class="hero-actions">
      <a class="button" data-slider-button="primary" href="<?= e($sliderConfig['primaryUrl']) ?>"<?= $sliderConfig['primaryLabel'] === '' ? ' hidden' : '' ?>><?= e($sliderConfig['primaryLabel']) ?></a>
      <a class="button button-ghost" data-slider-button="secondary" href="<?= e($sliderConfig['secondaryUrl']) ?>"<?= $sliderConfig['secondaryLabel'] === '' ? ' hidden' : '' ?>><?= e($sliderConfig['secondaryLabel']) ?></a>
    </div>
    <div class="hero-facts"><?php foreach (array_filter(explode("\n", $sliderConfig['facts'])) as $fact): ?><span>✓ <?= e(trim($fact)) ?></span><?php endforeach; ?></div>
  </div>
  <div class="shell slider-controls"<?= $sliderCount < 2 ? ' hidden' : '' ?>>
    <button type="button" class="slider-arrow" data-slider-prev aria-label="Vorheriges Bild"<?= !$sliderConfig['showArrows'] ? ' hidden' : '' ?>>←</button>
    <div class="slider-dots"<?= !$sliderConfig['showDots'] ? ' hidden' : '' ?>><?php foreach ($sliderConfig['slides'] as $i => $slide): ?><button type="button" data-slider-dot="<?= $i ?>" aria-label="Bild <?= $i + 1 ?> anzeigen" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"></button><?php endforeach; ?></div>
    <button type="button" class="slider-arrow" data-slider-next aria-label="Nächstes Bild"<?= !$sliderConfig['showArrows'] ? ' hidden' : '' ?>>→</button>
    <button type="button" class="slider-pause" data-slider-pause<?= !$sliderConfig['autoplay'] ? ' hidden' : '' ?>>Automatik pausieren</button>
  </div>
  <span class="sr-only" data-slider-status aria-live="polite" aria-atomic="true"></span>
</section>
