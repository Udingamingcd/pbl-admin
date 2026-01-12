<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="css/about.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/icons/Dompt.png" alt="Finansialku" height="30" class="d-inline-block align-text-top me-2">
                Finansialku
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center py-5 position-relative overflow-hidden">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-3 fw-bold mb-4 animate__animated animate__fadeInDown">
                        <span class="gradient-text">Finansialku</span>
                    </h1>
                    <p class="lead mb-4 animate__animated animate__fadeIn animate__delay-1s">
                        Solusi lengkap untuk mengelola keuangan pribadi Anda dengan mudah dan efektif
                    </p>
                    <div class="typing-container">
                        <div id="typing-text" class="typing-text"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </section>

    <div class="container team-section">
        <!-- Daftar Developer -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold section-title animate__animated animate__fadeInUp">Tim Pengembang</h1>
            <p class="text-muted">Kenali tim di balik kesuksesan Finansialku</p>
        </div>

        <div class="row g-4" id="developer-grid">
            <!-- Muhammad Syaiful -->
            <div class="col-md-6 col-lg-3">
                <div class="card developer-card h-100" data-developer="1">
                    <div class="card-img-container">
                        <img src="images/developer/udin finansialku.jpg" class="developer-img" alt="Muhammad Syaiful">
                        <div class="img-overlay">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">Muhammad Syaiful</h5>
                        <p class="card-text text-muted">Ketua Tim & UI/UX Designer</p>
                        <div class="badge-container">
                            <span class="badge bg-primary">CRUD User</span>
                            <span class="badge bg-success">Frontend</span>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button class="btn btn-sm btn-outline-primary view-details" data-bs-toggle="modal" data-bs-target="#developerModal1">
                            <i class="fas fa-info-circle me-1"></i> Detail
                        </button>
                    </div>
                </div>
            </div>

            <!-- Zahra Putri Armelia -->
            <div class="col-md-6 col-lg-3">
                <div class="card developer-card h-100" data-developer="2">
                    <div class="card-img-container">
                        <img src="images/developer/zahra finansialku.jpg" class="developer-img" alt="Zahra Putri Armelia">
                        <div class="img-overlay">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">Zahra Putri Armelia</h5>
                        <p class="card-text text-muted">Backend Developer</p>
                        <div class="badge-container">
                            <span class="badge bg-primary">CRUD Budget</span>
                            <span class="badge bg-danger">Backend</span>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button class="btn btn-sm btn-outline-primary view-details" data-bs-toggle="modal" data-bs-target="#developerModal2">
                            <i class="fas fa-info-circle me-1"></i> Detail
                        </button>
                    </div>
                </div>
            </div>

            <!-- Suci Ramdha Joenedy -->
            <div class="col-md-6 col-lg-3">
                <div class="card developer-card h-100" data-developer="3">
                    <div class="card-img-container">
                        <img src="images/developer/suci.jpg" class="developer-img" alt="Suci Ramdha Joenedy">
                        <div class="img-overlay">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">Suci Ramdha Joenedy</h5>
                        <p class="card-text text-muted">Backend Developer</p>
                        <div class="badge-container">
                            <span class="badge bg-primary">CRUD Transaksi</span>
                            <span class="badge bg-danger">Backend</span>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button class="btn btn-sm btn-outline-primary view-details" data-bs-toggle="modal" data-bs-target="#developerModal3">
                            <i class="fas fa-info-circle me-1"></i> Detail
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hakim Wiratama -->
            <div class="col-md-6 col-lg-3">
                <div class="card developer-card h-100" data-developer="4">
                    <div class="card-img-container">
                        <img src="images/developer/Hakim.jpg" class="developer-img" alt="Hakim Wiratama">
                        <div class="img-overlay">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">Hakim Wiratama</h5>
                        <p class="card-text text-muted">Backend Developer</p>
                        <div class="badge-container">
                            <span class="badge bg-primary">CRUD Financial Goal</span>
                            <span class="badge bg-danger">Backend</span>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button class="btn btn-sm btn-outline-primary view-details" data-bs-toggle="modal" data-bs-target="#developerModal4">
                            <i class="fas fa-info-circle me-1"></i> Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Tim -->
        <div class="row mt-5 pt-5">
            <div class="col-12">
                <div class="text-center mb-5">
                    <h2 class="section-title">Statistik Tim</h2>
                </div>
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-4">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="stat-number" data-count="4">0</h3>
                            <p class="stat-label">Anggota Tim</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-code-branch"></i>
                            </div>
                            <h3 class="stat-number" data-count="4">0</h3>
                            <p class="stat-label">Fitur Utama</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3 class="stat-number" data-count="8">0</h3>
                            <p class="stat-label">Minggu Pengembangan</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-bug"></i>
                            </div>
                            <h3 class="stat-number" data-count="100">0</h3>
                            <p class="stat-label">+ Jam Testing</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Pengembangan -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="text-center mb-5">
                    <h2 class="section-title">Timeline Pengembangan</h2>
                </div>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date">Minggu 1-2</div>
                        <div class="timeline-content">
                            <h5>Perencanaan & Analisis</h5>
                            <p>Analisis kebutuhan, studi kelayakan, dan perencanaan arsitektur sistem</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">Minggu 3-4</div>
                        <div class="timeline-content">
                            <h5>Desain UI/UX</h5>
                            <p>Wireframing, prototyping, dan desain antarmuka pengguna</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">Minggu 5-6</div>
                        <div class="timeline-content">
                            <h5>Pengembangan Backend</h5>
                            <p>Implementasi database, API, dan logika bisnis aplikasi</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">Minggu 7-8</div>
                        <div class="timeline-content">
                            <h5>Pengujian & Deployment</h5>
                            <p>Testing, debugging, dan peluncuran aplikasi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Partners -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card ai-partner-card animate__animated animate__fadeInUp">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-robot me-2"></i>Didukung Teknologi AI</h4>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6 text-center mb-4 mb-md-0">
                                <div class="ai-partner">
                                    <img src="assets/partners/logo deepseek.png" alt="DeepSeek" class="ai-logo mb-3">
                                    <h5>DeepSeek AI</h5>
                                    <p class="text-muted">Asisten pengembangan kode dan debugging</p>
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="ai-partner">
                                    <img src="assets/partners/logo chatgpt.png" alt="ChatGPT" class="ai-logo mb-3">
                                    <h5>ChatGPT</h5>
                                    <p class="text-muted">Ideasi fitur dan optimasi user experience</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <p class="mb-0">
                                <i class="fas fa-lightbulb text-warning"></i>
                                <span class="ms-2">AI membantu kami meningkatkan produktivitas hingga 70% dalam pengembangan</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 Finansialku. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal Developer 1 -->
    <div class="modal fade" id="developerModal1" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user me-2"></i>Muhammad Syaiful</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="images/developer/udin finansialku.jpg" alt="Muhammad Syaiful" class="modal-developer-img mb-3">
                            <div class="skill-tags">
                                <span class="badge bg-primary">UI/UX</span>
                                <span class="badge bg-success">Frontend</span>
                                <span class="badge bg-info">PHP</span>
                                <span class="badge bg-warning">JavaScript</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6><i class="fas fa-briefcase me-2"></i>Posisi: Ketua Tim & UI/UX Designer</h6>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 90%"></div>
                            </div>
                            <p><strong>Kontribusi:</strong></p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Mendesain antarmuka pengguna yang modern dan responsif</li>
                                <li class="list-group-item">Merancang pengalaman pengguna yang optimal</li>
                                <li class="list-group-item">Mengimplementasikan sistem CRUD untuk manajemen user</li>
                                <li class="list-group-item">Memastikan konsistensi desain di seluruh aplikasi</li>
                            </ul>
                            <div class="social-media mt-4">
                                <h6><i class="fas fa-share-alt me-2"></i>Sosial Media:</h6>
                                <div class="d-flex gap-3">
                                    <a href="#" class="social-link"><i class="fab fa-instagram"></i> Instagram</a>
                                    <a href="#" class="social-link"><i class="fab fa-linkedin"></i> LinkedIn</a>
                                    <a href="#" class="social-link"><i class="fab fa-github"></i> GitHub</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Developer 2 -->
    <div class="modal fade" id="developerModal2" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user me-2"></i>Zahra Putri Armelia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="images/developer/zahra finansialku.jpg" alt="Zahra Putri Armelia" class="modal-developer-img mb-3">
                            <div class="skill-tags">
                                <span class="badge bg-danger">Backend</span>
                                <span class="badge bg-info">PHP</span>
                                <span class="badge bg-secondary">MySQL</span>
                                <span class="badge bg-success">API</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6><i class="fas fa-briefcase me-2"></i>Posisi: Backend Developer</h6>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 85%"></div>
                            </div>
                            <p><strong>Kontribusi:</strong></p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Mengembangkan sistem CRUD untuk manajemen budget</li>
                                <li class="list-group-item">Mendesain struktur database yang optimal</li>
                                <li class="list-group-item">Membuat query dan stored procedure yang efisien</li>
                                <li class="list-group-item">Melakukan optimasi performa database</li>
                            </ul>
                            <div class="social-media mt-4">
                                <h6><i class="fas fa-share-alt me-2"></i>Sosial Media:</h6>
                                <div class="d-flex gap-3">
                                    <a href="#" class="social-link"><i class="fab fa-instagram"></i> Instagram</a>
                                    <a href="#" class="social-link"><i class="fab fa-linkedin"></i> LinkedIn</a>
                                    <a href="#" class="social-link"><i class="fab fa-github"></i> GitHub</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Developer 3 -->
    <div class="modal fade" id="developerModal3" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user me-2"></i>Suci Ramdha Joenedy</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="images/developer/suci.jpg" alt="Suci Ramdha Joenedy" class="modal-developer-img mb-3">
                            <div class="skill-tags">
                                <span class="badge bg-danger">Backend</span>
                                <span class="badge bg-info">PHP</span>
                                <span class="badge bg-warning">Security</span>
                                <span class="badge bg-success">API</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6><i class="fas fa-briefcase me-2"></i>Posisi: Backend Developer</h6>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 88%"></div>
                            </div>
                            <p><strong>Kontribusi:</strong></p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Mengembangkan sistem CRUD untuk manajemen transaksi</li>
                                <li class="list-group-item">Membuat sistem autentikasi dan otorisasi</li>
                                <li class="list-group-item">Mengimplementasikan logika bisnis aplikasi</li>
                                <li class="list-group-item">Memastikan keamanan backend</li>
                            </ul>
                            <div class="social-media mt-4">
                                <h6><i class="fas fa-share-alt me-2"></i>Sosial Media:</h6>
                                <div class="d-flex gap-3">
                                    <a href="#" class="social-link"><i class="fab fa-instagram"></i> Instagram</a>
                                    <a href="#" class="social-link"><i class="fab fa-linkedin"></i> LinkedIn</a>
                                    <a href="#" class="social-link"><i class="fab fa-github"></i> GitHub</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Developer 4 -->
    <div class="modal fade" id="developerModal4" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user me-2"></i>Hakim Wiratama</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="images/developer/Hakim.jpg" alt="Hakim Wiratama" class="modal-developer-img mb-3">
                            <div class="skill-tags">
                                <span class="badge bg-danger">Backend</span>
                                <span class="badge bg-info">PHP</span>
                                <span class="badge bg-secondary">Testing</span>
                                <span class="badge bg-success">Integration</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6><i class="fas fa-briefcase me-2"></i>Posisi: Backend Developer</h6>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 82%"></div>
                            </div>
                            <p><strong>Kontribusi:</strong></p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Mengembangkan sistem CRUD untuk financial goal</li>
                                <li class="list-group-item">Mengintegrasikan frontend dengan backend</li>
                                <li class="list-group-item">Mengembangkan fitur laporan dan analisis</li>
                                <li class="list-group-item">Melakukan testing dan debugging</li>
                            </ul>
                            <div class="social-media mt-4">
                                <h6><i class="fas fa-share-alt me-2"></i>Sosial Media:</h6>
                                <div class="d-flex gap-3">
                                    <a href="#" class="social-link"><i class="fab fa-instagram"></i> Instagram</a>
                                    <a href="#" class="social-link"><i class="fab fa-linkedin"></i> LinkedIn</a>
                                    <a href="#" class="social-link"><i class="fab fa-github"></i> GitHub</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/about.js"></script>
</body>
</html>