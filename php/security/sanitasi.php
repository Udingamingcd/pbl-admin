<?php
/**
 * Fungsi untuk sanitasi input data
 */

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

function sanitizeEmail($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

function sanitizeNumber($number) {
    return filter_var($number, FILTER_SANITIZE_NUMBER_INT);
}

function sanitizeFloat($number) {
    return filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

function sanitizeString($string) {
    return filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
}

function sanitizeURL($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

function preventXSS($data) {
    if (is_array($data)) {
        return array_map('preventXSS', $data);
    }
    
    // Remove potentially dangerous tags
    $data = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data);
    $data = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $data);
    $data = preg_replace('/<object\b[^>]*>(.*?)<\/object>/is', '', $data);
    $data = preg_replace('/<embed\b[^>]*>(.*?)<\/embed>/is', '', $data);
    
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function validatePasswordStrength($password) {
    $strength = 0;
    $messages = [];
    
    // Check length
    if (strlen($password) >= 8) {
        $strength++;
    } else {
        $messages[] = "Password minimal 8 karakter";
    }
    
    // Check for lowercase letters
    if (preg_match('/[a-z]/', $password)) {
        $strength++;
    } else {
        $messages[] = "Password harus mengandung huruf kecil";
    }
    
    // Check for uppercase letters
    if (preg_match('/[A-Z]/', $password)) {
        $strength++;
    } else {
        $messages[] = "Password harus mengandung huruf besar";
    }
    
    // Check for numbers
    if (preg_match('/[0-9]/', $password)) {
        $strength++;
    } else {
        $messages[] = "Password harus mengandung angka";
    }
    
    // Check for special characters
    if (preg_match('/[^a-zA-Z0-9]/', $password)) {
        $strength++;
    } else {
        $messages[] = "Password harus mengandung karakter spesial";
    }
    
    return [
        'strength' => $strength,
        'messages' => $messages,
        'is_strong' => $strength >= 4
    ];
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeFileName($filename) {
    // Remove path information
    $filename = basename($filename);
    
    // Replace spaces and special characters
    $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $filename);
    
    // Limit length
    if (strlen($filename) > 100) {
        $filename = substr($filename, 0, 100);
    }
    
    return $filename;
}

function validateFileUpload($file, $allowedTypes = [], $maxSize = 2097152) {
    $errors = [];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Terjadi kesalahan saat upload file";
        return ['success' => false, 'errors' => $errors];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        $errors[] = "Ukuran file terlalu besar. Maksimal: " . ($maxSize / 1024 / 1024) . "MB";
    }
    
    // Check file type
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
        $errors[] = "Tipe file tidak diizinkan. Gunakan: " . implode(', ', $allowedTypes);
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    
    if (isset($allowedMimeTypes[$fileType]) && $allowedMimeTypes[$fileType] !== $mimeType) {
        $errors[] = "Tipe MIME file tidak valid";
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors,
        'file_type' => $fileType,
        'mime_type' => $mimeType
    ];
}
?>