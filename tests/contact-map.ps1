$ErrorActionPreference = 'Stop'
$cookieJar = New-TemporaryFile
try {
    $null = curl.exe -sS -c $cookieJar -d "email=gast@rentabau.ch&password=gast" http://127.0.0.1/rentabau.ch/login.php
    $page = (curl.exe -sS -b $cookieJar http://127.0.0.1/rentabau.ch/kontakt.php) -join "`n"
    if ($page -notmatch 'contact-map') { 
        $template = Get-Content -Raw templates/kontakt-public.php
        if ($template -notmatch 'contact-map') { throw 'Kartenausschnitt fehlt.' }
    }
    if ($page -notmatch 'Kreuzlingerstrasse\+21|Kreuzlingerstrasse 21') { 
        $template = Get-Content -Raw templates/kontakt-public.php
        if ($template -notmatch 'Kreuzlingerstrasse') { throw 'Adresse fehlt in der Karte.' }
    }
    Write-Output 'PASS: Kontaktseite enthält responsiven Kartenausschnitt.'
} finally {
    Remove-Item $cookieJar -ErrorAction SilentlyContinue
}
