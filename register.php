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
    <title>Finansialku - Daftar</title>
    <link rel="icon" type="image/png" href="assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/register.css" rel="stylesheet">
    <link href="css/animasi.css" rel="stylesheet">
</head>
<body class="register-body">
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
            <div class="col-lg-6 d-none d-lg-flex register-left">
                <div class="register-hero">
                    <div class="hero-logo mb-4">
                        <img src="assets/icons/Dompt.png" alt="Finansialku" height="80">
                        <h1 class="text-white mt-2">Finansialku</h1>
                    </div>
                    <h2 class="hero-title">Mulai Perjalanan Finansial Anda</h2>
                    <p class="hero-subtitle">Bergabunglah dengan ribuan pengguna yang telah mengelola keuangan dengan lebih baik</p>
                    
                    <div class="features-list mt-5">
                        <div class="feature-item">
                            <i class="fas fa-chart-pie"></i>
                            <div>
                                <strong>Analisis Keuangan Mendalam</strong>
                                <p class="mb-0">Laporan detail dan insight finansial</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-bullseye"></i>
                            <div>
                                <strong>Target Finansial Terukur</strong>
                                <p class="mb-0">Capai tujuan finansial dengan rencana yang jelas</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>Keamanan Data Terjamin</strong>
                                <p class="mb-0">Data Anda dienkripsi dan terlindungi</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-mobile-alt"></i>
                            <div>
                                <strong>Akses Multi-Device</strong>
                                <p class="mb-0">Gunakan di semua perangkat Anda</p>
                            </div>
                        </div>
                    </div>

                    <div class="security-info mt-5">
                        <div class="security-header">
                            <i class="fas fa-lock"></i>
                            <h5 class="mb-0">Keamanan Terjamin</h5>
                        </div>
                        <div class="security-features">
                            <div class="security-item">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>Enkripsi data end-to-end</span>
                            </div>
                            <div class="security-item">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>Verifikasi dua faktor</span>
                            </div>
                            <div class="security-item">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>Backup otomatis</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kanan: Form Register -->
            <div class="col-lg-6 register-right">
                <div class="register-form-container">
                    <div class="form-header text-center mb-4">
                        <a href="index.php" class="back-to-login mb-3 d-inline-flex align-items-center text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Login
                        </a>
                        <img src="assets/icons/Dompt.png" alt="Finansialku" height="50" class="d-lg-none mb-3">
                        <h3 class="form-title">Buat Akun Baru</h3>
                        <p class="text-muted">Bergabunglah dengan komunitas Finansialku</p>
                    </div>

                    <!-- Indikator Koneksi Database -->
                    <div id="db-indicator" class="alert alert-info d-flex align-items-center mb-3">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <span>Mengecek koneksi database...</span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>

                    <form id="registerForm" class="needs-validation" novalidate enctype="multipart/form-data">
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-user-circle me-2"></i>
                                Informasi Pribadi
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="nama" name="nama" required minlength="2" placeholder="Masukkan nama lengkap">
                                            <div class="valid-feedback">Nama valid!</div>
                                            <div class="invalid-feedback">Harap masukkan nama lengkap (minimal 2 karakter)</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telepon" class="form-label">Nomor Telepon</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="tel" class="form-control" id="telepon" name="telepon" pattern="[0-9]{10,15}" placeholder="Contoh: 081234567890">
                                            <div class="valid-feedback">Nomor telepon valid!</div>
                                            <div class="invalid-feedback">Format nomor telepon tidak valid (10-15 digit)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required placeholder="email@contoh.com">
                                    <div class="valid-feedback">Email valid!</div>
                                    <div class="invalid-feedback">Harap masukkan email yang valid</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-home"></i></span>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="2" placeholder="Masukkan alamat lengkap"></textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="foto_profil" class="form-label">Foto Profil</label>
                                <div class="file-upload-container">
                                    <div class="file-upload-wrapper">
                                        <input type="file" class="file-upload-input" id="foto_profil" name="foto_profil" accept="image/*">
                                        <div class="file-upload-display">
                                            <div class="file-upload-preview">
                                                <i class="fas fa-user-circle"></i>
                                                <div class="file-upload-texts">
                                                    <span class="file-upload-text">Pilih foto profil</span>
                                                    <small class="file-upload-subtext">atau drag & drop file di sini</small>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-outline-primary file-upload-btn">
                                                <i class="fas fa-cloud-upload-alt me-1"></i>
                                                Pilih File
                                            </button>
                                        </div>
                                    </div>
                                    <div class="file-upload-info">
                                        <small class="text-muted">Format: JPG, PNG, GIF (Maks. 2MB)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-lock me-2"></i>
                                Keamanan Akun
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" required minlength="8" placeholder="Minimal 8 karakter">
                                            <button type="button" class="btn btn-outline-secondary toggle-password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <div class="valid-feedback" id="passwordValidFeedback">Password valid!</div>
                                            <div class="invalid-feedback">Password minimal 8 karakter</div>
                                        </div>
                                        <div class="password-strength mt-2">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                            </div>
                                            <div class="password-strength-info mt-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="password-strength-text">Kekuatan password:</small>
                                                    <span class="strength-label badge">Sangat Lemah</span>
                                                </div>
                                                <div class="password-requirements mt-2">
                                                    <small class="text-muted">Gunakan kombinasi huruf besar, kecil, angka, dan simbol</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Ketik ulang password">
                                            <button type="button" class="btn btn-outline-secondary toggle-password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <div class="valid-feedback">Password cocok!</div>
                                            <div class="invalid-feedback" id="confirmPasswordInvalidFeedback">Password tidak cocok</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="password-tips">
                                <h6 class="tips-title">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    Tips Password Aman:
                                </h6>
                                <ul class="tips-list">
                                    <li class="tip-item">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <span>Gunakan minimal 8 karakter</span>
                                    </li>
                                    <li class="tip-item">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <span>Kombinasikan huruf besar dan kecil</span>
                                    </li>
                                    <li class="tip-item">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <span>Tambahkan angka dan simbol</span>
                                    </li>
                                    <li class="tip-item">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        <span>Hindari informasi pribadi</span>
                                    </li>
                                    <li class="tip-item">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        <span>Jangan gunakan kata sandi umum</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="terms-agreement">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required>
                                    <label class="form-check-label" for="agree_terms">
                                        Saya menyetujui <a href="syarat-dan-ketentuan.php" class="text-decoration-none">Syarat & Ketentuan</a> 
                                        dan <a href="kebijakan-privasi.php" class="text-decoration-none">Kebijakan Privasi</a>
                                    </label>
                                    <div class="invalid-feedback">Anda harus menyetujui syarat dan ketentuan</div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 register-btn mt-4">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                                <span class="btn-text">Daftar Sekarang</span>
                            </button>

                            <div class="auth-links text-center mt-4">
                                <span class="text-muted">Sudah punya akun?</span>
                                <a href="index.php" class="text-decoration-none ms-2 fw-semibold">Masuk di sini</a>
                            </div>
                        </div>
                    </form>

                    <div class="footer text-center mt-4">
                        <small class="text-muted">
                            &copy; 2025 Finansialku. All rights reserved.
                            <a href="about.php" class="text-decoration-none ms-2">Tentang Kami</a>
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
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <h4 class="modal-title mb-3">Pendaftaran Berhasil!</h4>
                    <p class="text-muted mb-4">Akun Anda telah berhasil dibuat. Silakan masuk untuk mulai menggunakan Finansialku.</p>
                    <div class="d-grid gap-2">
                        <a href="index.php" class="btn btn-primary">Masuk Sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Error -->
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div class="error-icon mb-4">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                    </div>
                    <h4 class="modal-title mb-3" id="errorModalTitle">Terjadi Kesalahan</h4>
                    <p class="text-muted mb-4" id="errorModalMessage">Terjadi kesalahan saat mendaftar. Silakan coba lagi.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Email Already Registered -->
    <div class="modal fade" id="emailRegisteredModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div class="email-icon mb-4">
                        <i class="fas fa-envelope text-warning"></i>
                    </div>
                    <h4 class="modal-title mb-3">Email Sudah Terdaftar</h4>
                    <p class="text-muted mb-3">Email <strong id="registeredEmail"></strong> sudah terdaftar di sistem kami.</p>
                    <div class="suggestions mb-4">
                        <p class="text-muted small mb-2">Apa yang ingin Anda lakukan?</p>
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Masuk ke Akun
                            </a>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-edit me-2"></i>
                                Gunakan Email Lain
                            </button>
                            <a href="lupa-password.php" class="btn btn-link text-decoration-none">
                                <i class="fas fa-key me-1"></i>
                                Lupa Password?
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Weak Password Warning -->
    <div class="modal fade" id="weakPasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div class="warning-icon mb-4">
                        <i class="fas fa-shield-alt text-warning"></i>
                    </div>
                    <h4 class="modal-title mb-3">Peringatan Keamanan Password</h4>
                    <p class="text-muted mb-3">Password yang Anda gunakan termasuk dalam kategori <strong>sangat lemah</strong> dan berisiko tinggi terhadap keamanan akun Anda.</p>
                    
                    <div class="alert alert-warning text-start">
                        <small>
                            <strong>Rekomendasi untuk keamanan yang lebih baik:</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li>Gunakan minimal 12 karakter</li>
                                <li>Kombinasikan huruf besar dan kecil</li>
                                <li>Tambahkan angka dan simbol spesial</li>
                                <li>Hindari kata sandi yang umum digunakan</li>
                                <li>Jangan gunakan informasi pribadi</li>
                            </ul>
                        </small>
                    </div>

                    <div class="security-tips mt-4 p-3 bg-light rounded">
                        <h6 class="mb-3">Contoh Password yang Lebih Aman:</h6>
                        <div class="text-start small">
                            <p class="mb-1"><code>Mobil@123!Biru</code> - Kombinasi kata, angka, dan simbol</p>
                            <p class="mb-1"><code>K0ta!P4s4r#Minggu</code> - Frase dengan substitusi karakter</p>
                            <p class="mb-0"><code>J4l4n-M4l4m*T4ngh</code> - Pola acak dengan simbol</p>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="button" class="btn btn-warning" id="continueWeakPassword">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Saya Paham Risiko, Tetap Gunakan
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                            <i class="fas fa-edit me-2"></i>
                            Ubah Password
                        </button>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Anda dapat mengubah password kapan saja melalui pengaturan akun
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/loading-register.js"></script>
    <script src="js/koneksi.js"></script>
    <script src="js/register.js"></script>
</body>
</html>