<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../includes/bootstrap.php';

const RESERVATION_STATUSES = ['angefragt', 'bestätigt', 'abgeschlossen', 'storniert'];

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requestJson(): array
{
    $input = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($input)) {
        jsonResponse(['error' => 'Ungültige JSON-Anfrage.'], 400);
    }
    return $input;
}

function cleanString(array $input, string $key): string
{
    return trim((string) ($input[$key] ?? ''));
}

function validIsoDate(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function mapReservation(array $row): array
{
    return [
        'id' => (string) $row['id'],
        'userId' => $row['user_id'] === null ? null : (int) $row['user_id'],
        'machineId' => $row['machine_id'],
        'machineName' => $row['machine_name'],
        'customerName' => $row['customer_name'],
        'email' => $row['email'],
        'phone' => $row['phone'],
        'startDate' => $row['start_date'],
        'endDate' => $row['end_date'],
        'handover' => $row['handover'],
        'notes' => $row['notes'],
        'totalPrice' => (float) $row['total_price'],
        'days' => (int) $row['days'],
        'paymentPreference' => $row['payment_preference'] ?? '',
        'paymentStatus' => $row['payment_status'] ?? 'offen',
        'confirmedAt' => $row['confirmed_at'] ?? null,
        'paidAt' => $row['paid_at'] ?? null,
        'adminNote' => $row['admin_note'] ?? '',
        'status' => $row['status'],
        'createdAt' => (new DateTimeImmutable($row['created_at']))->format(DateTimeInterface::ATOM),
    ];
}

function mapAvailability(array $row): array
{
    return [
        'id' => (string) $row['id'],
        'machineId' => $row['machine_id'],
        'startDate' => $row['start_date'],
        'endDate' => $row['end_date'],
        'status' => $row['status'],
    ];
}

try {
    $database = databaseConnection();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $user = currentUser();
        $adminQueryRequested = isset($_GET['id']) || isset($_GET['customer']) || isset($_GET['machineId']) || isset($_GET['paymentPreference']) || isset($_GET['status']) || isset($_GET['startDate']) || isset($_GET['endDate']) || isset($_GET['sort']) || isset($_GET['direction']);
        if ($adminQueryRequested && ($user === null || ($user['role'] ?? '') !== 'admin')) {
            requireAdmin();
        }
        if ($user === null || ($user['role'] ?? '') === 'guest') {
            $statement = $database->query(
                "SELECT id, machine_id, start_date, end_date, status FROM reservations
                 WHERE status IN ('angefragt', 'bestätigt') ORDER BY start_date ASC, id DESC"
            );
            jsonResponse(['reservations' => array_map('mapAvailability', $statement->fetchAll())]);
        }

        if (($user['role'] ?? '') === 'admin') {
            $select = 'SELECT id, user_id, machine_id, machine_name, customer_name, email, phone, start_date, end_date, handover, notes, total_price, days, payment_preference, payment_status, confirmed_at, paid_at, admin_note, status, created_at FROM reservations';
            $where = [];
            $params = [];

            $detailId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($detailId !== false && $detailId !== null) {
                $where[] = 'id = :detail_id';
                $params['detail_id'] = $detailId;
            }

            $customer = trim((string) ($_GET['customer'] ?? ''));
            if ($customer !== '') {
                $where[] = '(customer_name LIKE :customer OR email LIKE :customer OR phone LIKE :customer)';
                $params['customer'] = '%' . mb_substr($customer, 0, 120) . '%';
            }

            $machineId = trim((string) ($_GET['machineId'] ?? ''));
            if ($machineId !== '') {
                $where[] = 'machine_id = :machine_id_filter';
                $params['machine_id_filter'] = mb_substr($machineId, 0, 120);
            }

            $paymentPreference = trim((string) ($_GET['paymentPreference'] ?? ''));
            if ($paymentPreference !== '') {
                $where[] = 'payment_preference = :payment_preference_filter';
                $params['payment_preference_filter'] = mb_substr($paymentPreference, 0, 40);
            }

            $status = trim((string) ($_GET['status'] ?? ''));
            if ($status !== '') {
                if (!in_array($status, RESERVATION_STATUSES, true)) {
                    jsonResponse(['error' => 'Ungültiger Reservationsstatus.'], 422);
                }
                $where[] = 'status = :status_filter';
                $params['status_filter'] = $status;
            }

            $startDateFilter = trim((string) ($_GET['startDate'] ?? ''));
            $endDateFilter = trim((string) ($_GET['endDate'] ?? ''));
            if ($startDateFilter !== '' && !validIsoDate($startDateFilter)) {
                jsonResponse(['error' => 'Ungültiges Startdatum.'], 422);
            }
            if ($endDateFilter !== '' && !validIsoDate($endDateFilter)) {
                jsonResponse(['error' => 'Ungültiges Enddatum.'], 422);
            }
            if ($startDateFilter !== '' && $endDateFilter !== '' && $endDateFilter < $startDateFilter) {
                jsonResponse(['error' => 'Das Enddatum darf nicht vor dem Startdatum liegen.'], 422);
            }
            if ($startDateFilter !== '') {
                $where[] = 'end_date >= :filter_start_date';
                $params['filter_start_date'] = $startDateFilter;
            }
            if ($endDateFilter !== '') {
                $where[] = 'start_date <= :filter_end_date';
                $params['filter_end_date'] = $endDateFilter;
            }

            $sortMap = [
                'customer' => 'customer_name',
                'machine' => 'machine_name',
                'startDate' => 'start_date',
                'endDate' => 'end_date',
                'totalPrice' => 'total_price',
                'status' => 'status',
                'paymentStatus' => 'payment_status',
                'createdAt' => 'created_at',
            ];
            $sort = $sortMap[(string) ($_GET['sort'] ?? 'createdAt')] ?? $sortMap['createdAt'];
            $direction = strtolower((string) ($_GET['direction'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
            $sql = $select;
            if ($where !== []) $sql .= ' WHERE ' . implode(' AND ', $where);
            $sql .= " ORDER BY {$sort} {$direction}, id DESC";
            $statement = $database->prepare($sql);
            $statement->execute($params);
            $rows = $statement->fetchAll();
            if ($detailId !== false && $detailId !== null) {
                if ($rows === []) jsonResponse(['error' => 'Reservation nicht gefunden.'], 404);
                jsonResponse(['reservation' => mapReservation($rows[0])]);
            }
            jsonResponse(['reservations' => array_map('mapReservation', $rows)]);
        } else {
            $statement = $database->prepare(
                'SELECT id, user_id, machine_id, machine_name, customer_name, email, phone, start_date, end_date, handover, notes, total_price, days, payment_preference, payment_status, confirmed_at, paid_at, admin_note, status, created_at
                 FROM reservations WHERE user_id = :user_id OR (user_id IS NULL AND email = :email)
                 ORDER BY created_at DESC, id DESC'
            );
            $statement->execute(['user_id' => (int) $user['id'], 'email' => $user['email']]);
        }
        jsonResponse(['reservations' => array_map('mapReservation', $statement->fetchAll())]);
    }

    if ($method === 'POST') {
        $loggedInUser = currentUser();
        if ($loggedInUser !== null && ($loggedInUser['role'] ?? '') === 'guest') {
            jsonResponse(['error' => 'Der Vorschauzugang kann keine Reservationen erstellen.'], 403);
        }
        $input = requestJson();
        foreach (['machineId', 'machineName', 'customerName', 'email', 'phone', 'startDate', 'endDate'] as $field) {
            if (cleanString($input, $field) === '') {
                jsonResponse(['error' => 'Bitte alle Pflichtfelder ausfüllen.'], 400);
            }
        }

        $email = strtolower(cleanString($input, 'email'));
        $startDate = cleanString($input, 'startDate');
        $endDate = cleanString($input, 'endDate');
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            jsonResponse(['error' => 'Bitte eine gültige E-Mail-Adresse eingeben.'], 422);
        }
        if (!validIsoDate($startDate) || !validIsoDate($endDate)) {
            jsonResponse(['error' => 'Bitte gültige Datumswerte verwenden.'], 422);
        }
        if ($endDate < $startDate) {
            jsonResponse(['error' => 'Das Enddatum darf nicht vor dem Startdatum liegen.'], 422);
        }

        $statement = $database->prepare(
            'INSERT INTO reservations
             (user_id, machine_id, machine_name, customer_name, email, phone, start_date, end_date, handover, notes, total_price, days, payment_preference, payment_status, status)
             VALUES
             (:user_id, :machine_id, :machine_name, :customer_name, :email, :phone, :start_date, :end_date, :handover, :notes, :total_price, :days, :payment_preference, :payment_status, :status)'
        );
        $statement->execute([
            'user_id' => $loggedInUser['id'] ?? null,
            'machine_id' => cleanString($input, 'machineId'),
            'machine_name' => cleanString($input, 'machineName'),
            'customer_name' => cleanString($input, 'customerName'),
            'email' => $email,
            'phone' => cleanString($input, 'phone'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'handover' => cleanString($input, 'handover') === 'lieferung' ? 'lieferung' : 'abholung',
            'notes' => cleanString($input, 'notes'),
            'total_price' => number_format(max(0, (float) ($input['totalPrice'] ?? 0)), 2, '.', ''),
            'days' => max(1, (int) ($input['days'] ?? 1)),
            'payment_preference' => cleanString($input, 'paymentPreference') ?: cleanString($input, 'paymentMethod'),
            'payment_status' => 'offen',
            'status' => 'angefragt',
        ]);

        $id = (string) $database->lastInsertId();
        $statement = $database->prepare(
            'SELECT id, user_id, machine_id, machine_name, customer_name, email, phone, start_date, end_date, handover, notes, total_price, days, payment_preference, payment_status, confirmed_at, paid_at, admin_note, status, created_at
             FROM reservations WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        jsonResponse(['success' => true, 'reservation' => mapReservation($statement->fetch())], 201);
    }

    if ($method === 'PATCH') {
        requireAdmin();
        $input = requestJson();
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $status = cleanString($input, 'status');
        $paymentStatus = cleanString($input, 'paymentStatus');
        $adminNote = cleanString($input, 'adminNote');
        if ($id === false) {
            jsonResponse(['error' => 'Ungültige Reservations-ID.'], 400);
        }
        if (!in_array($status, RESERVATION_STATUSES, true)) {
            jsonResponse(['error' => 'Ungültiger Reservationsstatus.'], 422);
        }

        if ($paymentStatus !== '' && !in_array($paymentStatus, ['offen', 'teilweise', 'bezahlt', 'zurueckerstattet'], true)) {
            jsonResponse(['error' => 'Ungültiger Zahlungsstatus.'], 422);
        }

        $database->beginTransaction();
        $beforeStatement = $database->prepare('SELECT status, payment_status, admin_note, confirmed_at, paid_at FROM reservations WHERE id = :id FOR UPDATE');
        $beforeStatement->execute(['id' => $id]);
        $before = $beforeStatement->fetch();
        if (!$before) { $database->rollBack(); jsonResponse(['error' => 'Reservation nicht gefunden.'], 404); }
        // Once an invoice exists, payment state is owned by its payment ledger.
        require_once __DIR__ . '/../includes/finance.php';
        if (financeReady($database)) {
            $managed = $database->prepare('SELECT id FROM fin_invoices WHERE source_key = ?');
            $managed->execute(['reservation:' . $id]);
            if ($managed->fetchColumn()) {
                if ($paymentStatus !== '' && $paymentStatus !== $before['payment_status']) {
                    $database->rollBack();
                    jsonResponse(['error' => 'Zahlungen zu dieser Reservation bitte unter Finanzen & Belege erfassen.'], 409);
                }
                $paymentStatus = '';
            }
        }

        $fields = ['status = :status'];
        $params = ['status' => $status, 'id' => $id];
        if ($paymentStatus !== '') {
            $fields[] = 'payment_status = :payment_status';
            $params['payment_status'] = $paymentStatus;
            $fields[] = $paymentStatus === 'bezahlt' ? 'paid_at = COALESCE(paid_at, NOW())' : 'paid_at = NULL';
        }
        if ($adminNote !== '') {
            $fields[] = 'admin_note = :admin_note';
            $params['admin_note'] = $adminNote;
        }
        if ($status === 'bestätigt') $fields[] = 'confirmed_at = COALESCE(confirmed_at, NOW())';
        $statement = $database->prepare('UPDATE reservations SET ' . implode(', ', $fields) . ' WHERE id = :id');
        $statement->execute($params);
        if ($statement->rowCount() === 0) {
            $exists = $database->prepare('SELECT COUNT(*) FROM reservations WHERE id = :id');
            $exists->execute(['id' => $id]);
            if ((int) $exists->fetchColumn() === 0) {
                jsonResponse(['error' => 'Reservation nicht gefunden.'], 404);
            }
        }
        $afterStatement = $database->prepare('SELECT status, payment_status, admin_note, confirmed_at, paid_at FROM reservations WHERE id = :id');
        $afterStatement->execute(['id' => $id]);
        $after = $afterStatement->fetch();
        $database->commit();
        auditLogSafe('update', 'reservations', 'reservation', $id, 'Reservation und Zahlungsstatus geändert', $before, $after);
        jsonResponse(['success' => true]);
    }

    jsonResponse(['error' => 'Methode nicht erlaubt.'], 405);
} catch (PDOException $exception) {
    if (isset($database) && $database->inTransaction()) $database->rollBack();
    error_log('Rent-A-Bau database error: ' . $exception->getMessage());
    jsonResponse(['error' => 'Die Datenbank ist momentan nicht erreichbar.'], 503);
} catch (Throwable $exception) {
    if (isset($database) && $database->inTransaction()) $database->rollBack();
    error_log('Rent-A-Bau API error: ' . $exception->getMessage());
    jsonResponse(['error' => 'Die Anfrage konnte nicht verarbeitet werden.'], 500);
}
