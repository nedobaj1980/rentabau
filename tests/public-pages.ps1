$ErrorActionPreference = 'Stop'
$base = 'http://localhost/rentabau.ch/'
$pages = @('index.php','maschinen.php','kategorie.php?cat=Bagger','maschine-detail.php?id=minibagger-18','bagger.php','fahrzeuge.php','kleinmaschinen.php','abbruch.php','kontakt.php','checkout.php')
foreach ($page in $pages) {
    $response = Invoke-WebRequest -Uri ($base + $page) -UseBasicParsing
    if ($response.StatusCode -ne 200) { throw "$page antwortet nicht mit HTTP 200." }
    if ($response.Content -match 'Website im Aufbau') { continue }
    if ($response.Content -notmatch 'Rent A Bau') { throw "$page enthält keine Marke." }
}
Write-Output 'PASS: Öffentliche PHP-Seiten sind erreichbar.'
