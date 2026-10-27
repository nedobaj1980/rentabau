<?php
declare(strict_types=1);

function currentUser(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function requireLogin(): array
{
    $user = currentUser();
    if ($user === null) {
        $isApi = str_contains((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/api/');
        if ($isApi || str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Anmeldung erforderlich.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        header('Location: konto.php?login=1', true, 302);
        exit;
    }
    return $user;
}

function requireAdmin(): array
{
    $user = requireLogin();
    if (($user['role'] ?? '') !== 'admin') {
        if (str_contains((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/api/')) {
            http_response_code($user === null ? 401 : 403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Administratorrechte erforderlich.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        http_response_code(403);
        exit('Zugriff verweigert.');
    }
    return $user;
}
