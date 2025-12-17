<?php
require_once '../../middleware/auth.php';
require_once '../../config.php';
require_once '../../koneksi.php';

// Pastikan session sudah start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$id = $_GET['id'] ?? 0;

$db = new Database();
$db->query('SELECT * FROM transaksi WHERE id = :id AND user_id = :user_id');
$db->bind(':id', $id);
$db->bind(':user_id', $_SESSION['user_id']);
$transaksi = $db->single();

if (!$transaksi) {
    $_SESSION['error_message'] = 'Transaksi tidak ditemukan.';
    header('Location: read.php');
    exit;
}

// Ambil data user dari database
$db->query('SELECT nama FROM users WHERE id = :user_id');
$db->bind(':user_id', $_SESSION['user_id']);
$user = $db->single();
$user_name = $user ? $user['nama'] : 'User';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
            $db->query('UPDATE transaksi SET kategori = :kategori, jenis = :jenis, jumlah = :jumlah, 
                        deskripsi = :deskripsi, tanggal = :tanggal, metode_bayar = :metode_bayar, 
                        lokasi = :lokasi WHERE id = :id AND user_id = :user_id');
            
            $db->bind(':kategori', $kategori);
            $db->bind(':jenis', $jenis);
            $db->bind(':jumlah', $jumlah);
            $db->bind(':deskripsi', $deskripsi);
            $db->bind(':tanggal', $tanggal);
            $db->bind(':metode_bayar', $metode_bayar);
            $db->bind(':lokasi', $lokasi);
            $db->bind(':id', $id);
            $db->bind(':user_id', $_SESSION['user_id']);
            
            if ($db->execute()) {
                $_SESSION['success_message'] = 'Transaksi berhasil diperbarui!';
                header('Location: read.php');
                exit;
            } else {
                $_SESSION['error_message'] = 'Gagal memperbarui transaksi.';
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
$kategori_list = $transaksi['jenis'] == 'pemasukan' ? $kategori_pemasukan : $kategori_pengeluaran;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --primary-dark: #1e3a8a;
            --secondary: #475569;
            --success: #059669;
            --danger: #dc2626;
            --warning: #d97706;
            --info: #0891b2;
            --light: #f8fafc;
            --dark: #0f172a;
            --card-bg: #ffffff;
            --body-bg: #f1f5f9;
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --border-radius: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-bs-theme="dark"] {
            --primary: #3b82f6;
            --primary-light: #60a5fa;
            --primary-dark: #1e40af;
            --secondary: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
            --light: #1e293b;
            --dark: #f8fafc;
            --card-bg: #1e293b;
            --body-bg: #0f172a;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px 0 rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--body-bg);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.5;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            box-shadow: var(--shadow-lg);
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            color: white !important;
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
            position: relative;
            min-height: 500px;
            overflow: hidden;
        }

        /* Form Steps Container - IMPROVED */
        .form-steps-container {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 450px;
        }

        .form-step {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            visibility: hidden;
            transform: translateX(50px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .form-step.active {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
            pointer-events: all;
            transition-delay: 0.2s;
            position: relative;
        }

        .form-step.exiting {
            opacity: 0;
            transform: translateX(-50px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-step.entering {
            opacity: 0;
            transform: translateX(50px);
        }

        /* Form Content Area */
        .form-content {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 1rem;
        }

        /* Form Controls */
        .form-control, .form-select {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: var(--transition);
            font-weight: 400;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
            background-color: var(--card-bg);
            color: var(--text-primary);
        }

        .form-control::placeholder {
            color: var(--text-muted);
            font-weight: 400;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }

        .form-text {
            color: var(--text-muted);
            font-size: 0.75rem;
            margin-top: 0.25rem;
            font-weight: 400;
        }

        .btn {
            border-radius: 6px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: var(--transition);
            border: 1px solid transparent;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-color: var(--primary);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);
            color: white;
            border-color: var(--warning);
        }

        .btn-outline-secondary {
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            background: transparent;
        }

        .btn-outline-secondary:hover {
            background-color: var(--border-color);
            color: var(--text-primary);
        }

        .btn-outline-light {
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            background: transparent;
        }

        .btn-outline-light:hover {
            background-color: var(--border-color);
            color: var(--text-primary);
        }

        /* Amount Input */
        .amount-input-container {
            position: relative;
        }

        .amount-limit {
            position: absolute;
            right: 0;
            top: -20px;
            font-size: 0.7rem;
            color: var(--text-muted);
            background: var(--card-bg);
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            font-weight: 500;
        }

        .input-group-text {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: 1px solid var(--border-color);
            color: white;
            border-right: none;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .input-group .form-control {
            border-left: none;
        }

        .alert {
            border-radius: 6px;
            border: 1px solid;
            padding: 0.75rem 1rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        h1, h2, h3, h4, h5, h6 {
            color: var(--text-primary);
            font-weight: 600;
            line-height: 1.3;
        }

        h2 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }

        /* Professional Form Sections */
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .form-section:last-of-type {
            border-bottom: none;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-title i {
            margin-right: 0.5rem;
            color: var(--primary);
            font-size: 1rem;
        }

        /* Transaction Type Buttons */
        .transaction-type-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--card-bg);
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            height: 100%;
        }

        .transaction-type-btn.active {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.05);
        }

        .transaction-type-btn i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .transaction-type-btn.pemasukan.active i {
            color: var(--success);
        }

        .transaction-type-btn.pengeluaran.active i {
            color: var(--danger);
        }

        .transaction-type-btn .type-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .transaction-type-btn .type-amount {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.125rem;
        }

        /* Form Actions - IMPROVED */
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            background-color: var(--card-bg);
            position: sticky;
            bottom: 0;
            z-index: 10;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .form-actions .btn {
            min-width: 120px;
            opacity: 1 !important;
            visibility: visible !important;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .form-actions .btn {
                width: 100%;
            }
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .stats-value {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stats-label {
            font-size: 0.75rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* Step Indicator - IMPROVED dengan jarak lebih baik */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 20px;
        }

        .step {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--text-muted);
            transition: var(--transition);
            position: relative;
            z-index: 2;
            border: 2px solid var(--border-color);
            font-size: 1rem;
            flex-shrink: 0;
        }

        .step.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
            transform: scale(1.1);
        }

        .step.completed {
            background: var(--success);
            color: white;
            border-color: var(--success);
        }

        .step-label {
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.75rem;
            color: var(--text-muted);
            white-space: nowrap;
            font-weight: 500;
            text-align: center;
            width: 100px;
        }

        .step.active .step-label {
            color: var(--primary);
            font-weight: 600;
        }

        .step.completed .step-label {
            color: var(--success);
        }

        .step-line {
            position: absolute;
            top: 50%;
            left: 50px;
            right: 50px;
            height: 3px;
            background: var(--border-color);
            transform: translateY(-50%);
            z-index: 1;
        }

        .step-line .progress {
            height: 100%;
            background: var(--primary);
            transition: var(--transition);
            width: 0%;
        }

        /* Feature Highlight */
        .feature-highlight {
            background: rgba(59, 130, 246, 0.05);
            border-radius: 6px;
            padding: 1rem;
            margin: 1rem 0;
            border-left: 3px solid var(--primary);
            border: 1px solid var(--border-color);
        }

        /* Review Cards */
        .review-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-bottom: 0.75rem;
        }

        .review-card .card-title {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .review-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.375rem 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.875rem;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .review-value {
            color: var(--text-primary);
            font-weight: 600;
            text-align: right;
        }

        /* Header Actions */
        .header-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }
            
            .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            
            h2 {
                font-size: 1.25rem;
            }
            
            .stats-card {
                padding: 0.75rem;
            }
            
            .step {
                width: 40px;
                height: 40px;
                font-size: 0.875rem;
            }
            
            .step-label {
                font-size: 0.65rem;
                bottom: -25px;
                width: 80px;
            }
            
            .step-line {
                left: 40px;
                right: 40px;
            }
            
            .form-section {
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
            }

            .header-actions {
                flex-direction: column;
                width: 100%;
                margin-top: 1rem;
            }

            .header-actions .btn {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .step-indicator {
                padding: 0 10px;
            }
            
            .step {
                width: 35px;
                height: 35px;
                font-size: 0.75rem;
            }
            
            .step-label {
                font-size: 0.6rem;
                bottom: -22px;
                width: 70px;
            }
            
            .step-line {
                left: 35px;
                right: 35px;
            }
        }

        /* Color Variations */
        .bg-success-light {
            background: linear-gradient(135deg, var(--success) 0%, #047857 100%);
        }

        .bg-warning-light {
            background: linear-gradient(135deg, var(--warning) 0%, #b45309 100%);
        }

        /* Validation Styles */
        .is-invalid {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.1) !important;
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
                <i class="fas fa-chart-line me-2"></i>Finansialku
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
                <div class="navbar-nav">
                    <div class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($user_name) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="d-flex flex-column align-items-center">
            <div class="loading-spinner"></div>
            <p class="text-white mt-2" style="font-size: 0.875rem;">Memperbarui transaksi...</p>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Header dengan tombol kembali -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-primary"><i class="fas fa-edit me-2"></i>Edit Transaksi</h2>
                <p class="text-muted mb-0" style="font-size: 0.875rem;">Perbarui informasi transaksi keuangan</p>
            </div>
            <div class="header-actions">
                <a href="read.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
                </a>
                <a href="../../../dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <div class="stats-value">Rp <?= number_format($transaksi['jumlah'], 0, ',', '.') ?></div>
                    <div class="stats-label">Nilai Transaksi</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card bg-success-light">
                    <div class="stats-value"><?= ucfirst($transaksi['jenis']) ?></div>
                    <div class="stats-label">Jenis Transaksi</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card bg-warning-light">
                    <div class="stats-value"><?= $transaksi['kategori'] ?></div>
                    <div class="stats-label">Kategori</div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Form Edit Transaksi</h5>
                        <a href="read.php" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-times me-1"></i>Batal
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger d-flex align-items-center mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div style="font-size: 0.875rem;"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Feature Highlight -->
                        <div class="feature-highlight mb-4">
                            <i class="fas fa-lightbulb"></i>
                            <strong style="font-size: 0.875rem;">Tips:</strong> 
                            <span style="font-size: 0.875rem;">Pastikan semua informasi sudah benar sebelum menyimpan</span>
                        </div>

                        <!-- Step Indicator - IMPROVED dengan jarak lebih baik -->
                        <div class="step-indicator">
                            <div class="step active">
                                1
                                <div class="step-label">Informasi Dasar</div>
                            </div>
                            <div class="step">
                                2
                                <div class="step-label">Detail Tambahan</div>
                            </div>
                            <div class="step">
                                3
                                <div class="step-label">Tinjau & Simpan</div>
                            </div>
                            <div class="step-line">
                                <div class="progress" id="stepProgress" style="width: 0%"></div>
                            </div>
                        </div>

                        <form method="post" id="updateForm">
                            <div class="form-steps-container">
                                <!-- Step 1: Basic Information -->
                                <div class="form-step active" id="step1">
                                    <div class="form-content">
                                        <!-- Jenis dan Kategori Section -->
                                        <div class="form-section">
                                            <div class="section-title">
                                                <i class="fas fa-tags"></i>Kategori Transaksi
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label"><i class="fas fa-exchange-alt"></i>Jenis Transaksi *</label>
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <div class="transaction-type-btn pemasukan <?= $transaksi['jenis'] == 'pemasukan' ? 'active' : '' ?>" data-type="pemasukan">
                                                                    <div>
                                                                        <i class="fas fa-money-bill-wave"></i>
                                                                        <div class="type-label">Pemasukan</div>
                                                                        <div class="type-amount">+ Saldo</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="transaction-type-btn pengeluaran <?= $transaksi['jenis'] == 'pengeluaran' ? 'active' : '' ?>" data-type="pengeluaran">
                                                                    <div>
                                                                        <i class="fas fa-shopping-cart"></i>
                                                                        <div class="type-label">Pengeluaran</div>
                                                                        <div class="type-amount">- Saldo</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" id="jenis" name="jenis" value="<?= $transaksi['jenis'] ?>" required>
                                                        <div class="invalid-feedback" id="jenis-error">Pilih jenis transaksi terlebih dahulu.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="kategori" class="form-label">Kategori *</label>
                                                        <select class="form-select" id="kategori" name="kategori" required>
                                                            <?php foreach ($kategori_list as $kategori_item): ?>
                                                                <option value="<?= $kategori_item ?>" 
                                                                    <?= $transaksi['kategori'] == $kategori_item ? 'selected' : '' ?>>
                                                                    <?= $kategori_item ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <div class="invalid-feedback" id="kategori-error">Pilih kategori transaksi terlebih dahulu.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Jumlah dan Tanggal Section -->
                                        <div class="form-section">
                                            <div class="section-title">
                                                <i class="fas fa-money-bill-wave"></i>Detail Transaksi
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3 amount-input-container">
                                                        <span class="amount-limit">Maks: 999 Triliun</span>
                                                        <label for="jumlah" class="form-label">Jumlah *</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">Rp</span>
                                                            <input type="text" class="form-control" id="jumlah" name="jumlah" 
                                                                   required value="<?= number_format($transaksi['jumlah'], 0, ',', '.') ?>"
                                                                   oninput="formatCurrency(this)" 
                                                                   placeholder="Masukkan jumlah transaksi">
                                                        </div>
                                                        <div class="invalid-feedback" id="jumlah-error">
                                                            Jumlah harus lebih dari 0 dan maksimal 999.999.999.999.999
                                                        </div>
                                                        <div class="form-text">Contoh: 1.000.000</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="tanggal" class="form-label">Tanggal *</label>
                                                        <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                                               value="<?= $transaksi['tanggal'] ?>" required>
                                                        <div class="invalid-feedback" id="tanggal-error">Tanggal transaksi harus diisi dan tidak boleh lebih dari hari ini.</div>
                                                        <div class="form-text">Tanggal transaksi</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- IMPROVED Navigation Buttons -->
                                    <div class="form-actions">
                                        <a href="read.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
                                        </a>
                                        <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                                            Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Step 2: Additional Information -->
                                <div class="form-step" id="step2">
                                    <div class="form-content">
                                        <!-- Deskripsi Section -->
                                        <div class="form-section">
                                            <div class="section-title">
                                                <i class="fas fa-align-left"></i>Deskripsi
                                            </div>
                                            <div class="mb-3">
                                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" 
                                                          placeholder="Tambahkan deskripsi transaksi (opsional)"><?= htmlspecialchars($transaksi['deskripsi'] ?? '') ?></textarea>
                                                <div class="form-text">Deskripsi membantu dalam pelacakan keuangan</div>
                                            </div>
                                        </div>

                                        <!-- Metode Pembayaran dan Lokasi Section -->
                                        <div class="form-section">
                                            <div class="section-title">
                                                <i class="fas fa-map-marker-alt"></i>Informasi Tambahan
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="metode_bayar" class="form-label">Metode Pembayaran</label>
                                                        <select class="form-select" id="metode_bayar" name="metode_bayar">
                                                            <option value="">Pilih metode pembayaran</option>
                                                            <option value="Tunai" <?= $transaksi['metode_bayar'] == 'Tunai' ? 'selected' : '' ?>>💵 Tunai</option>
                                                            <option value="Transfer Bank" <?= $transaksi['metode_bayar'] == 'Transfer Bank' ? 'selected' : '' ?>>🏦 Transfer Bank</option>
                                                            <option value="Kartu Kredit" <?= $transaksi['metode_bayar'] == 'Kartu Kredit' ? 'selected' : '' ?>>💳 Kartu Kredit</option>
                                                            <option value="E-Wallet" <?= $transaksi['metode_bayar'] == 'E-Wallet' ? 'selected' : '' ?>>📱 E-Wallet</option>
                                                            <option value="Debit" <?= $transaksi['metode_bayar'] == 'Debit' ? 'selected' : '' ?>>💳 Kartu Debit</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="lokasi" class="form-label">Lokasi</label>
                                                        <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                                               value="<?= htmlspecialchars($transaksi['lokasi'] ?? '') ?>" 
                                                               placeholder="Tempat transaksi (opsional)">
                                                        <div class="form-text">Nama merchant atau lokasi</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- IMPROVED Navigation Buttons -->
                                    <div class="form-actions">
                                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep(1)">
                                            <i class="fas fa-arrow-left me-2"></i>Kembali
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                                            Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Step 3: Review and Submit -->
                                <div class="form-step" id="step3">
                                    <div class="form-content">
                                        <div class="form-section">
                                            <div class="section-title">
                                                <i class="fas fa-check-circle"></i>Tinjau Informasi
                                            </div>
                                            
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="review-card">
                                                        <div class="card-title">Ringkasan Transaksi</div>
                                                        <div class="review-item">
                                                            <span class="review-label">Jenis:</span>
                                                            <span class="review-value" id="review-jenis"><?= ucfirst($transaksi['jenis']) ?></span>
                                                        </div>
                                                        <div class="review-item">
                                                            <span class="review-label">Kategori:</span>
                                                            <span class="review-value" id="review-kategori"><?= $transaksi['kategori'] ?></span>
                                                        </div>
                                                        <div class="review-item">
                                                            <span class="review-label">Jumlah:</span>
                                                            <span class="review-value" id="review-jumlah">Rp <?= number_format($transaksi['jumlah'], 0, ',', '.') ?></span>
                                                        </div>
                                                        <div class="review-item">
                                                            <span class="review-label">Tanggal:</span>
                                                            <span class="review-value" id="review-tanggal"><?= $transaksi['tanggal'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="review-card">
                                                        <div class="card-title">Detail Tambahan</div>
                                                        <div class="review-item">
                                                            <span class="review-label">Deskripsi:</span>
                                                            <span class="review-value" id="review-deskripsi"><?= $transaksi['deskripsi'] ?: '-' ?></span>
                                                        </div>
                                                        <div class="review-item">
                                                            <span class="review-label">Metode Bayar:</span>
                                                            <span class="review-value" id="review-metode"><?= $transaksi['metode_bayar'] ?: '-' ?></span>
                                                        </div>
                                                        <div class="review-item">
                                                            <span class="review-label">Lokasi:</span>
                                                            <span class="review-value" id="review-lokasi"><?= $transaksi['lokasi'] ?: '-' ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <span style="font-size: 0.875rem;">Periksa kembali informasi sebelum menyimpan</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- IMPROVED Navigation Buttons -->
                                    <div class="form-actions">
                                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)">
                                            <i class="fas fa-arrow-left me-2"></i>Kembali
                                        </button>
                                        <div>
                                            <a href="read.php" class="btn btn-outline-light me-2">
                                                <i class="fas fa-times me-1"></i>Batal
                                            </a>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-save me-1"></i>Update Transaksi
                                            </button>
                                        </div>
                                    </div>
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
        const totalSteps = 3;

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

        // Transaction type selection
        document.querySelectorAll('.transaction-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.transaction-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('jenis').value = this.dataset.type;
                
                // Update kategori options based on selected type
                updateKategoriOptions(this.dataset.type);
                updateReview();
            });
        });

        function updateKategoriOptions(type) {
            const kategoriSelect = document.getElementById('kategori');
            kategoriSelect.innerHTML = '';
            
            const kategoriList = type === 'pemasukan' ? kategoriPemasukan : kategoriPengeluaran;
            
            kategoriList.forEach(kategori => {
                const option = document.createElement('option');
                option.value = kategori;
                option.textContent = kategori;
                kategoriSelect.appendChild(option);
            });
            
            updateReview();
        }

        function formatCurrency(input) {
            // Hapus semua karakter non-digit
            let value = input.value.replace(/[^\d]/g, '');
            
            // Batasi maksimal 15 digit (999.999.999.999.999) untuk mendukung 999 triliun
            if (value.length > 15) {
                value = value.substring(0, 15);
                // Tampilkan pesan peringatan
                const warningElement = document.getElementById('amount-warning') || createAmountWarning();
                warningElement.style.display = 'block';
            } else {
                const warningElement = document.getElementById('amount-warning');
                if (warningElement) warningElement.style.display = 'none';
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
            
            updateReview();
        }

        function createAmountWarning() {
            const warning = document.createElement('div');
            warning.id = 'amount-warning';
            warning.className = 'alert alert-warning mt-2';
            warning.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Jumlah melebihi batas maksimal';
            
            const inputContainer = document.querySelector('.amount-input-container');
            inputContainer.appendChild(warning);
            return warning;
        }

        // Multi-step form functionality with animations
        function nextStep(targetStep) {
            if (targetStep > totalSteps) return;
            
            // Validate current step before proceeding
            if (!validateStep(currentStep)) {
                return;
            }
            
            const currentStepElement = document.getElementById(`step${currentStep}`);
            const targetStepElement = document.getElementById(`step${targetStep}`);
            
            // Add exiting animation to current step
            currentStepElement.classList.add('exiting');
            currentStepElement.classList.remove('active');
            
            // After animation completes, show target step
            setTimeout(() => {
                currentStepElement.classList.remove('exiting');
                
                // Add entering animation to target step
                targetStepElement.classList.add('entering');
                targetStepElement.classList.add('active');
                
                setTimeout(() => {
                    targetStepElement.classList.remove('entering');
                }, 50);
                
                currentStep = targetStep;
                updateStepIndicator();
                updateReview();
            }, 500);
        }

        function prevStep(targetStep) {
            if (targetStep < 1) return;
            
            const currentStepElement = document.getElementById(`step${currentStep}`);
            const targetStepElement = document.getElementById(`step${targetStep}`);
            
            // Add exiting animation to current step (reverse direction)
            currentStepElement.classList.add('exiting');
            currentStepElement.classList.remove('active');
            
            // After animation completes, show target step
            setTimeout(() => {
                currentStepElement.classList.remove('exiting');
                
                // Add entering animation to target step (reverse direction)
                targetStepElement.classList.add('entering');
                targetStepElement.classList.add('active');
                
                setTimeout(() => {
                    targetStepElement.classList.remove('entering');
                }, 50);
                
                currentStep = targetStep;
                updateStepIndicator();
                updateReview();
            }, 500);
        }

        function validateStep(step) {
            let isValid = true;
            
            if (step === 1) {
                // Validate jenis transaksi
                const jenis = document.getElementById('jenis').value;
                if (!jenis) {
                    document.getElementById('jenis-error').style.display = 'block';
                    isValid = false;
                } else {
                    document.getElementById('jenis-error').style.display = 'none';
                }
                
                // Validate kategori
                const kategori = document.getElementById('kategori').value;
                if (!kategori) {
                    document.getElementById('kategori-error').style.display = 'block';
                    isValid = false;
                } else {
                    document.getElementById('kategori-error').style.display = 'none';
                }
                
                // Validate jumlah
                const jumlahInput = document.getElementById('jumlah');
                let rawValue = jumlahInput.value.replace(/[^\d]/g, '');
                
                if (!rawValue || parseInt(rawValue, 10) <= 0) {
                    document.getElementById('jumlah-error').style.display = 'block';
                    jumlahInput.classList.add('is-invalid');
                    isValid = false;
                } else if (parseInt(rawValue, 10) > 999999999999999) {
                    document.getElementById('jumlah-error').style.display = 'block';
                    jumlahInput.classList.add('is-invalid');
                    isValid = false;
                } else {
                    document.getElementById('jumlah-error').style.display = 'none';
                    jumlahInput.classList.remove('is-invalid');
                }
                
                // Validate tanggal
                const tanggal = document.getElementById('tanggal').value;
                if (!tanggal) {
                    document.getElementById('tanggal-error').style.display = 'block';
                    isValid = false;
                } else if (!validateDate(document.getElementById('tanggal'))) {
                    document.getElementById('tanggal-error').style.display = 'block';
                    isValid = false;
                } else {
                    document.getElementById('tanggal-error').style.display = 'none';
                }
            }
            
            return isValid;
        }

        function updateStepIndicator() {
            // Update step indicator
            document.querySelectorAll('.step').forEach((step, index) => {
                step.classList.remove('active', 'completed');
                if (index < currentStep - 1) {
                    step.classList.add('completed');
                } else if (index === currentStep - 1) {
                    step.classList.add('active');
                }
            });
            
            // Update progress bar
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.getElementById('stepProgress').style.width = progress + '%';
        }

        // Update review section
        function updateReview() {
            document.getElementById('review-jenis').textContent = 
                document.getElementById('jenis').value === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran';
            
            document.getElementById('review-kategori').textContent = 
                document.getElementById('kategori').value;
            
            document.getElementById('review-jumlah').textContent = 
                'Rp ' + (document.getElementById('jumlah').value || '0');
            
            document.getElementById('review-tanggal').textContent = 
                document.getElementById('tanggal').value;
            
            const deskripsi = document.getElementById('deskripsi').value;
            document.getElementById('review-deskripsi').textContent = 
                deskripsi ? deskripsi : '-';
            
            const metode = document.getElementById('metode_bayar').value;
            document.getElementById('review-metode').textContent = 
                metode ? metode : '-';
            
            const lokasi = document.getElementById('lokasi').value;
            document.getElementById('review-lokasi').textContent = 
                lokasi ? lokasi : '-';
        }

        // Validasi form sebelum submit
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            // Validate all steps
            for (let i = 1; i <= totalSteps; i++) {
                if (!validateStep(i)) {
                    e.preventDefault();
                    // Go to the first step with error
                    nextStep(i);
                    return;
                }
            }
            
            const jumlahInput = document.getElementById('jumlah');
            let rawValue = jumlahInput.value.replace(/[^\d]/g, '');
            
            if (!rawValue || parseInt(rawValue, 10) <= 0) {
                e.preventDefault();
                showError('Jumlah harus lebih dari 0.');
                nextStep(1);
                jumlahInput.focus();
                return;
            }
            
            if (parseInt(rawValue, 10) > 999999999999999) {
                e.preventDefault();
                showError('Jumlah terlalu besar. Maksimal: 999.999.999.999.999');
                nextStep(1);
                jumlahInput.focus();
                return;
            }
            
            // Set nilai asli (tanpa format) ke hidden field atau format ulang input
            jumlahInput.value = rawValue;
            
            // Show loading overlay
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.classList.add('active');
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memperbarui...';
            submitBtn.disabled = true;
        });

        function showError(message) {
            // Create or update error alert
            let errorAlert = document.querySelector('.alert-danger');
            if (!errorAlert) {
                errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger d-flex align-items-center';
                errorAlert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><div style="font-size: 0.875rem;"></div>';
                document.querySelector('.card-body').insertBefore(errorAlert, document.querySelector('.feature-highlight'));
            }
            
            errorAlert.querySelector('div').textContent = message;
            errorAlert.style.display = 'block';
            
            // Scroll to error message
            errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Initialize event listeners for real-time updates
        document.querySelectorAll('#kategori, #tanggal, #deskripsi, #metode_bayar, #lokasi').forEach(input => {
            input.addEventListener('change', updateReview);
            input.addEventListener('input', updateReview);
        });

        // Tambahkan event listener untuk validasi real-time pada tanggal
        document.getElementById('tanggal').addEventListener('change', function() {
            validateDate(this);
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateReview();
            updateStepIndicator();
        });
    </script>
</body>
</html>