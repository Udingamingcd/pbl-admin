<?php
require_once 'php/middleware/auth.php';

// Get user data for display
$user_id = $_SESSION['user_id'];
try {
    $db = new Database();
    $db->query('SELECT id, nama, email, foto_profil, telepon, alamat, created_at, last_login FROM users WHERE id = :user_id');
    $db->bind(':user_id', $user_id);
    $user_data = $db->single();
    
    // Handle default avatar path - dari ROOT
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
    <title>Profil Saya - Finansialku</title>
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
                    <h1 class="h2">Profil Saya</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-sm btn-outline-secondary" id="refreshProfile">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Alert Section -->
                <div id="alertContainer"></div>

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
                                         id="profilePicture"
                                         onerror="this.src='/assets/icons/default-avatar.png'">
                                    <div class="profile-picture-overlay" id="changePhotoBtn">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <input type="file" id="profilePictureInput" accept="image/*" class="d-none">
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

                    <!-- Edit Profile Form -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Profil</h5>
                            </div>
                            <div class="card-body">
                                <form id="profileForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="nama" name="nama" 
                                                       value="<?php echo htmlspecialchars($user_data['nama'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telepon" class="form-label">Nomor Telepon</label>
                                                <input type="tel" class="form-control" id="telepon" name="telepon"
                                                       value="<?php echo htmlspecialchars($user_data['telepon'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($user_data['alamat'] ?? ''); ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Change Password Form -->
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">Ubah Password</h5>
                            </div>
                            <div class="card-body">
                                <form id="passwordForm">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                                        <div class="form-text">Password minimal 8 karakter</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key me-1"></i>Ubah Password
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        <div class="card shadow-sm mt-4 border-danger">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">Zona Berbahaya</h5>
                            </div>
                            <div class="card-body">
                                <h6 class="text-danger">Hapus Akun</h6>
                                <p class="text-muted small">
                                    Setelah menghapus akun, semua data Anda akan dihapus secara permanen. 
                                    Tindakan ini tidak dapat dibatalkan.
                                </p>
                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                    <i class="fas fa-trash me-1"></i>Hapus Akun Saya
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Hapus Akun</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <p>Semua data Anda termasuk:</p>
                    <ul>
                        <li>Data profil</li>
                        <li>Semua transaksi</li>
                        <li>Budget dan target finansial</li>
                        <li>Riwayat laporan</li>
                    </ul>
                    <p>akan dihapus secara permanen.</p>
                    
                    <form id="deleteAccountForm">
                        <div class="mb-3">
                            <label for="delete_password" class="form-label">Masukkan password untuk konfirmasi:</label>
                            <input type="password" class="form-control" id="delete_password" name="password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteAccount">
                        <i class="fas fa-trash me-1"></i>Hapus Akun
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'php/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/profil.js"></script>
</body>
</html>