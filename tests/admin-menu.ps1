$ErrorActionPreference = 'Stop'
$body = Get-Content -Raw templates/admin.php
if ($body -notmatch 'admin-menu') { throw 'Admin-Menü fehlt.' }
foreach ($label in @('Kalender','Reservationen','Produkte','Kategorien','Transport','Zahlungen','Rabatte')) {
    if ($body -notmatch [regex]::Escape($label)) { throw "Admin-Menüpunkt fehlt: $label" }
}
Write-Output 'PASS: Admin-Menü ist vorhanden und enthält alle Bereiche.'
