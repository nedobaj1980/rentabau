$ErrorActionPreference = 'Stop'
$columns = (& C:\xampp\mysql\bin\mysql.exe -N -B -h 127.0.0.1 -u root -D rentabau -e "SHOW COLUMNS FROM admin_audit_log" | Out-String)
foreach ($column in @('user_id','action','area','before_json','after_json','ip_address','created_at')) {
    if ($columns -notmatch "(?m)^$column\t") { throw "Auditfeld fehlt: $column" }
}
$deployment = (& C:\xampp\mysql\bin\mysql.exe -N -B -h 127.0.0.1 -u root -D rentabau -e "SHOW COLUMNS FROM deployment_log" | Out-String)
if ($deployment -notmatch '(?m)^release_name\t') { throw 'Deployment-Tabelle fehlt.' }
$response = curl.exe -sS -D - -o NUL http://127.0.0.1/rentabau.ch/api/audit.php
if (-not ($response -match 'HTTP/1\.1 401|HTTP/1\.0 401|HTTP/1\.1 503|HTTP/1\.0 503')) { throw 'Audit-API ist ohne Adminschutz erreichbar.' }
$template = Get-Content -Raw templates/admin.php
foreach ($token in @('tab-sec-audit','Aktivitätsprotokoll','loadAuditLog','handleDeploymentSubmit','api/audit.php')) {
    if ($template -notmatch [regex]::Escape($token)) { throw "Audit-Oberfläche fehlt: $token" }
}
foreach ($file in @('api/settings.php','api/category-templates.php','api/upload.php','admin-slider.php','api/reservations.php')) {
    $content = Get-Content -Raw $file
    if ($content -notmatch 'auditLogSafe') { throw "Automatische Admin-Protokollierung fehlt: $file" }
}
Write-Output 'PASS: Audit- und Deployment-Protokoll ist vorhanden und geschützt.'
