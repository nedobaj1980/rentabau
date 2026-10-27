<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$loginError = null;
if (currentUser() !== null) {
    $user = currentUser();
    redirectTo(($user['role'] ?? '') === 'admin' ? 'admin.php' : 'konto.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? null);
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
        $loginError = 'Bitte E-Mail-Adresse und Passwort eingeben.';
    } else {
        try {
            $statement = databaseConnection()->prepare('SELECT id, name, email, role, password_hash FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
            $statement->execute(['email' => $email]);
            $user = $statement->fetch();
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $loginError = 'E-Mail-Adresse oder Passwort ist nicht korrekt.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];
                redirectTo(($user['role'] ?? '') === 'admin' ? 'admin.php' : 'konto.php');
            }
        } catch (Throwable $exception) {
            error_log('Rent A Bau login error: ' . $exception->getMessage());
            $loginError = 'Die Anmeldung ist momentan technisch nicht möglich. Bitte prüfe die Online-Datenbankkonfiguration.';
        }
    }
}
?><!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rent A Bau – Seite im Aufbau</title>
  <link rel="icon" href="favicon.svg">
  <style>
    :root { --orange: #f28c28; --orange-dark: #c8650d; --ink: #f8fafc; --muted: #cbd5e1; --panel: #17202b; }
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; color: var(--ink); background: #0d141c; font: 16px/1.5 Arial, sans-serif; }
    .maintenance-card { width: min(560px, 100%); padding: 42px 32px; text-align: center; background: var(--panel); border: 1px solid #334155; border-radius: 16px; box-shadow: 0 24px 70px rgba(0,0,0,.35); }
    .maintenance-card img { width: min(330px, 90%); height: auto; margin-bottom: 26px; }
    .eyebrow { color: var(--orange); font-weight: 800; letter-spacing: .12em; text-transform: uppercase; font-size: 12px; }
    h1 { margin: 8px 0 12px; font-size: clamp(28px, 5vw, 42px); }
    p { margin: 0 auto 24px; max-width: 440px; color: var(--muted); }
    a { display: inline-flex; min-height: 46px; align-items: center; justify-content: center; padding: 0 22px; border-radius: 7px; background: var(--orange); color: #1f2937; font-weight: 800; text-decoration: none; }
    a:hover { background: #ffab4d; }
    small { display: block; margin-top: 24px; color: #94a3b8; }
  </style>
</head>
<body>
  <main class="maintenance-card">
    <img src="brand/rent-a-bau-logo-footer.png" alt="Rent A Bau Logo">
    <div class="eyebrow">Website im Aufbau</div>
    <h1>Wir bereiten etwas Neues vor.</h1>
    <p>Die neue Rent-A-Bau-Plattform wird momentan eingerichtet. Der Zugang ist nur mit einem bestehenden Kunden- oder Admin-Konto möglich.</p>
    <?php if ($loginError): ?><p style="color:#fecaca;font-weight:700;" role="alert"><?= e($loginError) ?></p><?php endif; ?>
    <form method="post" style="display:grid;gap:12px;text-align:left;margin:0 auto;max-width:390px;">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
      <label for="maintenance-email" style="font-weight:700;font-size:13px;">E-Mail-Adresse</label>
      <input id="maintenance-email" name="email" type="email" required autocomplete="username" style="padding:12px;border-radius:7px;border:1px solid #475569;background:#0f172a;color:#f8fafc;">
      <label for="maintenance-password" style="font-weight:700;font-size:13px;">Passwort</label>
      <input id="maintenance-password" name="password" type="password" required autocomplete="current-password" style="padding:12px;border-radius:7px;border:1px solid #475569;background:#0f172a;color:#f8fafc;">
      <button type="submit" style="min-height:46px;border:0;border-radius:7px;background:var(--orange);color:#1f2937;font-weight:800;font-size:15px;cursor:pointer;">Mit Zugangsdaten anmelden</button>
    </form>
    <small>Rent A Bau · Romanshorn</small>
  </main>
</body>
</html>
