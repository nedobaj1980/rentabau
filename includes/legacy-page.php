<?php
declare(strict_types=1);

if (!isset($legacyPage) || !is_string($legacyPage)) {
    http_response_code(500);
    exit('Keine Seitenvorlage angegeben.');
}
$legacyPath = realpath(__DIR__ . '/../' . $legacyPage);
$projectRoot = realpath(__DIR__ . '/..');
if ($legacyPath === false || $projectRoot === false || !str_starts_with($legacyPath, $projectRoot . DIRECTORY_SEPARATOR)) {
    http_response_code(500);
    exit('Ungültige Seitenvorlage.');
}
include $legacyPath;
