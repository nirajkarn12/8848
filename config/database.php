<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Pacific/auckland');

$dbhost = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'resinnep_ecommerceweb';
$dbuser = getenv('DB_USER') ?: 'root';
$dbpass = getenv('DB_PASS') ?: '';

$baseUrl = getenv('BASE_URL') ?: 'http://localhost/8848/';
define('BASE_URL', rtrim($baseUrl, '/') . '/');
define('ASSET_URL', BASE_URL . 'assets/');
define('UPLOAD_URL', ASSET_URL . 'uploads/');

define('SITE_NAME', '8848 Cleaning Service');

if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
    define('SMTP_USER', getenv('SMTP_USER') ?: 'nirajkarna66@gmail.com');
    define('SMTP_PASS', getenv('SMTP_PASS') ?: 'eptg ikjc lbbd yosq');
    define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 465));
    define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'nirajkarna66@gmail.com');
    define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: '8848 Cleaning Service');
    define('SMTP_REPLYTO_EMAIL', getenv('SMTP_REPLYTO_EMAIL') ?: 'nirajkarna66@gmail.com');
    define('SMTP_REPLYTO_NAME', getenv('SMTP_REPLYTO_NAME') ?: '8848 Cleaning Service');
}

try {
    $pdo = new PDO("mysql:host={$dbhost};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
