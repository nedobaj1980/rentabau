$ErrorActionPreference = 'Stop'
$base = 'http://localhost/rentabau.ch/'
$email = "qa-$([DateTimeOffset]::UtcNow.ToUnixTimeMilliseconds())@example.test"
$cookie = Join-Path $env:TEMP "rentabau-auth-$([Guid]::NewGuid()).txt"
try {
    $registerPage = curl.exe -sS -c $cookie ($base + 'register.php')
    $csrf = ([regex]::Match($registerPage, 'name="csrf_token" value="([^"]+)"')).Groups[1].Value
    if (-not $csrf) { throw 'Registrierungsseite enthält kein CSRF-Token.' }
    $headers = curl.exe -sS -D - -o NUL -b $cookie -c $cookie -X POST -H 'Content-Type: application/x-www-form-urlencoded' --data-urlencode "csrf_token=$csrf" --data-urlencode 'name=QA Kunde' --data-urlencode "email=$email" --data-urlencode 'password=Sicheres-Testpasswort-123!' ($base + 'register.php')
    if (-not ($headers -match 'HTTP/1\.1 302|HTTP/1\.0 302')) { throw 'Registrierung muss weiterleiten.' }
    $account = curl.exe -sS -b $cookie ($base + 'konto.php')
    if (-not ($account -match 'QA Kunde')) { throw 'Konto zeigt den angemeldeten Kunden nicht an.' }
    Write-Output 'PASS: Registrierung, Login und persönliches Konto funktionieren.'
}
finally {
    & 'C:\xampp\mysql\bin\mysql.exe' -h 127.0.0.1 -u root -D rentabau -e "DELETE FROM users WHERE email = '$email';" | Out-Null
    Remove-Item -LiteralPath $cookie -Force -ErrorAction SilentlyContinue
}
