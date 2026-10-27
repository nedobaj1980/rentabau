<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? null);
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if ($name === '') $errors[] = 'Bitte Namen eingeben.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Bitte gültige E-Mail-Adresse eingeben.';
    if (strlen($password) < 10) $errors[] = 'Das Passwort muss mindestens 10 Zeichen enthalten.';
    if (!$errors) {
        try {
            $statement = databaseConnection()->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)');
            $statement->execute(['name' => $name, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int) databaseConnection()->lastInsertId(), 'name' => $name, 'email' => $email, 'role' => 'customer'];
            redirectTo('konto.php');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') $errors[] = 'Für diese E-Mail-Adresse existiert bereits ein Konto.';
            else { error_log($exception->getMessage()); $errors[] = 'Registrierung momentan nicht möglich.'; }
        }
    }
}
$pageTitle = 'Konto erstellen – Rent A Bau';
?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($pageTitle) ?></title><link rel="icon" href="favicon.svg"><link rel="stylesheet" href="assets/css/style.css"></head><body>
<main class="container" style="max-width:560px;padding:80px 20px"><h1>Kundenkonto erstellen</h1><p>Verwalte deine Reservationen bequem an einem Ort.</p><?php foreach ($errors as $error): ?><p role="alert" style="color:#b91c1c"><?= e($error) ?></p><?php endforeach; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><label for="name">Name *</label><input id="name" name="name" required value="<?= e($_POST['name'] ?? '') ?>"><label for="email">E-Mail *</label><input id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>"><label for="password">Passwort * (mindestens 10 Zeichen)</label><input id="password" name="password" type="password" minlength="10" required><button class="button" type="submit">Konto erstellen</button></form><p><a href="login.php">Bereits registriert? Einloggen</a></p></main></body></html>
