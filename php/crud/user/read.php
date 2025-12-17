<?php
session_start();
require_once '../../config.php';
require_once '../../koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $db = new Database();
    $db->query('SELECT id, nama, email, foto_profil, telepon, alamat, created_at, last_login FROM users WHERE id = :id');
    $db->bind(':id', $user_id);
    $user = $db->single();
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'data' => $user
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User tidak ditemukan'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>