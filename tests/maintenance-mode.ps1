$ErrorActionPreference = 'Stop'
$config = Get-Content -Raw config/maintenance.php
$page = Get-Content -Raw maintenance.php
$bootstrap = Get-Content -Raw includes/bootstrap.php
$flag = Get-Content -Raw config/maintenance.enabled
foreach ($token in @('RENTABAU_MAINTENANCE_MODE','maintenance.php','login.php','json_encode')) {
    if (($config + $page + $bootstrap) -notmatch [regex]::Escape($token)) { throw "Wartungsmodus fehlt: $token" }
}
if ($flag.Trim() -ne '1') { throw 'Lokaler Aufbau-Flag fehlt.' }
php -l config/maintenance.php | Out-Null
php -l maintenance.php | Out-Null
$headers = curl.exe -sS -D - -o NUL http://127.0.0.1/rentabau.ch/
if (-not (($headers -match 'HTTP/1\.1 302|HTTP/1\.0 302') -and ($headers -match 'Location: maintenance\.php'))) { throw 'Startseite wird nicht auf die Aufbauseite umgeleitet.' }
$login = [string](curl.exe -sS http://127.0.0.1/rentabau.ch/login.php)
if (-not [bool]($login -match 'Anmelden|Willkommen bei Rent A Bau')) { throw 'Login bleibt im Wartungsmodus nicht erreichbar.' }
Write-Output 'PASS: Online-Aufbau-Modus ist vorbereitet.'
