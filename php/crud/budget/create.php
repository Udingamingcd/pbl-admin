<?php
require_once '../../middleware/auth.php';
// Auth middleware sudah memulai session dan mengecek authentication

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $nama_budget = $_POST['nama_budget'];
    $jumlah = $_POST['jumlah'];
    $periode = $_POST['periode'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_akhir = $_POST['tanggal_akhir'];

    try {
        $db = new Database();
        $db->query('INSERT INTO budget (user_id, nama_budget, jumlah, periode, kategori, deskripsi, tanggal_mulai, tanggal_akhir) 
                    VALUES (:user_id, :nama_budget, :jumlah, :periode, :kategori, :deskripsi, :tanggal_mulai, :tanggal_akhir)');
        
        $db->bind(':user_id', $user_id);
        $db->bind(':nama_budget', $nama_budget);
        $db->bind(':jumlah', $jumlah);
        $db->bind(':periode', $periode);
        $db->bind(':kategori', $kategori);
        $db->bind(':deskripsi', $deskripsi);
        $db->bind(':tanggal_mulai', $tanggal_mulai);
        $db->bind(':tanggal_akhir', $tanggal_akhir);
        
        if ($db->execute()) {
            $_SESSION['success_message'] = "Budget berhasil dibuat!";
            header('Location: read.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Gagal membuat budget.";
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
    <title>Buat Budget - Finansialku</title>
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
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-plus-circle me-2"></i>Buat Budget Baru
                            </h4>
                            <a href="read.php" class="btn btn-light btn-sm">
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
                                           placeholder="Contoh: Budget Makan Bulanan" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="jumlah" class="form-label">Jumlah Budget</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="jumlah" name="jumlah" 
                                               placeholder="0" min="0" step="1000" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="periode" class="form-label">Periode Budget</label>
                                    <select class="form-select" id="periode" name="periode" required>
                                        <option value="">Pilih Periode</option>
                                        <option value="harian">Harian</option>
                                        <option value="mingguan">Mingguan</option>
                                        <option value="bulanan" selected>Bulanan</option>
                                        <option value="tahunan">Tahunan</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="kategori" class="form-label">Kategori</label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($kategori_list as $kategori_item): ?>
                                            <option value="<?php echo $kategori_item; ?>"><?php echo $kategori_item; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" 
                                           value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_akhir" class="form-label">Tanggal Berakhir</label>
                                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" 
                                          rows="3" placeholder="Tambahkan deskripsi budget..."></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="read.php" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-times me-1"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Simpan Budget
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set default tanggal akhir (1 bulan dari sekarang)
        document.getElementById('tanggal_mulai').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const endDate = new Date(startDate);
            endDate.setMonth(endDate.getMonth() + 1);
            
            document.getElementById('tanggal_akhir').value = endDate.toISOString().split('T')[0];
        });

        // Trigger change event on load
        document.getElementById('tanggal_mulai').dispatchEvent(new Event('change'));
    </script>
</body>
</html>