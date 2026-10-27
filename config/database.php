<?php
declare(strict_types=1);

function rentabauIsLocalEnvironment(): bool
{
    if (PHP_SAPI === 'cli') return true;
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost'));
    $host = preg_replace('/:\d+$/', '', $host) ?: $host;
    return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
        || str_ends_with($host, '.local')
        || str_ends_with($host, '.test')
        || str_ends_with($host, '.localhost');
}

function rentabauEnvironmentValue(string $name, mixed $fallback = null): mixed
{
    $value = getenv($name);
    return $value === false || $value === '' ? $fallback : $value;
}

function databaseConnection(): PDO
{
    static $connection = null;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $hoststarConfig = [];
    $hoststarConfigFile = __DIR__ . '/database.hoststar.php';
    if (is_file($hoststarConfigFile)) {
        $hoststarConfig = require $hoststarConfigFile;
        if (!is_array($hoststarConfig)) $hoststarConfig = [];
    }

    $isLocal = rentabauIsLocalEnvironment();
    $host = (string) rentabauEnvironmentValue('RENTABAU_DB_HOST', $isLocal ? '127.0.0.1' : ($hoststarConfig['host'] ?? null));
    $port = (int) rentabauEnvironmentValue('RENTABAU_DB_PORT', $isLocal ? 3306 : ($hoststarConfig['port'] ?? 3306));
    $database = (string) rentabauEnvironmentValue('RENTABAU_DB_NAME', $isLocal ? 'rentabau' : ($hoststarConfig['database'] ?? null));
    $username = (string) rentabauEnvironmentValue('RENTABAU_DB_USER', $isLocal ? 'root' : ($hoststarConfig['username'] ?? null));
    $password = rentabauEnvironmentValue('RENTABAU_DB_PASSWORD', $isLocal ? '' : ($hoststarConfig['password'] ?? null));

    if ($host === '' || $database === '' || $username === '' || $password === null) {
        error_log('Rent A Bau: Hoststar-Datenbankdaten fehlen. Bitte config/database.hoststar.php oder RENTABAU_DB_* setzen.');
        throw new RuntimeException('Die Online-Datenbank ist noch nicht konfiguriert.');
    }

    $connection = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        (string) $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    return $connection;
}
