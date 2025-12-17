<?php
// FILE: ajax/ajax-login.php
ini_set('display_errors', 0);
error_reporting(0);

session_start();
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
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validation
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email dan password wajib diisi']);
        exit();
    }
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        exit();
    }
    
    // Check if user exists - SESUAI DENGAN QUERY FILE ASLI
    $db->query('SELECT id, nama, email, password, foto_profil FROM users WHERE email = :email');
    $db->bind(':email', $email);
    $user = $db->single();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email atau Password salah']);
        exit();
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Email atau Password salah']);
        exit();
    }
    
    // Update last login (FITUR TAMBAHAN)
    $db->query('UPDATE users SET last_login = NOW() WHERE id = :id');
    $db->bind(':id', $user['id']);
    $db->execute();
    
    // Set session - SESUAI DENGAN FILE ASLI
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_nama'] = $user['nama'];
    $_SESSION['user_foto'] = $user['foto_profil'] ?? null;
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Set remember me cookie if requested (FITUR TAMBAHAN)
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store token in database
        $db->query('UPDATE users SET remember_token = :token WHERE id = :id');
        $db->bind(':token', $token);
        $db->bind(':id', $user['id']);
        $db->execute();
        
        setcookie('remember_token', $token, $expiry, '/', '', true, true);
        setcookie('user_id', $user['id'], $expiry, '/', '', true, true);
    }
    
    // RESPONSE SESUAI FILE ASLI
    echo json_encode([
        'success' => true, 
        'message' => 'Login berhasil!',
        'redirect' => 'dashboard.php'  // Sesuaikan dengan struktur folder
    ]);
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem.']);
}
?>