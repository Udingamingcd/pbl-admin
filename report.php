<?php
session_start();
require_once 'php/middleware/auth.php';

// Set base path untuk assets
$base_path = '';

// Database connection
require_once 'php/koneksi.php';
$db = new Database();

// Default values
$start_date = date('Y-m-01'); // Awal bulan ini
$end_date = date('Y-m-d'); // Hari ini
$report_type = 'monthly';
$chart_type = 'line';

// Cek apakah ada filter yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_type = $_POST['report_type'] ?? 'monthly';
    $chart_type = $_POST['chart_type'] ?? 'line';
    
    // Atur rentang tanggal berdasarkan jenis laporan
    switch ($report_type) {
        case 'daily':
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
            break;
        case 'weekly':
            $start_date = date('Y-m-d', strtotime('monday this week'));
            $end_date = date('Y-m-d', strtotime('sunday this week'));
            break;
        case 'monthly':
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
            break;
        case 'yearly':
            $start_date = date('Y-01-01');
            $end_date = date('Y-12-31');
            break;
        case 'custom':
            $start_date = $_POST['start_date'] ?? $start_date;
            $end_date = $_POST['end_date'] ?? $end_date;
            break;
    }
}

// Fungsi untuk mendapatkan data laporan
function getReportData($db, $user_id, $start_date, $end_date, $report_type) {
    $data = [];
    
    try {
        // Total pemasukan dan pengeluaran dalam periode
        $db->query('SELECT 
                    COALESCE(SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE 0 END), 0) as total_pemasukan,
                    COALESCE(SUM(CASE WHEN jenis = "pengeluaran" THEN jumlah ELSE 0 END), 0) as total_pengeluaran,
                    COUNT(*) as total_transaksi
                    FROM transaksi 
                    WHERE user_id = :user_id 
                    AND tanggal BETWEEN :start_date AND :end_date');
        $db->bind(':user_id', $user_id);
        $db->bind(':start_date', $start_date);
        $db->bind(':end_date', $end_date);
        $data['summary'] = $db->single();
        
        // Saldo periode
        $data['summary']['saldo'] = $data['summary']['total_pemasukan'] - $data['summary']['total_pengeluaran'];
        
        // Data per kategori
        $db->query('SELECT 
                    kategori,
                    jenis,
                    COALESCE(SUM(jumlah), 0) as total,
                    COUNT(*) as jumlah_transaksi
                    FROM transaksi 
                    WHERE user_id = :user_id 
                    AND tanggal BETWEEN :start_date AND :end_date
                    GROUP BY kategori, jenis
                    ORDER BY jenis DESC, total DESC');
        $db->bind(':user_id', $user_id);
        $db->bind(':start_date', $start_date);
        $db->bind(':end_date', $end_date);
        $data['by_category'] = $db->resultSet();
        
        // Transaksi terperinci
        $db->query('SELECT * FROM transaksi 
                    WHERE user_id = :user_id 
                    AND tanggal BETWEEN :start_date AND :end_date
                    ORDER BY tanggal DESC, created_at DESC');
        $db->bind(':user_id', $user_id);
        $db->bind(':start_date', $start_date);
        $db->bind(':end_date', $end_date);
        $data['transactions'] = $db->resultSet();
        
        // Data untuk chart berdasarkan periode
        $data['chart_data'] = getChartDataByPeriod($db, $user_id, $start_date, $end_date, $report_type);
        
        // Statistik tambahan
        $data['avg_transaction'] = $data['summary']['total_transaksi'] > 0 ? 
            ($data['summary']['total_pemasukan'] + $data['summary']['total_pengeluaran']) / $data['summary']['total_transaksi'] : 0;
        
        $data['pemasukan_count'] = 0;
        $data['pengeluaran_count'] = 0;
        foreach ($data['transactions'] as $transaction) {
            if ($transaction['jenis'] == 'pemasukan') {
                $data['pemasukan_count']++;
            } else {
                $data['pengeluaran_count']++;
            }
        }
        
        return $data;
        
    } catch (Exception $e) {
        error_log("Report data error: " . $e->getMessage());
        return [
            'summary' => [
                'total_pemasukan' => 0,
                'total_pengeluaran' => 0,
                'saldo' => 0,
                'total_transaksi' => 0
            ],
            'by_category' => [],
            'transactions' => [],
            'chart_data' => ['labels' => [], 'pemasukan' => [], 'pengeluaran' => []],
            'avg_transaction' => 0,
            'pemasukan_count' => 0,
            'pengeluaran_count' => 0
        ];
    }
}

// Fungsi untuk mendapatkan data chart berdasarkan periode
function getChartDataByPeriod($db, $user_id, $start_date, $end_date, $period) {
    $labels = [];
    $pemasukan_data = [];
    $pengeluaran_data = [];
    
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    
    if ($period == 'daily' || ($end->diff($start)->days <= 31)) {
        // Harian
        $interval = new DateInterval('P1D');
        $period_series = new DatePeriod($start, $interval, $end);
        
        foreach ($period_series as $date) {
            $date_str = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            
            // Pemasukan hari ini
            $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                        WHERE user_id = :user_id AND jenis = "pemasukan" 
                        AND tanggal = :date');
            $db->bind(':user_id', $user_id);
            $db->bind(':date', $date_str);
            $pemasukan = $db->single();
            $pemasukan_data[] = (float)$pemasukan['total'];
            
            // Pengeluaran hari ini
            $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                        WHERE user_id = :user_id AND jenis = "pengeluaran" 
                        AND tanggal = :date');
            $db->bind(':user_id', $user_id);
            $db->bind(':date', $date_str);
            $pengeluaran = $db->single();
            $pengeluaran_data[] = (float)$pengeluaran['total'];
        }
        
    } elseif ($period == 'monthly' || ($end->diff($start)->days <= 365)) {
        // Bulanan
        $interval = new DateInterval('P1M');
        $period_series = new DatePeriod($start, $interval, $end);
        
        foreach ($period_series as $date) {
            $month_str = $date->format('Y-m');
            $labels[] = $date->format('M Y');
            
            // Pemasukan bulan ini
            $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                        WHERE user_id = :user_id AND jenis = "pemasukan" 
                        AND DATE_FORMAT(tanggal, "%Y-%m") = :month');
            $db->bind(':user_id', $user_id);
            $db->bind(':month', $month_str);
            $pemasukan = $db->single();
            $pemasukan_data[] = (float)$pemasukan['total'];
            
            // Pengeluaran bulan ini
            $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                        WHERE user_id = :user_id AND jenis = "pengeluaran" 
                        AND DATE_FORMAT(tanggal, "%Y-%m") = :month');
            $db->bind(':user_id', $user_id);
            $db->bind(':month', $month_str);
            $pengeluaran = $db->single();
            $pengeluaran_data[] = (float)$pengeluaran['total'];
        }
        
    } else {
        // Tahunan
        $interval = new DateInterval('P1Y');
        $period_series = new DatePeriod($start, $interval, $end);
        
        foreach ($period_series as $date) {
            $year_str = $date->format('Y');
            $labels[] = $date->format('Y');
            
            // Pemasukan tahun ini
            $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                        WHERE user_id = :user_id AND jenis = "pemasukan" 
                        AND DATE_FORMAT(tanggal, "%Y") = :year');
            $db->bind(':user_id', $user_id);
            $db->bind(':year', $year_str);
            $pemasukan = $db->single();
            $pemasukan_data[] = (float)$pemasukan['total'];
            
            // Pengeluaran tahun ini
            $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                        WHERE user_id = :user_id AND jenis = "pengeluaran" 
                        AND DATE_FORMAT(tanggal, "%Y") = :year');
            $db->bind(':user_id', $user_id);
            $db->bind(':year', $year_str);
            $pengeluaran = $db->single();
            $pengeluaran_data[] = (float)$pengeluaran['total'];
        }
    }
    
    // Jika data kosong, buat array minimal
    if (empty($labels)) {
        $labels = [date('d M', strtotime($start_date))];
        $pemasukan_data = [0];
        $pengeluaran_data = [0];
    }
    
    return [
        'labels' => $labels,
        'pemasukan' => $pemasukan_data,
        'pengeluaran' => $pengeluaran_data
    ];
}

// Ambil data laporan
$report_data = getReportData($db, $_SESSION['user_id'], $start_date, $end_date, $report_type);

// Format tanggal untuk display
$display_start_date = date('d M Y', strtotime($start_date));
$display_end_date = date('d M Y', strtotime($end_date));
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - Finansialku</title>
    <link rel="icon" type="image/png" href="assets/icons/Dompt.png">
    
    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="css/report.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
    
    <!-- PDF Generation Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="dashboard-container">
    <!-- Loading Screen -->
    <div id="loading" class="loading-screen">
        <div class="loading-content text-center">
            <div class="logo-container">
                <div class="logo-frame">
                    <div class="logo-image">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                </div>
            </div>
            <h3 class="loading-text mb-3">Menyiapkan Laporan</h3>
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
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
                <!-- Header Laporan -->
                <div class="report-header animate__animated animate__fadeInDown">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <a href="dashboard.php" class="btn btn-sm btn-outline-light me-3" title="Kembali ke Dashboard">
                                    <i class="fas fa-arrow-left me-1"></i>
                                </a>
                                <h1 class="h2 mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Laporan Keuangan
                                </h1>
                            </div>
                            <p class="text-light mb-0 opacity-75">
                                <i class="fas fa-calendar-alt me-1"></i>Periode: 
                                <span id="reportPeriod"><?php echo $display_start_date; ?> - <?php echo $display_end_date; ?></span>
                            </p>
                        </div>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2" title="Kembali ke Dashboard">
                                    <i class="fas fa-home me-1"></i>Dashboard
                                </a>
                                <button type="button" class="btn btn-light btn-sm" id="refreshReport">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" id="printReport">
                                    <i class="fas fa-print me-1"></i>Cetak
                                </button>
                                <button type="button" class="btn btn-success btn-sm" id="exportPDF">
                                    <i class="fas fa-file-pdf me-1"></i>Ekspor PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card shadow mb-4 filter-section animate__animated animate__fadeIn">
                    <div class="card-header bg-gradient-primary text-white">
                        <h6 class="m-0 fw-bold"><i class="fas fa-filter me-2"></i>Filter Laporan</h6>
                    </div>
                    <div class="card-body">
                        <form id="reportFilterForm" method="POST">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Jenis Laporan</label>
                                    <select class="form-select" name="report_type" id="reportType">
                                        <option value="daily" <?php echo $report_type == 'daily' ? 'selected' : ''; ?>>Harian</option>
                                        <option value="weekly" <?php echo $report_type == 'weekly' ? 'selected' : ''; ?>>Mingguan</option>
                                        <option value="monthly" <?php echo $report_type == 'monthly' ? 'selected' : ''; ?>>Bulanan</option>
                                        <option value="yearly" <?php echo $report_type == 'yearly' ? 'selected' : ''; ?>>Tahunan</option>
                                        <option value="custom" <?php echo $report_type == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3" id="startDateGroup">
                                    <label class="form-label fw-bold">Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="start_date" 
                                           value="<?php echo $start_date; ?>" 
                                           id="startDate" max="<?php echo date('Y-m-d'); ?>">
                                </div>
                                
                                <div class="col-md-3" id="endDateGroup">
                                    <label class="form-label fw-bold">Tanggal Akhir</label>
                                    <input type="date" class="form-control" name="end_date" 
                                           value="<?php echo $end_date; ?>" 
                                           id="endDate" max="<?php echo date('Y-m-d'); ?>">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Tipe Grafik</label>
                                    <select class="form-select" name="chart_type" id="chartType">
                                        <option value="line" <?php echo $chart_type == 'line' ? 'selected' : ''; ?>>Line Chart</option>
                                        <option value="bar" <?php echo $chart_type == 'bar' ? 'selected' : ''; ?>>Bar Chart</option>
                                        <option value="pie" <?php echo $chart_type == 'pie' ? 'selected' : ''; ?>>Pie Chart</option>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Filter Laporan
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="resetFilter">
                                        <i class="fas fa-redo me-1"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Report Content (Untuk PDF Export) -->
                <div id="reportContent" class="report-content">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card summary-card border-start-primary h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                                Total Pemasukan
                                            </div>
                                            <div class="h5 mb-0 fw-bold text-print-dark">
                                                Rp <?php echo number_format($report_data['summary']['total_pemasukan'], 0, ',', '.'); ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo $report_data['pemasukan_count']; ?> transaksi
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <div class="summary-icon">
                                                <i class="fas fa-arrow-down text-success"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card summary-card border-start-danger h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                                Total Pengeluaran
                                            </div>
                                            <div class="h5 mb-0 fw-bold text-print-dark">
                                                Rp <?php echo number_format($report_data['summary']['total_pengeluaran'], 0, ',', '.'); ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo $report_data['pengeluaran_count']; ?> transaksi
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <div class="summary-icon">
                                                <i class="fas fa-arrow-up text-danger"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card summary-card border-start-info h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                                Saldo Periode
                                            </div>
                                            <div class="h5 mb-0 fw-bold text-print-dark">
                                                Rp <?php echo number_format($report_data['summary']['saldo'], 0, ',', '.'); ?>
                                            </div>
                                            <small class="text-muted">
                                                Selisih pemasukan & pengeluaran
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <div class="summary-icon">
                                                <i class="fas fa-wallet text-info"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card summary-card border-start-warning h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                                Total Transaksi
                                            </div>
                                            <div class="h5 mb-0 fw-bold text-print-dark">
                                                <?php echo number_format($report_data['summary']['total_transaksi'], 0, ',', '.'); ?>
                                            </div>
                                            <small class="text-muted">
                                                Rata-rata: Rp <?php echo number_format($report_data['avg_transaction'], 0, ',', '.'); ?>
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <div class="summary-icon">
                                                <i class="fas fa-exchange-alt text-warning"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Section -->
                    <div class="card shadow mb-4 chart-section">
                        <div class="card-header bg-gradient-primary text-white">
                            <h6 class="m-0 fw-bold"><i class="fas fa-chart-line me-2"></i>Grafik Keuangan</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="reportChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card shadow h-100">
                                <div class="card-header bg-gradient-success text-white">
                                    <h6 class="m-0 fw-bold"><i class="fas fa-list-alt me-2"></i>Pemasukan per Kategori</h6>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    $pemasukan_categories = array_filter($report_data['by_category'], function($item) {
                                        return $item['jenis'] == 'pemasukan';
                                    });
                                    
                                    if (!empty($pemasukan_categories)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th class="text-print-dark">Kategori</th>
                                                        <th class="text-print-dark">Jumlah</th>
                                                        <th class="text-print-dark">Transaksi</th>
                                                        <th class="text-print-dark">Persentase</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($pemasukan_categories as $category): 
                                                        $percentage = $report_data['summary']['total_pemasukan'] > 0 ? 
                                                            ($category['total'] / $report_data['summary']['total_pemasukan']) * 100 : 0;
                                                    ?>
                                                        <tr>
                                                            <td class="text-print-dark"><?php echo htmlspecialchars($category['kategori']); ?></td>
                                                            <td class="text-print-dark fw-bold">Rp <?php echo number_format($category['total'], 0, ',', '.'); ?></td>
                                                            <td class="text-print-dark"><?php echo $category['jumlah_transaksi']; ?></td>
                                                            <td>
                                                                <div class="progress" style="height: 6px;">
                                                                    <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%"></div>
                                                                </div>
                                                                <small class="text-print-dark"><?php echo number_format($percentage, 1); ?>%</small>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-3"></i>
                                            <p class="text-print-dark">Tidak ada data pemasukan pada periode ini</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow h-100">
                                <div class="card-header bg-gradient-danger text-white">
                                    <h6 class="m-0 fw-bold"><i class="fas fa-list-alt me-2"></i>Pengeluaran per Kategori</h6>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    $pengeluaran_categories = array_filter($report_data['by_category'], function($item) {
                                        return $item['jenis'] == 'pengeluaran';
                                    });
                                    
                                    if (!empty($pengeluaran_categories)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th class="text-print-dark">Kategori</th>
                                                        <th class="text-print-dark">Jumlah</th>
                                                        <th class="text-print-dark">Transaksi</th>
                                                        <th class="text-print-dark">Persentase</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($pengeluaran_categories as $category): 
                                                        $percentage = $report_data['summary']['total_pengeluaran'] > 0 ? 
                                                            ($category['total'] / $report_data['summary']['total_pengeluaran']) * 100 : 0;
                                                    ?>
                                                        <tr>
                                                            <td class="text-print-dark"><?php echo htmlspecialchars($category['kategori']); ?></td>
                                                            <td class="text-print-dark fw-bold">Rp <?php echo number_format($category['total'], 0, ',', '.'); ?></td>
                                                            <td class="text-print-dark"><?php echo $category['jumlah_transaksi']; ?></td>
                                                            <td>
                                                                <div class="progress" style="height: 6px;">
                                                                    <div class="progress-bar bg-danger" style="width: <?php echo $percentage; ?>%"></div>
                                                                </div>
                                                                <small class="text-print-dark"><?php echo number_format($percentage, 1); ?>%</small>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                                            <p class="text-print-dark">Tidak ada data pengeluaran pada periode ini</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Transaksi -->
                    <div class="card shadow">
                        <div class="card-header bg-gradient-info text-white d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold"><i class="fas fa-table me-2"></i>Detail Transaksi</h6>
                            <small class="fw-bold"><?php echo count($report_data['transactions']); ?> transaksi ditemukan</small>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($report_data['transactions'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="transactionsTable">
                                        <thead>
                                            <tr>
                                                <th class="text-print-dark">Tanggal</th>
                                                <th class="text-print-dark">Kategori</th>
                                                <th class="text-print-dark">Deskripsi</th>
                                                <th class="text-print-dark">Jenis</th>
                                                <th class="text-print-dark">Jumlah</th>
                                                <th class="text-print-dark">Metode</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data['transactions'] as $transaction): ?>
                                                <tr>
                                                    <td class="text-print-dark"><?php echo date('d M Y', strtotime($transaction['tanggal'])); ?></td>
                                                    <td class="text-print-dark"><?php echo htmlspecialchars($transaction['kategori']); ?></td>
                                                    <td class="text-print-dark"><?php echo htmlspecialchars($transaction['deskripsi']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $transaction['jenis'] == 'pemasukan' ? 'success' : 'danger'; ?> print-badge">
                                                            <?php echo $transaction['jenis'] == 'pemasukan' ? 'Pemasukan' : 'Pengeluaran'; ?>
                                                        </span>
                                                    </td>
                                                    <td class="fw-bold <?php echo $transaction['jenis'] == 'pemasukan' ? 'text-success' : 'text-danger'; ?> text-print-dark">
                                                        <?php echo $transaction['jenis'] == 'pemasukan' ? '+' : '-'; ?>
                                                        Rp <?php echo number_format($transaction['jumlah'], 0, ',', '.'); ?>
                                                    </td>
                                                    <td class="text-print-dark"><?php echo htmlspecialchars($transaction['metode_bayar'] ?? '-'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-active">
                                                <td colspan="4" class="text-end fw-bold text-print-dark">TOTAL:</td>
                                                <td class="fw-bold text-success text-print-dark">
                                                    +Rp <?php echo number_format($report_data['summary']['total_pemasukan'], 0, ',', '.'); ?>
                                                </td>
                                                <td class="fw-bold text-danger text-print-dark">
                                                    -Rp <?php echo number_format($report_data['summary']['total_pengeluaran'], 0, ',', '.'); ?>
                                                </td>
                                            </tr>
                                            <tr class="table-primary">
                                                <td colspan="4" class="text-end fw-bold text-print-dark">SALDO AKHIR:</td>
                                                <td colspan="2" class="fw-bold text-print-dark">
                                                    Rp <?php echo number_format($report_data['summary']['saldo'], 0, ',', '.'); ?>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if (count($report_data['transactions']) > 20): ?>
                                    <nav aria-label="Transaction pagination">
                                        <ul class="pagination justify-content-center">
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                                            </li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                                            <li class="page-item">
                                                <a class="page-link" href="#">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                                
                            <?php else: ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-receipt fa-4x mb-3"></i>
                                    <h5 class="text-print-dark">Tidak Ada Transaksi</h5>
                                    <p class="text-print-dark">Tidak ditemukan transaksi pada periode yang dipilih</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Report Footer -->
                    <div class="report-footer mt-4 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-print-dark">Informasi Laporan:</h6>
                                <ul class="list-unstyled">
                                    <li><small class="text-print-dark">Periode: <?php echo $display_start_date; ?> - <?php echo $display_end_date; ?></small></li>
                                    <li><small class="text-print-dark">Dibuat pada: <?php echo date('d M Y H:i:s'); ?></small></li>
                                    <li><small class="text-print-dark">Oleh: <?php echo htmlspecialchars($_SESSION['user_nama'] ?? 'User'); ?></small></li>
                                </ul>
                            </div>
                            <div class="col-md-6 text-end">
                                <h6 class="fw-bold text-print-dark">Ringkasan:</h6>
                                <ul class="list-unstyled">
                                    <li><small class="text-print-dark">Total Pemasukan: Rp <?php echo number_format($report_data['summary']['total_pemasukan'], 0, ',', '.'); ?></small></li>
                                    <li><small class="text-print-dark">Total Pengeluaran: Rp <?php echo number_format($report_data['summary']['total_pengeluaran'], 0, ',', '.'); ?></small></li>
                                    <li><small class="text-print-dark">Saldo: Rp <?php echo number_format($report_data['summary']['saldo'], 0, ',', '.'); ?></small></li>
                                </ul>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Dokumen ini dibuat otomatis oleh Sistem Finansialku. Hak Cipta © <?php echo date('Y'); ?>.
                            </small>
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
    <script>
        // Data untuk chart dari PHP
        const chartData = {
            labels: <?php echo json_encode($report_data['chart_data']['labels']); ?>,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: <?php echo json_encode($report_data['chart_data']['pemasukan']); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    tension: 0.1
                },
                {
                    label: 'Pengeluaran',
                    data: <?php echo json_encode($report_data['chart_data']['pengeluaran']); ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderWidth: 2,
                    tension: 0.1
                }
            ]
        };
        
        const chartType = '<?php echo $chart_type; ?>';
        const reportType = '<?php echo $report_type; ?>';
    </script>
    <script src="js/report.js"></script>
</body>
</html>