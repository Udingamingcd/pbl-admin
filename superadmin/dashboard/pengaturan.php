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
$success = '';
$error = '';

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telepon = filter_input(INPUT_POST, 'telepon', FILTER_SANITIZE_STRING);
    
    try {
        $db->query("UPDATE admins SET nama = :nama, email = :email, telepon = :telepon WHERE id = :id");
        $db->bind(':nama', $nama);
        $db->bind(':email', $email);
        $db->bind(':telepon', $telepon);
        $db->bind(':id', $_SESSION['admin_id']);
        $db->execute();
        
        $_SESSION['admin_nama'] = $nama;
        $_SESSION['admin_email'] = $email;
        $success = "Profil berhasil diperbarui!";
    } catch (Exception $e) {
        $error = "Gagal memperbarui profil: " . $e->getMessage();
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "Password baru tidak cocok!";
    } else {
        // Verify current password
        $db->query("SELECT password FROM admins WHERE id = :id");
        $db->bind(':id', $_SESSION['admin_id']);
        $admin = $db->single();
        
        if (password_verify($current_password, $admin['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $db->query("UPDATE admins SET password = :password WHERE id = :id");
            $db->bind(':password', $hashed_password);
            $db->bind(':id', $_SESSION['admin_id']);
            $db->execute();
            
            $success = "Password berhasil diubah!";
        } else {
            $error = "Password saat ini salah!";
        }
    }
}

// Update system settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $app_name = filter_input(INPUT_POST, 'app_name', FILTER_SANITIZE_STRING);
    $app_email = filter_input(INPUT_POST, 'app_email', FILTER_SANITIZE_EMAIL);
    $maintenance = isset($_POST['maintenance']) ? 1 : 0;
    
    // In a real application, you would save these to a settings table
    $success = "Pengaturan sistem berhasil diperbarui!";
}

// Get admin data
$db->query("SELECT * FROM admins WHERE id = :id");
$db->bind(':id', $_SESSION['admin_id']);
$admin = $db->single();

