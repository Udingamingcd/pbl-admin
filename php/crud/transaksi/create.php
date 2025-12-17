<?php
require_once '../../middleware/auth.php';
require_once '../../config.php';
require_once '../../koneksi.php';

// Pastikan session sudah start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $kategori = $_POST['kategori'];
    $jenis = $_POST['jenis'];
    
    // Format jumlah: hapus pemisah ribuan dan konversi ke float
    $jumlah = str_replace('.', '', $_POST['jumlah']);
    $jumlah = floatval($jumlah);
    
    $deskripsi = $_POST['deskripsi'];
    $tanggal = $_POST['tanggal'];
    $metode_bayar = $_POST['metode_bayar'] ?? null;
    $lokasi = $_POST['lokasi'] ?? null;

    // Validasi server-side
    $errors = [];
    
    if (empty($jenis)) {
        $errors[] = 'Jenis transaksi harus dipilih.';
    }
    
    if (empty($kategori)) {
        $errors[] = 'Kategori transaksi harus dipilih.';
    }
    
    if ($jumlah <= 0) {
        $errors[] = 'Jumlah harus lebih dari 0.';
    } elseif ($jumlah > 999999999999999) {
        $errors[] = 'Jumlah terlalu besar. Maksimal: 999.999.999.999.999';
    }
    
    if (empty($tanggal)) {
        $errors[] = 'Tanggal transaksi harus diisi.';
    } else {
        // Validasi: tanggal tidak boleh lebih dari hari ini
        $today = date('Y-m-d');
        if ($tanggal > $today) {
            $errors[] = 'Tanggal transaksi tidak boleh lebih dari hari ini.';
        }
    }

    if (empty($errors)) {
        try {
            $db = new Database();
            $db->query('INSERT INTO transaksi (user_id, kategori, jenis, jumlah, deskripsi, tanggal, metode_bayar, lokasi) 
                        VALUES (:user_id, :kategori, :jenis, :jumlah, :deskripsi, :tanggal, :metode_bayar, :lokasi)');
            
            $db->bind(':user_id', $user_id);
            $db->bind(':kategori', $kategori);
            $db->bind(':jenis', $jenis);
            $db->bind(':jumlah', $jumlah);
            $db->bind(':deskripsi', $deskripsi);
            $db->bind(':tanggal', $tanggal);
            $db->bind(':metode_bayar', $metode_bayar);
            $db->bind(':lokasi', $lokasi);
            
            if ($db->execute()) {
                $_SESSION['success_message'] = 'Transaksi berhasil ditambahkan!';
                header('Location: read.php');
                exit;
            } else {
                $_SESSION['error_message'] = 'Gagal menambahkan transaksi.';
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}

$kategori_pemasukan = ['Gaji', 'Investasi', 'Bonus', 'Lainnya'];
$kategori_pengeluaran = ['Makanan', 'Transportasi', 'Hiburan', 'Kesehatan', 'Pendidikan', 'Belanja', 'Tagihan', 'Lainnya'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Transaksi - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3a0ca3;
            --secondary: #7209b7;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --card-bg: #ffffff;
            --body-bg: #f8fafc;
            --text-primary: #1a1a1a;
            --text-secondary: #6c757d;
            --border-color: #e9ecef;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
            --border-radius: 16px;
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --gradient-success: linear-gradient(135deg, #4cc9f0 0%, #4895ef 100%);
            --gradient-danger: linear-gradient(135deg, #f72585 0%, #b5179e 100%);
        }

        [data-bs-theme="dark"] {
            --primary: #4895ef;
            --primary-light: #4cc9f0;
            --primary-dark: #4361ee;
            --secondary: #b5179e;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4cc9f0;
            --light: #343a40;
            --dark: #f8f9fa;
            --card-bg: #1e1e1e;
            --body-bg: #121212;
            --text-primary: #f8f9fa;
            --text-secondary: #adb5bd;
            --border-color: #343a40;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.4);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--body-bg);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(67, 97, 238, 0.05) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(247, 37, 133, 0.05) 0%, transparent 20%);
        }

        .navbar {
            background: var(--gradient-primary);
            box-shadow: var(--shadow);
            padding: 0.75rem 0;
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background-color: var(--card-bg);
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-5px);
        }

        .card-header {
            background: var(--gradient-primary);
            color: white;
            border-bottom: none;
            padding: 1.5rem 2rem;
            font-weight: 600;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            animation: shimmer 3s infinite;
        }

        .card-body {
            padding: 2rem;
        }

        .form-control, .form-select {
            background-color: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-primary);
            padding: 1rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
            background-color: var(--card-bg);
            color: var(--text-primary);
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .form-text {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 1rem 2rem;
            transition: all 0.3s ease;
            border: none;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
        }

        .btn-outline-secondary {
            border: 2px solid var(--border-color);
            color: var(--text-secondary);
            background: transparent;
        }

        .btn-outline-secondary:hover {
            background-color: var(--border-color);
            color: var(--text-primary);
            border-color: var(--border-color);
            transform: translateY(-2px);
        }

        /* Perbaikan untuk input group dengan label */
        .amount-input-container {
            position: relative;
        }

        .input-group-text {
            background: var(--gradient-primary);
            border: 2px solid var(--border-color);
            color: white;
            border-right: none;
            font-weight: 600;
            padding: 1rem 1.25rem;
        }

        .input-group .form-control {
            border-left: none;
            padding-left: 0.5rem;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: var(--primary);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            backdrop-filter: blur(10px);
        }

        h2 {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 2rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .text-muted {
            color: var(--text-secondary) !important;
        }

        /* Modern Form Sections */
        .form-section {
            margin-bottom: 2.5rem;
            padding: 2rem;
            border-radius: var(--border-radius);
            background: var(--card-bg);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .form-section:hover::before {
            opacity: 1;
        }

        .form-section:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }

        /* PERBAIKAN: Form section title yang tepat di tengah */
        .form-section-title {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-color);
            text-align: center;
            width: 100%;
        }

        .form-section-title i {
            margin-right: 0.75rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.5rem;
        }

        /* PERBAIKAN: Teks deskripsi di bawah title yang tepat di tengah */
        .form-section-description {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-size: 1rem;
            line-height: 1.5;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Transaction Type Selector */
        .transaction-type-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .type-option {
            position: relative;
            cursor: pointer;
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            background: var(--card-bg);
            overflow: hidden;
        }

        .type-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .type-option.income::before {
            background: var(--gradient-success);
        }

        .type-option.expense::before {
            background: var(--gradient-danger);
        }

        .type-option:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .type-option.active {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .type-option.active::before {
            transform: scaleX(1);
        }

        .type-option.income.active {
            border-color: #4cc9f0;
        }

        .type-option.expense.active {
            border-color: #f72585;
        }

        .type-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        .type-option.income .type-icon {
            background: var(--gradient-success);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .type-option.expense .type-icon {
            background: var(--gradient-danger);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .type-label {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .type-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Floating Labels Enhanced */
        .floating-label {
            position: relative;
            margin-bottom: 2rem;
        }

        .floating-label .form-control,
        .floating-label .form-select {
            padding-top: 1.75rem;
            padding-bottom: 0.75rem;
            height: auto;
        }

        .floating-label label {
            position: absolute;
            top: 1rem;
            left: 1.25rem;
            font-size: 1rem;
            color: var(--text-secondary);
            transition: all 0.3s ease;
            pointer-events: none;
            background: var(--card-bg);
            padding: 0 0.5rem;
            z-index: 1;
            font-weight: 500;
        }

        .floating-label .form-control:focus ~ label,
        .floating-label .form-control:not(:placeholder-shown) ~ label,
        .floating-label .form-select:focus ~ label,
        .floating-label .form-select:not([value=""]) ~ label {
            top: -0.5rem;
            font-size: 0.85rem;
            color: var(--primary);
            font-weight: 600;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }

        .required-field::after {
            content: " *";
            color: var(--danger);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .card {
            animation: fadeIn 0.6s ease-out;
        }

        .form-section {
            animation: fadeIn 0.8s ease-out;
        }

        .form-section:nth-child(2) {
            animation-delay: 0.1s;
        }

        .form-section:nth-child(3) {
            animation-delay: 0.2s;
        }

        /* Progress Steps */
        .form-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            position: relative;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .form-progress::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--border-color);
            transform: translateY(-50%);
            z-index: 1;
        }

        .progress-step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--text-secondary);
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .progress-step.active {
            background: var(--gradient-primary);
            border-color: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        .progress-step.completed {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }

        .step-label {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: var(--text-secondary);
            white-space: nowrap;
        }

        .progress-step.active .step-label {
            color: var(--primary);
            font-weight: 600;
        }

        /* Character Counter */
        .char-counter {
            text-align: right;
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .char-counter.near-limit {
            color: var(--warning);
        }

        .char-counter.over-limit {
            color: var(--danger);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            
            .form-section {
                padding: 1.5rem;
            }
            
            .transaction-type-selector {
                grid-template-columns: 1fr;
                max-width: 100%;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .form-actions .btn {
                width: 100%;
            }

            .form-progress {
                max-width: 100%;
            }
        }

        /* Loading State */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            border-radius: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        [data-bs-theme="dark"] .loading::after {
            background: rgba(0,0,0,0.8);
        }

        /* Perbaikan khusus untuk input jumlah dengan label */
        .amount-input-group {
            position: relative;
        }
        
        .amount-input-group label {
            position: absolute;
            top: -0.5rem;
            left: 1rem;
            font-size: 0.85rem;
            color: var(--primary);
            background: var(--card-bg);
            padding: 0 0.5rem;
            z-index: 5;
            font-weight: 600;
        }
        
        .amount-input-group .input-group {
            margin-top: 0.5rem;
        }

        /* Style untuk tombol yang disabled */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .btn:disabled:hover::before {
            left: -100%;
        }

        /* PERBAIKAN: Container untuk form content yang terpusat */
        .form-content-centered {
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        /* PERBAIKAN: Row yang terpusat */
        .form-row-centered {
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* CSS Tambahan untuk Validasi */
        .is-invalid {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 4px rgba(247, 37, 133, 0.15) !important;
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: var(--danger);
            font-weight: 500;
        }

        .is-invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body data-bs-theme="dark">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chart-pie me-2"></i>Finansialku
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../../../dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="read.php">
                            <i class="fas fa-exchange-alt me-1"></i>Transaksi
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 main-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-plus me-2"></i>Tambah Transaksi</h2>
                <p class="text-muted mb-0">Catat pemasukan atau pengeluaran Anda dengan mudah</p>
            </div>
            <a href="../../../dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
            </a>
        </div>

        <!-- Progress Steps -->
        <div class="form-progress mb-5">
            <div class="progress-step active" data-step="1">
                <span>1</span>
                <div class="step-label">Jenis</div>
            </div>
            <div class="progress-step" data-step="2">
                <span>2</span>
                <div class="step-label">Detail</div>
            </div>
            <div class="progress-step" data-step="3">
                <span>3</span>
                <div class="step-label">Tambahan</div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Form Transaksi Baru</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger d-flex align-items-center mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="post" id="transactionForm">
                            <!-- Step 1: Transaction Type -->
                            <div class="form-section step-content" data-step="1">
                                <div class="form-section-title">
                                    <i class="fas fa-exchange-alt"></i> Jenis Transaksi
                                </div>
                                <p class="form-section-description">Pilih jenis transaksi yang ingin Anda catat</p>
                                
                                <div class="transaction-type-selector">
                                    <div class="type-option income" data-type="pemasukan">
                                        <i class="fas fa-money-bill-wave type-icon"></i>
                                        <span class="type-label">Pemasukan</span>
                                        <span class="type-description">Uang masuk ke dompet Anda</span>
                                    </div>
                                    <div class="type-option expense" data-type="pengeluaran">
                                        <i class="fas fa-shopping-cart type-icon"></i>
                                        <span class="type-label">Pengeluaran</span>
                                        <span class="type-description">Uang keluar dari dompet Anda</span>
                                    </div>
                                </div>
                                <input type="hidden" id="jenis" name="jenis" required>
                                <div class="invalid-feedback" id="jenis-error">Pilih jenis transaksi terlebih dahulu.</div>
                            </div>

                            <!-- Step 2: Transaction Details -->
                            <div class="form-section step-content" data-step="2" style="display: none;">
                                <div class="form-section-title">
                                    <i class="fas fa-info-circle"></i> Detail Transaksi
                                </div>
                                <p class="form-section-description">Isi informasi detail tentang transaksi Anda</p>
                                
                                <div class="form-content-centered">
                                    <div class="row form-row-centered">
                                        <div class="col-md-6">
                                            <div class="floating-label">
                                                <select class="form-select" id="kategori" name="kategori" required>
                                                    <option value="" disabled selected>Pilih Kategori</option>
                                                </select>
                                                <label for="kategori" class="required-field">Kategori Transaksi</label>
                                                <div class="invalid-feedback" id="kategori-error">Pilih kategori transaksi terlebih dahulu.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Perbaikan untuk input jumlah -->
                                            <div class="amount-input-group">
                                                <label for="jumlah" class="required-field">Jumlah Transaksi</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="text" class="form-control" id="jumlah" name="jumlah" 
                                                           required placeholder="0"
                                                           oninput="formatCurrency(this)">
                                                </div>
                                                <div class="char-counter" id="amount-counter">Maks: 999 Triliun</div>
                                                <div class="invalid-feedback" id="jumlah-error">Jumlah harus lebih dari 0 dan maksimal 999.999.999.999.999</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3 form-row-centered">
                                        <div class="col-md-6">
                                            <div class="floating-label">
                                                <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                                       value="<?= date('Y-m-d') ?>" required>
                                                <label for="tanggal" class="required-field">Tanggal Transaksi</label>
                                                <div class="invalid-feedback" id="tanggal-error">Tanggal transaksi harus diisi dan tidak boleh lebih dari hari ini.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="floating-label">
                                                <select class="form-select" id="metode_bayar" name="metode_bayar">
                                                    <option value="" disabled selected>Pilih Metode Pembayaran</option>
                                                    <option value="Tunai">💵 Tunai</option>
                                                    <option value="Transfer Bank">🏦 Transfer Bank</option>
                                                    <option value="Kartu Kredit">💳 Kartu Kredit</option>
                                                    <option value="E-Wallet">📱 E-Wallet</option>
                                                    <option value="Debit">💳 Kartu Debit</option>
                                                </select>
                                                <label for="metode_bayar">Metode Pembayaran</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Additional Information -->
                            <div class="form-section step-content" data-step="3" style="display: none;">
                                <div class="form-section-title">
                                    <i class="fas fa-sticky-note"></i> Informasi Tambahan
                                </div>
                                <p class="form-section-description">Tambahkan informasi lain yang relevan (opsional)</p>
                                
                                <div class="form-content-centered">
                                    <div class="floating-label">
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" 
                                                  rows="3" placeholder=" " maxlength="500"></textarea>
                                        <label for="deskripsi">Deskripsi Transaksi</label>
                                        <div class="char-counter" id="desc-counter">0/500 karakter</div>
                                    </div>
                                    <div class="floating-label mt-4">
                                        <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                               placeholder=" " maxlength="100">
                                        <label for="lokasi">Lokasi Transaksi</label>
                                        <div class="char-counter" id="location-counter">0/100 karakter</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="display: none;">
                                    <i class="fas fa-arrow-left me-1"></i>Sebelumnya
                                </button>
                                <div class="d-flex gap-2">
                                    <a href="read.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Batal
                                    </a>
                                    <button type="button" class="btn btn-primary" id="nextBtn">
                                        Selanjutnya <i class="fas fa-arrow-right ms-1"></i>
                                    </button>
                                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                        <i class="fas fa-check me-1"></i>Simpan Transaksi
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
    <script>
        const kategoriPemasukan = <?php echo json_encode($kategori_pemasukan); ?>;
        const kategoriPengeluaran = <?php echo json_encode($kategori_pengeluaran); ?>;
        let currentStep = 1;

        // Initialize the form
        document.addEventListener('DOMContentLoaded', function() {
            initializeForm();
        });

        function initializeForm() {
            // Transaction type selection
            document.querySelectorAll('.type-option').forEach(option => {
                option.addEventListener('click', function() {
                    // Remove active class from all options
                    document.querySelectorAll('.type-option').forEach(opt => {
                        opt.classList.remove('active');
                    });
                    
                    // Add active class to clicked option
                    this.classList.add('active');
                    
                    // Set the value of the hidden input
                    document.getElementById('jenis').value = this.dataset.type;
                    
                    // Update category options
                    updateCategoryOptions(this.dataset.type);
                    
                    // Enable next button
                    document.getElementById('nextBtn').disabled = false;
                    
                    // Remove error state
                    document.getElementById('jenis-error').style.display = 'none';
                    
                    // Debug log
                    console.log('Jenis transaksi dipilih:', this.dataset.type);
                });
            });

            // Character counters
            document.getElementById('deskripsi').addEventListener('input', function() {
                updateCharCounter('desc-counter', this.value.length, 500);
            });

            document.getElementById('lokasi').addEventListener('input', function() {
                updateCharCounter('location-counter', this.value.length, 100);
            });

            // Step navigation
            document.getElementById('nextBtn').addEventListener('click', nextStep);
            document.getElementById('prevBtn').addEventListener('click', prevStep);

            // Real-time validation for step 2 fields
            document.getElementById('kategori').addEventListener('change', function() {
                validateCurrentStep();
            });

            document.getElementById('tanggal').addEventListener('change', function() {
                validateDate(this);
                validateCurrentStep();
            });

            document.getElementById('jumlah').addEventListener('input', function() {
                validateCurrentStep();
            });

            // Initially disable next button until type is selected
            document.getElementById('nextBtn').disabled = true;
            
            // Debug info
            console.log('Form initialized, current step:', currentStep);
        }

        // Validasi tanggal tidak boleh lebih dari hari ini
        function validateDate(input) {
            const today = new Date().toISOString().split('T')[0];
            const selectedDate = input.value;
            
            if (selectedDate > today) {
                input.classList.add('is-invalid');
                return false;
            } else {
                input.classList.remove('is-invalid');
                return true;
            }
        }

        function updateCategoryOptions(type) {
            const kategoriSelect = document.getElementById('kategori');
            kategoriSelect.innerHTML = '<option value="" disabled selected>Pilih Kategori</option>';
            
            const kategoriList = type === 'pemasukan' ? kategoriPemasukan : kategoriPengeluaran;
            
            kategoriList.forEach(kategori => {
                const option = document.createElement('option');
                option.value = kategori;
                option.textContent = kategori;
                kategoriSelect.appendChild(option);
            });
            
            // Debug log
            console.log('Kategori diperbarui untuk:', type);
        }

        function updateCharCounter(counterId, currentLength, maxLength) {
            const counter = document.getElementById(counterId);
            counter.textContent = `${currentLength}/${maxLength} karakter`;
            
            // Update counter color based on usage
            counter.classList.remove('near-limit', 'over-limit');
            if (currentLength > maxLength * 0.8) {
                counter.classList.add('near-limit');
            }
            if (currentLength > maxLength) {
                counter.classList.add('over-limit');
            }
        }

        function nextStep() {
            // Validate current step
            if (!validateStep(currentStep)) {
                console.log('Validasi step', currentStep, 'gagal');
                return;
            }
            
            console.log('Pindah dari step', currentStep, 'ke step', currentStep + 1);
            
            // Hide current step
            document.querySelector(`.step-content[data-step="${currentStep}"]`).style.display = 'none';
            document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.remove('active');
            
            // Show next step
            currentStep++;
            document.querySelector(`.step-content[data-step="${currentStep}"]`).style.display = 'block';
            document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.add('active');
            
            // Update navigation buttons
            updateNavigationButtons();
        }

        function prevStep() {
            console.log('Kembali dari step', currentStep, 'ke step', currentStep - 1);
            
            // Hide current step
            document.querySelector(`.step-content[data-step="${currentStep}"]`).style.display = 'none';
            document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.remove('active');
            
            // Show previous step
            currentStep--;
            document.querySelector(`.step-content[data-step="${currentStep}"]`).style.display = 'block';
            document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.add('active');
            
            // Update navigation buttons
            updateNavigationButtons();
        }

        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            
            // Show/hide previous button
            if (currentStep > 1) {
                prevBtn.style.display = 'block';
            } else {
                prevBtn.style.display = 'none';
            }
            
            // Show/hide next and submit buttons
            if (currentStep < 3) {
                nextBtn.style.display = 'block';
                submitBtn.style.display = 'none';
            } else {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'block';
            }
            
            // Enable/disable next button based on step validation
            const isValid = validateStep(currentStep);
            nextBtn.disabled = !isValid;
            
            console.log('Navigation updated - Step:', currentStep, 'Valid:', isValid);
        }

        function validateStep(step) {
            console.log('Validating step:', step);
            
            switch(step) {
                case 1:
                    const jenis = document.getElementById('jenis').value;
                    console.log('Step 1 - Jenis:', jenis);
                    
                    if (!jenis) {
                        document.getElementById('jenis-error').style.display = 'block';
                        return false;
                    } else {
                        document.getElementById('jenis-error').style.display = 'none';
                        return true;
                    }
                    
                case 2:
                    const kategori = document.getElementById('kategori').value;
                    const jumlah = document.getElementById('jumlah').value.replace(/[^\d]/g, '');
                    const tanggal = document.getElementById('tanggal').value;
                    
                    console.log('Step 2 - Kategori:', kategori, 'Jumlah:', jumlah, 'Tanggal:', tanggal);
                    
                    // Validasi kategori
                    if (!kategori) {
                        document.getElementById('kategori-error').style.display = 'block';
                        return false;
                    } else {
                        document.getElementById('kategori-error').style.display = 'none';
                    }
                    
                    // Validasi jumlah
                    const isJumlahValid = jumlah && parseInt(jumlah) > 0 && parseInt(jumlah) <= 999999999999999;
                    if (!isJumlahValid) {
                        document.getElementById('jumlah-error').style.display = 'block';
                        return false;
                    } else {
                        document.getElementById('jumlah-error').style.display = 'none';
                    }
                    
                    // Validasi tanggal
                    const isTanggalValid = tanggal && validateDate(document.getElementById('tanggal'));
                    if (!isTanggalValid) {
                        document.getElementById('tanggal-error').style.display = 'block';
                        return false;
                    } else {
                        document.getElementById('tanggal-error').style.display = 'none';
                    }
                    
                    const isValid = kategori && isJumlahValid && isTanggalValid;
                    console.log('Step 2 validation result:', isValid);
                    return isValid;
                    
                case 3:
                    // Step 3 fields are optional, so always valid
                    console.log('Step 3 - Always valid');
                    return true;
                    
                default:
                    console.log('Step unknown - Invalid');
                    return false;
            }
        }

        function validateCurrentStep() {
            if (currentStep === 2) {
                const isValid = validateStep(2);
                document.getElementById('nextBtn').disabled = !isValid;
                console.log('Real-time validation - Step 2 valid:', isValid);
            }
        }

        function formatCurrency(input) {
            // Hapus semua karakter non-digit
            let value = input.value.replace(/[^\d]/g, '');
            
            // Batasi maksimal 15 digit (999.999.999.999.999) untuk mendukung 999 triliun
            if (value.length > 15) {
                value = value.substring(0, 15);
            }
            
            // Format sebagai currency dengan pemisah ribuan
            if (value) {
                // Konversi ke number untuk menghindari masalah dengan leading zeros
                const numberValue = parseInt(value, 10);
                if (!isNaN(numberValue)) {
                    input.value = numberValue.toLocaleString('id-ID');
                }
            } else {
                input.value = '';
            }
            
            // Update counter
            const counter = document.getElementById('amount-counter');
            if (value.length >= 15) {
                counter.classList.add('near-limit');
                counter.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Mendekati batas maksimal';
            } else {
                counter.classList.remove('near-limit');
                counter.textContent = 'Maks: 999 Triliun';
            }
            
            // Validate step 2 if we're on that step
            validateCurrentStep();
        }

        // Form submission
        document.getElementById('transactionForm').addEventListener('submit', function(e) {
            const jenisInput = document.getElementById('jenis');
            const kategoriInput = document.getElementById('kategori');
            const jumlahInput = document.getElementById('jumlah');
            const tanggalInput = document.getElementById('tanggal');
            
            let rawValue = jumlahInput.value.replace(/[^\d]/g, '');
            
            // Final validation
            const errors = [];
            
            if (!jenisInput.value) {
                errors.push('Pilih jenis transaksi terlebih dahulu.');
            }
            
            if (!kategoriInput.value) {
                errors.push('Pilih kategori transaksi terlebih dahulu.');
            }
            
            if (!rawValue || parseInt(rawValue, 10) <= 0) {
                errors.push('Jumlah harus lebih dari 0.');
            }
            
            if (parseInt(rawValue, 10) > 999999999999999) {
                errors.push('Jumlah terlalu besar. Maksimal: 999.999.999.999.999');
            }
            
            if (!tanggalInput.value) {
                errors.push('Tanggal transaksi harus diisi.');
            } else if (!validateDate(tanggalInput)) {
                errors.push('Tanggal transaksi tidak boleh lebih dari hari ini.');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                showError(errors.join('<br>'));
                return;
            }
            
            // Set nilai asli (tanpa format)
            jumlahInput.value = rawValue;
            
            // Show loading state
            const submitBtn = this.querySelector('#submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';
            submitBtn.disabled = true;
            
            // Add loading class to form
            this.classList.add('loading');
        });

        function showError(message) {
            // Create or update error alert
            let errorAlert = document.querySelector('.alert-danger');
            if (!errorAlert) {
                errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger d-flex align-items-center mb-4';
                errorAlert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><div></div>';
                document.querySelector('.card-body').insertBefore(errorAlert, document.querySelector('.card-body').firstChild);
            }
            
            errorAlert.querySelector('div').textContent = message;
            errorAlert.style.display = 'block';
            
            // Scroll to error message
            errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>
</body>
</html>