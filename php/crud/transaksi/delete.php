<?php
require_once '../../middleware/auth.php';
require_once '../../config.php';
require_once '../../koneksi.php';

// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validasi method request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Method request tidak diizinkan.';
    header('Location: read.php');
    exit;
}

// Debug: Cek apakah session berjalan
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Sesi tidak valid. Silakan login kembali.';
    header('Location: ../../login.php');
    exit;
}

// CSRF Protection
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['error_message'] = 'Token keamanan tidak valid.';
    header('Location: read.php');
    exit;
}

$id = $_POST['id'] ?? 0;

// Validasi ID
if (!$id || !is_numeric($id) || $id <= 0) {
    $_SESSION['error_message'] = 'ID transaksi tidak valid.';
    header('Location: read.php');
    exit;
}

try {
    $db = new Database();
    
    // Ambil data transaksi sebelum dihapus (untuk logging/feedback)
    $db->query('SELECT kategori, deskripsi, jumlah, jenis FROM transaksi WHERE id = :id AND user_id = :user_id');
    $db->bind(':id', $id);
    $db->bind(':user_id', $_SESSION['user_id']);
    $transaksi = $db->single();
    
    if (!$transaksi) {
        $_SESSION['error_message'] = 'Transaksi tidak ditemukan atau tidak memiliki akses.';
        header('Location: read.php');
        exit;
    }
    
    // Hapus transaksi
    $db->query('DELETE FROM transaksi WHERE id = :id AND user_id = :user_id');
    $db->bind(':id', $id);
    $db->bind(':user_id', $_SESSION['user_id']);
    
    if ($db->execute()) {
        // Buat pesan sukses yang informatif
        $jenis = $transaksi['jenis'] == 'pemasukan' ? 'pemasukan' : 'pengeluaran';
        $jumlah_format = 'Rp ' . number_format($transaksi['jumlah'], 0, ',', '.');
        
        $_SESSION['success_message'] = 
            "Transaksi {$jenis} <strong>{$transaksi['kategori']}</strong> " .
            "sebesar <strong>{$jumlah_format}</strong> berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = 'Gagal menghapus transaksi. Silakan coba lagi.';
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
    error_log('Delete Transaction Error - User: ' . $_SESSION['user_id'] . ' - ' . $e->getMessage());
}

// Regenerate CSRF token untuk keamanan
if (isset($_SESSION['csrf_token'])) {
    unset($_SESSION['csrf_token']);
}

header('Location: read.php');
exit;
?>