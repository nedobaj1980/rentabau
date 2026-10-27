$ErrorActionPreference = 'Stop'
$nav = Get-Content -Raw includes/navigation.php
$css = Get-Content -Raw assets/css/style.css
if ($nav -notmatch 'mobile-menu-toggle') { throw 'Mobile-Menü-Button fehlt.' }
if ($nav -notmatch 'mobile-menu-panel') { throw 'Mobile-Menü-Panel fehlt.' }
if ($nav -notmatch 'aria-expanded') { throw 'ARIA-Zustand des Mobile-Menüs fehlt.' }
if ($css -notmatch 'mobile-menu-toggle') { throw 'Mobile-Menü-CSS fehlt.' }
Write-Output 'PASS: Mobile-Navigation ist strukturell vorhanden.'
