<?php
declare(strict_types=1);

function auditSanitize(mixed $value): mixed
{
    if (is_array($value)) {
        $clean = [];
        foreach ($value as $key => $item) {
            $keyText = strtolower((string) $key);
            if (preg_match('/pass(word)?|secret|token|api[_-]?key|password_hash|smtp/i', $keyText)) {
                $clean[$key] = '[geschützt]';
            } else {
                $clean[$key] = auditSanitize($item);
            }
        }
        return $clean;
    }
    if (is_object($value)) return auditSanitize(get_object_vars($value));
    return is_scalar($value) || $value === null ? $value : (string) $value;
}

function auditLog(PDO $database, string $action, string $area, string $entityType = '', string|int|null $entityId = null, string $summary = '', mixed $before = null, mixed $after = null): void
{
    $user = currentUser() ?? [];
    $statement = $database->prepare(
        'INSERT INTO admin_audit_log (user_id, user_name, user_email, action, area, entity_type, entity_id, summary, before_json, after_json, ip_address, user_agent)
         VALUES (:user_id, :user_name, :user_email, :action, :area, :entity_type, :entity_id, :summary, :before_json, :after_json, :ip_address, :user_agent)'
    );
    $statement->execute([
        'user_id' => isset($user['id']) ? (int) $user['id'] : null,
        'user_name' => (string) ($user['name'] ?? ''),
        'user_email' => (string) ($user['email'] ?? ''),
        'action' => $action,
        'area' => $area,
        'entity_type' => $entityType,
        'entity_id' => $entityId === null ? '' : (string) $entityId,
        'summary' => mb_substr($summary, 0, 255),
        'before_json' => $before === null ? null : json_encode(auditSanitize($before), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'after_json' => $after === null ? null : json_encode(auditSanitize($after), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'ip_address' => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
        'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
    ]);
}

/**
 * Protokolliert eine Admin-Aktion, ohne die eigentliche Admin-Aktion bei einem
 * fehlenden/noch nicht migrierten Audit-Schema zu blockieren.
 */
function auditLogSafe(string $action, string $area, string $entityType = '', string|int|null $entityId = null, string $summary = '', mixed $before = null, mixed $after = null): void
{
    try {
        auditLog(databaseConnection(), $action, $area, $entityType, $entityId, $summary, $before, $after);
    } catch (Throwable $exception) {
        error_log('Rent A Bau audit error: ' . $exception->getMessage());
    }
}

function deploymentLog(PDO $database, string $releaseName, string $description = '', array $changedFiles = [], string $reference = ''): void
{
    $user = currentUser() ?? [];
    $statement = $database->prepare(
        'INSERT INTO deployment_log (user_id, user_name, user_email, release_name, description, changed_files_json, reference)
         VALUES (:user_id, :user_name, :user_email, :release_name, :description, :changed_files_json, :reference)'
    );
    $statement->execute([
        'user_id' => isset($user['id']) ? (int) $user['id'] : null,
        'user_name' => (string) ($user['name'] ?? ''),
        'user_email' => (string) ($user['email'] ?? ''),
        'release_name' => mb_substr($releaseName, 0, 120),
        'description' => mb_substr($description, 0, 255),
        'changed_files_json' => json_encode(array_values(array_map('strval', $changedFiles)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'reference' => mb_substr($reference, 0, 160),
    ]);
}
