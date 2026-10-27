<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], '', (bool) $params['secure'], (bool) $params['httponly']);
}
session_destroy();
redirectTo('index.php');
