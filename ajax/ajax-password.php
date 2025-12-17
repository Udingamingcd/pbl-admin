<?php
require_once '../php/config.php';
require_once '../php/koneksi.php';
require_once '../php/security/sanitasi.php';
require_once '../php/session/start-session.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

try {
    $db = new Database();
    $user_id = $_SESSION['user_id'];

    // Sanitize and validate input
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit();
    }

    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Password baru dan konfirmasi tidak cocok']);
        exit();
    }

    if (strlen($new_password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 8 karakter']);
        exit();
    }

    // Verify current password
    $db->query('SELECT password FROM users WHERE id = :id');
    $db->bind(':id', $user_id);
    $user = $db->single();

    if (!$user || !password_verify($current_password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Password saat ini salah']);
        exit();
    }

    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $db->query('UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id');
    $db->bind(':password', $hashed_password);
    $db->bind(':id', $user_id);

    if ($db->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Password berhasil diubah'
        ]);
    } else {
        throw new Exception('Gagal mengubah password');
    }

} catch (Exception $e) {
    error_log("Password change error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem.'
    ]);
}
?>