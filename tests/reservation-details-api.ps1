$ErrorActionPreference = 'Stop'
$base = 'http://localhost/rentabau.ch/api/reservations.php'
$unauthHeaders = curl.exe -sS -D - -o NUL "$base`?id=1"
if (-not ($unauthHeaders -match 'HTTP/1\.1 401|HTTP/1\.0 401')) { throw 'Detail-API ist ohne Adminschutz erreichbar.' }
$badDate = curl.exe -sS -D - -o NUL "$base`?startDate=2026-99-99"
if (-not ($badDate -match 'HTTP/1\.1 401|HTTP/1\.0 401')) { throw 'Admin-Filter muss ohne Anmeldung geschützt bleiben.' }
$api = Get-Content -Raw api/reservations.php
foreach ($token in @('paymentPreference','filter_start_date','filter_end_date','detail_id','sortMap')) {
    if ($api -notmatch [regex]::Escape($token)) { throw "API-Filter fehlt: $token" }
}
Write-Output 'PASS: Reservations-Detail-API ist geschützt und vorbereitet.'
