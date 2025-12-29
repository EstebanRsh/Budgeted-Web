<?php
declare(strict_types=1);

function loadEnv(): void
{
    static $loaded = false;
    if ($loaded) return;
    
    $envFile = dirname(__DIR__) . '/.env';
    if (!file_exists($envFile)) return;
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) return;
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }
        
        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
    $loaded = true;
}

function env(string $key, $default = null)
{
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

function envBool(string $key, bool $default = false): bool
{
    $value = getenv($key);
    if ($value === false) return $default;
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}

function envInt(string $key, int $default = 0): int
{
    $value = getenv($key);
    if ($value === false) return $default;
    return (int) $value;
}

loadEnv();
