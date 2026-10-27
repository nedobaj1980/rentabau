<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

requireAdmin();

$uploadDir = __DIR__ . '/../brand/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadedPaths = [];
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    // Handle multiple files
    if (isset($_FILES['images'])) {
        $files = $_FILES['images'];
        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed, true) && ($files['size'][$i] ?? 0) <= 8 * 1024 * 1024) {
                        $filename = 'machine_' . time() . '_' . rand(100, 999) . '_' . $i . '.' . $ext;
                        $targetPath = $uploadDir . $filename;
                        if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                            $uploadedPaths[] = 'brand/' . $filename;
                        }
                    }
                }
            }
        }
    }

    // Handle single file (image or file)
    if (empty($uploadedPaths)) {
        $singleFile = isset($_FILES['image']) ? $_FILES['image'] : (isset($_FILES['file']) ? $_FILES['file'] : null);
        if ($singleFile && $singleFile['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($singleFile['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed, true) && ($singleFile['size'] ?? 0) <= 8 * 1024 * 1024) {
                $filename = 'machine_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($singleFile['tmp_name'], $targetPath)) {
                    $uploadedPaths[] = 'brand/' . $filename;
                }
            }
        }
    }

    if (!empty($uploadedPaths)) {
        auditLogSafe('create', 'media', 'image', null, 'Bilddateien im Adminbereich hochgeladen', null, ['files' => $uploadedPaths]);
        echo json_encode([
            'success' => true,
            'imagePath' => $uploadedPaths[0],
            'imagePaths' => $uploadedPaths
        ]);
        exit();
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Keine gültigen Bilddateien hochgeladen']);
        exit();
    }
}

http_response_code(405);
echo json_encode(['error' => 'Methode nicht erlaubt']);
