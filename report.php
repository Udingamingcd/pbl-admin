<?php
session_start();
require_once 'php/middleware/auth.php';

// Ambil data laporan
require_once 'php/dashboard_data.php';
$dashboard_data = getDashboardData($_SESSION['user_id']);

// Set base path untuk assets dan links
$base_path = '';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Finansialku</title>
    
    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="<?php echo $base_path; ?>css/report.css" rel="stylesheet">
</head>
<body class="report-container">
    <!-- Loading Screen -->
    <div id="loading" class="loading-screen">
        <div class="loading-content text-center">
            <div class="logo-container">
                <div class="logo-frame">
                    <div class="logo-image">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            <h3 class="loading-text mb-3">Memuat Laporan Finansial</h3>
            <p class="loading-subtext mb-4" id="loadingSubtext">Mempersiapkan laporan lengkap untuk Anda</p>
            
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
                    <div class="step" data-step="2">Data Laporan</div>
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
    $current_page = 'report.php';
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
                                <i class="fas fa-chart-bar me-2 text-primary"></i>
                                <span class="welcome-text">Laporan Finansial</span>
                            </h1>
                            <p class="text-light mb-0 opacity-75" id="currentDateTime"></p>
                        </div>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="import-buttons">
                                <a href="dashboard.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                                <button type="button" class="btn btn-outline-light btn-sm" id="refreshReport">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                                <button class="btn btn-outline-success btn-sm" id="downloadExcel">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                                <button class="btn btn-outline-danger btn-sm" id="downloadPdf">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </button>
                                <button class="btn btn-outline-warning btn-sm" id="printReport">
                                    <i class="fas fa-print me-1"></i>Cetak
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Section -->
                <div id="alertContainer" class="animate__animated animate__fadeIn"></div>

                <!-- Report Content -->
                <div id="reportContent">
                    <!-- Kontrol Laporan -->
                    <div class="card shadow mb-4 animate__animated animate__fadeInUp">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 fw-bold text-primary">
                                <i class="fas fa-cogs me-2"></i>Kontrol Laporan
                            </h6>
                            <div class="report-controls">
                                <select class="form-select form-select-sm" id="pageSize">
                                    <option value="a4">A4 (210x297mm)</option>
                                    <option value="a3">A3 (297x420mm)</option>
                                    <option value="letter">Letter (216x279mm)</option>
                                    <option value="legal">Legal (216x356mm)</option>
                                </select>
                                <select class="form-select form-select-sm" id="reportPeriod">
                                    <option value="monthly">Bulan Ini</option>
                                    <option value="last_month">Bulan Lalu</option>
                                    <option value="quarterly">Kuartal Ini</option>
                                    <option value="yearly">Tahun Ini</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Canvas Laporan dan Editor -->
                    <div class="row">
                        <!-- Canvas Laporan -->
                        <div class="col-lg-8 mb-4">
                            <div class="card shadow h-100 animate__animated animate__fadeInUp">
                                <div class="card-body p-0">
                                    <div id="reportCanvasContainer" class="report-canvas-container">
                                        <div id="reportCanvas" class="report-canvas a4">
                                            <!-- Konten laporan akan dimuat di sini -->
                                            <div class="report-header text-center">
                                                <h1 class="report-title">LAPORAN FINANSIAL</h1>
                                                <div class="report-meta">
                                                    <p class="report-period">Periode: <span id="reportPeriodText"><?php echo date('F Y'); ?></span></p>
                                                    <p class="report-date">Dibuat pada: <?php echo date('d F Y'); ?></p>
                                                </div>
                                                <hr class="report-divider">
                                            </div>
                                            
                                            <div class="report-body">
                                                <!-- Ringkasan Eksekutif -->
                                                <div class="report-section executive-summary">
                                                    <h2 class="section-title">Ringkasan Eksekutif</h2>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="summary-card">
                                                                <div class="summary-value text-primary">Rp <?php echo number_format($dashboard_data['saldo_bulan_ini'], 0, ',', '.'); ?></div>
                                                                <div class="summary-label">Saldo Bulan Ini</div>
                                                                <div class="summary-change <?php echo $dashboard_data['perbandingan']['saldo_change'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                                    <?php echo $dashboard_data['perbandingan']['saldo_change'] >= 0 ? '+' : ''; ?><?php echo $dashboard_data['perbandingan']['saldo_change']; ?>%
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="summary-card">
                                                                <div class="summary-value text-success">Rp <?php echo number_format($dashboard_data['total_pemasukan'], 0, ',', '.'); ?></div>
                                                                <div class="summary-label">Total Pemasukan</div>
                                                                <div class="summary-change <?php echo $dashboard_data['perbandingan']['pemasukan_change'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                                    <?php echo $dashboard_data['perbandingan']['pemasukan_change'] >= 0 ? '+' : ''; ?><?php echo $dashboard_data['perbandingan']['pemasukan_change']; ?>%
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="summary-card">
                                                                <div class="summary-value text-danger">Rp <?php echo number_format($dashboard_data['total_pengeluaran'], 0, ',', '.'); ?></div>
                                                                <div class="summary-label">Total Pengeluaran</div>
                                                                <div class="summary-change <?php echo $dashboard_data['perbandingan']['pengeluaran_change'] >= 0 ? 'text-danger' : 'text-success'; ?>">
                                                                    <?php echo $dashboard_data['perbandingan']['pengeluaran_change'] >= 0 ? '+' : ''; ?><?php echo $dashboard_data['perbandingan']['pengeluaran_change']; ?>%
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="summary-card">
                                                                <div class="summary-value text-info"><?php echo $dashboard_data['target_tercapai']; ?>%</div>
                                                                <div class="summary-label">Target Tercapai</div>
                                                                <div class="summary-change text-info">
                                                                    <?php echo $dashboard_data['summary']['achieved_goals']; ?>/<?php echo $dashboard_data['summary']['total_goals']; ?> Target
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Grafik Utama -->
                                                <div class="report-section">
                                                    <h2 class="section-title">Analisis Trend Keuangan</h2>
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
                                                    <div class="chart-container-large">
                                                        <canvas id="reportChart" height="250"></canvas>
                                                    </div>
                                                    <div id="pieChartLegend" class="pie-legend"></div>
                                                </div>

                                                <!-- Detail Transaksi -->
                                                <div class="report-section">
                                                    <h2 class="section-title">Detail Transaksi</h2>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm">
                                                            <thead class="table-dark">
                                                                <tr>
                                                                    <th>Tanggal</th>
                                                                    <th>Kategori</th>
                                                                    <th>Deskripsi</th>
                                                                    <th>Jenis</th>
                                                                    <th>Jumlah</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="reportTransactions">
                                                                <?php if (!empty($dashboard_data['recent_transactions'])): ?>
                                                                    <?php foreach ($dashboard_data['recent_transactions'] as $transaction): ?>
                                                                        <tr>
                                                                            <td><?php echo date('d/m/Y', strtotime($transaction['tanggal'])); ?></td>
                                                                            <td><?php echo htmlspecialchars($transaction['kategori']); ?></td>
                                                                            <td><?php echo htmlspecialchars($transaction['deskripsi']); ?></td>
                                                                            <td>
                                                                                <span class="badge bg-<?php echo $transaction['jenis'] == 'pemasukan' ? 'success' : 'danger'; ?>">
                                                                                    <?php echo ucfirst($transaction['jenis']); ?>
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-<?php echo $transaction['jenis'] == 'pemasukan' ? 'success' : 'danger'; ?>">
                                                                                <?php echo $transaction['jenis'] == 'pemasukan' ? '+' : '-'; ?> 
                                                                                Rp <?php echo number_format($transaction['jumlah'], 0, ',', '.'); ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="5" class="text-center text-muted">Tidak ada transaksi untuk ditampilkan</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <!-- Analisis Kategori -->
                                                <div class="report-section">
                                                    <h2 class="section-title">Analisis Berdasarkan Kategori</h2>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h5>Pengeluaran per Kategori</h5>
                                                            <div class="category-breakdown">
                                                                <div class="category-item">
                                                                    <span class="category-name">Makanan & Minuman</span>
                                                                    <span class="category-percentage">35%</span>
                                                                    <div class="progress mt-1">
                                                                        <div class="progress-bar bg-danger" style="width: 35%"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="category-item">
                                                                    <span class="category-name">Transportasi</span>
                                                                    <span class="category-percentage">21%</span>
                                                                    <div class="progress mt-1">
                                                                        <div class="progress-bar bg-warning" style="width: 21%"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="category-item">
                                                                    <span class="category-name">Hiburan</span>
                                                                    <span class="category-percentage">14%</span>
                                                                    <div class="progress mt-1">
                                                                        <div class="progress-bar bg-info" style="width: 14%"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="category-item">
                                                                    <span class="category-name">Lainnya</span>
                                                                    <span class="category-percentage">30%</span>
                                                                    <div class="progress mt-1">
                                                                        <div class="progress-bar bg-secondary" style="width: 30%"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h5>Pencapaian Target</h5>
                                                            <div class="goals-progress">
                                                                <?php if (!empty($dashboard_data['goal_data'])): ?>
                                                                    <?php foreach ($dashboard_data['goal_data'] as $goal): 
                                                                        $progress = min(100, ($goal['terkumpul'] / $goal['target_jumlah']) * 100);
                                                                    ?>
                                                                        <div class="goal-item">
                                                                            <div class="d-flex justify-content-between">
                                                                                <span class="goal-name"><?php echo htmlspecialchars($goal['nama_goal']); ?></span>
                                                                                <span class="goal-percentage"><?php echo number_format($progress, 1); ?>%</span>
                                                                            </div>
                                                                            <div class="progress mt-1">
                                                                                <div class="progress-bar bg-success" style="width: <?php echo $progress; ?>%"></div>
                                                                            </div>
                                                                            <small class="text-muted">
                                                                                Rp <?php echo number_format($goal['terkumpul'], 0, ',', '.'); ?> / 
                                                                                Rp <?php echo number_format($goal['target_jumlah'], 0, ',', '.'); ?>
                                                                            </small>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <p class="text-muted">Tidak ada target aktif</p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Kesimpulan dan Rekomendasi -->
                                                <div class="report-section conclusion">
                                                    <h2 class="section-title">Kesimpulan & Rekomendasi</h2>
                                                    <div class="conclusion-content">
                                                        <h5>📈 Performa Keuangan</h5>
                                                        <p>Keuangan Anda menunjukkan tren <?php echo $dashboard_data['perbandingan']['saldo_change'] >= 0 ? 'positif' : 'perlu perbaikan'; ?> dengan pertumbuhan saldo sebesar <?php echo $dashboard_data['perbandingan']['saldo_change']; ?>% dari bulan sebelumnya.</p>
                                                        
                                                        <h5>💡 Rekomendasi</h5>
                                                        <ul>
                                                            <li>Pertahankan rasio menabung yang baik</li>
                                                            <li>Evaluasi pengeluaran kategori terbesar</li>
                                                            <li>Tingkatkan progres target yang belum tercapai</li>
                                                            <li>Pertimbangkan diversifikasi sumber pemasukan</li>
                                                        </ul>
                                                        
                                                        <h5>🎯 Langkah Selanjutnya</h5>
                                                        <p>Berdasarkan analisis ini, disarankan untuk fokus pada pengelolaan pengeluaran dan percepatan pencapaian target finansial jangka panjang.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="report-footer">
                                                <hr class="report-divider">
                                                <div class="footer-content">
                                                    <p class="footer-note">Laporan ini dibuat secara otomatis oleh Sistem Finansialku</p>
                                                    <p class="footer-contact">www.finansialku.com - support@finansialku.com</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Editor dan Kontrol -->
                        <div class="col-lg-4 mb-4">
                            <!-- Editor WYSIWYG -->
                            <div class="card shadow h-100 animate__animated animate__fadeInUp mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-edit me-2"></i>Editor Laporan
                                    </h6>
                                    <div class="editor-controls">
                                        <button class="btn btn-sm btn-outline-success" id="saveReportTextBtn">
                                            <i class="fas fa-save me-1"></i>Simpan
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" id="exportReportContent">
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
                                    <div id="reportTextEditor" class="wysiwyg-editor" contenteditable="true">
                                        <h3>Kesimpulan & Rekomendasi</h3>
                                        <p>Tambahkan kesimpulan dan rekomendasi Anda di sini...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Kontrol Warna -->
                            <div class="card shadow h-100 animate__animated animate__fadeInUp">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-palette me-2"></i>Kustomisasi Laporan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">Warna Laporan</h6>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label for="reportTextColor" class="form-label small">Warna Teks</label>
                                                <input type="color" class="form-control form-control-color" id="reportTextColor" value="#000000" title="Pilih warna teks">
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label for="reportBackgroundColor" class="form-label small">Warna Latar</label>
                                                <input type="color" class="form-control form-control-color" id="reportBackgroundColor" value="#ffffff" title="Pilih warna latar">
                                            </div>
                                            <div class="col-12">
                                                <button class="btn btn-primary w-100" id="applyReportColors">
                                                    <i class="fas fa-paint-brush me-1"></i>Terapkan Warna
                                                </button>
                                            </div>
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
    <script src="<?php echo $base_path; ?>js/loading-report.js"></script>
    <script src="<?php echo $base_path; ?>js/report.js"></script>
    <script src="<?php echo $base_path; ?>js/tema.js"></script>
</body>
</html>