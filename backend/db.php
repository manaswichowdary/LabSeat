<?php
declare(strict_types=1);

/**
 * Load config.php from this same folder.
 *
 * @return array{DB_HOST: string, DB_PORT: string, DB_NAME: string, DB_USER: string, DB_PASS: string}
 */
function labseat_load_config(): array
{
    $path = __DIR__ . '/config.php';
    if (! is_readable($path)) {
        throw new RuntimeException(
            'Missing config.php. Copy backend/config.example.php to backend/config.php and fill in DB_USER and DB_PASS.'
        );
    }

    /** @var array<string, string> $cfg */
    $cfg = require $path;

    foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $key) {
        if (! array_key_exists($key, $cfg)) {
            throw new RuntimeException("config.php must define the key \"{$key}\" (see config.example.php).");
        }
    }

    return $cfg;
}

/**
 * Open one shared PostgreSQL connection (PDO).
 *
 * @throws PDOException if PostgreSQL rejects the login or the server is down
 */
function labseat_pdo(): PDO
{
    $cfg = labseat_load_config();

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $cfg['DB_HOST'],
        $cfg['DB_PORT'],
        $cfg['DB_NAME']
    );

    $pdo = new PDO($dsn, $cfg['DB_USER'], $cfg['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

try {
    $pdo = labseat_pdo();
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed. Check config.php and that PostgreSQL is running.',
    ]);
    exit;
}