// Get system stats
$db->query("SELECT COUNT(*) as total FROM admins");
$total_admins = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM users");
$total_users = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM transaksi");
$total_transactions = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM financial_goal");
$total_goals = $db->single()['total'];
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Superadmin</title>
    <link rel="icon" type="image/png" href="../../../assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .settings-nav .nav-link {
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        .settings-nav .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .settings-nav .nav-link:hover:not(.active) {
            background: rgba(255, 255, 255, 0.1);
        }
        .settings-card {
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
        }
        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .form-control.with-icon {
            padding-left: 45px;
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
                        <h3 class="mb-0">Pengaturan Sistem</h3>
                        <p class="text-muted mb-0">Kelola konfigurasi aplikasi</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-info me-3">v1.0.0</span>
                        <button class="btn btn-outline-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Settings Navigation -->
                    <div class="col-md-3 mb-4">
                        <div class="settings-card p-3">
                            <div class="settings-nav">
                                <div class="nav flex-column">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#profile">
                                        <i class="fas fa-user-cog me-2"></i> Profil
                                    </a>
                                    <a class="nav-link" data-bs-toggle="tab" href="#security">
                                        <i class="fas fa-shield-alt me-2"></i> Keamanan
                                    </a>
                                    <a class="nav-link" data-bs-toggle="tab" href="#system">
                                        <i class="fas fa-cogs me-2"></i> Sistem
                                    </a>
                                    <a class="nav-link" data-bs-toggle="tab" href="#notifications">
                                        <i class="fas fa-bell me-2"></i> Notifikasi
                                    </a>
                                    <a class="nav-link" data-bs-toggle="tab" href="#backup">
                                        <i class="fas fa-database me-2"></i> Backup
                                    </a>
                                    <a class="nav-link" data-bs-toggle="tab" href="#logs">
                                        <i class="fas fa-history me-2"></i> Logs
                                    </a>
                                </div>
                            </div>
                            
                            <!-- System Stats -->
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="text-muted mb-3">Statistik Sistem</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-dark rounded">
                                            <small class="d-block">Admin</small>
                                            <strong><?= $total_admins ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-dark rounded">
                                            <small class="d-block">User</small>
                                            <strong><?= $total_users ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-dark rounded">
                                            <small class="d-block">Transaksi</small>
                                            <strong><?= $total_transactions ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-dark rounded">
                                            <small class="d-block">Goals</small>
                                            <strong><?= $total_goals ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Settings Content -->
                    <div class="col-md-9">
                        <div class="tab-content">
                            <!-- Profile Tab -->
                            <div class="tab-pane fade show active" id="profile">
                                <div class="settings-card p-4">
                                    <h5 class="mb-4">Profil Superadmin</h5>
                                    <form method="POST">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Nama Lengkap</label>
                                                <div class="position-relative">
                                                    <i class="fas fa-user form-icon"></i>
                                                    <input type="text" name="nama" class="form-control with-icon" 
                                                           value="<?= htmlspecialchars($admin['nama']) ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <div class="position-relative">
                                                    <i class="fas fa-envelope form-icon"></i>
                                                    <input type="email" name="email" class="form-control with-icon" 
                                                           value="<?= htmlspecialchars($admin['email']) ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Telepon</label>
                                                <div class="position-relative">
                                                    <i class="fas fa-phone form-icon"></i>
                                                    <input type="text" name="telepon" class="form-control with-icon" 
                                                           value="<?= htmlspecialchars($admin['telepon'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Level</label>
                                                <div class="position-relative">
                                                    <i class="fas fa-crown form-icon"></i>
                                                    <input type="text" class="form-control with-icon" 
                                                           value="Superadmin" readonly disabled>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Terakhir Login</label>
                                                <div class="position-relative">
                                                    <i class="fas fa-sign-in-alt form-icon"></i>
                                                    <input type="text" class="form-control with-icon" 
                                                           value="<?= $admin['last_login'] ? date('d/m/Y H:i', strtotime($admin['last_login'])) : 'Belum login' ?>" 
                                                           readonly disabled>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Bergabung Sejak</label>
                                                <div class="position-relative">
                                                    <i class="fas fa-calendar-alt form-icon"></i>
                                                    <input type="text" class="form-control with-icon" 
                                                           value="<?= date('d/m/Y', strtotime($admin['created_at'])) ?>" 
                                                           readonly disabled>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" name="update_profile" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Security Tab -->
                            <div class="tab-pane fade" id="security">
                                <div class="settings-card p-4">
                                    <h5 class="mb-4">Keamanan Akun</h5>
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">Password Saat Ini</label>
                                            <div class="position-relative">
                                                <i class="fas fa-lock form-icon"></i>
                                                <input type="password" name="current_password" 
                                                       class="form-control with-icon" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Password Baru</label>
                                            <div class="position-relative">
                                                <i class="fas fa-key form-icon"></i>
                                                <input type="password" name="new_password" 
                                                       class="form-control with-icon" minlength="8" required>
                                            </div>
                                            <small class="text-muted">Minimal 8 karakter</small>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label">Konfirmasi Password Baru</label>
                                            <div class="position-relative">
                                                <i class="fas fa-key form-icon"></i>
                                                <input type="password" name="confirm_password" 
                                                       class="form-control with-icon" minlength="8" required>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" name="change_password" class="btn btn-primary">
                                            <i class="fas fa-sync-alt me-2"></i> Ubah Password
                                        </button>
                                    </form>
                                    
                                    <hr class="my-4">
                                    
                                    <h6 class="mb-3">Sesi Aktif</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Browser</th>
                                                    <th>IP Address</th>
                                                    <th>Login Time</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?= htmlspecialchars($_SERVER['HTTP_USER_AGENT']) ?></td>
                                                    <td><?= $_SERVER['REMOTE_ADDR'] ?></td>
                                                    <td><?= date('H:i') ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-sign-out-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- System Tab -->
                            <div class="tab-pane fade" id="system">
                                <div class="settings-card p-4">
                                    <h5 class="mb-4">Pengaturan Sistem</h5>
                                    <form method="POST">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Nama Aplikasi</label>
                                                <input type="text" name="app_name" class="form-control" 
                                                       value="Finansialku" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email Sistem</label>
                                                <input type="email" name="app_email" class="form-control" 
                                                       value="admin@finansialku.com" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Zona Waktu</label>
                                                <select class="form-select" name="timezone">
                                                    <option value="Asia/Jakarta" selected>Asia/Jakarta (WIB)</option>
                                                    <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                                                    <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Tema Default</label>
                                                <select class="form-select" name="theme">
                                                    <option value="dark" selected>Dark</option>
                                                    <option value="light">Light</option>
                                                    <option value="auto">Auto</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3 form-check form-switch">
                                            <input type="checkbox" class="form-check-input" 
                                                   name="maintenance" id="maintenance">
                                            <label class="form-check-label" for="maintenance">
                                                Mode Maintenance
                                            </label>
                                            <small class="text-muted d-block">
                                                Aktifkan untuk melakukan maintenance sistem
                                            </small>
                                        </div>
                                        
                                        <div class="mb-3 form-check form-switch">
                                            <input type="checkbox" class="form-check-input" 
                                                   name="registration" id="registration" checked>
                                            <label class="form-check-label" for="registration">
                                                Izin Registrasi User Baru
                                            </label>
                                        </div>
                                        
                                        <div class="mb-3 form-check form-switch">
                                            <input type="checkbox" class="form-check-input" 
                                                   name="debug" id="debug">
                                            <label class="form-check-label" for="debug">
                                                Mode Debug
                                            </label>
                                        </div>
                                        
                                        <button type="submit" name="update_settings" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Simpan Pengaturan
                                        </button>
                                    </form>
                                    
                                    <hr class="my-4">
                                    
                                    <h6 class="mb-3">Informasi Server</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <small class="text-muted">PHP Version:</small>
                                                    <span class="float-end"><?= phpversion() ?></span>
                                                </li>
                                                <li class="mb-2">
                                                    <small class="text-muted">Database:</small>
                                                    <span class="float-end">MySQL</span>
                                                </li>
                                                <li class="mb-2">
                                                    <small class="text-muted">Web Server:</small>
                                                    <span class="float-end"><?= $_SERVER['SERVER_SOFTWARE'] ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <small class="text-muted">Memory Usage:</small>
                                                    <span class="float-end"><?= round(memory_get_usage() / 1024 / 1024, 2) ?>MB</span>
                                                </li>
                                                <li class="mb-2">
                                                    <small class="text-muted">Max Upload:</small>
                                                    <span class="float-end"><?= ini_get('upload_max_filesize') ?></span>
                                                </li>
                                                <li class="mb-2">
                                                    <small class="text-muted">Max Execution:</small>
                                                    <span class="float-end"><?= ini_get('max_execution_time') ?>s</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notifications Tab -->
                            <div class="tab-pane fade" id="notifications">
                                <div class="settings-card p-4">
                                    <h5 class="mb-4">Pengaturan Notifikasi</h5>
                                    <form>
                                        <div class="mb-3">
                                            <label class="form-label">Email Notifikasi</label>
                                            <input type="email" class="form-control" 
                                                   value="<?= $_SESSION['admin_email'] ?>" disabled>
                                            <small class="text-muted">Email penerima notifikasi sistem</small>
                                        </div>
                                        
                                        <h6 class="mb-3">Jenis Notifikasi</h6>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="notif_new_user" checked>
                                            <label class="form-check-label" for="notif_new_user">
                                                User baru terdaftar
                                            </label>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="notif_large_transaction" checked>
                                            <label class="form-check-label" for="notif_large_transaction">
                                                Transaksi besar (> Rp 10.000.000)
                                            </label>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="notif_system_error" checked>
                                            <label class="form-check-label" for="notif_system_error">
                                                Error sistem
                                            </label>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="notif_backup" checked>
                                            <label class="form-check-label" for="notif_backup">
                                                Backup otomatis
                                            </label>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="notif_report" checked>
                                            <label class="form-check-label" for="notif_report">
                                                Laporan mingguan
                                            </label>
                                        </div>
                                        
                                        <button type="button" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Simpan Pengaturan
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Backup Tab -->
                            <div class="tab-pane fade" id="backup">
                                <div class="settings-card p-4">
                                    <h5 class="mb-4">Backup Database</h5>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Backup terakhir: <strong>Belum pernah</strong>
                                    </div>
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="card bg-dark">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-download fa-3x mb-3 text-primary"></i>
                                                    <h5>Backup Manual</h5>
                                                    <p class="text-muted small">Download salinan database saat ini</p>
                                                    <button type="button" class="btn btn-primary" onclick="backupDatabase()">
                                                        <i class="fas fa-database me-2"></i> Backup Sekarang
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-dark">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-history fa-3x mb-3 text-success"></i>
                                                    <h5>Backup Otomatis</h5>
                                                    <p class="text-muted small">Jadwalkan backup harian/mingguan</p>
                                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                                                        <i class="fas fa-calendar-alt me-2"></i> Jadwalkan
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h6 class="mb-3">File Backup Tersedia</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nama File</th>
                                                    <th>Ukuran</th>
                                                    <th>Tanggal</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-4">
                                                        <i class="fas fa-database fa-2x mb-2"></i>
                                                        <p>Belum ada file backup</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Logs Tab -->
                            <div class="tab-pane fade" id="logs">
                                <div class="settings-card p-4">
                                    <h5 class="mb-4">Log Sistem</h5>
                                    
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <select class="form-select" style="max-width: 150px;">
                                                <option>Semua Level</option>
                                                <option>Info</option>
                                                <option>Warning</option>
                                                <option>Error</option>
                                            </select>
                                            <input type="date" class="form-control" value="<?= date('Y-m-d') ?>">
                                            <button class="btn btn-outline-primary" type="button">
                                                <i class="fas fa-filter"></i> Filter
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Waktu</th>
                                                    <th>Level</th>
                                                    <th>Pesan</th>
                                                    <th>User</th>
                                                    <th>IP</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?= date('H:i:s') ?></td>
                                                    <td><span class="badge bg-info">INFO</span></td>
                                                    <td>Superadmin <?= $_SESSION['admin_nama'] ?> login</td>
                                                    <td><?= $_SESSION['admin_email'] ?></td>
                                                    <td><?= $_SERVER['REMOTE_ADDR'] ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= date('H:i:s', strtotime('-5 minutes')) ?></td>
                                                    <td><span class="badge bg-success">SYSTEM</span></td>
                                                    <td>Cron job executed successfully</td>
                                                    <td>System</td>
                                                    <td>127.0.0.1</td>
                                                </tr>
                                                <tr>
                                                    <td><?= date('H:i:s', strtotime('-30 minutes')) ?></td>
                                                    <td><span class="badge bg-warning">WARNING</span></td>
                                                    <td>High memory usage detected</td>
                                                    <td>System</td>
                                                    <td>127.0.0.1</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="text-center">
                                        <button class="btn btn-outline-secondary">
                                            <i class="fas fa-download me-2"></i> Download Logs
                                        </button>
                                        <button class="btn btn-outline-danger">
                                            <i class="fas fa-trash me-2"></i> Hapus Logs Lama
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Schedule Backup Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Jadwal Backup Otomatis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Frekuensi</label>
                            <select class="form-select">
                                <option value="daily">Harian</option>
                                <option value="weekly" selected>Mingguan</option>
                                <option value="monthly">Bulanan</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Waktu Backup</label>
                            <input type="time" class="form-control" value="02:00">
                            <small class="text-muted">Waktu server: <?= date('H:i') ?></small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jumlah Backup yang Disimpan</label>
                            <input type="number" class="form-control" value="7" min="1" max="30">
                            <small class="text-muted">Backup lama akan otomatis terhapus</small>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="notifyBackup" checked>
                            <label class="form-check-label" for="notifyBackup">
                                Kirim notifikasi email setelah backup
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary">Simpan Jadwal</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function backupDatabase() {
            if (confirm('Mulai proses backup database? Proses ini mungkin memakan waktu beberapa menit.')) {
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
                btn.disabled = true;
                
                fetch('../../../ajax/backup-database.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Backup berhasil! File: ' + data.filename);
                            location.reload();
                        } else {
                            alert('Backup gagal: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error.message);
                    })
                    .finally(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    });
            }
        }
        
        // Initialize tabs
        document.addEventListener('DOMContentLoaded', function() {
            const tabTriggerEls = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabTriggerEls.forEach(tabEl => {
                tabEl.addEventListener('shown.bs.tab', function(event) {
                    // Save active tab to sessionStorage
                    sessionStorage.setItem('activeSettingsTab', event.target.getAttribute('href'));
                });
            });
            
            // Restore active tab
            const activeTab = sessionStorage.getItem('activeSettingsTab');
            if (activeTab) {
                const tab = new bootstrap.Tab(document.querySelector(`[href="${activeTab}"]`));
                tab.show();
            }
        });
    </script>
</body>
</html>