<?php
session_start();
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/koneksi.php';

// Cek apakah user adalah superadmin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_level'] !== 'superadmin') {
    header('Location: ../auth/login.php');
    exit();
}

// Update last activity
$db = new Database();
$db->query("UPDATE admins SET last_activity = NOW() WHERE id = :id");
$db->bind(':id', $_SESSION['admin_id']);
$db->execute();

// Ambil statistik untuk superadmin
$stats = [];

// Total Admin
$db->query("SELECT COUNT(*) as total FROM admins WHERE level = 'admin' AND status = 'aktif'");
$stats['total_admin'] = $db->single()['total'];

// Total Superadmin
$db->query("SELECT COUNT(*) as total FROM admins WHERE level = 'superadmin' AND status = 'aktif'");
$stats['total_superadmin'] = $db->single()['total'];

// Total User
$db->query("SELECT COUNT(*) as total FROM users");
$stats['total_user'] = $db->single()['total'];

// User Baru (Bulan Ini)
$db->query("SELECT COUNT(*) as total FROM users WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$stats['new_users_month'] = $db->single()['total'];

// Total Transaksi
$db->query("SELECT COUNT(*) as total FROM transaksi");
$stats['total_transaksi'] = $db->single()['total'];

// Total Budget
$db->query("SELECT COUNT(*) as total FROM budget");
$stats['total_budget'] = $db->single()['total'];

// Total Financial Goals
$db->query("SELECT COUNT(*) as total FROM financial_goal");
$stats['total_goals'] = $db->single()['total'];

// Pemasukan Bulan Ini
$db->query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'pemasukan' AND MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())");
$stats['income_month'] = $db->single()['total'];

// Pengeluaran Bulan Ini
$db->query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'pengeluaran' AND MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())");
$stats['expense_month'] = $db->single()['total'];

// Saldo Bulan Ini
$stats['balance_month'] = $stats['income_month'] - $stats['expense_month'];

// Admin Terbaru (5 terbaru)
$db->query("SELECT id, nama, email, status, last_login, created_at FROM admins WHERE level = 'admin' ORDER BY created_at DESC LIMIT 5");
$recent_admins = $db->resultSet();

// User Terbaru (5 terbaru)
$db->query("SELECT id, nama, email, last_login, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $db->resultSet();

// Aktivitas Terbaru (login terakhir admin)
$db->query("SELECT nama, email, last_login FROM admins WHERE last_login IS NOT NULL ORDER BY last_login DESC LIMIT 5");
$recent_activities = $db->resultSet();

// Chart data untuk 6 bulan terakhir
$chart_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    
    $db->query("SELECT COALESCE(SUM(jumlah), 0) as income FROM transaksi WHERE jenis = 'pemasukan' AND DATE_FORMAT(tanggal, '%Y-%m') = :month");
    $db->bind(':month', $month);
    $income = $db->single()['income'];
    
    $db->query("SELECT COALESCE(SUM(jumlah), 0) as expense FROM transaksi WHERE jenis = 'pengeluaran' AND DATE_FORMAT(tanggal, '%Y-%m') = :month");
    $db->bind(':month', $month);
    $expense = $db->single()['expense'];
    
    $chart_data[] = [
        'month' => $month_name,
        'income' => (float)$income,
        'expense' => (float)$expense,
        'profit' => (float)($income - $expense)
    ];
}

