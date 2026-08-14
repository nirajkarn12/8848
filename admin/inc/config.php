<?php
// Error Reporting Turn On
ini_set('error_reporting', E_ALL);

// Setting up the time zone
date_default_timezone_set('Pacific/auckland');

// Host Name
$dbhost = getenv('DB_HOST') ?: 'localhost';

// Database Name
$dbname   = getenv('DB_NAME') ?: '8848';

// Database Username
$dbuser = getenv('DB_USER') ?: 'root';

// Database Password
$dbpass = getenv('DB_PASS') ?: '';

// Defining base url
define("BASE_URL", getenv('BASE_URL') ?: 'http://localhost/8848/');

// Getting Admin url
define("ADMIN_URL", BASE_URL . "admin" . "/");
// SMTP Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'nirajkarna66@gmail.com');
define('SMTP_PASS', 'eptg ikjc lbbd yosq');
define('SMTP_PORT', 465);

define('SMTP_FROM_EMAIL', 'nirajkarna66@gmail.com');
define('SMTP_FROM_NAME', '8848 Cleaning Service');

define('SMTP_REPLYTO_EMAIL', 'nirajkarna66@gmail.com');
define('SMTP_REPLYTO_NAME', '8848 Cleaning Service');

try {
	$pdo = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch( PDOException $exception ) {
	echo "Connection error :" . $exception->getMessage();
}