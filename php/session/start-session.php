<?php
/**
 * Start session dengan pengaturan keamanan yang lebih baik
 */

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 7200); // 2 hours

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically for security (kurangi frekuensi)
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 3600) { // 1 hour instead of 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Set session timeout (2 hours instead of 1 hour)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 7200)) {
    // Last request was more than 2 hours ago
    session_unset();
    session_destroy();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Prevent session fixation
if (!isset($_SESSION['initiated'])) {
    $_SESSION['initiated'] = true;
}

// Initialize session variables if not set
if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = false;
}
?>