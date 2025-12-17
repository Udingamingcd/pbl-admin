<?php
session_start();
require_once '../../php/config.php';
require_once '../../php/koneksi.php';
require_once '../auth.php'; // File autentikasi admin

// Ambil statistik
$db = new Database();

// Jumlah user
$db->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $db->single()['total_users'];

// User online (dalam 5 menit terakhir)
$db->query("SELECT COUNT(*) as online_users FROM users WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
$online_users = $db->single()['online_users'];

// Total transaksi hari ini
$db->query("SELECT COUNT(*) as today_transactions FROM transaksi WHERE DATE(tanggal) = CURDATE()");
$today_transactions = $db->single()['today_transactions'];

// Total pemasukan bulan ini
$db->query("SELECT SUM(jumlah) as monthly_income FROM transaksi 
           WHERE jenis = 'pemasukan' AND MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())");
$monthly_income = $db->single()['monthly_income'] ?: 0;

// User terbaru (5 user)
$db->query("SELECT nama, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $db->resultSet();
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Finansialku</title>
    <link rel="icon" type="image/png" href="../../assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../css/dashboard.css" rel="stylesheet">
    <style>
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .sidebar-menu a {
            display: block;
            padding: 10px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu a:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .sidebar-menu a.active {
            color: white;
            background: rgba(255,255,255,0.2);
            border-left: 4px solid white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 admin-sidebar">
                <div class="sidebar-brand">
                    <img src="../../assets/icons/Dompt.png" alt="Logo" height="40" class="mb-2">
                    <h5 class="mb-0">Finansialku Admin</h5>
                    <small class="text-white-50"><?= $_SESSION['admin_nama'] ?></small>
                </div>
                
                <div class="sidebar-menu">
                    <a href="index.php" class="active">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a href="kelola-user.php">
                        <i class="fas fa-users me-2"></i> Kelola User
                    </a>
                    <a href="transaksi.php">
                        <i class="fas fa-exchange-alt me-2"></i> Transaksi
                    </a>
                    <a href="laporan.php">
                        <i class="fas fa-chart-bar me-2"></i> Laporan
                    </a>
                    <?php if ($_SESSION['admin_level'] === 'superadmin'): ?>
                    <a href="../../superadmin/dashboard/index.php">
                        <i class="fas fa-crown me-2"></i> Superadmin Panel
                    </a>
                    <?php endif; ?>
                    <a href="../logout.php" class="text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-3">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Dashboard Admin</h3>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary me-3">
                            <i class="fas fa-user me-1"></i> <?= $_SESSION['admin_level'] ?>
                        </span>
                        <span class="text-muted">
                            <i class="far fa-clock me-1"></i> <?= date('d/m/Y H:i:s') ?>
                        </span>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0"><?= $total_users ?></h2>
                                        <p class="mb-0">Total User</p>
                                    </div>
                                    <i class="fas fa-users fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0"><?= $online_users ?></h2>
                                        <p class="mb-0">User Online</p>
                                    </div>
                                    <i class="fas fa-user-check fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0"><?= $today_transactions ?></h2>
                                        <p class="mb-0">Transaksi Hari Ini</p>
                                    </div>
                                    <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0">Rp <?= number_format($monthly_income, 0, ',', '.') ?></h2>
                                        <p class="mb-0">Pemasukan Bulan Ini</p>
                                    </div>
                                    <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Users -->
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
                                        <th>Tanggal Bergabung</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['nama']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/dashboard.js"></script>
</body>
</html>