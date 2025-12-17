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
    <style>
        .card {
            border: none;
            border-radius: 15px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
        }
        .btn {
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-edit me-2"></i>Edit Budget
                            </h4>
                            <a href="read.php" class="btn btn-dark btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="budgetForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama_budget" class="form-label">Nama Budget</label>
                                    <input type="text" class="form-control" id="nama_budget" name="nama_budget" 
                                           value="<?php echo htmlspecialchars($budget['nama_budget']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="jumlah" class="form-label">Jumlah Budget</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="jumlah" name="jumlah" 
                                               value="<?php echo $budget['jumlah']; ?>" min="0" step="1000" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="periode" class="form-label">Periode Budget</label>
                                    <select class="form-select" id="periode" name="periode" required>
                                        <option value="harian" <?php echo $budget['periode'] == 'harian' ? 'selected' : ''; ?>>Harian</option>
                                        <option value="mingguan" <?php echo $budget['periode'] == 'mingguan' ? 'selected' : ''; ?>>Mingguan</option>
                                        <option value="bulanan" <?php echo $budget['periode'] == 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
                                        <option value="tahunan" <?php echo $budget['periode'] == 'tahunan' ? 'selected' : ''; ?>>Tahunan</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="kategori" class="form-label">Kategori</label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <?php foreach ($kategori_list as $kategori_item): ?>
                                            <option value="<?php echo $kategori_item; ?>" 
                                                <?php echo $budget['kategori'] == $kategori_item ? 'selected' : ''; ?>>
                                                <?php echo $kategori_item; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" 
                                           value="<?php echo $budget['tanggal_mulai']; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_akhir" class="form-label">Tanggal Berakhir</label>
                                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" 
                                           value="<?php echo $budget['tanggal_akhir']; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" 
                                          rows="3" placeholder="Tambahkan deskripsi budget..."><?php echo htmlspecialchars($budget['deskripsi']); ?></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="read.php" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-times me-1"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-1"></i>Update Budget
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>