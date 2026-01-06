<?php
session_start();
require_once '../../php/middleware/admin_auth.php';
AdminAuth::guest();
require_once '../../php/koneksi.php';

$db = new Database();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = "Email harus diisi";
    } else {
        // Cek apakah email terdaftar sebagai superadmin
        $db->query('SELECT id, nama, email FROM admins WHERE email = :email AND level = "superadmin" AND status = "aktif"');
        $db->bind(':email', $email);
        $admin = $db->single();
        
        if ($admin) {
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Simpan token ke database
            $db->query('UPDATE admins SET reset_token = :token, reset_expires = :expires WHERE id = :id');
            $db->bind(':token', $reset_token);
            $db->bind(':expires', $reset_expires);
            $db->bind(':id', $admin['id']);
            
            if ($db->execute()) {
                // Simpan dalam session untuk verifikasi
                $_SESSION['reset_admin_id'] = $admin['id'];
                $_SESSION['reset_token'] = $reset_token;
                
                $success = "Link reset password telah dikirim ke email Anda. Silakan cek email Anda untuk instruksi lebih lanjut.";
                
                // Catatan: Di sini Anda harus mengirim email dengan link reset
                // Untuk demo, kita hanya akan menampilkan token
                echo "<div class='alert alert-info'>
                        <strong>DEMO MODE:</strong> Token reset: $reset_token<br>
                        <small>Dalam aplikasi nyata, token ini akan dikirim via email</small>
                      </div>";
                
            } else {
                $error = "Gagal membuat reset token";
            }
        } else {
            $error = "Email tidak terdaftar sebagai superadmin atau akun tidak aktif";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Super Admin Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .forgot-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .forgot-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
        }
        .forgot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        .forgot-body {
            padding: 40px;
        }
        .steps-container {
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .step-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body class="forgot-container">
    <div class="forgot-card">
        <div class="forgot-header">
            <div style="width: 60px; height: 60px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <img src="../../assets/icons/Dompt.png" alt="Logo" width="40">
            </div>
            <h3>Reset Password Super Admin</h3>
            <p class="mb-0 opacity-75">Masukkan email superadmin untuk mereset password</p>
        </div>
        
        <div class="forgot-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="email" class="form-label">Email Super Admin</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="superadmin@finansialku.com" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                
                <div class="steps-container">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div>
                            <strong>Masukkan email superadmin</strong>
                            <small class="text-muted d-block">Email yang terdaftar di sistem</small>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div>
                            <strong>Verifikasi email</strong>
                            <small class="text-muted d-block">Sistem akan mengirimkan link reset ke email Anda</small>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div>
                            <strong>Buat password baru</strong>
                            <small class="text-muted d-block">Password minimal 8 karakter</small>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-key me-2"></i>Kirim Link Reset Password
                    </button>
                    <a href="login.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Login
                    </a>
                </div>
            </form>
            
            <div class="text-center mt-4 pt-3 border-top">
                <small class="text-muted">
                    &copy; <?php echo date('Y'); ?> Finansialku - Sistem Super Admin
                    <br>
                    <a href="../../index.php" class="text-decoration-none">Login sebagai User</a> • 
                    <a href="../../admin/auth/login.php" class="text-decoration-none">Login sebagai Admin</a>
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            
            if (!email) {
                e.preventDefault();
                alert('Email harus diisi!');
                return false;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Format email tidak valid!');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>