<?php
session_start();
require_once '../../php/config.php';
require_once '../../php/koneksi.php';

// Redirect jika sudah login
if (isset($_SESSION['admin_id'])) {
    header('Location: ../dashboard/index.php');
    exit();
}

// Cek apakah sudah ada superadmin
try {
    $db = new Database();
    $db->query("SELECT COUNT(*) as total FROM admins WHERE level = 'superadmin'");
    $result = $db->single();
    $superadmin_exists = ($result['total'] > 0);
} catch (Exception $e) {
    // Jika error, anggap sudah ada superadmin untuk keamanan
    $superadmin_exists = true;
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Finansialku</title>
    <link rel="icon" type="image/png" href="../../assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../css/login.css" rel="stylesheet">
    <style>
        .admin-login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
        }
        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .admin-header i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="login-body">
    <div class="container">
        <div class="admin-login-container">
            <div class="admin-header">
                <i class="fas fa-user-shield"></i>
                <h3 class="text-white">Admin Login</h3>
                <p class="text-muted">Masukkan kredensial admin Anda</p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="proses-login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Admin</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button type="button" class="btn btn-outline-secondary toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </button>

                <?php if (!$superadmin_exists): ?>
                <div class="text-center mb-3">
                    <a href="../../superadmin/auth/register.php" class="text-decoration-none" id="register-link">
                        <i class="fas fa-user-plus me-1"></i> Registrasi Superadmin
                    </a>
                </div>
                <?php endif; ?>

                <div class="text-center">
                    <a href="lupa-password.php" class="text-decoration-none">Lupa Password?</a>
                    <span class="mx-2">•</span>
                    <a href="../../index.php" class="text-decoration-none">Kembali ke User Login</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const passwordInput = this.parentNode.querySelector('input');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Cegah akses langsung ke register.php jika sudah ada superadmin
        <?php if ($superadmin_exists): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('register') || window.location.pathname.includes('register.php')) {
                alert('Akses ditolak! Superadmin sudah terdaftar. Silakan login.');
                window.location.href = 'login.php';
            }
            
            // Tambahkan event listener untuk mencegah klik pada link register jika masih ada
            const registerLink = document.getElementById('register-link');
            if (registerLink) {
                registerLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    alert('Akses ditolak! Superadmin sudah terdaftar. Silakan login.');
                    window.location.href = 'login.php';
                });
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>