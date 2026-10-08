<?php

declare(strict_types=1);

function initializeSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'lifetime' => 3600,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function getDbConfig(): array
{
    $configPath = __DIR__ . '/config/config.php';
    if (file_exists($configPath)) {
        $config = require $configPath;
        if (is_array($config)) {
            foreach ($config as $key => $value) {
                if (is_string($key) && is_scalar($value)) {
                    $_ENV[$key] = (string) $value;
                    putenv($key . '=' . (string) $value);
                }
            }
        }
    }

    return [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'database' => getenv('DB_NAME') ?: 'cloud_database',
    ];
}

function getDbConnection(): mysqli
{
    $config = getDbConfig();
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);

    if ($mysqli->connect_errno) {
        throw new RuntimeException('Database connection failed. Please check configuration and try again.');
    }

    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}
