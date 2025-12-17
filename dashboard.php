<?php
session_start();
require_once 'php/middleware/auth.php';

// Ambil data dashboard
require_once 'php/dashboard_data.php';
$dashboard_data = getDashboardData($_SESSION['user_id']);

// Ambil saldo total user (semua waktu)
require_once 'php/koneksi.php';
$db = new Database();
$db->query('SELECT 
            COALESCE(SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE 0 END), 0) as total_pemasukan_seumur_hidup,
            COALESCE(SUM(CASE WHEN jenis = "pengeluaran" THEN jumlah ELSE 0 END), 0) as total_pengeluaran_seumur_hidup
            FROM transaksi 
            WHERE user_id = :user_id');
$db->bind(':user_id', $_SESSION['user_id']);
$saldo_total_data = $db->single();
$saldo_total = $saldo_total_data['total_pemasukan_seumur_hidup'] - $saldo_total_data['total_pengeluaran_seumur_hidup'];

// Set base path untuk assets dan links
$base_path = '';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Finansialku</title>
    <link rel="icon" type="image/png" href="assets/icons/Dompt.png">
    
    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="<?php echo $base_path; ?>css/dashboard.css" rel="stylesheet">
</head>
<body class="dashboard-container">
    <!-- Loading Screen - IMPROVED -->
    <div id="loading" class="loading-screen">
        <div class="loading-content text-center">
            <div class="logo-container">
                <div class="logo-frame">
                    <div class="logo-image">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
            <h3 class="loading-text mb-3">Memuat Dashboard Finansial</h3>
            <p class="loading-subtext mb-4" id="loadingSubtext">Mempersiapkan pengalaman terbaik untuk Anda</p>
            
            <!-- Loading Stats dalam Layout Terstruktur -->
            <div class="loading-stats-improved">
                <div class="stats-grid">
                    <div class="stat-card-loading">
                        <div class="stat-icon-loading">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="stat-content-loading">
                            <div class="stat-value-loading"><?php echo $dashboard_data['summary']['total_transactions']; ?></div>
                            <div class="stat-label-loading">Transaksi</div>
                        </div>
                    </div>
                    <div class="stat-card-loading">
                        <div class="stat-icon-loading">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="stat-content-loading">
                            <div class="stat-value-loading"><?php echo $dashboard_data['summary']['active_goals']; ?></div>
                            <div class="stat-label-loading">Target Aktif</div>
                        </div>
                    </div>
                    <div class="stat-card-loading">
                        <div class="stat-icon-loading">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="stat-content-loading">
                            <div class="stat-value-loading"><?php echo count($dashboard_data['budget_data']); ?></div>
                            <div class="stat-label-loading">Kategori Budget</div>
                        </div>
                    </div>
                </div>
            </div>
            
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
                    <div class="step" data-step="2">Data Pengguna</div>
                    <div class="step" data-step="3">Transaksi</div>
                    <div class="step" data-step="4">Analisis</div>
                    <div class="step" data-step="5">Selesai</div>
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
    $current_page = 'dashboard.php';
    include 'php/includes/navbar.php'; 
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Main Content - Full Width -->
            <main class="col-12 main-content">
                <!-- Welcome Section -->
                <div class="welcome-section animate__animated animate__fadeInDown">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <div>
                            <h1 class="h2 mb-2" id="welcomeTitle">
                                <span class="welcome-text">Selamat Datang, </span>
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_nama'] ?? 'User'); ?>!</span>
                            </h1>
                            
                            <!-- Tambahan: Saldo User -->
                            <div class="user-saldo mb-1">
                                <i class="fas fa-wallet"></i>
                                <span>Saldo Total:</span>
                                <span class="saldo-value">Rp <?php echo number_format($saldo_total, 0, ',', '.'); ?></span>
                            </div>
                            
                            <p class="text-light mb-0 opacity-75" id="currentDateTime"></p>
                        </div>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-light btn-sm" id="refreshDashboard">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                                <a href="report.php" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-chart-bar me-1"></i>Laporan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Section -->
                <div id="alertContainer" class="animate__animated animate__fadeIn"></div>

                <!-- Dashboard Content -->
                <div id="dashboardContent">
                    <!-- Stats dan Tips dalam Satu Baris - Layout Baru -->
                    <div class="row mb-4 stats-and-tips-container">
                        <!-- Stats Cards (8 kolom) -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="row stats-cards-grid h-100">
                                <!-- Saldo Bulan Ini -->
                                <div class="col-md-6 mb-3">
                                    <div class="card stat-card border-start-primary h-100 animate__animated animate__fadeInLeft">
                                        <div class="card-body d-flex flex-column justify-content-center">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                                        Saldo Bulan Ini
                                                    </div>
                                                    <div class="h5 mb-0 fw-bold text-gray-800" id="saldoBulanIni">
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
                                                    <div class="stat-icon">
                                                        <i class="fas fa-wallet"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-hover-effect"></div>
                                    </div>
                                </div>

                                <!-- Total Pemasukan -->
                                <div class="col-md-6 mb-3">
                                    <div class="card stat-card border-start-success h-100 animate__animated animate__fadeInLeft">
                                        <div class="card-body d-flex flex-column justify-content-center">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                                        Total Pemasukan
                                                    </div>
                                                    <div class="h5 mb-0 fw-bold text-gray-800" id="totalPemasukan">
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
                                                    <div class="stat-icon">
                                                        <i class="fas fa-arrow-down text-success"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-hover-effect"></div>
                                    </div>
                                </div>

                                <!-- Total Pengeluaran -->
                                <div class="col-md-6 mb-3">
                                    <div class="card stat-card border-start-danger h-100 animate__animated animate__fadeInRight">
                                        <div class="card-body d-flex flex-column justify-content-center">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                                        Total Pengeluaran
                                                    </div>
                                                    <div class="h5 mb-0 fw-bold text-gray-800" id="totalPengeluaran">
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
                                                    <div class="stat-icon">
                                                        <i class="fas fa-arrow-up text-danger"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-hover-effect"></div>
                                    </div>
                                </div>

                                <!-- Target Tercapai -->
                                <div class="col-md-6 mb-3">
                                    <div class="card stat-card border-start-info h-100 animate__animated animate__fadeInRight">
                                        <div class="card-body d-flex flex-direction: column; justify-content: center;">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                                        Target Tercapai
                                                    </div>
                                                    <div class="h5 mb-0 fw-bold text-gray-800" id="targetTercapai">
                                                        <?php echo $dashboard_data['target_tercapai']; ?>%
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo $dashboard_data['summary']['achieved_goals']; ?> dari <?php echo $dashboard_data['summary']['total_goals']; ?> target tercapai
                                                    </small>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="stat-icon">
                                                        <i class="fas fa-bullseye"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-hover-effect"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tips Finansial (4 kolom) -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow h-100 financial-tips-card animate__animated animate__fadeInUp">
                                <div class="card-header bg-gradient-success text-white d-flex justify-content-between align-items-center py-2">
                                    <h6 class="m-0 fw-bold">
                                        <i class="fas fa-lightbulb me-2"></i>Tips Finansial
                                    </h6>
                                    <button class="btn btn-sm btn-outline-light" id="nextTipBtn" title="Tips berikutnya">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="tips-container flex-grow-1 d-flex flex-column justify-content-between">
                                        <div class="tip-content">
                                            <div class="tip-icon text-center mb-3">
                                                <i class="fas fa-lightbulb fa-2x text-success"></i>
                                            </div>
                                            <h6 id="tipTitle" class="text-center fw-bold text-primary mb-3"></h6>
                                            <p class="text-muted text-center tip-description mb-4" id="tipDescription"></p>
                                        </div>
                                        <div class="tip-controls-container">
                                            <div class="tip-controls text-center mb-3">
                                                <button class="btn btn-sm btn-outline-success" id="prevTipBtn">
                                                    <i class="fas fa-chevron-left me-1"></i>Sebelumnya
                                                </button>
                                                <button class="btn btn-sm btn-success ms-2" id="nextTipManualBtn">
                                                    Selanjutnya<i class="fas fa-chevron-right ms-1"></i>
                                                </button>
                                            </div>
                                            <div class="tip-progress">
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar bg-success" id="tipProgress" role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <small class="text-muted" id="tipTimer">10s</small>
                                                    <small class="text-muted">Tips berikutnya</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Grid - Layout Profesional -->
                    <div class="row content-grid">
                        <!-- Full Width Column -->
                        <div class="col-12">
                            <!-- Financial Chart dengan Sidebar -->
                            <div class="card shadow mb-4 chart-container animate__animated animate__fadeInUp">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-chart-line me-2"></i>Grafik Keuangan
                                    </h6>
                                    <div class="chart-controls">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check" name="chartType" id="chartLine" value="line" checked>
                                            <label class="btn btn-outline-primary" for="chartLine">
                                                <i class="fas fa-chart-line"></i>
                                            </label>
                                            <input type="radio" class="btn-check" name="chartType" id="chartBar" value="bar">
                                            <label class="btn btn-outline-primary" for="chartBar">
                                                <i class="fas fa-chart-bar"></i>
                                            </label>
                                        </div>
                                        <select class="form-select form-select-sm ms-2" id="chartPeriod">
                                            <option value="daily">Harian</option>
                                            <option value="weekly" selected>Mingguan</option>
                                            <option value="monthly">Bulanan</option>
                                            <option value="yearly">Tahunan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Chart Area (8 kolom) -->
                                        <div class="col-lg-8">
                                            <div class="chart-area">
                                                <canvas id="financeChart" height="300"></canvas>
                                            </div>
                                            <div class="chart-trends mt-3" id="chartTrends"></div>
                                        </div>
                                        
                                        <!-- Sidebar Kanan (4 kolom) -->
                                        <div class="col-lg-4">
                                            <!-- Quick Actions Card -->
                                            <div class="card shadow quick-actions-card mb-3">
                                                <div class="card-header bg-gradient-primary text-white py-2">
                                                    <h6 class="m-0 fw-bold">
                                                        <i class="fas fa-bolt me-2"></i>Aksi Cepat
                                                    </h6>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <a href="<?php echo $base_path; ?>php/crud/transaksi/create.php" class="quick-action-btn btn btn-primary w-100 h-100">
                                                                <div class="action-icon">
                                                                    <i class="fas fa-exchange-alt"></i>
                                                                </div>
                                                                <div class="action-text">
                                                                    <small>Tambah</small>
                                                                    <strong>Transaksi</strong>
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="col-6">
                                                            <a href="<?php echo $base_path; ?>php/crud/budget/create.php" class="quick-action-btn btn btn-success w-100 h-100">
                                                                <div class="action-icon">
                                                                    <i class="fas fa-chart-pie"></i>
                                                                </div>
                                                                <div class="action-text">
                                                                    <small>Buat</small>
                                                                    <strong>Budget</strong>
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="col-6">
                                                            <a href="<?php echo $base_path; ?>php/crud/financial_goal/create.php" class="quick-action-btn btn btn-info w-100 h-100">
                                                                <div class="action-icon">
                                                                    <i class="fas fa-bullseye"></i>
                                                                </div>
                                                                <div class="action-text">
                                                                    <small>Buat</small>
                                                                    <strong>Target</strong>
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="col-6">
                                                            <a href="<?php echo $base_path; ?>analisis.php" class="quick-action-btn btn btn-warning w-100 h-100">
                                                                <div class="action-icon">
                                                                    <i class="fas fa-chart-line"></i>
                                                                </div>
                                                                <div class="action-text">
                                                                    <small>Lihat</small>
                                                                    <strong>Analisis</strong>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Recent Transactions -->
                                            <div class="card shadow">
                                                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                                    <h6 class="m-0 fw-bold text-primary">
                                                        <i class="fas fa-history me-2"></i>Transaksi Terbaru
                                                    </h6>
                                                    <a href="<?php echo $base_path; ?>php/crud/transaksi/read.php" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-list"></i>
                                                    </a>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div id="recentTransactions" class="transaction-list">
                                                        <?php if (!empty($dashboard_data['recent_transactions'])): ?>
                                                            <?php foreach (array_slice($dashboard_data['recent_transactions'], 0, 3) as $transaction): ?>
                                                                <div class="transaction-item p-2 border-bottom">
                                                                    <div class="d-flex justify-content-between align-items-start">
                                                                        <div class="transaction-info flex-grow-1">
                                                                            <div class="d-flex align-items-center mb-1">
                                                                                <div class="transaction-icon me-2">
                                                                                    <?php if ($transaction['jenis'] == 'pemasukan'): ?>
                                                                                        <i class="fas fa-arrow-down text-success"></i>
                                                                                    <?php else: ?>
                                                                                        <i class="fas fa-arrow-up text-danger"></i>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                                <h6 class="mb-0 fw-bold text-primary" style="font-size: 0.8rem;"><?php echo htmlspecialchars($transaction['deskripsi']); ?></h6>
                                                                            </div>
                                                                            <div class="transaction-meta">
                                                                                <small class="text-muted" style="font-size: 0.65rem;">
                                                                                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($transaction['kategori']); ?>
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="transaction-amount ms-2">
                                                                            <span class="badge bg-<?php echo $transaction['jenis'] == 'pemasukan' ? 'success' : 'danger'; ?>" style="font-size: 0.7rem;">
                                                                                <?php echo $transaction['jenis'] == 'pemasukan' ? '+' : '-'; ?> 
                                                                                Rp <?php echo number_format($transaction['jumlah'], 0, ',', '.'); ?>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                            <?php if (count($dashboard_data['recent_transactions']) > 3): ?>
                                                                <div class="text-center p-2">
                                                                    <a href="<?php echo $base_path; ?>php/crud/transaksi/read.php" class="btn btn-sm btn-outline-primary">
                                                                        Lihat Semua
                                                                    </a>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <div class="text-center text-muted p-3">
                                                                <div class="empty-state">
                                                                    <i class="fas fa-receipt fa-2x mb-2"></i>
                                                                    <p class="mb-2" style="font-size: 0.8rem;">Belum Ada Transaksi</p>
                                                                    <a href="<?php echo $base_path; ?>php/crud/transaksi/create.php" class="btn btn-primary btn-sm">
                                                                        <i class="fas fa-plus me-1"></i>Tambah
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Budget dan Target Section - DI BAWAH GRAFIK -->
                            <div class="row">
                                <!-- Budget Aktif -->
                                <div class="col-md-6 mb-4">
                                    <div class="card shadow h-100 animate__animated animate__fadeInUp">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>Budget Aktif</h6>
                                            <a href="<?php echo $base_path; ?>php/crud/budget/read.php" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-cog me-1"></i>Kelola
                                            </a>
                                        </div>
                                        <div class="card-body">
                                            <?php if (!empty($dashboard_data['budget_data'])): ?>
                                                <?php foreach (array_slice($dashboard_data['budget_data'], 0, 3) as $budget): 
                                                    $usage_percentage = min(100, ($budget['terpakai'] / $budget['jumlah']) * 100);
                                                    $usage_class = $usage_percentage >= 90 ? 'danger' : ($usage_percentage >= 75 ? 'warning' : 'success');
                                                ?>
                                                    <div class="budget-item mb-3">
                                                        <div class="budget-header d-flex justify-content-between align-items-center mb-2">
                                                            <div class="budget-title">
                                                                <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($budget['nama_budget']); ?></h6>
                                                                <small class="text-muted"><?php echo htmlspecialchars($budget['kategori']); ?></small>
                                                            </div>
                                                            <div class="budget-badge">
                                                                <span class="badge bg-<?php echo $usage_class; ?>">
                                                                    <?php echo number_format($usage_percentage, 0); ?>%
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="progress-container mb-2">
                                                            <div class="progress" style="height: 6px; border-radius: 10px;">
                                                                <div class="progress-bar bg-warning" 
                                                                     role="progressbar" 
                                                                     style="width: <?php echo $usage_percentage; ?>%"
                                                                     aria-valuenow="<?php echo $usage_percentage; ?>" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <div class="budget-labels d-flex justify-content-between mt-1">
                                                                <small class="text-muted">Terpakai: Rp <?php echo number_format($budget['terpakai'], 0, ',', '.'); ?></small>
                                                                <small class="text-muted">Sisa: Rp <?php echo number_format($budget['jumlah'] - $budget['terpakai'], 0, ',', '.'); ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (count($dashboard_data['budget_data']) > 3): ?>
                                                    <div class="text-center mt-3">
                                                        <a href="<?php echo $base_path; ?>php/crud/budget/read.php" class="btn btn-sm btn-outline-warning">
                                                            Lihat Semua Budget
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="text-center text-muted py-4">
                                                    <div class="empty-state">
                                                        <i class="fas fa-chart-pie fa-3x mb-3 text-muted"></i>
                                                        <h6 class="fw-bold text-primary">Belum Ada Budget</h6>
                                                        <p class="mb-3">Atur budget untuk kategori pengeluaran</p>
                                                        <a href="<?php echo $base_path; ?>php/crud/budget/create.php" class="btn btn-warning btn-sm">
                                                            <i class="fas fa-plus me-1"></i>Buat Budget
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Target Finansial -->
                                <div class="col-md-6 mb-4">
                                    <div class="card shadow h-100 animate__animated animate__fadeInUp">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-bullseye me-2"></i>Target Finansial</h6>
                                            <a href="<?php echo $base_path; ?>php/crud/financial_goal/read.php" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-cog me-1"></i>Kelola
                                            </a>
                                        </div>
                                        <div class="card-body">
                                            <?php if (!empty($dashboard_data['goal_data'])): ?>
                                                <?php foreach (array_slice($dashboard_data['goal_data'], 0, 3) as $goal): 
                                                    $progress = min(100, ($goal['terkumpul'] / $goal['target_jumlah']) * 100);
                                                    $days_left = ceil((strtotime($goal['tenggat_waktu']) - time()) / (60 * 60 * 24));
                                                    $progress_class = $progress >= 75 ? 'high' : ($progress >= 50 ? 'medium' : 'low');
                                                ?>
                                                    <div class="goal-item mb-3">
                                                        <div class="goal-header d-flex justify-content-between align-items-center mb-2">
                                                            <div class="goal-title">
                                                                <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($goal['nama_goal']); ?></h6>
                                                                <small class="text-muted">Tenggat: <?php echo date('d M Y', strtotime($goal['tenggat_waktu'])); ?></small>
                                                            </div>
                                                            <div class="goal-badge">
                                                                <span class="badge bg-<?php echo $progress_class == 'high' ? 'success' : ($progress_class == 'medium' ? 'warning' : 'danger'); ?>">
                                                                    <?php echo $days_left; ?> hari
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="progress-container mb-2">
                                                            <div class="progress" style="height: 8px; border-radius: 10px;">
                                                                <div class="progress-bar bg-info progress-<?php echo $progress_class; ?>" 
                                                                     role="progressbar" 
                                                                     style="width: <?php echo $progress; ?>%"
                                                                     aria-valuenow="<?php echo $progress; ?>" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <div class="progress-labels d-flex justify-content-between mt-1">
                                                                <small class="text-muted">Rp <?php echo number_format($goal['terkumpul'], 0, ',', '.'); ?></small>
                                                                <small class="text-muted"><?php echo number_format($progress, 1); ?>%</small>
                                                                <small class="text-muted">Rp <?php echo number_format($goal['target_jumlah'], 0, ',', '.'); ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (count($dashboard_data['goal_data']) > 3): ?>
                                                    <div class="text-center mt-3">
                                                        <a href="<?php echo $base_path; ?>php/crud/financial_goal/read.php" class="btn btn-sm btn-outline-info">
                                                            Lihat Semua Target
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="text-center text-muted py-4">
                                                    <div class="empty-state">
                                                        <i class="fas fa-bullseye fa-3x mb-3 text-muted"></i>
                                                        <h6 class="fw-bold text-primary">Belum Ada Target</h6>
                                                        <p class="mb-3">Mulai rencanakan tujuan finansial Anda</p>
                                                        <a href="<?php echo $base_path; ?>php/crud/financial_goal/create.php" class="btn btn-info btn-sm">
                                                            <i class="fas fa-plus me-1"></i>Buat Target Pertama
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
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
    <script src="<?php echo $base_path; ?>js/loading-dashboard.js"></script>
    <script src="<?php echo $base_path; ?>js/dashboard.js"></script>
    <!-- tema.js dihapus karena hanya untuk halaman report.php -->
</body>
</html>