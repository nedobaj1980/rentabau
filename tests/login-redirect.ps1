$ErrorActionPreference = 'Stop'
$headers = curl.exe -sS -D - -o NUL http://localhost/rentabau.ch/konto.php
if (-not ($headers -match 'Location: .*konto\.php\?login=1')) { throw 'Nicht angemeldete Besucher werden nicht gezielt zum Login-Einstieg geführt.' }
$body = curl.exe -sS http://localhost/rentabau.ch/konto.php?login=1
if (($body -match 'Location:|ERR_TOO_MANY_REDIRECTS') -or (-not ($body -match 'E-Mail-Adresse|Anmelden'))) { throw 'konto.php?login=1 muss das Login-Formular direkt anzeigen.' }
Write-Output 'PASS: Konto-Login-Einstieg ist ohne Redirect-Schleife erreichbar.'
