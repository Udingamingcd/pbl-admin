<?php
require_once '../php/config.php';
require_once '../php/koneksi.php';
require_once '../php/security/sanitasi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

try {
    $db = new Database();
    
    // Sanitize input data
    $email = sanitizeEmail($_POST['email'] ?? '');
    
    // Validation
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email wajib diisi']);
        exit();
    }
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        exit();
    }
    
    // Check if email exists
    $db->query('SELECT id, nama FROM users WHERE email = :email');
    $db->bind(':email', $email);
    $user = $db->single();
    
    if (!$user) {
        // For security, don't reveal if email exists or not
        echo json_encode([
            'success' => true, 
            'message' => 'Jika email terdaftar, link reset password akan dikirim'
        ]);
        exit();
    }
    
    // Generate reset token
    $reset_token = bin2hex(random_bytes(32));
    $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Update user with reset token
    $db->query('UPDATE users SET reset_token = :reset_token, reset_expires = :reset_expires WHERE id = :id');
    $db->bind(':reset_token', $reset_token);
    $db->bind(':reset_expires', $reset_expires);
    $db->bind(':id', $user['id']);
    
    if ($db->execute()) {
        // In a real application, you would send an email here
        // For demo purposes, we'll just log the token
        error_log("Reset token for {$email}: {$reset_token}");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Link reset password telah dikirim ke email Anda'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat memproses permintaan']);
    }
    
} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.']);
}
?>