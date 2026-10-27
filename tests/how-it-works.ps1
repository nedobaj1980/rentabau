$ErrorActionPreference = 'Stop'
$page = (curl.exe -sS http://localhost/rentabau.ch/so-funktionierts.php) -join "`n"
$homeTemplate = Get-Content -Raw templates/index.php
$howFile = Get-Content -Raw so-funktionierts.php
if ($page -notmatch 'So funktioniert' -and $howFile -notmatch 'So funktioniert') { throw 'Detailseite fehlt.' }
if ($page -notmatch 'Maschine' -and $howFile -notmatch 'Maschine') { throw 'Erklärung zur Maschinenauswahl fehlt.' }
if ($homeTemplate -notmatch 'so-funktionierts.php') { throw 'Button zur Detailseite fehlt auf der Startseite.' }
Write-Output 'PASS: Detailseite und Home-Verlinkung vorhanden.'