// Convert chart data to JSON for JavaScript
$chart_json = json_encode($chart_data);
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Dashboard - Finansialku</title>
    <link rel="icon" type="image/png" href="../../../assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .superadmin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #4a235a 100%);
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        .sidebar-brand {
            padding: 25px 20px;
            text-align: center;
            color: white;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .user-profile {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 2rem;
            border: 3px solid white;
            box-shadow: 0 0 15px rgba(255,255,255,0.2);
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        .sidebar-menu a:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left: 4px solid #667eea;
        }
        .sidebar-menu a.active {
            color: white;
            background: rgba(102, 126, 234, 0.2);
            border-left: 4px solid #667eea;
        }
        .sidebar-menu i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            transition: all 0.3s;
            overflow: hidden;
            position: relative;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .chart-container {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 20px;
            backdrop-filter: blur(10px);
        }
        .badge-superadmin {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
        }
        .activity-item {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            background: rgba(255,255,255,0.05);
            border-left: 4px solid #667eea;
        }
        .bg-gradient-purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .bg-gradient-blue {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        }
        .bg-gradient-green {
            background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
        }
        .bg-gradient-orange {
            background: linear-gradient(135deg, #f46b45 0%, #eea849 100%);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 superadmin-sidebar">
                <div class="sidebar-brand">
                    <img src="../../../assets/icons/Dompt.png" alt="Logo" height="40" class="mb-2">
                    <h5 class="mb-0">Finansialku</h5>
                    <small class="text-white-50">Superadmin Panel</small>
                </div>
                
                <div class="user-profile">
                    <div class="user-avatar">
                        <i class="fas fa-crown"></i>
                    </div>
                    <h6 class="text-white mb-1"><?= htmlspecialchars($_SESSION['admin_nama']) ?></h6>
                    <span class="badge badge-superadmin">SUPERADMIN</span>
                    <p class="text-white-50 small mt-2"><?= htmlspecialchars($_SESSION['admin_email']) ?></p>
                </div>
                
                <div class="sidebar-menu">
                    <a href="index.php" class="active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="kelola-admin.php">
                        <i class="fas fa-users-cog"></i> Kelola Admin
                    </a>
                    <a href="kelola-user.php">
                        <i class="fas fa-user-friends"></i> Kelola User
                    </a>
                    <a href="transaksi.php">
                        <i class="fas fa-exchange-alt"></i> Transaksi
                    </a>
                    <a href="laporan.php">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                    <a href="pengaturan.php">
                        <i class="fas fa-cogs"></i> Pengaturan
                    </a>
                    <a href="../../admin/dashboard/index.php" class="mt-4">
                        <i class="fas fa-user-shield"></i> Admin Panel
                    </a>
                    <a href="../logout.php" class="text-danger mt-4">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-0">Superadmin Dashboard</h3>
                        <p class="text-muted mb-0">Selamat datang, <span class="text-primary"><?= htmlspecialchars($_SESSION['admin_nama']) ?></span></p>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <small class="text-muted">Server Time:</small>
                            <div id="server-time" class="fw-bold"><?= date('d/m/Y H:i:s') ?></div>
                        </div>
                        <button class="btn btn-outline-primary" onclick="refreshPage()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-gradient-purple text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0"><?= $stats['total_superadmin'] + $stats['total_admin'] ?></h2>
                                        <p class="mb-0">Total Admin</p>
                                        <small><?= $stats['total_superadmin'] ?> Superadmin, <?= $stats['total_admin'] ?> Admin</small>
                                    </div>
                                    <i class="fas fa-user-shield fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-gradient-blue text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0"><?= $stats['total_user'] ?></h2>
                                        <p class="mb-0">Total User</p>
                                        <small><?= $stats['new_users_month'] ?> baru bulan ini</small>
                                    </div>
                                    <i class="fas fa-users fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-gradient-green text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0">Rp <?= number_format($stats['income_month'], 0, ',', '.') ?></h2>
                                        <p class="mb-0">Pemasukan Bulan Ini</p>
                                        <small>Rp <?= number_format($stats['balance_month'], 0, ',', '.') ?> saldo</small>
                                    </div>
                                    <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-gradient-orange text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0"><?= $stats['total_transaksi'] ?></h2>
                                        <p class="mb-0">Total Transaksi</p>
                                        <small><?= $stats['total_budget'] ?> Budget, <?= $stats['total_goals'] ?> Goals</small>
                                    </div>
                                    <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts & Tables Row -->
                <div class="row">
                    <!-- Left Column: Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Grafik Keuangan 6 Bulan Terakhir</h5>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-light" onclick="updateChart('income')">Pemasukan</button>
                                    <button class="btn btn-sm btn-outline-light" onclick="updateChart('expense')">Pengeluaran</button>
                                    <button class="btn btn-sm btn-outline-light active" onclick="updateChart('profit')">Profit</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="financeChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column: Aktivitas Terbaru -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">Aktivitas Login Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars($activity['nama']) ?></strong>
                                        <small class="text-muted"><?= $activity['last_login'] ? date('H:i', strtotime($activity['last_login'])) : '-' ?></small>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($activity['email']) ?></small>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (empty($recent_activities)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-history fa-2x mb-2"></i>
                                    <p>Tidak ada aktivitas terbaru</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tables Row -->
                <div class="row">
                    <!-- Admin Terbaru -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Admin Terbaru</h5>
                                <a href="kelola-admin.php" class="btn btn-sm btn-light">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Login Terakhir</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_admins as $admin): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($admin['nama']) ?></td>
                                                <td><?= htmlspecialchars($admin['email']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $admin['status'] == 'aktif' ? 'success' : 'danger' ?>">
                                                        <?= ucfirst($admin['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= $admin['last_login'] ? date('d/m H:i', strtotime($admin['last_login'])) : 'Belum login' ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                            <?php if (empty($recent_admins)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">
                                                    Tidak ada data admin
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Terbaru -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">User Terbaru</h5>
                                <a href="kelola-user.php" class="btn btn-sm btn-light">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Email</th>
                                                <th>Bergabung</th>
                                                <th>Login Terakhir</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_users as $user): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($user['nama']) ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                                <td><?= $user['last_login'] ? date('d/m H:i', strtotime($user['last_login'])) : 'Belum login' ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                            <?php if (empty($recent_users)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">
                                                    Tidak ada data user
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card border-0 shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="kelola-admin.php?action=add" class="btn btn-primary w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                                    <span>Tambah Admin</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="laporan.php?type=users" class="btn btn-success w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-file-export fa-2x mb-2"></i>
                                    <span>Export User</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="pengaturan.php" class="btn btn-info w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-cogs fa-2x mb-2"></i>
                                    <span>Pengaturan</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="backup.php" class="btn btn-warning w-100 py-3 d-flex flex-column align-items-center">
                                    <i class="fas fa-database fa-2x mb-2"></i>
                                    <span>Backup Data</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="mt-4 text-center text-muted">
                    <small>
                        &copy; 2025 Finansialku Superadmin Panel | 
                        <span id="online-users">0</span> User Online | 
                        Memory Usage: <span id="memory-usage"><?= round(memory_get_usage() / 1024 / 1024, 2) ?>MB</span>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart Data
        const chartData = <?= $chart_json ?>;
        let currentChartType = 'profit';
        let financeChart = null;
        
        // Initialize Chart
        function initChart() {
            const ctx = document.getElementById('financeChart').getContext('2d');
            const labels = chartData.map(item => item.month);
            const incomeData = chartData.map(item => item.income);
            const expenseData = chartData.map(item => item.expense);
            const profitData = chartData.map(item => item.profit);
            
            const datasets = {
                'income': [{
                    label: 'Pemasukan',
                    data: incomeData,
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }],
                'expense': [{
                    label: 'Pengeluaran',
                    data: expenseData,
                    backgroundColor: 'rgba(220, 53, 69, 0.2)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }],
                'profit': [{
                    label: 'Profit',
                    data: profitData,
                    backgroundColor: 'rgba(23, 162, 184, 0.2)',
                    borderColor: 'rgba(23, 162, 184, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            };
            
            financeChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets[currentChartType]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#fff'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#fff',
                                callback: function(value) {
                                    return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#fff'
                            }
                        }
                    }
                }
            });
        }
        
        // Update Chart Type
        function updateChart(type) {
            currentChartType = type;
            
            // Update active button
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update chart
            if (financeChart) {
                financeChart.destroy();
            }
            initChart();
        }
        
        // Update Server Time
        function updateServerTime() {
            const now = new Date();
            const options = { 
                timeZone: 'Asia/Jakarta',
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            const formatter = new Intl.DateTimeFormat('id-ID', options);
            document.getElementById('server-time').textContent = formatter.format(now);
        }
        
        // Refresh Online Users
        function refreshOnlineUsers() {
            fetch('../../../ajax/cek-online-users.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('online-users').textContent = data.online_users;
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Refresh Page
        function refreshPage() {
            event.target.classList.add('fa-spin');
            setTimeout(() => {
                location.reload();
            }, 300);
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize chart
            initChart();
            
            // Start timers
            setInterval(updateServerTime, 1000);
            setInterval(refreshOnlineUsers, 30000);
            
            // Initial calls
            updateServerTime();
            refreshOnlineUsers();
            
            // Check for session timeout
            setInterval(() => {
                fetch('../../../ajax/cek-session.php')
                    .then(response => response.json())
                    .then(data => {
                        if (!data.valid) {
                            window.location.href = '../auth/login.php';
                        }
                    });
            }, 60000);
        });
    </script>
</body>
</html>