<?php
declare(strict_types=1);

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): void
{
    if (!is_string($token) || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
        http_response_code(419);
        exit('Ungültige Formularsitzung. Bitte Seite neu laden.');
    }
}
