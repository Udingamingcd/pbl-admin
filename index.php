<?php
session_start();
require_once 'php/config.php';
require_once 'php/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finansialku - Login</title>
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
            <div class="floating-element coin">🪙</div>
            <div class="floating-element money">💴</div>
            <div class="floating-element chart">📊</div>
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
                    <h2 class="hero-title">Kelola Keuangan dengan Mudah</h2>
                    <p class="hero-subtitle">Pantau pengeluaran harian, mingguan, bulanan, dan tahunan dalam satu aplikasi</p>
                    
                    <div class="features-list mt-5">
                        <div class="feature-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Analisis Keuangan Mendalam</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-bullseye"></i>
                            <span>Target Finansial yang Terukur</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Keamanan Data Terjamin</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kanan: Form Login -->
            <div class="col-lg-6 login-right">
                <div class="login-form-container">
                    <div class="form-header text-center mb-4">
                        <img src="assets/icons/Dompt.png" alt="Finansialku" height="50" class="d-lg-none mb-3">
                        <h3 class="form-title">Masuk ke Akun Anda</h3>
                        <p class="text-muted">Selamat datang kembali!</p>
                    </div>

                    <!-- Indikator Koneksi Database -->
                    <div id="db-indicator" class="alert alert-info d-flex align-items-center mb-3">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <span>Mengecek koneksi database...</span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>

                    <form id="loginForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Harap masukkan email yang valid</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                <button type="button" class="btn btn-outline-secondary toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">Password minimal 8 karakter</div>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3 login-btn">
                            Masuk
                        </button>

                        <div class="text-center">
                            <a href="lupa-password.php" class="text-decoration-none">Lupa Password?</a>
                            <span class="mx-2">•</span>
                            <a href="register.php" class="text-decoration-none">Daftar Akun Baru</a>
                        </div>


                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            &copy; 2025 Finansialku. All rights reserved.
                            <a href="about.php" class="text-decoration-none ms-2">Tentang Kami</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/loading-login.js"></script>
    <script src="js/login.js"></script>
</body>
</html>