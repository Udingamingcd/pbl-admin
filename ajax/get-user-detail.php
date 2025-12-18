<?php
session_start();
require_once '../php/config.php';
require_once '../php/koneksi.php';

// Cek apakah user adalah superadmin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_level'] !== 'superadmin') {
    http_response_code(403);
    exit('Access Denied');
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('Invalid Request');
}

$db = new Database();
$user_id = (int)$_GET['id'];

// Get user details
$db->query("SELECT * FROM users WHERE id = :id");
$db->bind(':id', $user_id);
$user = $db->single();

if (!$user) {
    echo '<div class="alert alert-danger">User tidak ditemukan</div>';
    exit();
}

// Get user stats
$db->query("SELECT COUNT(*) as total FROM transaksi WHERE user_id = :id");
$db->bind(':id', $user_id);
$transactions = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM budget WHERE user_id = :id");
$db->bind(':id', $user_id);
$budgets = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM financial_goal WHERE user_id = :id");
$db->bind(':id', $user_id);
$goals = $db->single()['total'];

// Get recent transactions
$db->query("SELECT * FROM transaksi WHERE user_id = :id ORDER BY tanggal DESC LIMIT 5");
$db->bind(':id', $user_id);
$recent_transactions = $db->resultSet();
?>

<div class="row">
    <div class="col-md-4">
        <div class="text-center mb-4">
            <div class="mb-3">
                <?php if ($user['foto_profil']): ?>
                    <img src="../../../uploads/profil/<?= $user['foto_profil'] ?>" 
                         class="rounded-circle" width="100" height="100">
                <?php else: ?>
                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px;">
                        <i class="fas fa-user fa-3x text-white"></i>
                    </div>
                <?php endif; ?>
            </div>
            <h5><?= htmlspecialchars($user['nama']) ?></h5>
            <span class="badge bg-<?= $user['email_verified'] ? 'success' : 'warning' ?>">
                <?= $user['email_verified'] ? 'Terverifikasi' : 'Belum Verifikasi' ?>
            </span>
        </div>
        
        <div class="list-group mb-4">
            <div class="list-group-item d-flex justify-content-between">
                <span>Email</span>
                <strong><?= htmlspecialchars($user['email']) ?></strong>
            </div>
            <div class="list-group-item d-flex justify-content-between">
                <span>Telepon</span>
                <strong><?= htmlspecialchars($user['telepon'] ?: '-') ?></strong>
            </div>
            <div class="list-group-item d-flex justify-content-between">
                <span>Bergabung</span>
                <strong><?= date('d/m/Y', strtotime($user['created_at'])) ?></strong>
            </div>
            <div class="list-group-item d-flex justify-content-between">
                <span>Login Terakhir</span>
                <strong><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Belum login' ?></strong>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <h6>Statistik User</h6>
        <div class="row mb-4">
            <div class="col-4">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body p-2">
                        <h6 class="mb-0">Transaksi</h6>
                        <h4 class="mb-0"><?= $transactions ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card text-center bg-success text-white">
                    <div class="card-body p-2">
                        <h6 class="mb-0">Budget</h6>
                        <h4 class="mb-0"><?= $budgets ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card text-center bg-info text-white">
                    <div class="card-body p-2">
                        <h6 class="mb-0">Goals</h6>
                        <h4 class="mb-0"><?= $goals ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <h6>Transaksi Terakhir</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_transactions as $trans): ?>
                    <tr>
                        <td><?= date('d/m', strtotime($trans['tanggal'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $trans['jenis'] === 'pemasukan' ? 'success' : 'danger' ?>">
                                <?= ucfirst($trans['jenis']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($trans['kategori']) ?></td>
                        <td>Rp <?= number_format($trans['jumlah'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($recent_transactions)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Belum ada transaksi
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            <a href="../superadmin/dashboard/transaksi.php?user_id=<?= $user_id ?>" 
               class="btn btn-primary btn-sm">
                <i class="fas fa-eye me-1"></i> Lihat Semua Transaksi
            </a>
        </div>
    </div>
</div>

<div class="mt-4">
    <h6>Alamat</h6>
    <p class="text-muted"><?= htmlspecialchars($user['alamat'] ?: 'Tidak ada alamat') ?></p>
</div>