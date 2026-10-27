$ErrorActionPreference = 'Stop'
$headers = curl.exe -sS -D - -o NUL http://localhost/rentabau.ch/admin.php
if (-not ($headers -match 'HTTP/1\.1 302|HTTP/1\.0 302|HTTP/1\.1 403')) { throw 'Adminbereich ist ohne Anmeldung erreichbar.' }
Write-Output 'PASS: Adminbereich ist ohne Berechtigung geschützt.'
