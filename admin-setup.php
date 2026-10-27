<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$setupToken = getenv('RENTABAU_ADMIN_SETUP_TOKEN') ?: '';
if ($setupToken === '' || !hash_equals($setupToken, (string) ($_GET['token'] ?? ''))) {
    http_response_code(404);
    exit('Einrichtung nicht verfügbar.');
}

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? null);
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
        $message = 'Name, gültige E-Mail und ein Passwort mit mindestens 12 Zeichen sind erforderlich.';
    } else {
        $statement = databaseConnection()->prepare('INSERT INTO users (role, name, email, password_hash) VALUES (\'admin\', :name, :email, :password_hash)');
        $statement->execute(['name' => $name, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
        $message = 'Admin-Konto erstellt. Entferne anschließend den Setup-Token aus der Apache/PHP-Umgebung.';
    }
}
?><!doctype html><html lang="de"><head><meta charset="utf-8"><title>Admin einrichten</title><link rel="stylesheet" href="assets/css/style.css"></head><body><main class="container" style="max-width:560px;padding:64px 20px"><h1>Admin-Konto einrichten</h1><?php if ($message): ?><p role="alert"><?= e($message) ?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><label for="name">Name</label><input id="name" name="name" required><label for="email">E-Mail</label><input id="email" name="email" type="email" required><label for="password">Passwort (mindestens 12 Zeichen)</label><input id="password" name="password" type="password" minlength="12" required><button class="button" type="submit">Admin erstellen</button></form></main></body></html>
