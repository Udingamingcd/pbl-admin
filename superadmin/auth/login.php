<?php
session_start();
require_once '../../php/middleware/admin_auth.php';

// Cek jika sudah login sebagai superadmin, redirect ke dashboard
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_level']) && $_SESSION['admin_level'] === 'superadmin') {
    header('Location: ../dashboard/index.php');
    exit();
}

require_once '../../php/koneksi.php';
$db = new Database();

$error = '';
$success = '';

// Proses form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validasi input
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi";
    } else {
        // Cari admin dengan email yang diberikan dan level superadmin
        $db->query('SELECT * FROM admins WHERE email = :email AND level = "superadmin" AND status = "aktif"');
        $db->bind(':email', $email);
        $admin = $db->single();
        
        if ($admin) {
            // Verifikasi password
            if (password_verify($password, $admin['password'])) {
                // Set session data
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nama'] = $admin['nama'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_level'] = $admin['level'];
                
                // Update last login time
                $db->query('UPDATE admins SET last_login = NOW() WHERE id = :id');
                $db->bind(':id', $admin['id']);
                $db->execute();
                
                // Set cookie untuk remember me jika dipilih
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 hari
                    
                    // Update remember token di database
                    $db->query('UPDATE admins SET remember_token = :token WHERE id = :id');
                    $db->bind(':token', $token);
                    $db->bind(':id', $admin['id']);
                    $db->execute();
                    
                    // Set cookie
                    setcookie('admin_remember', $token, $expiry, '/', '', false, true);
                }
                
                // Track session untuk mengetahui admin online
                $db->query('INSERT INTO admin_sessions (admin_id, session_id, ip_address, user_agent) 
                           VALUES (:admin_id, :session_id, :ip, :agent)');
                $db->bind(':admin_id', $admin['id']);
                $db->bind(':session_id', session_id());
                $db->bind(':ip', $_SERVER['REMOTE_ADDR']);
                $db->bind(':agent', $_SERVER['HTTP_USER_AGENT']);
                $db->execute();
                
                // Redirect ke dashboard superadmin
                header('Location: ../dashboard/index.php');
                exit();
            } else {
                $error = "Password yang dimasukkan salah";
            }
        } else {
            $error = "Email tidak terdaftar sebagai superadmin atau akun tidak aktif";
        }
    }
}

// Cek cookie remember me untuk auto login
if (empty($_SESSION['admin_id']) && isset($_COOKIE['admin_remember'])) {
    $token = $_COOKIE['admin_remember'];
    
    $db->query('SELECT * FROM admins WHERE remember_token = :token AND level = "superadmin" AND status = "aktif"');
    $db->bind(':token', $token);
    $admin = $db->single();
    
    if ($admin) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nama'] = $admin['nama'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_level'] = $admin['level'];
        
        // Update last login
        $db->query('UPDATE admins SET last_login = NOW() WHERE id = :id');
        $db->bind(':id', $admin['id']);
        $db->execute();
        
        // Track session
        $db->query('INSERT INTO admin_sessions (admin_id, session_id, ip_address, user_agent) 
                   VALUES (:admin_id, :session_id, :ip, :agent)');
        $db->bind(':admin_id', $admin['id']);
        $db->bind(':session_id', session_id());
        $db->bind(':ip', $_SERVER['REMOTE_ADDR']);
        $db->bind(':agent', $_SERVER['HTTP_USER_AGENT']);
        $db->execute();
        
        header('Location: ../dashboard/index.php');
        exit();
    }
}

// Cek apakah ada superadmin di database, jika tidak redirect ke register
$db->query('SELECT COUNT(*) as count FROM admins WHERE level = "superadmin"');
$result = $db->single();

if ($result['count'] == 0) {
    // Tidak ada superadmin, redirect ke halaman register
    header('Location: register.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Super Admin - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../css/login.css" rel="stylesheet">
    <style>
        .superadmin-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
        }
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ff416c 0%, #ff4b2b 100%);
        }
        .login-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .login-logo img {
            width: 50px;
            height: 50px;
        }
        .superadmin-badge {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        .login-body {
            padding: 40px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 14px;
            font-weight: 600;
            border-radius: 10px;
            width: 100%;
            color: white;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        .password-toggle {
            cursor: pointer;
            color: #6c757d;
        }
        .form-icon {
            width: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="superadmin-login">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <img src="../../assets/icons/Dompt.png" alt="Finansialku">
            </div>
            <h3>Login Super Admin</h3>
            <div class="superadmin-badge">SYSTEM ADMINISTRATOR</div>
            <p class="mb-0 opacity-75">Akses penuh sistem manajemen Finansialku</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo htmlspecialchars($_GET['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="mb-4">
                    <label for="email" class="form-label fw-bold">Email Super Admin</label>
                    <div class="input-group">
                        <span class="input-group-text form-icon">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="superadmin@finansialku.com" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text form-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Masukkan password" required>
                        <span class="input-group-text password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="form-text text-end">
                        <a href="lupa-password.php" class="text-decoration-none">
                            <small>Lupa Password?</small>
                        </a>
                    </div>
                </div>
                
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
                </div>
                
                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-login" id="submitBtn">
                        <i class="fas fa-sign-in-alt me-2"></i>Login sebagai Super Admin
                    </button>
                </div>
            </form>
            
            <div class="text-center">
                <div class="mb-3">
                    <a href="../../admin/auth/login.php" class="text-decoration-none">
                        <i class="fas fa-user-tie me-1"></i>Login sebagai Admin Biasa
                    </a>
                    <span class="mx-2 text-muted">•</span>
                    <a href="../../index.php" class="text-decoration-none">
                        <i class="fas fa-user me-1"></i>Login sebagai User
                    </a>
                </div>
                
                <div class="mt-4 pt-3 border-top">
                    <p class="text-muted mb-2">
                        <small>
                            <i class="fas fa-shield-alt me-1"></i>
                            Akses terbatas untuk administrator sistem
                        </small>
                    </p>
                    <p class="text-muted mb-0">
                        <small>
                            Tidak punya akses? Hubungi administrator sistem
                        </small>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="login-footer">
            <small class="text-muted">
                &copy; <?php echo date('Y'); ?> Finansialku. Hak cipta dilindungi undang-undang.
                <br>
                <a href="#" class="text-decoration-none">Kebijakan Privasi</a> • 
                <a href="#" class="text-decoration-none">Syarat & Ketentuan</a>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('title', 'Sembunyikan password');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('title', 'Tampilkan password');
            }
        });
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('submitBtn');
            
            if (!email || !password) {
                e.preventDefault();
                alert('Email dan password harus diisi!');
                return false;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Format email tidak valid!');
                return false;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            submitBtn.disabled = true;
            
            return true;
        });
        
        // Auto focus on email field
        document.getElementById('email').focus();
        
        // Enter key to submit form
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                if (e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    document.getElementById('loginForm').submit();
                }
            }
        });
        
        // Check if page was loaded with back/forward cache
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>