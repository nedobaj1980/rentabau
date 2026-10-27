<?php
declare(strict_types=1);

function maintenanceModeEnabled(): bool
{
    $value = getenv('RENTABAU_MAINTENANCE_MODE');
    if ($value !== false && $value !== '') {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }

    // A local flag keeps XAMPP protected without relying on Apache SetEnv.
    if (is_file(__DIR__ . '/maintenance.enabled')) {
        return true;
    }

    // On the production host the site stays private until explicitly disabled.
    if (function_exists('rentabauIsLocalEnvironment') && !rentabauIsLocalEnvironment()) {
        return true;
    }
    return false;
}

function enforceMaintenanceMode(): void
{
    if (!maintenanceModeEnabled() || currentUser() !== null) {
        return;
    }

    $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (in_array($script, ['login.php', 'logout.php', 'maintenance.php'], true)) {
        return;
    }

    $isApi = str_contains((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/api/');
    if ($isApi || str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
        http_response_code(503);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Die Website befindet sich momentan im Aufbau.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Location: maintenance.php', true, 302);
    exit;
}
