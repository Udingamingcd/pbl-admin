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

// Handle delete transaction
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $db->query("DELETE FROM transaksi WHERE id = :id");
    $db->bind(':id', $_GET['id']);
    $db->execute();
    
    $_SESSION['success'] = "Transaksi berhasil dihapus!";
    header('Location: transaksi.php');
    exit();
}

// Filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Build query
$query = "SELECT t.*, u.nama as user_nama, u.email as user_email 
          FROM transaksi t 
          JOIN users u ON t.user_id = u.id 
          WHERE t.tanggal BETWEEN :start_date AND :end_date";

$params = [
    ':start_date' => $start_date,
    ':end_date' => $end_date
];

if (!empty($jenis)) {
    $query .= " AND t.jenis = :jenis";
    $params[':jenis'] = $jenis;
}

if ($user_id > 0) {
    $query .= " AND t.user_id = :user_id";
    $params[':user_id'] = $user_id;
}

$query .= " ORDER BY t.tanggal DESC, t.created_at DESC";

// Get total transactions
$db->query(str_replace('t.*, u.nama as user_nama, u.email as user_email', 'COUNT(*) as total', $query));
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$total_transactions = $db->single()['total'];

// Pagination
$per_page = 15;
$total_pages = ceil($total_transactions / $per_page);
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

$query .= " LIMIT :offset, :limit";

// Get transactions
$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $per_page, PDO::PARAM_INT);
$transactions = $db->resultSet();

// Get users for filter
$db->query("SELECT id, nama, email FROM users ORDER BY nama");
$users = $db->resultSet();

// Get summary stats
$db->query("SELECT 
    SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END) as total_income,
    SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END) as total_expense
    FROM transaksi WHERE tanggal BETWEEN :start_date AND :end_date");
$db->bind(':start_date', $start_date);
$db->bind(':end_date', $end_date);
$summary = $db->single();
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Superadmin</title>
    <link rel="icon" type="image/png" href="../../../assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .income-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }
        .expense-badge {
            background: linear-gradient(45deg, #dc3545, #fd7e14);
            color: white;
        }
        .amount-income {
            color: #28a745;
            font-weight: bold;
        }
        .amount-expense {
            color: #dc3545;
            font-weight: bold;
        }
        .filter-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
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
                        <h3 class="mb-0">Transaksi User</h3>
                        <p class="text-muted mb-0">Monitor semua transaksi keuangan</p>
                    </div>
                    <div>
                        <a href="javascript:void(0)" onclick="exportTransactions()" class="btn btn-success">
                            <i class="fas fa-file-export"></i> Export
                        </a>
                    </div>
                </div>
                
                <!-- Success Message -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Transaksi</h5>
                                <h2 class="mb-0"><?= number_format($total_transactions, 0, ',', '.') ?></h2>
                                <small><?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Pemasukan</h5>
                                <h2 class="mb-0">Rp <?= number_format($summary['total_income'] ?? 0, 0, ',', '.') ?></h2>
                                <small>Periode terpilih</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Pengeluaran</h5>
                                <h2 class="mb-0">Rp <?= number_format($summary['total_expense'] ?? 0, 0, ',', '.') ?></h2>
                                <small>Periode terpilih</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-card mb-4">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Jenis Transaksi</label>
                            <select name="jenis" class="form-select">
                                <option value="">Semua Jenis</option>
                                <option value="pemasukan" <?= $jenis === 'pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
                                <option value="pengeluaran" <?= $jenis === 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>User</label>
                            <select name="user_id" class="form-select">
                                <option value="0">Semua User</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= $user_id == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['nama']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter Data
                            </button>
                            <a href="transaksi.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Transactions Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Jenis</th>
                                        <th>Kategori</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal</th>
                                        <th>Metode</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $trans): ?>
                                    <tr>
                                        <td>#<?= $trans['id'] ?></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($trans['user_nama']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($trans['user_email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= $trans['jenis'] === 'pemasukan' ? 'income-badge' : 'expense-badge' ?>">
                                                <?= ucfirst($trans['jenis']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($trans['kategori']) ?></td>
                                        <td class="<?= $trans['jenis'] === 'pemasukan' ? 'amount-income' : 'amount-expense' ?>">
                                            Rp <?= number_format($trans['jumlah'], 0, ',', '.') ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($trans['tanggal'])) ?></td>
                                        <td><?= htmlspecialchars($trans['metode_bayar'] ?: '-') ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" 
                                                    onclick="viewTransaction(<?= $trans['id'] ?>)" 
                                                    title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="?action=delete&id=<?= $trans['id'] ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Hapus transaksi ini?')"
                                               title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($transactions)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-exchange-alt fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">Tidak ada data transaksi</p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" 
                                       href="?page=<?= $current_page - 1 ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&jenis=<?= $jenis ?>&user_id=<?= $user_id ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                        <a class="page-link" 
                                           href="?page=<?= $i ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&jenis=<?= $jenis ?>&user_id=<?= $user_id ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" 
                                       href="?page=<?= $current_page + 1 ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&jenis=<?= $jenis ?>&user_id=<?= $user_id ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal View Transaction -->
    <div class="modal fade" id="viewTransactionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="transactionDetailContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewTransaction(transId) {
            fetch(`../../../ajax/get-transaction-detail.php?id=${transId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('transactionDetailContent').innerHTML = html;
                    const modal = new bootstrap.Modal(document.getElementById('viewTransactionModal'));
                    modal.show();
                })
                .catch(error => {
                    document.getElementById('transactionDetailContent').innerHTML = 
                        '<div class="alert alert-danger">Gagal memuat data transaksi</div>';
                });
        }
        
        function exportTransactions() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = `../../../ajax/export-transactions.php?${params.toString()}`;
        }
        
        // Update date inputs max to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="start_date"]').max = today;
            document.querySelector('input[name="end_date"]').max = today;
        });
    </script>
</body>
</html>