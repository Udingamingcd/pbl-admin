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

// Handle actions
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $db->query("DELETE FROM users WHERE id = :id");
        $db->bind(':id', $_GET['id']);
        $db->execute();
        
        $_SESSION['success'] = "User berhasil dihapus!";
        header('Location: kelola-user.php');
        exit();
    }
    
    if ($_GET['action'] === 'toggle_verify' && isset($_GET['id'])) {
        $db->query("UPDATE users SET email_verified = IF(email_verified = 1, 0, 1) WHERE id = :id");
        $db->bind(':id', $_GET['id']);
        $db->execute();
        
        $_SESSION['success'] = "Status verifikasi berhasil diubah!";
        header('Location: kelola-user.php');
        exit();
    }
}

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_verified = isset($_GET['verified']) ? $_GET['verified'] : '';

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (nama LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($filter_verified === 'verified') {
    $query .= " AND email_verified = 1";
} elseif ($filter_verified === 'unverified') {
    $query .= " AND email_verified = 0";
}

$query .= " ORDER BY created_at DESC";

// Get total count for pagination
$db->query("SELECT COUNT(*) as total FROM users");
$total_users = $db->single()['total'];

// Pagination
$per_page = 10;
$total_pages = ceil($total_users / $per_page);
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

$query .= " LIMIT :offset, :limit";

// Get users
$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $per_page, PDO::PARAM_INT);
$users = $db->resultSet();
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Superadmin</title>
    <link rel="icon" type="image/png" href="../../../assets/icons/Dompt.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .table-actions {
            white-space: nowrap;
        }
        .badge-verified {
            background: linear-gradient(45deg, #00b09b, #96c93d);
        }
        .badge-unverified {
            background: linear-gradient(45deg, #f46b45, #eea849);
        }
        .search-box {
            max-width: 300px;
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
                        <h3 class="mb-0">Kelola User</h3>
                        <p class="text-muted mb-0">Total <?= $total_users ?> user terdaftar</p>
                    </div>
                    <div>
                        <a href="javascript:void(0)" onclick="exportUsers()" class="btn btn-success">
                            <i class="fas fa-file-export"></i> Export CSV
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
                
                <!-- Filter & Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="verified" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="verified" <?= $filter_verified === 'verified' ? 'selected' : '' ?>>Terverifikasi</option>
                                    <option value="unverified" <?= $filter_verified === 'unverified' ? 'selected' : '' ?>>Belum Verifikasi</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Telepon</th>
                                        <th>Status</th>
                                        <th>Bergabung</th>
                                        <th>Login Terakhir</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>#<?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['nama']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['telepon'] ?: '-') ?></td>
                                        <td>
                                            <span class="badge <?= $user['email_verified'] ? 'badge-verified' : 'badge-unverified' ?>">
                                                <?= $user['email_verified'] ? 'Terverifikasi' : 'Belum Verifikasi' ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <?= $user['last_login'] ? date('d/m H:i', strtotime($user['last_login'])) : 'Belum login' ?>
                                        </td>
                                        <td class="table-actions">
                                            <a href="javascript:void(0)" 
                                               onclick="viewUser(<?= $user['id'] ?>)" 
                                               class="btn btn-info btn-sm" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?action=toggle_verify&id=<?= $user['id'] ?>" 
                                               class="btn btn-warning btn-sm" 
                                               title="<?= $user['email_verified'] ? 'Batalkan Verifikasi' : 'Verifikasi' ?>">
                                                <i class="fas fa-<?= $user['email_verified'] ? 'times' : 'check' ?>"></i>
                                            </a>
                                            <a href="?action=delete&id=<?= $user['id'] ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Hapus user ini? Semua data terkait akan ikut terhapus!')"
                                               title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">Tidak ada data user</p>
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
                                    <a class="page-link" href="?page=<?= $current_page - 1 ?>&search=<?= urlencode($search) ?>&verified=<?= $filter_verified ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&verified=<?= $filter_verified ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $current_page + 1 ?>&search=<?= urlencode($search) ?>&verified=<?= $filter_verified ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- User Stats -->
                <div class="row mt-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total User</h5>
                                <h2 class="mb-0"><?= $total_users ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Terverifikasi</h5>
                                <?php
                                $db->query("SELECT COUNT(*) as total FROM users WHERE email_verified = 1");
                                $verified = $db->single()['total'];
                                ?>
                                <h2 class="mb-0"><?= $verified ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Belum Verifikasi</h5>
                                <?php
                                $db->query("SELECT COUNT(*) as total FROM users WHERE email_verified = 0");
                                $unverified = $db->single()['total'];
                                ?>
                                <h2 class="mb-0"><?= $unverified ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">User Bulan Ini</h5>
                                <?php
                                $db->query("SELECT COUNT(*) as total FROM users WHERE MONTH(created_at) = MONTH(CURDATE())");
                                $monthly = $db->single()['total'];
                                ?>
                                <h2 class="mb-0"><?= $monthly ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal View User -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="userDetailContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewUser(userId) {
            fetch(`../../../ajax/get-user-detail.php?id=${userId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('userDetailContent').innerHTML = html;
                    const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
                    modal.show();
                })
                .catch(error => {
                    document.getElementById('userDetailContent').innerHTML = 
                        '<div class="alert alert-danger">Gagal memuat data user</div>';
                });
        }
        
        function exportUsers() {
            const search = '<?= urlencode($search) ?>';
            const verified = '<?= $filter_verified ?>';
            window.location.href = `../../../ajax/export-users.php?search=${search}&verified=${verified}`;
        }
        
        // Auto refresh every 60 seconds
        setInterval(() => {
            if (!document.hidden) {
                const url = new URL(window.location.href);
                if (!url.searchParams.has('search') && !url.searchParams.has('verified')) {
                    window.location.reload();
                }
            }
        }, 60000);
    </script>
</body>
</html>