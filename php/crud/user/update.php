<?php
require_once '../../config.php';
require_once '../../koneksi.php';
require_once '../../security/sanitasi.php';
require_once '../../session/start-session.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

// Validasi session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login']);
    exit();
}

try {
    $db = new Database();
    $user_id = $_SESSION['user_id'];

    // Ambil data lama user
    $db->query('SELECT foto_profil, nama FROM users WHERE id = :user_id');
    $db->bind(':user_id', $user_id);
    $oldUser = $db->single();

    if (!$oldUser) {
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
        exit();
    }

    // --- Upload Foto Profil ---
    $foto_profil = $oldUser['foto_profil'];
    $isPhotoUpdate = false;
    
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        // Folder uploads di ROOT - sesuaikan path
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/profil/';
        
        // Alternatif jika DOCUMENT_ROOT tidak bekerja
        // $uploadDir = dirname(dirname(dirname(__DIR__))) . '/uploads/profil/';
        
        // Pastikan direktori upload ada
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create directory: " . $uploadDir);
                echo json_encode(['success' => false, 'message' => 'Gagal membuat direktori upload: ' . $uploadDir]);
                exit();
            }
        }

        // Cek jika direktori writable
        if (!is_writable($uploadDir)) {
            error_log("Directory not writable: " . $uploadDir);
            echo json_encode(['success' => false, 'message' => 'Direktori upload tidak dapat ditulisi. Periksa permission folder.']);
            exit();
        }

        $fileExtension = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.']);
            exit();
        }

        if ($_FILES['foto_profil']['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar (maks. 2MB).']);
            exit();
        }

        // Validasi file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['foto_profil']['tmp_name']);
        finfo_close($finfo);
        
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($mime_type, $allowed_mime_types)) {
            echo json_encode(['success' => false, 'message' => 'Tipe file tidak valid.']);
            exit();
        }

        // Generate unique filename - hanya angka
        $filename = $user_id . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $uploadPath)) {
            // Gunakan path relatif untuk akses web - dari ROOT
            $foto_profil = '/uploads/profil/' . $filename;
            $isPhotoUpdate = true;

            // Hapus foto lama (selain default)
            if (!empty($oldUser['foto_profil']) && 
                $oldUser['foto_profil'] !== '/assets/icons/default-avatar.png' &&
                $oldUser['foto_profil'] !== '' &&
                file_exists($_SERVER['DOCUMENT_ROOT'] . $oldUser['foto_profil'])) {
                @unlink($_SERVER['DOCUMENT_ROOT'] . $oldUser['foto_profil']);
            }
            
            error_log("Profile photo uploaded successfully: " . $foto_profil . " to " . $uploadPath);
        } else {
            $uploadError = $_FILES['foto_profil']['error'];
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE)',
                UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
                UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension PHP'
            ];
            
            $errorMessage = $errorMessages[$uploadError] ?? 'Unknown upload error: ' . $uploadError;
            error_log("Failed to move uploaded file to: " . $uploadPath . " - Error: " . $errorMessage);
            
            echo json_encode(['success' => false, 'message' => 'Gagal mengunggah foto profil. Error: ' . $errorMessage]);
            exit();
        }
    }

    // Jika hanya upload foto tanpa data form lainnya
    if ($isPhotoUpdate && empty($_POST['nama']) && empty($_POST['email']) && empty($_POST['telepon']) && empty($_POST['alamat'])) {
        $db->query('UPDATE users SET foto_profil = :foto_profil, updated_at = NOW() WHERE id = :user_id');
        $db->bind(':foto_profil', $foto_profil);
        $db->bind(':user_id', $user_id);
        
        if ($db->execute()) {
            // Update session
            $_SESSION['user_foto'] = $foto_profil;
            
            // Ambil data user terbaru untuk response
            $db->query('SELECT nama, email, telepon, alamat, foto_profil FROM users WHERE id = :user_id');
            $db->bind(':user_id', $user_id);
            $updatedUser = $db->single();
            
            echo json_encode([
                'success' => true,
                'message' => 'Foto profil berhasil diubah.',
                'user' => $updatedUser
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui foto profil di database.']);
        }
        exit();
    }

    // --- Data input umum ---
    $nama = sanitizeInput($_POST['nama'] ?? '');
    $email = sanitizeEmail($_POST['email'] ?? '');
    $telepon = sanitizeInput($_POST['telepon'] ?? '');
    $alamat = sanitizeInput($_POST['alamat'] ?? '');

    // --- Validasi data umum ---
    if (empty($nama) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Nama dan email wajib diisi.']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
        exit();
    }

    // Cek duplikasi email
    $db->query('SELECT id FROM users WHERE email = :email AND id != :user_id');
    $db->bind(':email', $email);
    $db->bind(':user_id', $user_id);
    $existingUser = $db->single();

    if ($existingUser) {
        echo json_encode(['success' => false, 'message' => 'Email sudah digunakan oleh user lain.']);
        exit();
    }

    // --- Build Query Update ---
    $updateFields = [
        'nama = :nama',
        'email = :email', 
        'telepon = :telepon',
        'alamat = :alamat',
        'foto_profil = :foto_profil',
        'updated_at = NOW()'
    ];

    $params = [
        ':user_id' => $user_id,
        ':nama' => $nama,
        ':email' => $email,
        ':telepon' => $telepon,
        ':alamat' => $alamat,
        ':foto_profil' => $foto_profil
    ];

    $query = 'UPDATE users SET ' . implode(', ', $updateFields) . ' WHERE id = :user_id';

    $db->query($query);
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }

    if ($db->execute()) {
        // Update session
        $_SESSION['user_nama'] = $nama;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_telepon'] = $telepon;
        $_SESSION['user_alamat'] = $alamat;
        $_SESSION['user_foto'] = $foto_profil;

        // Ambil data user terbaru untuk response
        $db->query('SELECT nama, email, telepon, alamat, foto_profil FROM users WHERE id = :user_id');
        $db->bind(':user_id', $user_id);
        $updatedUser = $db->single();

        echo json_encode([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'user' => $updatedUser
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data.']);
    }

} catch (Exception $e) {
    error_log("User update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
}
?>