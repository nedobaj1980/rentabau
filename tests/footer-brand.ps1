$ErrorActionPreference = 'Stop'
$footer = Get-Content -Raw includes/footer.php
$css = Get-Content -Raw assets/css/style.css
if ($footer -notmatch 'rent-a-bau-logo-footer\.png') { throw 'Footer-Logo fehlt.' }
if ($footer -notmatch 'Service|Konto') { throw 'Footer-Spalten fehlen.' }
if ($css -notmatch 'border-top: 4px solid var\(--orange\)') { throw 'Footer-Akzent fehlt.' }
Write-Output 'PASS: Professionelle Footer-Struktur vorhanden.'
