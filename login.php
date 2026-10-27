<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

// If user already logged in, redirect
if (currentUser()) {
    $user = currentUser();
    redirectTo(($user['role'] ?? '') === 'admin' ? 'admin.php' : 'konto.php');
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? null);
    $action = $_POST['action'] ?? 'login';

    if ($action === 'register' && maintenanceModeEnabled()) {
        $error = 'Die Registrierung ist während des Website-Aufbaus deaktiviert.';
    } elseif ($action === 'register') {
        $name = trim((string) ($_POST['reg_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['reg_email'] ?? '')));
        $password = (string) ($_POST['reg_password'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
            $error = 'Bitte fülle alle Pflichtfelder aus.';
        } else {
            $db = databaseConnection();
            $stmt = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $error = 'Diese E-Mail-Adresse wird bereits verwendet.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $db->prepare('INSERT INTO users (name, email, password_hash, role, is_active) VALUES (:name, :email, :hash, "customer", 1)');
                $insert->execute(['name' => $name, 'email' => $email, 'hash' => $hash]);
                $userId = (int) $db->lastInsertId();

                session_regenerate_id(true);
                $_SESSION['user'] = ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'customer'];
                redirectTo('konto.php');
            }
        }
    } else {
        // Login Action
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $error = 'Bitte gib deine E-Mail-Adresse und dein Passwort ein.';
        } else {
            $db = databaseConnection();
            $stmt = $db->prepare('SELECT id, name, email, role, password_hash FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'E-Mail-Adresse oder Passwort ist nicht korrekt.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int) $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                redirectTo(($user['role'] ?? '') === 'admin' ? 'admin.php' : 'konto.php');
            }
        }
    }
}

$pageTitle = 'Anmelden & Registrieren | Rent A Bau';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="Melde dich in deinem Rent A Bau Kundenkonto an oder erstelle ein neues Konto für deine Baumaschinenmieten.">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" href="favicon.svg">
  <style>
    .auth-container {
      max-width: 480px;
      margin: 60px auto;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 32px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.04);
    }
    .auth-tabs {
      display: flex;
      border-bottom: 2px solid var(--paper);
      margin-bottom: 24px;
    }
    .auth-tab {
      flex: 1;
      text-align: center;
      padding: 12px;
      font-weight: 800;
      font-size: 15px;
      color: var(--muted);
      cursor: pointer;
      border-bottom: 2px solid transparent;
      margin-bottom: -2px;
      transition: all 0.2s ease;
    }
    .auth-tab.active {
      color: var(--orange-dark);
      border-bottom-color: var(--orange);
    }
    .form-group {
      margin-bottom: 16px;
    }
    .form-group label {
      display: block;
      font-size: 12px;
      font-weight: 800;
      margin-bottom: 6px;
    }
    .form-group input {
      width: 100%;
      padding: 12px;
      border-radius: 6px;
      border: 1px solid var(--line);
      font-size: 14px;
      box-sizing: border-box;
    }
    .alert-box {
      background: #fef2f2;
      border: 1px solid #fca5a5;
      color: #991b1b;
      padding: 12px 14px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="site-header">
    <div class="shell nav-wrap">
      <a href="index.php" class="brand" aria-label="Rent A Bau Startseite">
        <img src="brand/rent-a-bau-logo.png" alt="Rent A Bau Logo">
      </a>
      <nav>
        <a href="index.php">Startseite</a>
        <a href="maschinen.php">Maschinen</a>
        <a href="so-funktionierts.php">So funktioniert's</a>
        <a href="kontakt.php">Kontakt & Delivery</a>
      </nav>
      <div class="nav-actions">
        <a href="login.php" class="text-link active">Anmelden</a>
        <a href="admin.php" class="button button-small button-dark">Admin</a>
      </div>
    </div>
  </header>

  <main class="shell">
    
    <div class="auth-container">
      
      <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0 0 6px; font-size: 24px;">Willkommen bei Rent A Bau</h2>
        <p style="color: var(--muted); margin: 0; font-size: 14px;">Verwalte deine Baumaschinen-Reservationen & Rechnungen</p>
      </div>

      <div class="auth-tabs">
        <div class="auth-tab active" id="tab-login" onclick="switchTab('login')">🔑 Anmelden</div>
        <?php if (!maintenanceModeEnabled()): ?><div class="auth-tab" id="tab-register" onclick="switchTab('register')">✨ Konto erstellen</div><?php endif; ?>
      </div>

      <?php if ($error): ?>
        <div class="alert-box">
          ⚠️ <?= e($error) ?>
        </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form id="form-login" method="post">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <div class="form-group">
          <label for="email">E-Mail-Adresse *</label>
          <input type="email" id="email" name="email" required placeholder="z. B. hans.muster@beispiel.ch" value="<?= e($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="password">Passwort *</label>
          <input type="password" id="password" name="password" required placeholder="Dein Passwort">
        </div>

        <button type="submit" class="button" style="width: 100%; padding: 14px; font-size: 16px; font-weight: 800; text-align: center;">
          🚀 Anmelden
        </button>
      </form>

      <!-- Registration Form -->
      <?php if (!maintenanceModeEnabled()): ?><form id="form-register" method="post" style="display: none;">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <div class="form-group">
          <label for="reg_name">Vor- und Nachname *</label>
          <input type="text" id="reg_name" name="reg_name" placeholder="z. B. Hans Muster">
        </div>

        <div class="form-group">
          <label for="reg_email">E-Mail-Adresse *</label>
          <input type="email" id="reg_email" name="reg_email" placeholder="z. B. hans.muster@beispiel.ch">
        </div>

        <div class="form-group">
          <label for="reg_password">Passwort festlegen *</label>
          <input type="password" id="reg_password" name="reg_password" minlength="10" placeholder="Mindestens 10 Zeichen">
        </div>

        <button type="submit" class="button button-dark" style="width: 100%; padding: 14px; font-size: 16px; font-weight: 800; text-align: center;">
          ✨ Neues Konto erstellen
        </button>
      </form><?php endif; ?>

    </div>

  </main>

  <!-- Footer -->
  <footer>
    <div class="shell footer-grid">
      <img src="brand/rent-a-bau-logo.png" alt="Rent A Bau Logo">
      <div>
        <strong>Rent A Bau</strong>
        <span>Baumaschinenvermietung in der Ostschweiz</span>
      </div>
      <div>
        <a href="login.php">Kundenkonto</a>
        <a href="admin.php">Admin Dashboard</a>
      </div>
    </div>
    <div class="shell footer-bottom">
      <span>© 2026 Rent A Bau</span>
      <span>rentabau.ch · Preise exkl. MWST</span>
    </div>
  </footer>

  <script>
    function switchTab(mode) {
      document.getElementById('tab-login').classList.toggle('active', mode === 'login');
      document.getElementById('tab-register').classList.toggle('active', mode === 'register');
      document.getElementById('form-login').style.display = mode === 'login' ? 'block' : 'none';
      document.getElementById('form-register').style.display = mode === 'register' ? 'block' : 'none';
    }
  </script>

</body>
</html>
