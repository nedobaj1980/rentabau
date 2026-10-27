<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/../includes/bootstrap.php';

function auditJsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    requireAdmin();
    $database = databaseConnection();
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method === 'POST') {
        $payload = json_decode(file_get_contents('php://input') ?: '', true);
        if (!is_array($payload)) auditJsonResponse(['error' => 'Ungültige JSON-Daten.'], 400);
        $releaseName = trim((string) ($payload['releaseName'] ?? ''));
        if ($releaseName === '') auditJsonResponse(['error' => 'Bitte einen Release- oder Änderungsnamen angeben.'], 422);
        $description = trim((string) ($payload['description'] ?? ''));
        $reference = trim((string) ($payload['reference'] ?? ''));
        $changedFiles = $payload['changedFiles'] ?? [];
        if (!is_array($changedFiles)) $changedFiles = preg_split('/\R+/', (string) $changedFiles) ?: [];
        $changedFiles = array_values(array_filter(array_map(static fn($file): string => trim((string) $file), $changedFiles), static fn(string $file): bool => $file !== ''));
        if (count($changedFiles) > 100) $changedFiles = array_slice($changedFiles, 0, 100);
        deploymentLog($database, $releaseName, $description, $changedFiles, $reference);
        auditJsonResponse(['success' => true, 'message' => 'Deployment wurde protokolliert.']);
    }
    if ($method !== 'GET') auditJsonResponse(['error' => 'Methode nicht erlaubt.'], 405);
    $where = [];
    $params = [];
    $admin = trim((string) ($_GET['admin'] ?? ''));
    $area = trim((string) ($_GET['area'] ?? ''));
    $action = trim((string) ($_GET['action'] ?? ''));
    $from = trim((string) ($_GET['from'] ?? ''));
    $to = trim((string) ($_GET['to'] ?? ''));
    if ($admin !== '') { $where[] = '(user_name LIKE :admin OR user_email LIKE :admin)'; $params['admin'] = '%' . mb_substr($admin, 0, 120) . '%'; }
    if ($area !== '') { $where[] = 'area = :area'; $params['area'] = mb_substr($area, 0, 80); }
    if ($action !== '') { $where[] = 'action = :action'; $params['action'] = mb_substr($action, 0, 80); }
    if ($from !== '') { $where[] = 'created_at >= :from_date'; $params['from_date'] = $from . ' 00:00:00'; }
    if ($to !== '') { $where[] = 'created_at <= :to_date'; $params['to_date'] = $to . ' 23:59:59'; }
    $sql = 'SELECT id, user_id, user_name, user_email, action, area, entity_type, entity_id, summary, before_json, after_json, ip_address, user_agent, created_at FROM admin_audit_log';
    if ($where !== []) $sql .= ' WHERE ' . implode(' AND ', $where);
    $sql .= ' ORDER BY created_at DESC, id DESC LIMIT 250';
    $statement = $database->prepare($sql);
    $statement->execute($params);
    $rows = array_map(static function (array $row): array {
        $row['before'] = $row['before_json'] ? json_decode((string) $row['before_json'], true) : null;
        $row['after'] = $row['after_json'] ? json_decode((string) $row['after_json'], true) : null;
        unset($row['before_json'], $row['after_json'], $row['ip_address'], $row['user_agent']);
        return $row;
    }, $statement->fetchAll());
    $deployments = $database->query('SELECT id, user_name, user_email, release_name, description, changed_files_json, reference, created_at FROM deployment_log ORDER BY created_at DESC, id DESC LIMIT 100')->fetchAll();
    foreach ($deployments as &$deployment) {
        $deployment['changedFiles'] = $deployment['changed_files_json'] ? json_decode((string) $deployment['changed_files_json'], true) : [];
        unset($deployment['changed_files_json']);
    }
    auditJsonResponse(['audit' => $rows, 'deployments' => $deployments]);
} catch (PDOException $exception) {
    error_log('Rent A Bau audit database error: ' . $exception->getMessage());
    auditJsonResponse(['error' => 'Das Aktivitätsprotokoll ist momentan nicht verfügbar.'], 503);
} catch (Throwable $exception) {
    error_log('Rent A Bau audit error: ' . $exception->getMessage());
    auditJsonResponse(['error' => 'Das Aktivitätsprotokoll konnte nicht geladen werden.'], 500);
}
