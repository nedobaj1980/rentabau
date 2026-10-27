$ErrorActionPreference = 'Stop'
$template = Get-Content -Raw templates/admin.php
$script = Get-Content -Raw templates/admin.php
$css = Get-Content -Raw assets/css/style.css
foreach ($id in @('reservation-filter-customer','reservation-filter-machine','reservation-filter-start','reservation-filter-end','reservation-filter-status','reservation-filter-payment','reservation-filter-sort','reservation-detail-panel')) {
    if ($template -notmatch [regex]::Escape($id)) { throw "Reservations-Element fehlt: $id" }
}
foreach ($functionName in @('handleReservationFilterSubmit','resetReservationFilters','openReservationDetail','closeReservationDetail')) {
    if ($script -notmatch "function $functionName") { throw "Reservations-Funktion fehlt: $functionName" }
}
foreach ($className in @('reservation-filter-form','reservation-detail-panel','reservation-detail-grid')) {
    if ($css -notmatch [regex]::Escape($className)) { throw "Reservations-CSS fehlt: $className" }
}
if ($template -notmatch 'Details') { throw 'Details-Aktion fehlt.' }
Write-Output 'PASS: Reservations-Filter und Detailansicht sind strukturell vorhanden.'
