$ErrorActionPreference = 'Stop'

$baseUrl = 'http://localhost/rentabau.ch'
$mysql = 'C:\xampp\mysql\bin\mysql.exe'
$marker = "phase1-$([DateTimeOffset]::UtcNow.ToUnixTimeMilliseconds())@example.test"

function Invoke-JsonRequest {
    param(
        [Parameter(Mandatory)] [string] $Method,
        [Parameter(Mandatory)] [string] $Url,
        [hashtable] $Body
    )

    Add-Type -AssemblyName System.Net.Http
    $handler = [System.Net.Http.HttpClientHandler]::new()
    $handler.AllowAutoRedirect = $false
    $client = [System.Net.Http.HttpClient]::new($handler)
    try {
        $request = [System.Net.Http.HttpRequestMessage]::new(
            [System.Net.Http.HttpMethod]::new($Method),
            $Url
        )
        if ($null -ne $Body) {
            $request.Content = [System.Net.Http.StringContent]::new(
                ($Body | ConvertTo-Json -Compress),
                [System.Text.Encoding]::UTF8,
                'application/json'
            )
        }
        $response = $client.SendAsync($request).GetAwaiter().GetResult()
        $jsonText = $response.Content.ReadAsStringAsync().GetAwaiter().GetResult()
        $body = $null
        if ($jsonText.Trim() -ne '') { try { $body = $jsonText | ConvertFrom-Json } catch { $body = $null } }
        return @{ Status = [int]$response.StatusCode; Body = $body }
    }
    finally {
        $client.Dispose()
        $handler.Dispose()
    }
}

function Assert-Equal {
    param($Expected, $Actual, [string] $Message)
    if ($Expected -ne $Actual) {
        throw "$Message (erwartet: $Expected, erhalten: $Actual)"
    }
}

try {
    $confirmedStatus = "best$([char]0x00E4)tigt"
    $payload = @{
        machineId = 'phase1-test-machine'
        machineName = 'Phase-1-Testmaschine'
        customerName = 'API Test'
        email = $marker
        phone = '+41 79 000 00 00'
        startDate = '2026-10-05'
        endDate = '2026-10-06'
        handover = 'lieferung'
        notes = 'Automatischer Integrationstest'
        totalPrice = 321.50
        days = 2
        paymentPreference = 'twint'
    }

    $created = Invoke-JsonRequest -Method POST -Url "$baseUrl/api/reservations.php" -Body $payload
    Assert-Equal 201 $created.Status 'Eine gueltige Reservation muss mit HTTP 201 gespeichert werden'

    $count = & $mysql -N -B -h 127.0.0.1 -u root -D rentabau -e "SELECT COUNT(*) FROM reservations WHERE email = '$marker';"
    Assert-Equal '1' "$count" 'Die Reservation muss in MySQL gespeichert sein'
    $paymentPreference = & $mysql -N -B -h 127.0.0.1 -u root -D rentabau -e "SELECT payment_preference FROM reservations WHERE email = '$marker';"
    Assert-Equal 'twint' "$paymentPreference" 'Die Zahlungspräferenz muss in MySQL gespeichert sein'

    $invalidEmail = $payload.Clone()
    $invalidEmail.email = 'keine-email'
    $invalidEmail.customerName = 'Invalid Email Test'
    $response = Invoke-JsonRequest -Method POST -Url "$baseUrl/api/reservations.php" -Body $invalidEmail
    Assert-Equal 422 $response.Status 'Eine ungueltige E-Mail-Adresse muss abgewiesen werden'

    $invalidDates = $payload.Clone()
    $invalidDates.email = "dates-$marker"
    $invalidDates.startDate = '2026-10-07'
    $invalidDates.endDate = '2026-10-06'
    $response = Invoke-JsonRequest -Method POST -Url "$baseUrl/api/reservations.php" -Body $invalidDates
    Assert-Equal 422 $response.Status 'Ein Enddatum vor dem Startdatum muss abgewiesen werden'

    $reservationId = [string]$created.Body.reservation.id
    $patched = Invoke-JsonRequest -Method PATCH -Url "$baseUrl/api/reservations.php" -Body @{ id = $reservationId; status = $confirmedStatus }
    if ($patched.Status -notin 302,401) { throw 'Statusänderungen müssen für nicht angemeldete Anfragen geschützt sein.' }

    $list = Invoke-JsonRequest -Method GET -Url "$baseUrl/api/reservations.php"
    Assert-Equal 200 $list.Status 'Die Reservationsliste muss abrufbar sein'

    Write-Output 'PASS: Phase-1-Reservations-API speichert und liest MySQL korrekt.'
}
finally {
    & $mysql -h 127.0.0.1 -u root -D rentabau -e "DELETE FROM reservations WHERE email IN ('$marker', 'dates-$marker');" | Out-Null
}
