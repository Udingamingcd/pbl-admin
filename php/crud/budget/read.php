<?php
require_once '../../middleware/auth.php';
// Auth middleware sudah memulai session dan mengecek authentication

$user_id = $_SESSION['user_id'];
$db = new Database();

// Ambil data budget
$db->query('SELECT * FROM budget WHERE user_id = :user_id ORDER BY created_at DESC');
$db->bind(':user_id', $user_id);
$budgets = $db->resultSet();

// Hitung total budget dan terpakai
$total_budget = 0;
$total_terpakai = 0;

foreach ($budgets as $budget) {
    $total_budget += $budget['jumlah'];
    
    // Hitung pengeluaran untuk budget ini
    $db->query('SELECT COALESCE(SUM(jumlah), 0) as terpakai 
                FROM transaksi 
                WHERE user_id = :user_id 
                AND jenis = "pengeluaran" 
                AND kategori = :kategori 
                AND tanggal BETWEEN :tanggal_mulai AND :tanggal_akhir');
    $db->bind(':user_id', $user_id);
    $db->bind(':kategori', $budget['kategori']);
    $db->bind(':tanggal_mulai', $budget['tanggal_mulai']);
    $db->bind(':tanggal_akhir', $budget['tanggal_akhir']);
    $usage = $db->single();
    $total_terpakai += $usage['terpakai'];
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Budget - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .card {
            border: none;
            border-radius: 15px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .progress {
            height: 8px;
            border-radius: 10px;
        }
        .budget-card {
            border-left: 4px solid;
        }
        .status-active {
            border-left-color: #28a745;
        }
        .status-warning {
            border-left-color: #ffc107;
        }
        .status-danger {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1">
                    <i class="fas fa-chart-pie me-2 text-warning"></i>Kelola Budget
                </h2>
                <p class="text-muted mb-0">Kelola budget dan monitor pengeluaran Anda</p>
            </div>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Tambah Budget
            </a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Budget</h6>
                                <h4 class="mb-0">Rp <?php echo number_format($total_budget, 0, ',', '.'); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-wallet fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Terpakai</h6>
                                <h4 class="mb-0">Rp <?php echo number_format($total_terpakai, 0, ',', '.'); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Sisa Budget</h6>
                                <h4 class="mb-0">Rp <?php echo number_format($total_budget - $total_terpakai, 0, ',', '.'); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-piggy-bank fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Budget Aktif</h6>
                                <h4 class="mb-0"><?php echo count($budgets); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-list fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget List -->
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Daftar Budget
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($budgets)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chart-pie fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada budget</h5>
                        <p class="text-muted mb-4">Mulai buat budget pertama Anda untuk mengelola pengeluaran</p>
                        <a href="create.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Buat Budget Pertama
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Budget</th>
                                    <th>Kategori</th>
                                    <th>Periode</th>
                                    <th>Jumlah</th>
                                    <th>Terpakai</th>
                                    <th>Sisa</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($budgets as $budget): 
                                    // Hitung pengeluaran untuk budget ini
                                    $db->query('SELECT COALESCE(SUM(jumlah), 0) as terpakai 
                                                FROM transaksi 
                                                WHERE user_id = :user_id 
                                                AND jenis = "pengeluaran" 
                                                AND kategori = :kategori 
                                                AND tanggal BETWEEN :tanggal_mulai AND :tanggal_akhir');
                                    $db->bind(':user_id', $user_id);
                                    $db->bind(':kategori', $budget['kategori']);
                                    $db->bind(':tanggal_mulai', $budget['tanggal_mulai']);
                                    $db->bind(':tanggal_akhir', $budget['tanggal_akhir']);
                                    $usage = $db->single();
                                    $terpakai = $usage['terpakai'];
                                    $sisa = $budget['jumlah'] - $terpakai;
                                    $persentase = $budget['jumlah'] > 0 ? ($terpakai / $budget['jumlah']) * 100 : 0;
                                    
                                    // Tentukan status
                                    if ($persentase >= 90) {
                                        $status_class = 'status-danger';
                                        $status_text = 'Bahaya';
                                        $progress_class = 'bg-danger';
                                    } elseif ($persentase >= 75) {
                                        $status_class = 'status-warning';
                                        $status_text = 'Peringatan';
                                        $progress_class = 'bg-warning';
                                    } else {
                                        $status_class = 'status-active';
                                        $status_text = 'Aman';
                                        $progress_class = 'bg-success';
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($budget['nama_budget']); ?></strong>
                                        <?php if ($budget['deskripsi']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($budget['deskripsi']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $budget['kategori']; ?></span>
                                    </td>
                                    <td><?php echo ucfirst($budget['periode']); ?></td>
                                    <td>Rp <?php echo number_format($budget['jumlah'], 0, ',', '.'); ?></td>
                                    <td>Rp <?php echo number_format($terpakai, 0, ',', '.'); ?></td>
                                    <td>Rp <?php echo number_format($sisa, 0, ',', '.'); ?></td>
                                    <td>
                                        <div class="progress" style="height: 8px; width: 100px;">
                                            <div class="progress-bar <?php echo $progress_class; ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo min($persentase, 100); ?>%"
                                                 aria-valuenow="<?php echo $persentase; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted"><?php echo number_format($persentase, 1); ?>%</small>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?php echo $progress_class == 'bg-danger' ? 'bg-danger' : 
                                                   ($progress_class == 'bg-warning' ? 'bg-warning' : 'bg-success'); ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="update.php?id=<?php echo $budget['id']; ?>" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $budget['id']; ?>)" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus budget ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="deleteLink" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(budgetId) {
            const deleteLink = document.getElementById('deleteLink');
            deleteLink.href = `delete.php?id=${budgetId}`;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>