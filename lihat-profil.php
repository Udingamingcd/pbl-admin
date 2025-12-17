<?php
require_once 'php/middleware/auth.php';
require_once 'php/koneksi.php';

// Get user data for display
$user_id = $_SESSION['user_id'];
try {
    $db = new Database();
    $db->query('SELECT id, nama, email, foto_profil, telepon, alamat, created_at, last_login FROM users WHERE id = :user_id');
    $db->bind(':user_id', $user_id);
    $user_data = $db->single();
    
    // Handle default avatar path
    if (empty($user_data['foto_profil']) || $user_data['foto_profil'] === 'assets/icons/default-avatar.png') {
        $user_data['foto_profil'] = '/assets/icons/default-avatar.png';
    }
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    $user_data = [];
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Profil - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/profil.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <?php include 'php/includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'php/includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Lihat Profil</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="profil.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit me-1"></i>Edit Profil
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Profile Information -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Foto Profil</h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="profile-picture-container mb-3">
                                    <img src="<?php echo htmlspecialchars($user_data['foto_profil'] ?? '/assets/icons/default-avatar.png'); ?>" 
                                         alt="Foto Profil" 
                                         class="profile-picture rounded-circle"
                                         onerror="this.src='/assets/icons/default-avatar.png'">
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($user_data['nama'] ?? 'User'); ?></h5>
                                <p class="text-muted"><?php echo htmlspecialchars($user_data['email'] ?? ''); ?></p>
                                
                                <div class="profile-stats mt-4">
                                    <div class="stat-item">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                        <div>
                                            <small class="text-muted">Bergabung</small>
                                            <div class="fw-bold">
                                                <?php 
                                                if (isset($user_data['created_at'])) {
                                                    echo date('d M Y', strtotime($user_data['created_at']));
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-sign-in-alt text-success"></i>
                                        <div>
                                            <small class="text-muted">Login Terakhir</small>
                                            <div class="fw-bold">
                                                <?php 
                                                if (isset($user_data['last_login'])) {
                                                    echo date('d M Y H:i', strtotime($user_data['last_login']));
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Details -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Profil</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3 fw-bold">Nama Lengkap</div>
                                    <div class="col-sm-9"><?php echo htmlspecialchars($user_data['nama'] ?? '-'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3 fw-bold">Email</div>
                                    <div class="col-sm-9"><?php echo htmlspecialchars($user_data['email'] ?? '-'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3 fw-bold">Telepon</div>
                                    <div class="col-sm-9"><?php echo htmlspecialchars($user_data['telepon'] ?? '-'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3 fw-bold">Alamat</div>
                                    <div class="col-sm-9"><?php echo htmlspecialchars($user_data['alamat'] ?? '-'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3 fw-bold">ID Pengguna</div>
                                    <div class="col-sm-9"><?php echo htmlspecialchars($user_data['id'] ?? '-'); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Actions -->
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">Aksi Akun</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2 d-md-flex">
                                    <a href="profil.php" class="btn btn-primary">
                                        <i class="fas fa-edit me-1"></i>Edit Profil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'php/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>