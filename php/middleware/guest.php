<?php
/**
 * Middleware untuk halaman yang hanya bisa diakses oleh guest (belum login)
 */

session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit();
}
?>