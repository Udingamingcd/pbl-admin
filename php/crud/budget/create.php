<?php
require_once '../../middleware/auth.php';
// Auth middleware sudah memulai session dan mengecek authentication

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $nama_budget = $_POST['nama_budget'];
    // Gunakan jumlah_raw yang sudah diunformat
    $jumlah = isset($_POST['jumlah_raw']) ? $_POST['jumlah_raw'] : str_replace('.', '', $_POST['jumlah']);
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
// Ikon untuk setiap kategori
$kategori_icons = [
    'Makanan' => 'fa-utensils',
    'Transportasi' => 'fa-car',
    'Hiburan' => 'fa-film',
    'Belanja' => 'fa-shopping-bag',
    'Kesehatan' => 'fa-heartbeat',
    'Pendidikan' => 'fa-graduation-cap',
    'Tagihan' => 'fa-file-invoice',
    'Lainnya' => 'fa-ellipsis-h'
];
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Budget - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../css/animasi.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: var(--primary-gradient);
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
        
        .btn-primary {
            background: var(--primary-gradient);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
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
            background: var(--success-gradient);
            color: white;
        }
        
        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
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
        
        .wizard-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            z-index: 1;
        }
        
        .wizard-step {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        
        .wizard-step .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: bold;
        }
        
        .wizard-step.active .step-circle {
            background: var(--primary-gradient);
        }
        
        .wizard-step .step-label {
            font-size: 0.85rem;
            color: #a0a0c0;
        }
        
        .wizard-step.active .step-label {
            color: #fff;
            font-weight: 600;
        }
        
        /* Dropdown yang benar - tanpa duplikasi */
        .form-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: none;
            padding-right: 2.5rem;
            position: relative;
        }
        
        /* Container untuk dropdown dengan panah custom */
        .select-wrapper {
            position: relative;
            display: block;
        }
        
        .select-wrapper::after {
            content: "▼";
            font-size: 12px;
            color: #a0a0c0;
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            transition: all 0.3s ease;
            z-index: 2;
        }
        
        /* Saat dropdown focus atau terbuka, panah berubah menjadi ke atas */
        .select-wrapper.dropdown-open::after {
            content: "▲";
            color: #764ba2;
        }
        
        /* Saat dropdown focus (tapi belum tentu terbuka) */
        .form-select:focus + .select-wrapper::after {
            color: #764ba2;
        }
        
        /* Style untuk option */
        .form-select option {
            background-color: #1e1e28;
            color: #fff;
            padding: 12px;
        }
        
        .form-select option:checked,
        .form-select option:hover,
        .form-select option:focus {
            background-color: rgba(118, 75, 162, 0.3);
            color: #fff;
        }
        
        /* Untuk browser WebKit (Chrome, Safari) */
        .form-select::-webkit-scrollbar {
            width: 8px;
        }
        
        .form-select::-webkit-scrollbar-track {
            background: rgba(30, 30, 40, 0.9);
        }
        
        .form-select::-webkit-scrollbar-thumb {
            background: rgba(118, 75, 162, 0.5);
            border-radius: 4px;
        }
        
        .form-select::-webkit-scrollbar-thumb:hover {
            background: rgba(118, 75, 162, 0.7);
        }
        
        /* Untuk Firefox */
        .form-select option {
            scrollbar-width: thin;
            scrollbar-color: rgba(118, 75, 162, 0.5) rgba(30, 30, 40, 0.9);
        }
        
        /* Style untuk input jumlah yang sudah diformat */
        #jumlah.formatted {
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        
        /* Style untuk validasi */
        .was-validated .form-control:invalid,
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        
        .was-validated .form-control:valid,
        .form-control.is-valid {
            border-color: #198754;
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
                        <h1 class="h3 mb-1">
                            <i class="fas fa-plus-circle me-2" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>Buat Budget Baru
                        </h1>
                        <p class="text-muted mb-0">Buat budget baru untuk mengatur keuangan Anda</p>
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
        
        <!-- Wizard Steps -->
        <div class="row mb-4 fade-in">
            <div class="col-12">
                <div class="wizard-steps">
                    <div class="wizard-step active">
                        <div class="step-circle">1</div>
                        <div class="step-label">Informasi Dasar</div>
                    </div>
                    <div class="wizard-step">
                        <div class="step-circle">2</div>
                        <div class="step-label">Jumlah & Periode</div>
                    </div>
                    <div class="wizard-step">
                        <div class="step-circle">3</div>
                        <div class="step-label">Kategori & Tanggal</div>
                    </div>
                    <div class="wizard-step">
                        <div class="step-circle">4</div>
                        <div class="step-label">Konfirmasi</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center fade-in">
            <div class="col-lg-10">
                <div class="card shadow-lg">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 text-white">
                                <i class="fas fa-plus-circle me-2"></i>Formulir Budget Baru
                            </h4>
                            <div class="badge bg-white bg-opacity-25 px-3 py-2">
                                <i class="fas fa-lightbulb me-1"></i>Tips: Budget yang realistis
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
                                               placeholder="Contoh: Budget Makan Bulanan" required>
                                        <div class="invalid-feedback">
                                            Mohon isi nama budget.
                                        </div>
                                        <small class="form-text text-muted mt-2">
                                            Beri nama yang mudah diingat untuk budget Anda
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label for="jumlah" class="form-label">
                                            <i class="fas fa-money-bill-wave me-2"></i>Jumlah Budget
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control" id="jumlah" name="jumlah" 
                                                   placeholder="0" required>
                                            <!-- Input hidden untuk menyimpan nilai tanpa format -->
                                            <input type="hidden" id="jumlah_raw" name="jumlah_raw">
                                            <div class="invalid-feedback">
                                                Mohon isi jumlah budget minimal Rp 1.000.
                                            </div>
                                        </div>
                                        <small class="form-text text-muted mt-2">
                                            <i class="fas fa-info-circle me-1"></i>Contoh: 1.000.000
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
                                        <div class="select-wrapper">
                                            <select class="form-select" id="periode" name="periode" required>
                                                <option value="">Pilih Periode</option>
                                                <option value="harian">Harian</option>
                                                <option value="mingguan">Mingguan</option>
                                                <option value="bulanan" selected>Bulanan</option>
                                                <option value="tahunan">Tahunan</option>
                                            </select>
                                        </div>
                                        <small class="form-text text-muted mt-2">
                                            Pilih periode pengulangan budget
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label for="kategori" class="form-label">
                                            <i class="fas fa-filter me-2"></i>Kategori
                                        </label>
                                        <div class="select-wrapper">
                                            <select class="form-select" id="kategori" name="kategori" required>
                                                <option value="">Pilih Kategori</option>
                                                <?php foreach ($kategori_list as $kategori_item): ?>
                                                    <option value="<?php echo $kategori_item; ?>">
                                                        <?php echo $kategori_item; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <small class="form-text text-muted mt-2">
                                            Kategori membantu dalam pelacakan pengeluaran
                                        </small>
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
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label for="tanggal_akhir" class="form-label">
                                            <i class="fas fa-stop-circle me-2"></i>Tanggal Berakhir
                                        </label>
                                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" required>
                                        <small class="form-text text-muted mt-2">
                                            Budget akan aktif hingga tanggal ini
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-group">
                                    <label for="deskripsi" class="form-label">
                                        <i class="fas fa-align-left me-2"></i>Deskripsi (Opsional)
                                    </label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" 
                                              rows="4" placeholder="Tambahkan deskripsi atau catatan tentang budget ini..."></textarea>
                                    <div class="form-text">Maksimal 500 karakter.</div>
                                </div>
                            </div>

                            <!-- Preview Card -->
                            <div class="alert alert-info bg-dark border-info mb-4">
                                <h6 class="mb-3"><i class="fas fa-eye me-2"></i>Pratinjau Budget</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Nama Budget</small>
                                        <strong id="previewNama">-</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Jumlah</small>
                                        <strong id="previewJumlah">Rp 0</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Periode</small>
                                        <strong id="previewPeriode">-</strong>
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
                                    <button type="button" onclick="resetForm()" class="btn btn-outline-light">
                                        <i class="fas fa-redo me-1"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Simpan Budget
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
        // Fungsi untuk format Rupiah dengan titik
        function formatRupiah(angka) {
            if (!angka) return '';
            
            // Hapus semua karakter selain angka
            let number_string = angka.toString().replace(/\D/g, '');
            
            // Format dengan titik sebagai pemisah ribuan
            let split = number_string.split('.');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        // Fungsi untuk unformat (hapus titik)
        function unformatRupiah(rupiah) {
            return rupiah.toString().replace(/\D/g, '');
        }

        // Format input jumlah saat ketik
        document.getElementById('jumlah').addEventListener('keyup', function(e) {
            // Dapatkan posisi kursor
            let cursorPosition = this.selectionStart;
            
            // Format nilai
            let formattedValue = formatRupiah(this.value);
            this.value = formattedValue;
            
            // Toggle class formatted
            if (formattedValue) {
                this.classList.add('formatted');
            } else {
                this.classList.remove('formatted');
            }
            
            // Sesuaikan posisi kursor setelah format
            let formattedLength = this.value.length;
            let originalLength = e.target.value.length;
            let delta = formattedLength - originalLength;
            
            this.setSelectionRange(cursorPosition + delta, cursorPosition + delta);
            
            // Update preview
            updatePreview();
        });

        // Juga format saat input (untuk paste)
        document.getElementById('jumlah').addEventListener('input', function() {
            this.value = formatRupiah(this.value);
            if (this.value) {
                this.classList.add('formatted');
            } else {
                this.classList.remove('formatted');
            }
            updatePreview();
        });

        // Saat form submit, unformat nilai
        document.getElementById('budgetForm').addEventListener('submit', function(e) {
            // Unformat nilai jumlah sebelum submit
            const jumlahInput = document.getElementById('jumlah');
            const jumlahUnformatted = unformatRupiah(jumlahInput.value);
            
            // Set nilai ke input hidden
            document.getElementById('jumlah_raw').value = jumlahUnformatted;
            
            // Validasi minimal 1000
            if (parseInt(jumlahUnformatted) < 1000) {
                e.preventDefault();
                jumlahInput.classList.add('is-invalid');
                jumlahInput.nextElementSibling.textContent = 'Jumlah minimal Rp 1.000';
                jumlahInput.focus();
            }
        });

        // Update fungsi updatePreview untuk format Rupiah
        function updatePreview() {
            document.getElementById('previewNama').textContent = 
                document.getElementById('nama_budget').value || '-';
            
            const jumlah = unformatRupiah(document.getElementById('jumlah').value);
            const formattedJumlah = jumlah ? 'Rp ' + formatRupiah(jumlah) : 'Rp 0';
            document.getElementById('previewJumlah').textContent = formattedJumlah;
            
            const periode = document.getElementById('periode').value;
            document.getElementById('previewPeriode').textContent = 
                periode ? periode.charAt(0).toUpperCase() + periode.slice(1) : '-';
        }

        // Reset form
        function resetForm() {
            document.getElementById('budgetForm').reset();
            document.getElementById('jumlah').value = '';
            document.getElementById('jumlah').classList.remove('formatted', 'is-invalid');
            document.getElementById('tanggal_mulai').value = '<?php echo date('Y-m-d'); ?>';
            document.getElementById('tanggal_mulai').dispatchEvent(new Event('change'));
            updatePreview();
        }

        // Update preview on input
        document.getElementById('nama_budget').addEventListener('input', updatePreview);
        document.getElementById('periode').addEventListener('change', function() {
            updatePreview();
            // Update tanggal akhir berdasarkan periode baru
            document.getElementById('tanggal_mulai').dispatchEvent(new Event('change'));
        });
        document.getElementById('tanggal_mulai').addEventListener('change', updatePreview);

        // Set default tanggal akhir (1 bulan dari sekarang)
        document.getElementById('tanggal_mulai').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const endDate = new Date(startDate);
            
            const periode = document.getElementById('periode').value;
            switch(periode) {
                case 'harian':
                    endDate.setDate(endDate.getDate() + 30); // Default 30 hari
                    break;
                case 'mingguan':
                    endDate.setDate(endDate.getDate() + 28); // 4 minggu
                    break;
                case 'bulanan':
                    endDate.setMonth(endDate.getMonth() + 1);
                    break;
                case 'tahunan':
                    endDate.setFullYear(endDate.getFullYear() + 1);
                    break;
                default:
                    endDate.setMonth(endDate.getMonth() + 1);
            }
            
            document.getElementById('tanggal_akhir').value = endDate.toISOString().split('T')[0];
            updatePreview();
        });

        // Trigger change event on load
        document.getElementById('tanggal_mulai').dispatchEvent(new Event('change'));
        
        // Initialize preview
        updatePreview();
        
        // Animate form inputs on focus
        document.querySelectorAll('.form-control, .form-select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('input-focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('input-focused');
            });
        });
        
        // Wizard step animation
        const wizardSteps = document.querySelectorAll('.wizard-step');
        const formGroups = document.querySelectorAll('.form-group');
        
        formGroups.forEach((group, index) => {
            group.addEventListener('focusin', () => {
                wizardSteps.forEach(step => step.classList.remove('active'));
                wizardSteps[Math.min(index, wizardSteps.length - 1)].classList.add('active');
            });
        });
        
        // Logic untuk mengubah panah dropdown saat diklik/dibuka
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.form-select');
            
            dropdowns.forEach(dropdown => {
                let wrapper = dropdown.parentElement;
                let isOpen = false;
                
                // Untuk mouse click
                dropdown.addEventListener('mousedown', function(e) {
                    isOpen = !isOpen;
                    if (isOpen) {
                        wrapper.classList.add('dropdown-open');
                    } else {
                        wrapper.classList.remove('dropdown-open');
                    }
                });
                
                // Untuk keyboard (tab + space/enter)
                dropdown.addEventListener('keydown', function(e) {
                    if (e.key === ' ' || e.key === 'Enter' || e.key === 'Spacebar') {
                        isOpen = true;
                        wrapper.classList.add('dropdown-open');
                    }
                });
                
                dropdown.addEventListener('keyup', function(e) {
                    if (e.key === ' ' || e.key === 'Enter' || e.key === 'Spacebar') {
                        setTimeout(() => {
                            isOpen = false;
                            wrapper.classList.remove('dropdown-open');
                        }, 100);
                    }
                });
                
                // Saat kehilangan fokus
                dropdown.addEventListener('blur', function() {
                    isOpen = false;
                    wrapper.classList.remove('dropdown-open');
                });
                
                // Saat pilihan berubah
                dropdown.addEventListener('change', function() {
                    isOpen = false;
                    wrapper.classList.remove('dropdown-open');
                });
                
                // Cek status awal
                dropdown.addEventListener('focus', function() {
                    wrapper.classList.add('dropdown-open');
                });
            });
            
            // Fix untuk iOS Safari
            if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                document.querySelectorAll('.form-select').forEach(select => {
                    select.style.webkitAppearance = 'menulist';
                });
            }
            
            // Format saat load jika ada nilai sebelumnya
            const jumlahInput = document.getElementById('jumlah');
            if (jumlahInput.value) {
                jumlahInput.value = formatRupiah(jumlahInput.value);
                jumlahInput.classList.add('formatted');
                updatePreview();
            }
        });

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
    </script>
</body>
</html>