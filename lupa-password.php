<?php
require_once 'php/middleware/guest.php';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Finansialku</title>
    <link rel="icon" type="image/png" href="assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
    <link href="css/animasi.css" rel="stylesheet">
</head>
<body class="login-body">
    <!-- Loading Screen -->
    <div id="loading" class="loading-screen">
        <div class="loading-content">
            <div class="loading-logo">
                <img src="assets/icons/Dompt.png" alt="Finansialku" class="logo-bounce">
                <h2 class="mt-3 text-white">Finansialku</h2>
            </div>
            <div class="progress mt-4" style="width: 200px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Background Animation -->
    <div class="background-animation">
        <div class="floating-elements">
            <div class="floating-element coin">💰</div>
            <div class="floating-element money">💵</div>
            <div class="floating-element chart">📈</div>
            <div class="floating-element piggy">🐷</div>
            <div class="floating-element card">💳</div>
        </div>
    </div>

    <div class="container-fluid vh-100">
        <div class="row h-100">
            <!-- Kiri: Konten Marketing -->
            <div class="col-lg-6 d-none d-lg-flex login-left">
                <div class="login-hero">
                    <div class="hero-logo mb-4">
                        <img src="assets/icons/Dompt.png" alt="Finansialku" height="80">
                        <h1 class="text-white mt-2">Finansialku</h1>
                    </div>
                    <h2 class="hero-title">Reset Password Anda</h2>
                    <p class="hero-subtitle">Kami akan mengirimkan link reset password ke email Anda</p>
                    
                    <div class="features-list mt-5">
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Proses Aman dan Terenkripsi</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-clock"></i>
                            <span>Link Berlaku 1 Jam</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-envelope"></i>
                            <span>Instruksi dikirim via Email</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kanan: Form Lupa Password -->
            <div class="col-lg-6 login-right">
                <div class="login-form-container">
                    <div class="form-header text-center mb-4">
                        <a href="index.php" class="back-to-login">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Login
                        </a>
                        <img src="assets/icons/Dompt.png" alt="Finansialku" height="50" class="d-lg-none mb-3">
                        <h3 class="form-title">Lupa Password</h3>
                        <p class="text-muted">Masukkan email Anda untuk reset password</p>
                    </div>

                    <!-- Indikator Koneksi Database -->
                    <div id="db-indicator" class="alert alert-info d-flex align-items-center mb-3">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <span>Mengecek koneksi database...</span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>

                    <form id="forgotPasswordForm" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="email" class="form-label">Email Terdaftar</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Harap masukkan email yang valid</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3 login-btn">
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                            Kirim Link Reset
                        </button>

                        <div class="text-center">
                            <span class="text-muted">Ingat password?</span>
                            <a href="index.php" class="text-decoration-none ms-2">Masuk di sini</a>
                        </div>
                    </form>

                    <div class="additional-info mt-4 p-3 bg-light rounded">
                        <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Informasi</h6>
                        <p class="small text-muted mb-2">Jika Anda tidak menerima email:</p>
                        <ul class="small text-muted mb-0">
                            <li>Periksa folder spam</li>
                            <li>Pastikan email yang dimasukkan benar</li>
                            <li>Hubungi support jika masih bermasalah</li>
                        </ul>
                    </div>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            &copy; 2025 Finansialku. All rights reserved.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Success -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div class="success-icon mb-4">
                        <i class="fas fa-paper-plane text-primary"></i>
                    </div>
                    <h4 class="modal-title mb-3">Link Terkirim!</h4>
                    <p class="text-muted mb-4">Kami telah mengirimkan link reset password ke email Anda. Silakan periksa inbox dan ikuti instruksi yang diberikan.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Mengerti</button>
                        <a href="index.php" class="btn btn-outline-primary">Kembali ke Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/loading-login.js"></script>
    <script src="js/lupa-password.js"></script>
</body>
</html>