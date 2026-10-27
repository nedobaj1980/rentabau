<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (currentUser() === null && ($_GET['login'] ?? '') === '1') {
    require __DIR__ . '/login.php';
    exit;
}

$user = requireLogin();
$statement = databaseConnection()->prepare('SELECT id, machine_name, start_date, end_date, handover, total_price, days, status, created_at FROM reservations WHERE user_id = :user_id OR (user_id IS NULL AND email = :email) ORDER BY created_at DESC, id DESC');
$statement->execute(['user_id' => $user['id'], 'email' => $user['email']]);
$reservations = $statement->fetchAll();
$pageTitle = 'Mein Kundenkonto – Rent A Bau';
include __DIR__ . '/includes/header.php';
?><main class="container" style="padding:64px 20px"><div style="display:flex;justify-content:space-between;gap:20px;align-items:center;flex-wrap:wrap"><div><p style="color:var(--orange-dark);font-weight:900;text-transform:uppercase;letter-spacing:.14em">Kundenportal</p><h1>Meine Miethistorie &amp; Zahlungen</h1><p>Willkommen, <?= e($user['name']) ?>.</p></div><a class="button button-outline" href="logout.php">Abmelden</a></div><section class="card" style="padding:26px;margin-top:30px"><h2>Meine Reservationen</h2><?php if (!$reservations): ?><p>Noch keine Reservationen vorhanden.</p><?php else: foreach ($reservations as $reservation): ?><article style="border:1px solid var(--line);padding:18px;margin:14px 0;border-radius:8px"><h3><?= e($reservation['machine_name']) ?></h3><p><?= e($reservation['start_date']) ?> bis <?= e($reservation['end_date']) ?> · <?= e($reservation['handover']) ?></p><strong><?= e(number_format((float) $reservation['total_price'], 2, '.', "'")) ?> CHF</strong><span class="status-pill" style="margin-left:14px"><?= e($reservation['status']) ?></span></article><?php endforeach; endif; ?></section></main><?php include __DIR__ . '/includes/footer.php'; ?>
