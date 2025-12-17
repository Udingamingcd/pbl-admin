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
$response = ['success' => false, 'message' => ''];

try {
    $db = new Database();
    
    // Handle profile picture upload
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/profil/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
        $fileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($fileExtension), $allowedTypes)) {
            throw new Exception('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
        }
        
        // Validate file size (max 2MB)
        if ($_FILES['foto_profil']['size'] > 2 * 1024 * 1024) {
            throw new Exception('Ukuran file terlalu besar. Maksimal 2MB.');
        }
        
        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $filePath)) {
            // Update database with new photo path
            $db->query('UPDATE users SET foto_profil = :foto_profil WHERE id = :id');
            $db->bind(':foto_profil', 'uploads/profil/' . $fileName);
            $db->bind(':id', $user_id);
            $db->execute();
            
            $_SESSION['user_foto'] = 'uploads/profil/' . $fileName;
            
            $response = [
                'success' => true,
                'message' => 'Foto profil berhasil diubah',
                'user' => ['foto_profil' => 'uploads/profil/' . $fileName]
            ];
        } else {
            throw new Exception('Gagal mengupload file.');
        }
    } else {
        // Handle regular profile update
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
        $telepon = $_POST['telepon'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        
        // Validate required fields
        if (empty($nama) || empty($email)) {
            throw new Exception('Nama dan email wajib diisi.');
        }
        
        // Check if email already exists (excluding current user)
        $db->query('SELECT id FROM users WHERE email = :email AND id != :id');
        $db->bind(':email', $email);
        $db->bind(':id', $user_id);
        $existingUser = $db->single();
        
        if ($existingUser) {
            throw new Exception('Email sudah digunakan oleh pengguna lain.');
        }
        
        // Update user data
        $db->query('UPDATE users SET nama = :nama, email = :email, telepon = :telepon, alamat = :alamat WHERE id = :id');
        $db->bind(':nama', $nama);
        $db->bind(':email', $email);
        $db->bind(':telepon', $telepon);
        $db->bind(':alamat', $alamat);
        $db->bind(':id', $user_id);
        $db->execute();
        
        // Update session
        $_SESSION['user_nama'] = $nama;
        $_SESSION['user_email'] = $email;
        
        $response = [
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'nama' => $nama,
                'email' => $email,
                'telepon' => $telepon,
                'alamat' => $alamat
            ]
        ];
    }
    
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
?>