<?php
require_once '../../middleware/auth.php';
// Auth middleware sudah memulai session dan mengecek authentication

$user_id = $_SESSION['user_id'];

// Ambil data budget yang akan diupdate
if (!isset($_GET['id'])) {
    header('Location: read.php');
    exit();
}

$budget_id = $_GET['id'];
$db = new Database();

// Cek kepemilikan budget
$db->query('SELECT * FROM budget WHERE id = :id AND user_id = :user_id');
$db->bind(':id', $budget_id);
$db->bind(':user_id', $user_id);
$budget = $db->single();

if (!$budget) {
    $_SESSION['error_message'] = "Budget tidak ditemukan.";
    header('Location: read.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_budget = $_POST['nama_budget'];
    $jumlah = $_POST['jumlah'];
    $periode = $_POST['periode'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_akhir = $_POST['tanggal_akhir'];

    try {
        $db->query('UPDATE budget SET 
                    nama_budget = :nama_budget, 
                    jumlah = :jumlah, 
                    periode = :periode, 
                    kategori = :kategori, 
                    deskripsi = :deskripsi, 
                    tanggal_mulai = :tanggal_mulai, 
                    tanggal_akhir = :tanggal_akhir,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id AND user_id = :user_id');
        
        $db->bind(':nama_budget', $nama_budget);
        $db->bind(':jumlah', $jumlah);
        $db->bind(':periode', $periode);
        $db->bind(':kategori', $kategori);
        $db->bind(':deskripsi', $deskripsi);
        $db->bind(':tanggal_mulai', $tanggal_mulai);
        $db->bind(':tanggal_akhir', $tanggal_akhir);
        $db->bind(':id', $budget_id);
        $db->bind(':user_id', $user_id);
        
        if ($db->execute()) {
            $_SESSION['success_message'] = "Budget berhasil diupdate!";
            header('Location: read.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Gagal mengupdate budget.";
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Kategori default untuk budget
$kategori_list = ['Makanan', 'Transportasi', 'Hiburan', 'Belanja', 'Kesehatan', 'Pendidikan', 'Tagihan', 'Lainnya'];
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Budget - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../css/animasi.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .card {
            border: none;
            border-radius: 20px;
            background: rgba(30, 30, 40, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .card-header {
            background: var(--warning-gradient);
            border-bottom: none;
            padding: 1.5rem 2rem;
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            padding: 14px 18px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .form-label {
            font-weight: 600;
            color: #a0a0c0;
            margin-bottom: 8px;
        }
        
        .btn {
            border-radius: 12px;
            padding: 14px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 87, 108, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .btn-dashboard {
            background: var(--primary-gradient);
            color: white;
        }
        
        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .input-group-text {
            background: rgba(118, 75, 162, 0.2);
            border: 2px solid rgba(118, 75, 162, 0.3);
            color: #a0a0c0;
            border-right: none;
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Header Navigasi -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h3 mb-1">
                            <i class="fas fa-edit me-2" style="background: var(--warning-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>Edit Budget
                        </h2>
                        <p class="text-muted mb-0">Perbarui informasi budget Anda</p>
                    </div>
                    <div class="btn-group">
                        <a href="../../../dashboard.php" class="btn btn-dashboard">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                        <a href="read.php" class="btn btn-secondary ms-2">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center fade-in">
            <div class="col-lg-10">
                <div class="card shadow-lg">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 text-dark">
                                <i class="fas fa-edit me-2"></i>Edit Budget
                            </h4>
                            <div class="badge bg-dark bg-opacity-50 px-3 py-2">
                                <i class="fas fa-id-card me-1"></i>ID: <?php echo $budget_id; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="d-flex">
                                    <i class="fas fa-exclamation-triangle me-3 fa-lg mt-1"></i>
                                    <div>
                                        <strong>Error!</strong><br>
                                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="budgetForm" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label for="nama_budget" class="form-label">
                                            <i class="fas fa-tag me-2"></i>Nama Budget
                                        </label>
                                        <input type="text" class="form-control" id="nama_budget" name="nama_budget" 
                                               value="<?php echo htmlspecialchars($budget['nama_budget']); ?>" 
                                               placeholder="Masukkan nama budget" required>
                                        <div class="invalid-feedback">
                                            Mohon isi nama budget.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label for="jumlah" class="form-label">
                                            <i class="fas fa-money-bill-wave me-2"></i>Jumlah Budget
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" id="jumlah" name="jumlah" 
                                                   value="<?php echo $budget['jumlah']; ?>" min="0" step="1000" 
                                                   placeholder="0" required>
                                            <div class="invalid-feedback">
                                                Mohon isi jumlah budget.
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            <i class="fas fa-info-circle me-1"></i>Jumlah dalam Rupiah
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label for="periode" class="form-label">
                                            <i class="fas fa-calendar-alt me-2"></i>Periode Budget
                                        </label>
                                        <select class="form-select" id="periode" name="periode" required>
                                            <option value="harian" <?php echo $budget['periode'] == 'harian' ? 'selected' : ''; ?>>Harian</option>
                                            <option value="mingguan" <?php echo $budget['periode'] == 'mingguan' ? 'selected' : ''; ?>>Mingguan</option>
                                            <option value="bulanan" <?php echo $budget['periode'] == 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
                                            <option value="tahunan" <?php echo $budget['periode'] == 'tahunan' ? 'selected' : ''; ?>>Tahunan</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label for="kategori" class="form-label">
                                            <i class="fas fa-filter me-2"></i>Kategori
                                        </label>
                                        <select class="form-select" id="kategori" name="kategori" required>
                                            <?php foreach ($kategori_list as $kategori_item): ?>
                                                <option value="<?php echo $kategori_item; ?>" 
                                                    <?php echo $budget['kategori'] == $kategori_item ? 'selected' : ''; ?>>
                                                    <i class="fas fa-<?php 
                                                        switch($kategori_item) {
                                                            case 'Makanan': echo 'utensils'; break;
                                                            case 'Transportasi': echo 'car'; break;
                                                            case 'Hiburan': echo 'film'; break;
                                                            case 'Belanja': echo 'shopping-bag'; break;
                                                            case 'Kesehatan': echo 'heartbeat'; break;
                                                            case 'Pendidikan': echo 'graduation-cap'; break;
                                                            case 'Tagihan': echo 'file-invoice'; break;
                                                            default: echo 'ellipsis-h';
                                                        }
                                                    ?> me-2"></i>
                                                    <?php echo $kategori_item; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label for="tanggal_mulai" class="form-label">
                                            <i class="fas fa-play-circle me-2"></i>Tanggal Mulai
                                        </label>
                                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" 
                                               value="<?php echo $budget['tanggal_mulai']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label for="tanggal_akhir" class="form-label">
                                            <i class="fas fa-stop-circle me-2"></i>Tanggal Berakhir
                                        </label>
                                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" 
                                               value="<?php echo $budget['tanggal_akhir']; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-group">
                                    <label for="deskripsi" class="form-label">
                                        <i class="fas fa-align-left me-2"></i>Deskripsi
                                    </label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" 
                                              rows="4" placeholder="Tambahkan deskripsi atau catatan tentang budget ini..."><?php echo htmlspecialchars($budget['deskripsi']); ?></textarea>
                                    <div class="form-text">Opsional. Maksimal 500 karakter.</div>
                                </div>
                            </div>

                            <!-- Budget Summary -->
                            <div class="alert alert-info bg-dark border-info mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar-check fa-2x me-3 text-info"></i>
                                            <div>
                                                <small class="text-muted d-block">Periode Aktif</small>
                                                <strong><?php echo date('d M Y', strtotime($budget['tanggal_mulai'])); ?> - <?php echo date('d M Y', strtotime($budget['tanggal_akhir'])); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-3 mt-md-0">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock fa-2x me-3 text-info"></i>
                                            <div>
                                                <small class="text-muted d-block">Terakhir Diupdate</small>
                                                <strong><?php echo date('d M Y H:i', strtotime($budget['updated_at'])); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary">
                                <div>
                                    <a href="read.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Batal
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <button type="button" onclick="history.back()" class="btn btn-outline-light">
                                        <i class="fas fa-undo me-1"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-1"></i>Update Budget
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../../js/animasi.js"></script>
    <script>
        // Form Validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
        
        // Auto format currency
        document.getElementById('jumlah').addEventListener('input', function(e) {
            let value = e.target.value;
            e.target.value = value.replace(/\D/g, '');
        });
        
        // Date validation
        document.getElementById('tanggal_mulai').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const endDateInput = document.getElementById('tanggal_akhir');
            const endDate = new Date(endDateInput.value);
            
            if (endDate < startDate) {
                endDateInput.value = this.value;
            }
        });
        
        // Animate form inputs on focus
        document.querySelectorAll('.form-control, .form-select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('input-focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('input-focused');
            });
        });
    </script>
</body>
</html>