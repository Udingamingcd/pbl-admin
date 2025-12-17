<?php
session_start();
require_once '../../config.php';
require_once '../../koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login']);
    exit();
}

$user_id = $_SESSION['user_id'];
$password = $_POST['password'] ?? '';

if (empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Password wajib diisi']);
    exit();
}

try {
    $db = new Database();
    
    // Verify password
    $db->query('SELECT password FROM users WHERE id = :id');
    $db->bind(':id', $user_id);
    $user = $db->single();
    
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Password salah']);
        exit();
    }
    
    // Delete user account (this will cascade delete related records due to foreign key constraints)
    $db->query('DELETE FROM users WHERE id = :id');
    $db->bind(':id', $user_id);
    $db->execute();
    
    // Destroy session
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Akun berhasil dihapus'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>