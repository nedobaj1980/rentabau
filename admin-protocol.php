<?php
declare(strict_types=1);
require_once __DIR__.'/includes/bootstrap.php';requireAdmin();
header('Cache-Control: private, no-store');
?>
<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Übergabe & Abnahme | Rent A Bau</title>
<link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/finance-protocol.css?v=<?=filemtime(__DIR__.'/assets/css/finance-protocol.css')?>"></head><body>
<?php include __DIR__.'/includes/navigation.php'; ?>
<main class="shell protocol-page">
<a href="admin-finance.php" class="text-link">← Finanzen & Belege</a>
<header class="protocol-heading"><div><span class="eyebrow">Auftragsdokumentation</span><h1>Übergabe & Abnahme</h1><p>Maschinenzustand, Fotos und Unterschriften an einem Ort.</p></div><a class="button button-outline" id="protocol-finance-link" href="admin-finance.php">Zur Auftragsakte</a></header>
<div id="protocol-status" class="protocol-status" role="status" aria-live="polite">Auftrag wird geladen …</div>
<label class="protocol-field">Auftrag auswählen<select id="protocol-order"><option value="">Bitte auswählen</option></select></label>
<div id="protocol-order-info" class="protocol-order-info"></div>
<div class="protocol-layout"><aside class="protocol-sidebar"><h2>Protokolle</h2><div id="protocol-list"></div><form id="protocol-create"><label class="protocol-field">Neues Protokoll<select name="type"><option value="handover">Übergabe</option><option value="return">Rücknahme / Abnahme</option></select></label><button type="submit" class="button">+ Protokoll anlegen</button></form></aside>
<section class="protocol-content" id="protocol-detail"><p>Wähle ein vorhandenes Protokoll oder lege ein neues an.</p></section></div>
</main><script type="application/json" id="protocol-initial"><?=json_encode(['csrf'=>csrfToken()],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_HEX_APOS)?></script><script src="assets/js/finance-protocol.js?v=<?=filemtime(__DIR__.'/assets/js/finance-protocol.js')?>"></script></body></html>
