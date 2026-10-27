$ErrorActionPreference = 'Stop'
$columns = (& C:\xampp\mysql\bin\mysql.exe -N -B -h 127.0.0.1 -u root -D rentabau -e "SHOW COLUMNS FROM reservations" | Out-String)
foreach ($column in @('payment_status','confirmed_at','paid_at','admin_note')) {
    if ($columns -notmatch "(?m)^$column\t") { throw "Reservationsfeld fehlt: $column" }
}
$template = Get-Content -Raw templates/admin.php
foreach ($label in @('Zahlungsstatus','Notiz','Reservation bestätigen','Zahlung erhalten')) {
    if ($template -notmatch [regex]::Escape($label)) { throw "Admin-Aktion fehlt: $label" }
}
foreach ($className in @('reservation-toolbar','reservation-quick-actions')) {
    if ($template -notmatch [regex]::Escape($className)) { throw "Reservations-Toolbar fehlt: $className" }
}
if ($template -notmatch '＋ Katalog einsehen' -or $template -notmatch '↻ Anfragen aktualisieren') {
    throw 'Schnellaktionen fehlen oberhalb der Reservationsliste.'
}
Write-Output 'PASS: Reservations-Arbeitsablauf ist vorhanden.'
