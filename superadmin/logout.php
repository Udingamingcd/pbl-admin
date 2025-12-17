<?php
session_start();

/* HAPUS SEMUA SESSION ADMIN */
unset($_SESSION['admin_id']);
unset($_SESSION['admin_nama']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_level']);

/* HAPUS SEMUA SESSION LAIN (AMAN) */
session_unset();
session_destroy();

/* REDIRECT KE LOGIN ADMIN */
header('Location: /admin/auth/login.php');
exit;
