$ErrorActionPreference = 'Stop'
$base = 'http://localhost/rentabau.ch/'
$marker = "contact-$([DateTimeOffset]::UtcNow.ToUnixTimeMilliseconds())@example.test"
$cookie = Join-Path $env:TEMP "rentabau-contact-$([Guid]::NewGuid()).txt"
try {
    $page = curl.exe -sS -c $cookie ($base + 'kontakt.php')
    $csrf = ([regex]::Match($page, 'name="csrf_token" value="([^"]+)"')).Groups[1].Value
    if (-not $csrf) { throw 'Kontaktformular enthält kein CSRF-Token.' }
    $result = curl.exe -sS -b $cookie -c $cookie -X POST -H 'Content-Type: application/x-www-form-urlencoded' --data-urlencode "csrf_token=$csrf" --data-urlencode 'name=QA Kontakt' --data-urlencode "email=$marker" --data-urlencode 'message=Testanfrage' ($base + 'kontakt.php')
    if (-not ($result -match 'erfolgreich')) { throw 'Kontaktformular meldet keine erfolgreiche Speicherung.' }
    $count = C:\xampp\mysql\bin\mysql.exe -N -B -h 127.0.0.1 -u root -D rentabau -e "SELECT COUNT(*) FROM contact_requests WHERE email='$marker';"
    if ([int]$count -ne 1) { throw 'Kontaktanfrage wurde nicht in MySQL gespeichert.' }
    Write-Output 'PASS: Kontaktanfragen werden serverseitig gespeichert.'
}
finally {
    & C:\xampp\mysql\bin\mysql.exe -h 127.0.0.1 -u root -D rentabau -e "DELETE FROM contact_requests WHERE email='$marker';" | Out-Null
    Remove-Item -LiteralPath $cookie -Force -ErrorAction SilentlyContinue
}
