<?php
session_start();
require_once '../php/config.php';
require_once '../php/koneksi.php';
require_once '../php/security/sanitasi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = new Database();
    
    // Debug log
    error_log("Registration attempt started");
    
    // Sanitize input data
    $nama = sanitizeInput($_POST['nama'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telepon = sanitizeInput($_POST['telepon'] ?? '');
    $alamat = sanitizeInput($_POST['alamat'] ?? '');
    $agree_terms = isset($_POST['agree_terms']) && $_POST['agree_terms'] === 'on';

    // Validation
    $errors = [];
    
    if (empty($nama) || strlen($nama) < 2) {
        $errors[] = 'Nama lengkap minimal 2 karakter';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    }
    
    // Hanya validasi panjang password, tidak validasi strength
    if (empty($password) || strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Password dan konfirmasi password tidak cocok';
    }
    
    if (!$agree_terms) {
        $errors[] = 'Anda harus menyetujui syarat dan ketentuan';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit();
    }
    
    // Check if email already exists
    $db->query('SELECT id FROM users WHERE email = :email');
    $db->bind(':email', $email);
    $existingUser = $db->single();
    
    if ($existingUser) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        exit();
    }
    
    // Handle file upload
    $foto_profil = null;
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/profil/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.']);
            exit();
        }
        
        if ($_FILES['foto_profil']['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar (maks. 2MB)']);
            exit();
        }
        
        $filename = uniqid() . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $uploadPath)) {
            $foto_profil = 'uploads/profil/' . $filename;
        } else {
            error_log("File upload failed: " . $_FILES['foto_profil']['error']);
        }
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate verification token
    $verification_token = bin2hex(random_bytes(32));
    
    // Insert user into database
    $db->query('INSERT INTO users (nama, email, password, foto_profil, telepon, alamat, verification_token) 
                VALUES (:nama, :email, :password, :foto_profil, :telepon, :alamat, :verification_token)');
    
    $db->bind(':nama', $nama);
    $db->bind(':email', $email);
    $db->bind(':password', $hashedPassword);
    $db->bind(':foto_profil', $foto_profil);
    $db->bind(':telepon', $telepon);
    $db->bind(':alamat', $alamat);
    $db->bind(':verification_token', $verification_token);
    
    if ($db->execute()) {
        $userId = $db->lastInsertId();
        
        error_log("User registered successfully: $email (ID: $userId)");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Registrasi berhasil! Silakan masuk.',
            'user_id' => $userId
        ]);
    } else {
        $errorInfo = $db->getError();
        error_log("Database error: " . print_r($errorInfo, true));
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data: ' . $errorInfo[2]]);
    }
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
}
?>