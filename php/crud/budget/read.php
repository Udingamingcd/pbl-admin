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
    <link rel="stylesheet" href="../../../css/animasi.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --danger-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --warning-gradient: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }
        
        .card {
            border: none;
            border-radius: 20px;
            background: rgba(30, 30, 40, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .card-header {
            background: var(--primary-gradient);
            border-bottom: none;
            padding: 1.5rem 2rem;
        }
        
        .summary-card {
            border-radius: 16px;
            padding: 1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
        
        .summary-card .card-body {
            position: relative;
            z-index: 2;
        }
        
        .summary-card-primary { background: var(--primary-gradient); }
        .summary-card-info { background: var(--success-gradient); }
        .summary-card-success { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .summary-card-warning { background: var(--warning-gradient); }
        
        .progress {
            height: 10px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }
        
        .progress-bar {
            border-radius: 10px;
            transition: width 0.6s ease;
        }
        
        .table {
            color: #e0e0e0;
        }
        
        .table-hover tbody tr {
            transition: all 0.3s ease;
        }
        
        .table-hover tbody tr:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: scale(1.01);
        }
        
        .badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .btn {
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-group-sm .btn {
            padding: 8px 12px;
            border-radius: 8px;
        }
        
        .btn-dashboard {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .chart-container {
            height: 200px;
            position: relative;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-active { background: #4caf50; box-shadow: 0 0 10px #4caf50; }
        .status-warning { background: #ff9800; box-shadow: 0 0 10px #ff9800; }
        .status-danger { background: #f44336; box-shadow: 0 0 10px #f44336; }
        
        .modal-content {
            border-radius: 20px;
            background: rgba(30, 30, 40, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header Navigasi -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="h3 mb-1">
                            <i class="fas fa-chart-pie me-2" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>Kelola Budget
                        </h1>
                        <p class="text-muted mb-0">Monitor dan kelola semua budget Anda</p>
                    </div>
                    <div class="btn-group">
                        <a href="../../../dashboard.php" class="btn btn-dashboard">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                        <a href="create.php" class="btn btn-primary ms-2">
                            <i class="fas fa-plus-circle me-1"></i>Tambah Budget
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="row mb-4 fade-in">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Berhasil!</h5>
                            <p class="mb-0"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="row mb-4 fade-in">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Error!</h5>
                            <p class="mb-0"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="row g-4 mb-4 fade-in">
            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-card-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 opacity-75">Total Budget</h6>
                                <h2 class="card-title mb-0">Rp <?php echo number_format($total_budget, 0, ',', '.'); ?></h2>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-wallet fa-2x opacity-50"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="opacity-75"><?php echo count($budgets); ?> budget aktif</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-card-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 opacity-75">Total Terpakai</h6>
                                <h2 class="card-title mb-0">Rp <?php echo number_format($total_terpakai, 0, ',', '.'); ?></h2>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="opacity-75">
                                <?php echo $total_budget > 0 ? number_format(($total_terpakai / $total_budget) * 100, 1) : '0'; ?>% dari total
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-card-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 opacity-75">Sisa Budget</h6>
                                <h2 class="card-title mb-0">Rp <?php echo number_format($total_budget - $total_terpakai, 0, ',', '.'); ?></h2>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-piggy-bank fa-2x opacity-50"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="opacity-75">Available for spending</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-card-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 opacity-75">Budget Aktif</h6>
                                <h2 class="card-title mb-0"><?php echo count($budgets); ?></h2>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-list-check fa-2x opacity-50"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="opacity-75">Dalam monitoring</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget List -->
        <div class="row fade-in">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark">
                                <i class="fas fa-list me-2"></i>Daftar Budget
                            </h5>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-dark bg-opacity-50 me-2">
                                    <i class="fas fa-filter me-1"></i><?php echo count($budgets); ?> items
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-dark btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-sort me-1"></i>Urutkan
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="?sort=terbaru"><i class="fas fa-calendar me-2"></i>Terbaru</a></li>
                                        <li><a class="dropdown-item" href="?sort=nama"><i class="fas fa-sort-alpha-down me-2"></i>Nama A-Z</a></li>
                                        <li><a class="dropdown-item" href="?sort=jumlah"><i class="fas fa-money-bill me-2"></i>Jumlah Tertinggi</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($budgets)): ?>
                            <div class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-chart-pie fa-5x text-muted mb-4" style="opacity: 0.3;"></i>
                                    <h4 class="text-muted mb-3">Belum ada budget</h4>
                                    <p class="text-muted mb-4">Mulai buat budget pertama Anda untuk mengelola pengeluaran dengan lebih efektif</p>
                                    <a href="create.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-plus me-2"></i>Buat Budget Pertama
                                    </a>
                                </div>
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
                                            <th class="text-end">Aksi</th>
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
                                                $status_badge = 'danger';
                                            } elseif ($persentase >= 75) {
                                                $status_class = 'status-warning';
                                                $status_text = 'Peringatan';
                                                $progress_class = 'bg-warning';
                                                $status_badge = 'warning';
                                            } else {
                                                $status_class = 'status-active';
                                                $status_text = 'Aman';
                                                $progress_class = 'bg-success';
                                                $status_badge = 'success';
                                            }
                                            
                                            // Tentukan ikon berdasarkan kategori
                                            $kategori_icon = '';
                                            switch($budget['kategori']) {
                                                case 'Makanan': $kategori_icon = 'utensils'; break;
                                                case 'Transportasi': $kategori_icon = 'car'; break;
                                                case 'Hiburan': $kategori_icon = 'film'; break;
                                                case 'Belanja': $kategori_icon = 'shopping-bag'; break;
                                                case 'Kesehatan': $kategori_icon = 'heartbeat'; break;
                                                case 'Pendidikan': $kategori_icon = 'graduation-cap'; break;
                                                case 'Tagihan': $kategori_icon = 'file-invoice'; break;
                                                default: $kategori_icon = 'ellipsis-h';
                                            }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="budget-icon me-3">
                                                        <div class="icon-circle-sm bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                                                            <i class="fas fa-<?php echo $kategori_icon; ?>"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <strong class="d-block"><?php echo htmlspecialchars($budget['nama_budget']); ?></strong>
                                                        <?php if ($budget['deskripsi']): ?>
                                                            <small class="text-muted d-block"><?php echo htmlspecialchars(substr($budget['deskripsi'], 0, 50)) . (strlen($budget['deskripsi']) > 50 ? '...' : ''); ?></small>
                                                        <?php endif; ?>
                                                        <small class="text-muted">
                                                            <i class="far fa-calendar me-1"></i>
                                                            <?php echo date('d M Y', strtotime($budget['tanggal_mulai'])); ?> - <?php echo date('d M Y', strtotime($budget['tanggal_akhir'])); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25">
                                                    <i class="fas fa-<?php echo $kategori_icon; ?> me-1"></i><?php echo $budget['kategori']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    <?php echo ucfirst($budget['periode']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">Rp <?php echo number_format($budget['jumlah'], 0, ',', '.'); ?></div>
                                            </td>
                                            <td>
                                                <div class="text-warning">Rp <?php echo number_format($terpakai, 0, ',', '.'); ?></div>
                                            </td>
                                            <td>
                                                <div class="text-success">Rp <?php echo number_format($sisa, 0, ',', '.'); ?></div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1 me-3">
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar <?php echo $progress_class; ?>" 
                                                                 role="progressbar" 
                                                                 style="width: <?php echo min($persentase, 100); ?>%"
                                                                 aria-valuenow="<?php echo $persentase; ?>" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-nowrap" style="width: 50px;">
                                                        <small class="text-muted"><?php echo number_format($persentase, 1); ?>%</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $status_badge; ?> bg-opacity-25 text-<?php echo $status_badge; ?> border border-<?php echo $status_badge; ?> border-opacity-25">
                                                    <span class="status-indicator <?php echo $status_class; ?>"></span>
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="update.php?id=<?php echo $budget['id']; ?>" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Edit Budget">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm" 
                                                            onclick="showDeleteModal(<?php echo $budget['id']; ?>, '<?php echo htmlspecialchars(addslashes($budget['nama_budget'])); ?>')"
                                                            data-bs-toggle="tooltip" 
                                                            title="Hapus Budget">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted">
                                    Menampilkan <strong><?php echo count($budgets); ?></strong> dari <strong><?php echo count($budgets); ?></strong> budget
                                </div>
                                <nav>
                                    <ul class="pagination mb-0">
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#" tabindex="-1">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <div class="icon-circle-lg bg-danger bg-opacity-10 text-danger rounded-circle p-4 mb-3 mx-auto">
                            <i class="fas fa-trash fa-2x"></i>
                        </div>
                        <h4 class="mb-2">Hapus Budget?</h4>
                        <p class="text-muted mb-0" id="deleteBudgetName">Anda akan menghapus budget "<strong></strong>". Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <a href="#" id="deleteLink" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Hapus Budget
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../../js/animasi.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
        
        // Delete modal
        function showDeleteModal(budgetId, budgetName) {
            const deleteLink = document.getElementById('deleteLink');
            const deleteBudgetName = document.getElementById('deleteBudgetName');
            
            deleteLink.href = `delete.php?id=${budgetId}`;
            deleteBudgetName.innerHTML = `Anda akan menghapus budget "<strong>${budgetName}</strong>". Tindakan ini tidak dapat dibatalkan.`;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Animate progress bars on scroll
        function animateProgressBars() {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        }
        
        // Animate cards on scroll
        function animateCards() {
            const cards = document.querySelectorAll('.card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        }
        
        // Run animations on load
        document.addEventListener('DOMContentLoaded', function() {
            animateProgressBars();
            animateCards();
            
            // Add hover effects to table rows
            const tableRows = document.querySelectorAll('.table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = 'rgba(102, 126, 234, 0.05)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
        });
    </script>
</body>
</html>