$ErrorActionPreference = 'Stop'
$contact = Get-Content -Raw templates/kontakt-public.php
$machines = Get-Content -Raw templates/maschinen.php
$css = Get-Content -Raw assets/css/style.css
if ($contact -notmatch 'contact-layout') { throw 'Kontaktlayout fehlt.' }
if ($machines -notmatch 'loading="lazy"') { throw 'Lazy-Loading für Maschinenbilder fehlt.' }
if ($css -notmatch 'contact-layout') { throw 'Responsive Kontaktlayout fehlt.' }
Write-Output 'PASS: Öffentliche Optimierungsklassen sind vorhanden.'
