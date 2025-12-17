<?php
session_start();
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/koneksi.php';

// Cek apakah user adalah superadmin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_level'] !== 'superadmin') {
    header('Location: ../auth/login.php');
    exit();
}

$db = new Database();

// Default date range (last 30 days)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['type']) ? $_GET['type'] : 'overview';

// Generate reports based on type
$report_data = [];
$report_title = '';

switch ($report_type) {
    case 'users':
        $report_title = 'Laporan User';
        // User growth report
        $db->query("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as new_users,
                SUM(COUNT(*)) OVER (ORDER BY DATE(created_at)) as total_users
            FROM users
            WHERE created_at BETWEEN :start_date AND :end_date
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $db->bind(':start_date', $start_date . ' 00:00:00');
        $db->bind(':end_date', $end_date . ' 23:59:59');
        $report_data = $db->resultSet();
        break;
        
    case 'transactions':
        $report_title = 'Laporan Transaksi';
        // Transaction summary by day
        $db->query("
            SELECT 
                tanggal as date,
                SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END) as income,
                SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END) as expense,
                COUNT(*) as transaction_count
            FROM transaksi
            WHERE tanggal BETWEEN :start_date AND :end_date
            GROUP BY tanggal
            ORDER BY tanggal
        ");
        $db->bind(':start_date', $start_date);
        $db->bind(':end_date', $end_date);
        $report_data = $db->resultSet();
        break;
        
    case 'financial':
        $report_title = 'Laporan Keuangan';
        // Financial summary
        $db->query("
            SELECT 
                u.nama as user_name,
                u.email,
                COUNT(t.id) as transaction_count,
                SUM(CASE WHEN t.jenis = 'pemasukan' THEN t.jumlah ELSE 0 END) as total_income,
                SUM(CASE WHEN t.jenis = 'pengeluaran' THEN t.jumlah ELSE 0 END) as total_expense,
                (SUM(CASE WHEN t.jenis = 'pemasukan' THEN t.jumlah ELSE 0 END) - 
                 SUM(CASE WHEN t.jenis = 'pengeluaran' THEN t.jumlah ELSE 0 END)) as net_balance
            FROM users u
            LEFT JOIN transaksi t ON u.id = t.user_id 
                AND t.tanggal BETWEEN :start_date AND :end_date
            GROUP BY u.id
            HAVING transaction_count > 0 OR net_balance != 0
            ORDER BY net_balance DESC
        ");
        $db->bind(':start_date', $start_date);
        $db->bind(':end_date', $end_date);
        $report_data = $db->resultSet();
        break;
        
    case 'goals':
        $report_title = 'Laporan Financial Goals';
        $db->query("
            SELECT 
                fg.*,
                u.nama as user_name,
                u.email,
                (fg.terkumpul / fg.target_jumlah * 100) as progress_percent
            FROM financial_goal fg
            JOIN users u ON fg.user_id = u.id
            WHERE fg.created_at BETWEEN :start_date AND :end_date
            ORDER BY fg.created_at DESC
        ");
        $db->bind(':start_date', $start_date . ' 00:00:00');
        $db->bind(':end_date', $end_date . ' 23:59:59');
        $report_data = $db->resultSet();
        break;
        
    default: // overview
        $report_title = 'Laporan Overview';
        // Get summary data
        $db->query("
            SELECT 
                (SELECT COUNT(*) FROM users WHERE created_at BETWEEN :start_date AND :end_date) as new_users,
                (SELECT COUNT(*) FROM transaksi WHERE tanggal BETWEEN :start_date AND :end_date) as transactions,
                (SELECT SUM(jumlah) FROM transaksi WHERE jenis = 'pemasukan' AND tanggal BETWEEN :start_date AND :end_date) as total_income,
                (SELECT SUM(jumlah) FROM transaksi WHERE jenis = 'pengeluaran' AND tanggal BETWEEN :start_date AND :end_date) as total_expense,
                (SELECT COUNT(*) FROM financial_goal WHERE created_at BETWEEN :start_date AND :end_date) as new_goals,
                (SELECT COUNT(*) FROM budget WHERE created_at BETWEEN :start_date AND :end_date) as new_budgets
        ");
        $db->bind(':start_date', $start_date . ' 00:00:00');
        $db->bind(':end_date', $end_date . ' 23:59:59');
        $report_data = $db->single();
        break;
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Superadmin</title>
    <link rel="icon" type="image/png" href="../../../assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-card {
            border-radius: 10px;
            border: none;
            transition: all 0.3s;
            height: 100%;
        }
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .report-header {
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding: 20px;
            background: rgba(0,0,0,0.2);
        }
        .chart-container {
            height: 300px;
            position: relative;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            line-height: 1;
        }
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .progress-goal {
            height: 25px;
            border-radius: 12px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__ . '/../../../php/includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-0">Laporan & Analytics</h3>
                        <p class="text-muted mb-0"><?= $report_title ?> - <?= date('d M Y', strtotime($start_date)) ?> s/d <?= date('d M Y', strtotime($end_date)) ?></p>
                    </div>
                    <div>
                        <button onclick="window.print()" class="btn btn-secondary">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button onclick="exportReport()" class="btn btn-success">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                    </div>
                </div>
                
                <!-- Date Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label>Tanggal Mulai</label>
                                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>" max="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Tanggal Akhir</label>
                                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>" max="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Jenis Laporan</label>
                                <select name="type" class="form-select" onchange="this.form.submit()">
                                    <option value="overview" <?= $report_type === 'overview' ? 'selected' : '' ?>>Overview</option>
                                    <option value="users" <?= $report_type === 'users' ? 'selected' : '' ?>>Laporan User</option>
                                    <option value="transactions" <?= $report_type === 'transactions' ? 'selected' : '' ?>>Laporan Transaksi</option>
                                    <option value="financial" <?= $report_type === 'financial' ? 'selected' : '' ?>>Laporan Keuangan</option>
                                    <option value="goals" <?= $report_type === 'goals' ? 'selected' : '' ?>>Laporan Financial Goals</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Report Content -->
                <div class="card">
                    <div class="card-body">
                        <?php if ($report_type === 'overview'): ?>
                            <!-- Overview Report -->
                            <div class="row mb-4">
                                <div class="col-md-3 mb-3">
                                    <div class="report-card bg-primary text-white p-4">
                                        <div class="stat-number"><?= $report_data['new_users'] ?? 0 ?></div>
                                        <div class="stat-label">User Baru</div>
                                        <i class="fas fa-users fa-2x opacity-50 position-absolute" style="bottom: 20px; right: 20px;"></i>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="report-card bg-success text-white p-4">
                                        <div class="stat-number"><?= $report_data['transactions'] ?? 0 ?></div>
                                        <div class="stat-label">Transaksi</div>
                                        <i class="fas fa-exchange-alt fa-2x opacity-50 position-absolute" style="bottom: 20px; right: 20px;"></i>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="report-card bg-info text-white p-4">
                                        <div class="stat-number">Rp <?= number_format($report_data['total_income'] ?? 0, 0, ',', '.') ?></div>
                                        <div class="stat-label">Total Pemasukan</div>
                                        <i class="fas fa-money-bill-wave fa-2x opacity-50 position-absolute" style="bottom: 20px; right: 20px;"></i>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="report-card bg-warning text-white p-4">
                                        <div class="stat-number"><?= ($report_data['new_goals'] ?? 0) + ($report_data['new_budgets'] ?? 0) ?></div>
                                        <div class="stat-label">Goals & Budget</div>
                                        <i class="fas fa-bullseye fa-2x opacity-50 position-absolute" style="bottom: 20px; right: 20px;"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Charts -->
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Aktivitas User</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="userActivityChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Distribusi Transaksi</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="transactionDistributionChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        <?php elseif ($report_type === 'users'): ?>
                            <!-- Users Report -->
                            <h5 class="mb-3">Pertumbuhan User</h5>
                            <div class="chart-container mb-4">
                                <canvas id="userGrowthChart"></canvas>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>User Baru</th>
                                            <th>Total User</th>
                                            <th>Pertumbuhan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $row): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($row['date'])) ?></td>
                                            <td><?= $row['new_users'] ?></td>
                                            <td><?= $row['total_users'] ?></td>
                                            <td>
                                                <?php 
                                                $growth = $row['new_users'] > 0 ? '+'.$row['new_users'] : $row['new_users'];
                                                $class = $row['new_users'] > 0 ? 'text-success' : ($row['new_users'] < 0 ? 'text-danger' : '');
                                                ?>
                                                <span class="<?= $class ?>">
                                                    <i class="fas fa-arrow-<?= $row['new_users'] > 0 ? 'up' : ($row['new_users'] < 0 ? 'down' : 'right') ?>"></i>
                                                    <?= $growth ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                        <?php elseif ($report_type === 'transactions'): ?>
                            <!-- Transactions Report -->
                            <h5 class="mb-3">Ringkasan Transaksi Harian</h5>
                            <div class="chart-container mb-4">
                                <canvas id="transactionChart"></canvas>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Jumlah Transaksi</th>
                                            <th>Pemasukan</th>
                                            <th>Pengeluaran</th>
                                            <th>Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $row): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($row['date'])) ?></td>
                                            <td><?= $row['transaction_count'] ?></td>
                                            <td class="text-success">Rp <?= number_format($row['income'], 0, ',', '.') ?></td>
                                            <td class="text-danger">Rp <?= number_format($row['expense'], 0, ',', '.') ?></td>
                                            <td class="<?= ($row['income'] - $row['expense']) >= 0 ? 'text-success' : 'text-danger' ?>">
                                                Rp <?= number_format($row['income'] - $row['expense'], 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                        <?php elseif ($report_type === 'financial'): ?>
                            <!-- Financial Report -->
                            <h5 class="mb-3">Ringkasan Keuangan per User</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>Jumlah Transaksi</th>
                                            <th>Total Pemasukan</th>
                                            <th>Total Pengeluaran</th>
                                            <th>Saldo Bersih</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['user_name']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= $row['transaction_count'] ?></td>
                                            <td class="text-success">Rp <?= number_format($row['total_income'], 0, ',', '.') ?></td>
                                            <td class="text-danger">Rp <?= number_format($row['total_expense'], 0, ',', '.') ?></td>
                                            <td class="<?= $row['net_balance'] >= 0 ? 'text-success' : 'text-danger' ?> fw-bold">
                                                Rp <?= number_format($row['net_balance'], 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                        <?php elseif ($report_type === 'goals'): ?>
                            <!-- Goals Report -->
                            <h5 class="mb-3">Financial Goals User</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Nama Goal</th>
                                            <th>Target</th>
                                            <th>Terkumpul</th>
                                            <th>Progress</th>
                                            <th>Tenggat</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['user_name']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_goal']) ?></td>
                                            <td>Rp <?= number_format($row['target_jumlah'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($row['terkumpul'], 0, ',', '.') ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress-goal flex-grow-1 me-2">
                                                        <div class="progress h-100">
                                                            <div class="progress-bar bg-success" 
                                                                 style="width: <?= min(100, $row['progress_percent']) ?>%">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span><?= round($row['progress_percent'], 1) ?>%</span>
                                                </div>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($row['tenggat_waktu'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $row['status'] === 'tercapai' ? 'success' : 
                                                    ($row['status'] === 'aktif' ? 'primary' : 'danger')
                                                ?>">
                                                    <?= ucfirst($row['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportReport() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = `../../../ajax/export-report.php?${params.toString()}`;
        }
        
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($report_type === 'overview'): ?>
                // User Activity Chart
                const userActivityCtx = document.getElementById('userActivityChart').getContext('2d');
                new Chart(userActivityCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                        datasets: [{
                            label: 'User Aktif',
                            data: [12, 19, 8, 15, 22, 18],
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
                
                // Transaction Distribution Chart
                const transactionCtx = document.getElementById('transactionDistributionChart').getContext('2d');
                new Chart(transactionCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pemasukan', 'Pengeluaran'],
                        datasets: [{
                            data: [<?= $report_data['total_income'] ?? 0 ?>, <?= $report_data['total_expense'] ?? 0 ?>],
                            backgroundColor: ['#28a745', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
                
            <?php elseif ($report_type === 'users'): ?>
                // User Growth Chart
                const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
                new Chart(userGrowthCtx, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode(array_map(function($row) {
                            return date('d M', strtotime($row['date']));
                        }, $report_data)) ?>,
                        datasets: [{
                            label: 'Total User',
                            data: <?= json_encode(array_column($report_data, 'total_users')) ?>,
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
                
            <?php elseif ($report_type === 'transactions'): ?>
                // Transaction Chart
                const transactionChartCtx = document.getElementById('transactionChart').getContext('2d');
                new Chart(transactionChartCtx, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode(array_map(function($row) {
                            return date('d M', strtotime($row['date']));
                        }, $report_data)) ?>,
                        datasets: [{
                            label: 'Pemasukan',
                            data: <?= json_encode(array_column($report_data, 'income')) ?>,
                            backgroundColor: '#28a745'
                        }, {
                            label: 'Pengeluaran',
                            data: <?= json_encode(array_column($report_data, 'expense')) ?>,
                            backgroundColor: '#dc3545'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                    }
                                }
                            }
                        }
                    }
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>