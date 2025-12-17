<?php
session_start();
require_once '../../php/config.php';
require_once '../../php/koneksi.php';

// Cek apakah sudah ada superadmin
try {
    $db = new Database();
    $db->query("SELECT COUNT(*) as total FROM admins WHERE level = 'superadmin'");
    $result = $db->single();
    
    if ($result['total'] > 0) {
        // Jika sudah ada superadmin, redirect ke login di admin/auth
        echo "<script>
            alert('Superadmin sudah terdaftar. Silakan login!');
            window.location.href = '../../admin/auth/login.php';
        </script>";
        exit();
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Proses registrasi jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        try {
            $db->beginTransaction();
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert superadmin
            $db->query("INSERT INTO admins (nama, email, password, level, status) 
                       VALUES (:nama, :email, :password, 'superadmin', 'aktif')");
            $db->bind(':nama', $nama);
            $db->bind(':email', $email);
            $db->bind(':password', $hashed_password);
            $db->execute();
            
            $db->endTransaction();
            
            $_SESSION['success'] = "Superadmin berhasil didaftarkan! Silakan login.";
            header('Location: ../../admin/auth/login.php');
            exit();
            
        } catch (Exception $e) {
            $db->cancelTransaction();
            $error = "Gagal mendaftarkan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Superadmin - Finansialku</title>
    <link rel="icon" type="image/png" href="../../assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../css/register.css" rel="stylesheet">
</head>
<body class="register-body">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <i class="fas fa-crown fa-3x mb-3"></i>
                        <h3>Registrasi Superadmin</h3>
                        <p class="mb-0">Setup awal sistem admin</p>
                    </div>
                    <div class="card-body p-5">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" name="nama" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" name="password" minlength="8" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-user-plus me-2"></i> Daftarkan Superadmin
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="../../admin/auth/login.php" class="text-decoration-none">
                                <i class="fas fa-sign-in-alt me-1"></i> Sudah punya akun? Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>