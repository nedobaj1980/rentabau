$ErrorActionPreference = 'Stop'

$response = Invoke-WebRequest -Uri 'http://localhost/rentabau.ch/index.php' -UseBasicParsing
if ($response.StatusCode -ne 200) { throw "index.php antwortet nicht mit HTTP 200." }
if ($response.Content -notmatch 'Rent A Bau') { throw 'PHP-Seite enthält keine Rent-A-Bau-Marke.' }
if ($response.Content -match 'Website im Aufbau') { Write-Output 'PASS: PHP-Grundgerüst ist durch den Online-Aufbau-Modus geschützt.'; exit 0 }
if ($response.Content -notmatch 'assets/css/style\.css') { throw 'Gemeinsames Stylesheet fehlt.' }
Write-Output 'PASS: PHP-Grundgerüst antwortet und bindet gemeinsame Darstellung ein.'
