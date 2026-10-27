<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$message = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? null);
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $topic = trim((string) ($_POST['topic'] ?? ''));
    $body = trim((string) ($_POST['message'] ?? ''));
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $body === '') {
        $error = 'Bitte Name, gültige E-Mail-Adresse und Nachricht ausfüllen.';
    } else {
        $statement = databaseConnection()->prepare('INSERT INTO contact_requests (name, email, phone, topic, message) VALUES (:name, :email, :phone, :topic, :message)');
        $statement->execute(['name' => $name, 'email' => $email, 'phone' => $phone, 'topic' => $topic, 'message' => $body]);
        $message = 'Deine Anfrage wurde erfolgreich gespeichert. Wir melden uns in Kürze.';
        $_POST = [];
    }
}
$pageTitle = 'Kontakt – Rent A Bau';
include __DIR__ . '/templates/kontakt-public.php';
exit;
?><main class="container" style="padding:64px 20px;max-width:900px"><p style="color:var(--orange-dark);font-weight:900;text-transform:uppercase;letter-spacing:.14em">Kontakt</p><h1>Wie können wir dir helfen?</h1><?php if ($message): ?><p role="status" style="color:#166534"><?= e($message) ?></p><?php endif; ?><?php if ($error): ?><p role="alert" style="color:#b91c1c"><?= e($error) ?></p><?php endif; ?><form method="post" class="reservation-form"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><label for="name">Name *</label><input id="name" name="name" required value="<?= e($_POST['name'] ?? '') ?>"><label for="email">E-Mail *</label><input id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>"><label for="phone">Telefon</label><input id="phone" name="phone" type="tel" value="<?= e($_POST['phone'] ?? '') ?>"><label for="topic">Thema</label><select id="topic" name="topic"><option value="Reservation">Reservation</option><option value="Lieferung">Lieferung</option><option value="Allgemeine Anfrage">Allgemeine Anfrage</option></select><label for="message">Nachricht *</label><textarea id="message" name="message" rows="5" required><?= e($_POST['message'] ?? '') ?></textarea><button class="button" type="submit">Nachricht senden</button></form></main><?php include __DIR__ . '/includes/footer.php'; ?>
