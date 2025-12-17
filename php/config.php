<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'finansialku');

// Konfigurasi Aplikasi
define('APP_NAME', 'Finansialku');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/finansialku');

// URL dasar untuk routing
define('BASE_URL', 'http://localhost/finansialku/');

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting
error_reporting(0);
ini_set('display_errors', 0);
?>
