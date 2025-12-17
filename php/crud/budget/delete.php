<?php
require_once '../../middleware/auth.php';
// Auth middleware sudah memulai session dan mengecek authentication

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "ID budget tidak valid.";
    header('Location: read.php');
    exit();
}

$budget_id = $_GET['id'];
$db = new Database();

try {
    // Cek kepemilikan budget sebelum menghapus
    $db->query('SELECT id FROM budget WHERE id = :id AND user_id = :user_id');
    $db->bind(':id', $budget_id);
    $db->bind(':user_id', $user_id);
    $budget = $db->single();
    
    if (!$budget) {
        $_SESSION['error_message'] = "Budget tidak ditemukan atau tidak memiliki akses.";
        header('Location: read.php');
        exit();
    }
    
    // Hapus budget
    $db->query('DELETE FROM budget WHERE id = :id AND user_id = :user_id');
    $db->bind(':id', $budget_id);
    $db->bind(':user_id', $user_id);
    
    if ($db->execute()) {
        $_SESSION['success_message'] = "Budget berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus budget.";
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
}

header('Location: read.php');
exit();
?>