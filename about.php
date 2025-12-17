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
    <link href="css/about.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/icons/Dompt.png" alt="Finansialku" height="30" class="d-inline-block align-text-top me-2">
                Finansialku
            </a>
        </div>
    </nav>

    <!-- Carousel Tahapan Pembuatan -->
    <div id="processCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#processCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#processCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#processCarousel" data-bs-slide-to="2"></button>
            <button type="button" data-bs-target="#processCarousel" data-bs-slide-to="3"></button>
            <button type="button" data-bs-target="#processCarousel" data-bs-slide-to="4"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images/process/planning.jpg" class="d-block w-100" alt="Perencanaan Aplikasi">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Tahap 1: Perencanaan</h5>
                    <p>Menganalisis kebutuhan dan merancang struktur aplikasi Finansialku</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/process/design.jpg" class="d-block w-100" alt="Desain UI/UX">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Tahap 2: Desain UI/UX</h5>
                    <p>Membuat antarmuka pengguna yang menarik dan mudah digunakan</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/process/development.jpg" class="d-block w-100" alt="Pengembangan">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Tahap 3: Pengembangan</h5>
                    <p>Mengimplementasikan fitur-fitur utama aplikasi</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/process/testing.jpg" class="d-block w-100" alt="Pengujian">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Tahap 4: Pengujian</h5>
                    <p>Melakukan testing untuk memastikan kualitas aplikasi</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/process/launch.jpg" class="d-block w-100" alt="Peluncuran">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Tahap 5: Peluncuran</h5>
                    <p>Meluncurkan Finansialku untuk digunakan oleh pengguna</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#processCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#processCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <!-- Animasi Teks Ketikan -->
    <div class="typing-container">
        <div id="typing-text" class="typing-text"></div>
    </div>

    <div class="container team-section">
        <!-- Daftar Developer -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold section-title">Tim Pengembang</h1>
        </div>

        <div class="row g-4">
            <!-- Muhammad Syaiful -->
            <div class="col-md-6 col-lg-3">
                <div class="card developer-card h-100" data-bs-toggle="modal" data-bs-target="#developerModal1">
                    <div class="card-img-container">
                        <img src="images/developer/udin finansialku.jpg" class="developer-img" alt="Muhammad Syaiful">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">Muhammad Syaiful</h5>
                        <p class="card-text text-muted">Ketua Tim & UI/UX Designer</p>
                        <small class="text-primary">CRUD User</small>
                    </div>
                </div>
            </div>

            <!-- Zahra Putri Armelia -->
            <div class="col-md-6 col-lg-3">
                <div class="card developer-card h-100" data-bs-toggle="modal" data-bs-target="#developerModal2">
                    <div class="card-img-container">
                        <img src="images/developer/zahra finansialku.jpg" class="developer-img" alt="Zahra Putri Armelia">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">Zahra Putri Armelia</h5>
                        <p class="card-text text-muted">Backend Developer</p>
                        <small class="text-primary">CRUD Budget</small>
                    </div>
                </div>
            </div>

            <!-- Suci Ramdha Joenedy -->
            <div class="col-md-6 col-lg-3">
                <div class="card developer-card h-100" data-bs-toggle="modal" data-bs-target="#developerModal3">
                    <div class="card-img-container">
                        <img src="images/developer/suci.jpg" class="developer-img" alt="Suci Ramdha Joenedy">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">Suci Ramdha Joenedy</h5>
                        <p class="card-text text-muted">Backend Developer</p>
                        <small class="text-primary">CRUD Transaksi</small>
                    </div>
                </div>
            </div>

            <!-- Hakim Wiratama -->
            <div class="col-md-6 col-lg-3">
                <div class="card developer-card h-100" data-bs-toggle="modal" data-bs-target="#developerModal4">
                    <div class="card-img-container">
                        <img src="images/developer/Hakim.jpg" class="developer-img" alt="Hakim Wiratama">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">Hakim Wiratama</h5>
                        <p class="card-text text-muted">Backend Developer</p>
                        <small class="text-primary">CRUD Financial Goal</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proses Pengembangan -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="text-center mb-5">
                    <h2 class="section-title">Proses Pengembangan</h2>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="process-step">
                            <i class="fas fa-lightbulb"></i>
                            <h5>Perencanaan</h5>
                            <p>Menganalisis kebutuhan dan merancang struktur aplikasi</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="process-step">
                            <i class="fas fa-palette"></i>
                            <h5>Desain UI/UX</h5>
                            <p>Membuat antarmuka yang menarik dan mudah digunakan</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="process-step">
                            <i class="fas fa-code"></i>
                            <h5>Pengembangan</h5>
                            <p>Mengimplementasikan fitur-fitur utama aplikasi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Partners -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card ai-partner-card">
                    <div class="card-header">
                        <h4 class="mb-0">Didukung Oleh</h4>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6 text-center mb-3">
                                <img src="assets/partners/deepseek.png" alt="DeepSeek" height="50" class="me-3">
                                <span class="h5">DeepSeek AI</span>
                            </div>
                            <div class="col-md-6 text-center mb-3">
                                <img src="assets/partners/chatgpt.png" alt="ChatGPT" height="50" class="me-3">
                                <span class="h5">ChatGPT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2025 Finansialku. All rights reserved.</p>
        </div>
    </footer>

    <!-- Modal Developer 1 -->
    <div class="modal fade" id="developerModal1" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Muhammad Syaiful</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="images/developer/syaiful.jpg" alt="Muhammad Syaiful" class="modal-developer-img">
                        </div>
                        <div class="col-md-8">
                            <h6>Posisi: Ketua Tim & UI/UX Designer</h6>
                            <p><strong>Kontribusi:</strong></p>
                            <ul>
                                <li>Mendesain antarmuka pengguna yang modern dan responsif</li>
                                <li>Merancang pengalaman pengguna yang optimal</li>
                                <li>Mengimplementasikan sistem CRUD untuk manajemen user</li>
                                <li>Memastikan konsistensi desain di seluruh aplikasi</li>
                            </ul>
                            <div class="social-media mt-4">
                                <h6>Sosial Media:</h6>
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
                <div class="modal-header">
                    <h5 class="modal-title">Zahra Putri Armelia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="images/developer/zahra finansialku.jpg" alt="Zahra Putri Armelia" class="modal-developer-img">
                        </div>
                        <div class="col-md-8">
                            <h6>Posisi: Backend Developer</h6>
                            <p><strong>Kontribusi:</strong></p>
                            <ul>
                                <li>Mengembangkan sistem CRUD untuk manajemen budget</li>
                                <li>Mendesain struktur database yang optimal</li>
                                <li>Membuat query dan stored procedure yang efisien</li>
                                <li>Melakukan optimasi performa database</li>
                            </ul>
                            <div class="social-media mt-4">
                                <h6>Sosial Media:</h6>
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
                <div class="modal-header">
                    <h5 class="modal-title">Suci Ramdha Joenedy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="images/developer/suci.jpg" alt="Suci Ramdha Joenedy" class="modal-developer-img">
                        </div>
                        <div class="col-md-8">
                            <h6>Posisi: Backend Developer</h6>
                            <p><strong>Kontribusi:</strong></p>
                            <ul>
                                <li>Mengembangkan sistem CRUD untuk manajemen transaksi</li>
                                <li>Membuat sistem autentikasi dan otorisasi</li>
                                <li>Mengimplementasikan logika bisnis aplikasi</li>
                                <li>Memastikan keamanan backend</li>
                            </ul>
                            <div class="social-media mt-4">
                                <h6>Sosial Media:</h6>
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
                <div class="modal-header">
                    <h5 class="modal-title">Hakim Wiratama</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="images/developer/Hakim.jpg" alt="Hakim Wiratama" class="modal-developer-img">
                        </div>
                        <div class="col-md-8">
                            <h6>Posisi: Backend Developer</h6>
                            <p><strong>Kontribusi:</strong></p>
                            <ul>
                                <li>Mengembangkan sistem CRUD untuk financial goal</li>
                                <li>Mengintegrasikan frontend dengan backend</li>
                                <li>Mengembangkan fitur laporan dan analisis</li>
                                <li>Melakukan testing dan debugging</li>
                            </ul>
                            <div class="social-media mt-4">
                                <h6>Sosial Media:</h6>
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