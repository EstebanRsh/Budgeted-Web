<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/env.php';

session_start();

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$baseUrl = rtrim($scriptDir, '/');
if ($baseUrl === '' || $baseUrl === '/') {
    $baseUrl = '/';
} else {
    $baseUrl .= '/';
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $baseUrl);
}

date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/app/helpers/auth.php';
require_once APP_ROOT . '/app/helpers/pagination.php';
require_once APP_ROOT . '/app/helpers/flash.php';
require_once APP_ROOT . '/app/helpers/validation.php';
require_once APP_ROOT . '/app/helpers/audit.php';
