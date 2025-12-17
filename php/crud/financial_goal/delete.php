<?php
define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/middleware/auth.php';
require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/koneksi.php';

if (!isset($_GET['id'])) {
    header('Location: read.php');
    exit();
}

try {
    $db = new Database();
    $db->query('DELETE FROM financial_goal WHERE id = :id AND user_id = :user_id');
    $db->bind(':id', $_GET['id']);
    $db->bind(':user_id', $_SESSION['user_id']);
    
    if ($db->execute()) {
        $_SESSION['success_message'] = 'Target finansial berhasil dihapus!';
    } else {
        $_SESSION['error_message'] = 'Gagal menghapus target finansial';
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Terjadi kesalahan: ' . $e->getMessage();
}

header('Location: read.php');
exit();