$ErrorActionPreference = 'Stop'
$role = (& C:\xampp\mysql\bin\mysql.exe -N -B -h 127.0.0.1 -u root -D rentabau -e "SELECT role FROM users WHERE email='gast@rentabau.ch' LIMIT 1" | Out-String).Trim()
if ($role -ne 'guest') { throw "Gastkonto fehlt oder hat falsche Rolle: $role" }
$api = Get-Content -Raw api/reservations.php
if ($api -notmatch 'Vorschauzugang kann keine Reservationen erstellen') { throw 'Gastzugang ist nicht schreibgeschützt.' }
if ($api -notmatch "role.*guest") { throw 'Gast-Availability-Logik fehlt.' }
$page = [string](curl.exe -sS http://127.0.0.1/rentabau.ch/maintenance.php)
if ($page -notmatch 'maintenance-email') { throw 'Gastlogin fehlt auf maintenance.php.' }
$login = [string](curl.exe -sS http://127.0.0.1/rentabau.ch/login.php)
if ($login -match 'id="tab-register"') { throw 'Registrierung ist im Aufbau-Modus sichtbar.' }
Write-Output 'PASS: Gast-Vorschauzugang ist vorhanden und schreibgeschützt.'
