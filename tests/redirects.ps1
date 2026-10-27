$ErrorActionPreference = 'Stop'
$base = 'http://localhost/rentabau.ch/'
foreach ($page in @('index.html','maschinen.html','kategorie.html?cat=Bagger','maschine-detail.html?id=minibagger-18','kontakt.html','konto.html','admin.html')) {
    $headers = curl.exe -sS -D - -o NUL ($base + $page)
    if (-not ($headers -match 'HTTP/1\.1 301|HTTP/1\.0 301')) { throw "$page leitet nicht mit HTTP 301 weiter." }
    if (-not ($headers -match 'Location: .*\.php')) { throw "$page hat kein PHP-Ziel." }
}
Write-Output 'PASS: HTML-Kompatibilitätsweiterleitungen funktionieren.'
