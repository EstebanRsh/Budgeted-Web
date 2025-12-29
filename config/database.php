<?php
declare(strict_types=1);

if (!function_exists('db')) {
    function db(): PDO
    {
        static $pdo = null;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        $config = [
            'host'    => env('DB_HOST', 'localhost'),
            'dbname'  => env('DB_NAME', 'presupuestos_app'),
            'user'    => env('DB_USER', 'root'),
            'pass'    => env('DB_PASS', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
        ];

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['dbname'],
            $config['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }

        return $pdo;
    }
}
