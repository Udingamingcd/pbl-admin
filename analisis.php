<?php
session_start();
require_once 'php/middleware/auth.php';

// Ambil data analisis
require_once 'php/dashboard_data.php';
$dashboard_data = getDashboardData($_SESSION['user_id']);

// Set base path untuk assets dan links
$base_path = '';

// Calculate additional metrics
$rasio_menabung = $dashboard_data['total_pemasukan'] > 0 ? 
    (($dashboard_data['saldo_bulan_ini'] / $dashboard_data['total_pemasukan']) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis - Finansialku</title>
    
    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="<?php echo $base_path; ?>css/analisis.css" rel="stylesheet">
</head>
<body class="analisis-container">
    <!-- Loading Screen -->
    <div id="loading" class="loading-screen">
        <div class="loading-content text-center">
            <div class="logo-container">
                <div class="logo-frame">
                    <div class="logo-image">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <h3 class="loading-text mb-3">Memuat Analisis Finansial</h3>
            <p class="loading-subtext mb-4" id="loadingSubtext">Mempersiapkan analisis mendalam untuk Anda</p>
            
            <!-- Progress Bar dengan Persentase -->
            <div class="progress-container-improved">
                <div class="progress-header">
                    <span class="progress-label">Progress Memuat</span>
                    <span class="progress-percentage" id="progressPercentage">0%</span>
                </div>
                <div class="progress improved-progress" style="height: 12px; border-radius: 10px;">
                    <div class="progress-bar improved-progress-bar" role="progressbar" style="width: 0%">
                        <div class="progress-indicator"></div>
                    </div>
                </div>
                <div class="progress-steps">
                    <div class="step active" data-step="1">Inisialisasi</div>
                    <div class="step" data-step="2">Data Analisis</div>
                    <div class="step" data-step="3">Grafik</div>
                    <div class="step" data-step="4">Selesai</div>
                </div>
                <p class="loading-text-improved mt-3" id="loadingText">Menginisialisasi sistem...</p>
            </div>
        </div>
    </div>

    <!-- Notification Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-bell text-primary me-2"></i>
                <strong class="me-auto">Notifikasi</strong>
                <small>Baru saja</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage">
                Hello, world! This is a toast message.
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <?php 
    $current_page = 'analisis.php';
    include 'php/includes/navbar.php'; 
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <main class="col-12 main-content">
                <!-- Header Section -->
                <div class="header-section animate__animated animate__fadeInDown">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <div>
                            <h1 class="h2 mb-2">
                                <i class="fas fa-chart-line me-2 text-primary"></i>
                                <span class="welcome-text">Analisis Finansial</span>
                            </h1>
                            <p class="text-light mb-0 opacity-75" id="currentDateTime"></p>
                        </div>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="import-buttons d-flex flex-wrap gap-2">
                                <a href="dashboard.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                                <button type="button" class="btn btn-outline-light btn-sm" id="refreshAnalisis">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                                <button class="btn btn-outline-success btn-sm" id="exportExcelBtn">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                                <button class="btn btn-outline-danger btn-sm" id="exportPdfBtn">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </button>
                                <button class="btn btn-outline-warning btn-sm" id="exportImageFull">
                                    <i class="fas fa-image me-1"></i>Gambar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Section -->
                <div id="alertContainer" class="animate__animated animate__fadeIn"></div>

                <!-- Analisis Content -->
                <div id="analisisContent">
                    <!-- Statistik Cepat -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-start-primary h-100 animate__animated animate__fadeInLeft">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                                Saldo Bulan Ini
                                            </div>
                                            <div class="h5 mb-0 fw-bold text-gray-800">
                                                Rp <?php echo number_format($dashboard_data['saldo_bulan_ini'], 0, ',', '.'); ?>
                                            </div>
                                            <small class="text-muted percentage-change" data-value="<?php echo $dashboard_data['perbandingan']['saldo_change']; ?>">
                                                <?php if ($dashboard_data['perbandingan']['saldo_change'] >= 0): ?>
                                                    <i class="fas fa-arrow-up text-success me-1"></i>+<?php echo $dashboard_data['perbandingan']['saldo_change']; ?>%
                                                <?php else: ?>
                                                    <i class="fas fa-arrow-down text-danger me-1"></i><?php echo $dashboard_data['perbandingan']['saldo_change']; ?>%
                                                <?php endif; ?>
                                                dari bulan lalu
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-wallet fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-start-success h-100 animate__animated animate__fadeInLeft">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                                Total Pemasukan
                                            </div>
                                            <div class="h5 mb-0 fw-bold text-gray-800">
                                                Rp <?php echo number_format($dashboard_data['total_pemasukan'], 0, ',', '.'); ?>
                                            </div>
                                            <small class="text-muted percentage-change" data-value="<?php echo $dashboard_data['perbandingan']['pemasukan_change']; ?>">
                                                <?php if ($dashboard_data['perbandingan']['pemasukan_change'] >= 0): ?>
                                                    <i class="fas fa-arrow-up text-success me-1"></i>+<?php echo $dashboard_data['perbandingan']['pemasukan_change']; ?>%
                                                <?php else: ?>
                                                    <i class="fas fa-arrow-down text-danger me-1"></i><?php echo $dashboard_data['perbandingan']['pemasukan_change']; ?>%
                                                <?php endif; ?>
                                                dari bulan lalu
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-arrow-down fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-start-danger h-100 animate__animated animate__fadeInRight">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                                Total Pengeluaran
                                            </div>
                                            <div class="h5 mb-0 fw-bold text-gray-800">
                                                Rp <?php echo number_format($dashboard_data['total_pengeluaran'], 0, ',', '.'); ?>
                                            </div>
                                            <small class="text-muted percentage-change" data-value="<?php echo $dashboard_data['perbandingan']['pengeluaran_change']; ?>">
                                                <?php if ($dashboard_data['perbandingan']['pengeluaran_change'] >= 0): ?>
                                                    <i class="fas fa-arrow-up text-danger me-1"></i>+<?php echo $dashboard_data['perbandingan']['pengeluaran_change']; ?>%
                                                <?php else: ?>
                                                    <i class="fas fa-arrow-down text-success me-1"></i><?php echo $dashboard_data['perbandingan']['pengeluaran_change']; ?>%
                                                <?php endif; ?>
                                                dari bulan lalu
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-arrow-up fa-2x text-danger"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-start-info h-100 animate__animated animate__fadeInRight">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                                Target Tercapai
                                            </div>
                                            <div class="h5 mb-0 fw-bold text-gray-800">
                                                <?php echo $dashboard_data['target_tercapai']; ?>%
                                            </div>
                                            <small class="text-muted">
                                                <?php echo $dashboard_data['summary']['achieved_goals']; ?> dari <?php echo $dashboard_data['summary']['total_goals']; ?> target tercapai
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-bullseye fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grafik Analisis Utama -->
                    <div class="row mb-4">
                        <!-- Grafik Trend -->
                        <div class="col-lg-8 mb-4">
                            <div class="card shadow h-100 animate__animated animate__fadeInUp">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-chart-area me-2"></i>Analisis Trend Keuangan
                                    </h6>
                                    <div class="chart-controls-improved">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check" name="mainChartType" id="mainChartLine" value="line" checked>
                                            <label class="btn btn-outline-primary" for="mainChartLine">
                                                <i class="fas fa-chart-line"></i> Trend
                                            </label>
                                            <input type="radio" class="btn-check" name="mainChartType" id="mainChartPie" value="pie">
                                            <label class="btn btn-outline-primary" for="mainChartPie">
                                                <i class="fas fa-chart-pie"></i> Distribusi
                                            </label>
                                        </div>
                                        <select class="form-select form-select-sm" id="chartPeriod">
                                            <option value="daily">Harian</option>
                                            <option value="weekly" selected>Mingguan</option>
                                            <option value="monthly">Bulanan</option>
                                            <option value="yearly">Tahunan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="analisisChart" height="400"></canvas>
                                    </div>
                                    <div id="pieChartLegend" class="pie-legend"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Analisis Cepat & Kontrol -->
                        <div class="col-lg-4 mb-4">
                            <!-- Analisis Cepat -->
                            <div class="card shadow h-100 animate__animated animate__fadeInUp mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-bolt me-2"></i>Analisis Cepat
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="quick-analysis">
                                        <div class="analysis-item mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="analysis-label">Rasio Menabung</span>
                                                <span class="analysis-value text-success">
                                                    <?php echo number_format($rasio_menabung, 1); ?>%
                                                </span>
                                            </div>
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" style="width: <?php echo min($rasio_menabung, 100); ?>%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="analysis-item mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="analysis-label">Pengeluaran vs Budget</span>
                                                <span class="analysis-value text-warning">85%</span>
                                            </div>
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-warning" style="width: 85%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="analysis-item mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="analysis-label">Kesehatan Keuangan</span>
                                                <span class="analysis-value text-info">Baik</span>
                                            </div>
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-info" style="width: 75%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="analysis-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="analysis-label">Trend Bulanan</span>
                                                <span class="analysis-value text-success">
                                                    <i class="fas fa-arrow-up me-1"></i>Naik
                                                </span>
                                            </div>
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" style="width: 65%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kontrol Ekspor -->
                            <div class="card shadow h-100 animate__animated animate__fadeInUp">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-download me-2"></i>Ekspor Analisis
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-danger" id="exportPdfFull">
                                            <i class="fas fa-file-pdf me-1"></i>Export PDF Lengkap
                                        </button>
                                        <button class="btn btn-outline-success" id="exportExcelFull">
                                            <i class="fas fa-file-excel me-1"></i>Export Excel Lengkap
                                        </button>
                                        <button class="btn btn-outline-primary" id="exportImageFull">
                                            <i class="fas fa-image me-1"></i>Export Gambar Lengkap
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Analisis -->
                    <div class="row mb-4">
                        <!-- Analisis Kategori -->
                        <div class="col-md-6 mb-4">
                            <div class="card shadow h-100 animate__animated animate__fadeInUp">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-tags me-2"></i>Analisis Berdasarkan Kategori
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="category-analysis">
                                        <div class="category-item mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="category-name">Makanan & Minuman</span>
                                                <span class="category-amount text-danger">Rp 1.250.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">35% dari total pengeluaran</small>
                                                <small class="text-danger">-15% dari bulan lalu</small>
                                            </div>
                                            <div class="progress mt-1" style="height: 4px;">
                                                <div class="progress-bar bg-danger" style="width: 35%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="category-item mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="category-name">Transportasi</span>
                                                <span class="category-amount text-warning">Rp 750.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">21% dari total pengeluaran</small>
                                                <small class="text-success">+5% dari bulan lalu</small>
                                            </div>
                                            <div class="progress mt-1" style="height: 4px;">
                                                <div class="progress-bar bg-warning" style="width: 21%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="category-item mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="category-name">Hiburan</span>
                                                <span class="category-amount text-info">Rp 500.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">14% dari total pengeluaran</small>
                                                <small class="text-danger">-8% dari bulan lalu</small>
                                            </div>
                                            <div class="progress mt-1" style="height: 4px;">
                                                <div class="progress-bar bg-info" style="width: 14%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="category-item">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="category-name">Lainnya</span>
                                                <span class="category-amount text-secondary">Rp 1.100.000</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">30% dari total pengeluaran</small>
                                                <small class="text-success">+12% dari bulan lalu</small>
                                            </div>
                                            <div class="progress mt-1" style="height: 4px;">
                                                <div class="progress-bar bg-secondary" style="width: 30%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Prediksi Masa Depan -->
                        <div class="col-md-6 mb-4">
                            <div class="card shadow h-100 animate__animated animate__fadeInUp">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-crystal-ball me-2"></i>Prediksi & Proyeksi
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="prediction-analysis">
                                        <div class="prediction-item mb-3 p-3 bg-light rounded">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-piggy-bank text-success me-2"></i>
                                                <strong>Proyeksi Tabungan 6 Bulan</strong>
                                            </div>
                                            <p class="mb-2">Berdasarkan tren saat ini, tabungan Anda diperkirakan mencapai:</p>
                                            <h5 class="text-success mb-0">Rp 15.750.000</h5>
                                        </div>
                                        
                                        <div class="prediction-item mb-3 p-3 bg-light rounded">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                <strong>Peringatan Pengeluaran</strong>
                                            </div>
                                            <p class="mb-2">Pengeluaran kategori Makanan & Minuman perlu dikontrol:</p>
                                            <div class="progress mt-1" style="height: 8px;">
                                                <div class="progress-bar bg-warning" style="width: 85%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="prediction-item p-3 bg-light rounded">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-bullseye text-info me-2"></i>
                                                <strong>Target yang Akan Tercapai</strong>
                                            </div>
                                            <p class="mb-2">2 dari 5 target finansial diperkirakan tercapai dalam 3 bulan:</p>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">Tabungan Liburan</small>
                                                <small class="text-success">+45%</small>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <small class="text-muted">Dana Darurat</small>
                                                <small class="text-warning">+28%</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Editor dan Kontrol -->
                    <div class="row">
                        <!-- Editor Teks WYSIWYG -->
                        <div class="col-lg-8 mb-4">
                            <div class="card shadow h-100 animate__animated animate__fadeInUp">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-edit me-2"></i>Editor Analisis - Mode Word
                                    </h6>
                                    <div class="editor-controls">
                                        <button class="btn btn-sm btn-outline-success" id="saveTextBtn">
                                            <i class="fas fa-save me-1"></i>Simpan
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" id="exportTextContent">
                                            <i class="fas fa-download me-1"></i>Ekspor
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="wysiwyg-toolbar">
                                        <button class="btn btn-sm" data-command="bold" title="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button class="btn btn-sm" data-command="italic" title="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button class="btn btn-sm" data-command="underline" title="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-heading"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-command="formatBlock" data-value="h1">Heading 1</a></li>
                                                <li><a class="dropdown-item" href="#" data-command="formatBlock" data-value="h2">Heading 2</a></li>
                                                <li><a class="dropdown-item" href="#" data-command="formatBlock" data-value="h3">Heading 3</a></li>
                                                <li><a class="dropdown-item" href="#" data-command="formatBlock" data-value="p">Paragraph</a></li>
                                            </ul>
                                        </div>
                                        <button class="btn btn-sm" data-command="insertUnorderedList" title="Bullet List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button class="btn btn-sm" data-command="insertOrderedList" title="Numbered List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <input type="color" class="form-control-color" id="textColorPicker" title="Text Color">
                                        <input type="color" class="form-control-color" id="bgColorPicker" title="Background Color">
                                    </div>
                                    <div id="analisisTextEditor" class="wysiwyg-editor" contenteditable="true">
                                        <h2>Analisis Finansial <?php echo date('F Y'); ?></h2>
                                        <p>Analisis ini berisi evaluasi lengkap mengenai kondisi keuangan Anda.</p>
                                        
                                        <h3>Ringkasan Eksekutif</h3>
                                        <p>Berdasarkan data yang terkumpul, berikut adalah performa keuangan Anda:</p>
                                        
                                        <ul>
                                            <li><strong>Saldo Bulan Ini:</strong> Rp <?php echo number_format($dashboard_data['saldo_bulan_ini'], 0, ',', '.'); ?></li>
                                            <li><strong>Total Pemasukan:</strong> Rp <?php echo number_format($dashboard_data['total_pemasukan'], 0, ',', '.'); ?></li>
                                            <li><strong>Total Pengeluaran:</strong> Rp <?php echo number_format($dashboard_data['total_pengeluaran'], 0, ',', '.'); ?></li>
                                        </ul>

                                        <h3>Analisis dan Rekomendasi</h3>
                                        <p>Tambahkan analisis dan rekomendasi Anda di sini...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Analisis Kategori dan Kontrol -->
                        <div class="col-lg-4 mb-4">
                            <!-- Kontrol Warna -->
                            <div class="card shadow h-100 animate__animated animate__fadeInUp">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-palette me-2"></i>Kustomisasi
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label for="textColor" class="form-label small">Warna Teks</label>
                                            <input type="color" class="form-control form-control-color" id="textColor" value="#000000" title="Pilih warna teks">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="backgroundColor" class="form-label small">Warna Latar</label>
                                            <input type="color" class="form-control form-control-color" id="backgroundColor" value="#ffffff" title="Pilih warna latar">
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-primary w-100" id="applyColors">
                                                <i class="fas fa-paint-brush me-1"></i>Terapkan Warna
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'php/includes/footer.php'; ?>

    <!-- JavaScript Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="<?php echo $base_path; ?>js/loading-analisis.js"></script>
    <script src="<?php echo $base_path; ?>js/analisis.js"></script>
    <script src="<?php echo $base_path; ?>js/tema.js"></script>
</body>
</html>