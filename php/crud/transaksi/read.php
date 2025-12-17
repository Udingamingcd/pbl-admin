<?php
require_once '../../middleware/auth.php';
require_once '../../config.php';
require_once '../../koneksi.php';

// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db = new Database();

// Get filter parameters from GET
$period = $_GET['period'] ?? 'all';
$jenis = $_GET['jenis'] ?? 'all';
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// Build SQL queries based on filters
$whereClause = 'WHERE user_id = :user_id';
$params = [':user_id' => $_SESSION['user_id']];

if ($jenis !== 'all') {
    $whereClause .= ' AND jenis = :jenis';
    $params[':jenis'] = $jenis;
}

if (!empty($category)) {
    $whereClause .= ' AND kategori = :category';
    $params[':category'] = $category;
}

if (!empty($search)) {
    $whereClause .= ' AND (kategori LIKE :search OR deskripsi LIKE :search OR metode_bayar LIKE :search)';
    $params[':search'] = "%$search%";
}

switch ($period) {
    case 'today':
        $whereClause .= ' AND DATE(tanggal) = CURDATE()';
        $dateLabel = 'Hari Ini';
        break;
    case 'week':
        $whereClause .= ' AND YEARWEEK(tanggal, 1) = YEARWEEK(CURDATE(), 1)';
        $dateLabel = 'Minggu Ini';
        break;
    case 'month':
        $whereClause .= ' AND YEAR(tanggal) = YEAR(CURDATE()) AND MONTH(tanggal) = MONTH(CURDATE())';
        $dateLabel = 'Bulan Ini';
        break;
    case 'year':
        $whereClause .= ' AND YEAR(tanggal) = YEAR(CURDATE())';
        $dateLabel = 'Tahun Ini';
        break;
    case 'all':
    default:
        $dateLabel = 'Semua Waktu';
        break;
}

// Get transactions based on filters
$db->query('SELECT * FROM transaksi ' . $whereClause . ' ORDER BY tanggal DESC, created_at DESC');
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$transaksi = $db->resultSet();

// Hitung total pemasukan dan pengeluaran berdasarkan filter
$db->query('SELECT 
            SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE 0 END) as total_pemasukan,
            SUM(CASE WHEN jenis = "pengeluaran" THEN jumlah ELSE 0 END) as total_pengeluaran
            FROM transaksi ' . $whereClause);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$total = $db->single();

// Set default values if null
$total['total_pemasukan'] = $total['total_pemasukan'] ?: 0;
$total['total_pengeluaran'] = $total['total_pengeluaran'] ?: 0;

$saldo = $total['total_pemasukan'] - $total['total_pengeluaran'];

// Hitung persentase
$total_all = $total['total_pemasukan'] + $total['total_pengeluaran'];
$pemasukan_persen = $total_all > 0 ? ($total['total_pemasukan'] / $total_all) * 100 : 0;
$pengeluaran_persen = $total_all > 0 ? ($total['total_pengeluaran'] / $total_all) * 100 : 0;

// Get unique categories for filter
$db->query('SELECT DISTINCT kategori FROM transaksi WHERE user_id = :user_id ORDER BY kategori');
$db->bind(':user_id', $_SESSION['user_id']);
$kategori = $db->resultSet();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Finansialku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --success-light: #34d399;
            --danger: #ef4444;
            --danger-light: #f87171;
            --warning: #f59e0b;
            --info: #06b6d4;
            --card-bg: #ffffff;
            --body-bg: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --gradient-primary: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            --gradient-success: linear-gradient(135deg, var(--success) 0%, var(--success-light) 100%);
            --gradient-danger: linear-gradient(135deg, var(--danger) 0%, var(--danger-light) 100%);
            --glass-bg: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        [data-bs-theme="dark"] {
            --primary: #818cf8;
            --primary-light: #a5b4fc;
            --primary-dark: #6366f1;
            --success: #34d399;
            --success-light: #6ee7b7;
            --danger: #f87171;
            --danger-light: #fca5a5;
            --card-bg: #1e293b;
            --body-bg: #0f172a;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #64748b;
            --border-color: #334155;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            --shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 10px 10px -5px rgba(0, 0, 0, 0.3);
            --glass-bg: rgba(30, 41, 59, 0.8);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--body-bg);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding-bottom: 2rem;
            background: linear-gradient(135deg, var(--body-bg) 0%, rgba(99, 102, 241, 0.03) 100%);
            min-height: 100vh;
        }

        /* Navigation */
        .navbar {
            background: var(--glass-bg) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 0;
            box-shadow: 0 1px 10px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        /* Layout */
        .container {
            max-width: 1200px;
        }

        /* Cards */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-4px);
            border-color: var(--primary-light);
        }

        /* Summary Cards */
        .summary-card {
            border: none;
            border-radius: 20px;
            transition: all 0.4s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
            background: var(--gradient-primary);
            color: white;
        }

        .summary-card.income {
            background: var(--gradient-success);
        }

        .summary-card.expense {
            background: var(--gradient-danger);
        }

        .summary-card.balance {
            background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
        }

        .stats-number {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 0.25rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stats-label {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.9);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        /* Table */
        .table-container {
            border-radius: 16px;
            overflow: hidden;
        }

        .table {
            color: var(--text-primary);
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
            font-size: 0.875rem;
        }

        .table th {
            background-color: rgba(99, 102, 241, 0.05);
            color: var(--text-secondary);
            font-weight: 700;
            border: none;
            padding: 1.25rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        .table td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            transition: all 0.3s ease;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .table tbody tr:hover {
            background: rgba(99, 102, 241, 0.03);
            transform: translateX(8px);
        }

        /* Badges */
        .badge {
            font-weight: 600;
            padding: 0.5rem 0.75rem;
            border-radius: 10px;
            font-size: 0.7rem;
            border: 1px solid transparent;
            backdrop-filter: blur(10px);
        }

        .badge-income {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .badge-expense {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
            border-color: rgba(239, 68, 68, 0.3);
        }

        .category-badge {
            background: rgba(99, 102, 241, 0.15);
            color: var(--primary);
            border-color: rgba(99, 102, 241, 0.3);
        }

        /* Buttons */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            font-size: 0.8rem;
            padding: 0.75rem 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-outline-secondary {
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            background: transparent;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            padding: 0;
            opacity: 0.7;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .action-btn:hover {
            opacity: 1;
            transform: scale(1.1) rotate(5deg);
        }

        /* Typography */
        h2 {
            font-weight: 800;
            color: var(--text-primary);
            font-size: 2rem;
            letter-spacing: -1px;
            margin-bottom: 0.5rem;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: var(--text-muted);
            opacity: 0.3;
        }

        /* Mobile Cards */
        .mobile-transaction-card {
            display: none;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 0.75rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--glass-border);
            cursor: pointer;
        }

        .mobile-transaction-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-3px);
            border-color: var(--primary-light);
        }

        .transaction-amount {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .transaction-date {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Animations */
        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(30px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .card, .summary-card {
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--body-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        /* Filter Container Styles - BARU */
        .filter-container {
            animation: fadeIn 0.5s ease-out;
            will-change: transform;
            contain: content;
        }

        .filter-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .filter-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            animation: slideIn 0.4s ease-out backwards;
        }

        .filter-card:nth-child(1) { animation-delay: 0.1s; }
        .filter-card:nth-child(2) { animation-delay: 0.2s; }
        .filter-card:nth-child(3) { animation-delay: 0.3s; }

        .filter-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

        .filter-card-header {
            padding: 1rem 1.5rem;
            background: rgba(99, 102, 241, 0.05);
            border-bottom: 1px solid var(--glass-border);
        }

        .filter-card-header h6 {
            margin: 0;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .filter-card-body {
            padding: 1.5rem;
        }

        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .filter-btn {
            flex: 1;
            min-width: 100px;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: transparent;
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            will-change: transform, background-color, color;
        }

        .filter-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: var(--gradient-primary);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .filter-btn.income.active {
            background: var(--gradient-success);
        }

        .filter-btn.expense.active {
            background: var(--gradient-danger);
        }

        .filter-btn-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
        }

        .filter-count {
            background: rgba(255, 255, 255, 0.15);
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-size: 0.75rem;
            min-width: 24px;
            text-align: center;
        }

        .current-filter {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid var(--glass-border);
        }

        .filter-info {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-label {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .filter-tag {
            background: var(--gradient-primary);
            color: white;
            padding: 0.35rem 1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .filter-tag.income {
            background: var(--gradient-success);
        }

        .filter-tag.expense {
            background: var(--gradient-danger);
        }

        .btn-reset-filter {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            background: transparent;
            color: var(--text-secondary);
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-reset-filter:hover {
            background: var(--border-color);
            color: var(--text-primary);
            transform: translateY(-1px);
        }

        .search-wrapper {
            position: relative;
            width: 100%;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 2.5rem 0.75rem 2.75rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: var(--glass-bg);
            color: var(--text-primary);
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            z-index: 1;
        }

        .btn-clear-search {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-clear-search:hover {
            background: var(--border-color);
            color: var(--text-primary);
        }

        .category-filter-wrapper {
            margin-top: 1rem;
        }

        .category-select {
            width: 100%;
            max-width: 300px;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: var(--glass-bg);
            color: var(--text-primary);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236466f1' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        .category-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* New Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Stats Overview */
        .stats-overview {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
        }

        .stats-chart {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-bar {
            flex: 1;
            height: 40px;
            border-radius: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .chart-bar.income {
            background: var(--gradient-success);
        }

        .chart-bar.expense {
            background: var(--gradient-danger);
        }

        .chart-bar:hover {
            transform: scale(1.05);
        }

        .chart-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        /* Progress Bar */
        .progress-bar {
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
            background: rgba(255,255,255,0.3);
            margin-top: 0.75rem;
            backdrop-filter: blur(10px);
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255,255,255,0.8);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        /* Pulse Animation */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .table-container {
                display: none;
            }
            
            .mobile-transaction-card {
                display: block;
            }
            
            .stats-number {
                font-size: 1.5rem;
            }
            
            .container {
                padding: 0 1rem;
            }

            .header-buttons {
                flex-direction: column;
                gap: 0.5rem;
                width: 100%;
            }

            .header-buttons .btn {
                width: 100%;
            }

            .filter-cards {
                grid-template-columns: 1fr;
            }
            
            .current-filter {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .filter-info {
                flex-direction: column;
                gap: 0.75rem;
                width: 100%;
            }
            
            .filter-item {
                justify-content: center;
            }
            
            .filter-btn {
                min-width: calc(50% - 0.25rem);
            }
            
            .filter-card-body {
                padding: 1rem;
            }
        }

        @media (max-width: 576px) {
            .filter-btn {
                min-width: 100%;
            }
            
            .category-select {
                max-width: 100%;
            }
        }

        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: var(--gradient-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            text-decoration: none;
            border: none;
            font-size: 1.25rem;
        }

        .fab:hover {
            transform: translateY(-4px) scale(1.1);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.6);
            color: white;
        }
    </style>
</head>
<body data-bs-theme="dark">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
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

    <div class="container mt-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Transaksi</h2>
                <p class="text-muted mb-0">Kelola pemasukan dan pengeluaran Anda dengan mudah</p>
            </div>
            <div class="d-flex gap-2 header-buttons">
                <a href="../../../dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                </a>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Tambah Transaksi
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 16px; border: none; background: var(--gradient-success); color: white;">
                <i class="fas fa-check-circle me-2"></i>
                <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 16px; border: none; background: var(--gradient-danger); color: white;">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filter Container -->
        <div class="filter-container mb-4">
            <!-- Current Filter -->
            <div class="current-filter mb-3">
                <div class="filter-info">
                    <div class="filter-item">
                        <span class="filter-label">Periode:</span>
                        <span class="filter-tag"><?= $dateLabel ?></span>
                    </div>
                    <div class="filter-item">
                        <span class="filter-label">Jenis:</span>
                        <?php if ($jenis === 'all'): ?>
                            <span class="filter-tag">Semua Transaksi</span>
                        <?php elseif ($jenis === 'pemasukan'): ?>
                            <span class="filter-tag income">Pemasukan</span>
                        <?php elseif ($jenis === 'pengeluaran'): ?>
                            <span class="filter-tag expense">Pengeluaran</span>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="button" class="btn-reset-filter" onclick="window.location.href='read.php'">
                    <i class="fas fa-refresh me-1"></i>Reset Filter
                </button>
            </div>

            <!-- Filter Cards -->
            <div class="filter-cards">
                <!-- Type Filter Card -->
                <div class="filter-card">
                    <div class="filter-card-header">
                        <h6><i class="fas fa-filter me-2"></i>Filter Jenis</h6>
                    </div>
                    <div class="filter-card-body">
                        <div class="filter-buttons type-filter">
                            <button class="filter-btn <?= $jenis === 'all' ? 'active' : '' ?>" 
                                    onclick="changeFilter('jenis', 'all')">
                                <div class="filter-btn-content">
                                    <i class="fas fa-list"></i>
                                    <span>Semua</span>
                                </div>
                                <span class="filter-count"><?= count($transaksi) ?></span>
                            </button>
                            <button class="filter-btn income <?= $jenis === 'pemasukan' ? 'active' : '' ?>" 
                                    onclick="changeFilter('jenis', 'pemasukan')">
                                <div class="filter-btn-content">
                                    <i class="fas fa-arrow-down"></i>
                                    <span>Pemasukan</span>
                                </div>
                                <span class="filter-count"><?= count(array_filter($transaksi, fn($t) => $t['jenis'] === 'pemasukan')) ?></span>
                            </button>
                            <button class="filter-btn expense <?= $jenis === 'pengeluaran' ? 'active' : '' ?>" 
                                    onclick="changeFilter('jenis', 'pengeluaran')">
                                <div class="filter-btn-content">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>Pengeluaran</span>
                                </div>
                                <span class="filter-count"><?= count(array_filter($transaksi, fn($t) => $t['jenis'] === 'pengeluaran')) ?></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Period Filter Card -->
                <div class="filter-card">
                    <div class="filter-card-header">
                        <h6><i class="fas fa-calendar me-2"></i>Filter Periode</h6>
                    </div>
                    <div class="filter-card-body">
                        <div class="filter-buttons period-filter">
                            <button class="filter-btn <?= $period === 'all' ? 'active' : '' ?>" 
                                    onclick="changeFilter('period', 'all')">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <span>Semua</span>
                            </button>
                            <button class="filter-btn <?= $period === 'today' ? 'active' : '' ?>" 
                                    onclick="changeFilter('period', 'today')">
                                <i class="fas fa-sun me-1"></i>
                                <span>Hari Ini</span>
                            </button>
                            <button class="filter-btn <?= $period === 'week' ? 'active' : '' ?>" 
                                    onclick="changeFilter('period', 'week')">
                                <i class="fas fa-calendar-week me-1"></i>
                                <span>Minggu Ini</span>
                            </button>
                            <button class="filter-btn <?= $period === 'month' ? 'active' : '' ?>" 
                                    onclick="changeFilter('period', 'month')">
                                <i class="fas fa-calendar me-1"></i>
                                <span>Bulan Ini</span>
                            </button>
                            <button class="filter-btn <?= $period === 'year' ? 'active' : '' ?>" 
                                    onclick="changeFilter('period', 'year')">
                                <i class="fas fa-calendar-year me-1"></i>
                                <span>Tahun Ini</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Search Card -->
                <div class="filter-card">
                    <div class="filter-card-header">
                        <h6><i class="fas fa-search me-2"></i>Cari Transaksi</h6>
                    </div>
                    <div class="filter-card-body">
                        <div class="search-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   class="search-input" 
                                   id="searchInput" 
                                   placeholder="Cari berdasarkan kategori, deskripsi..."
                                   value="<?= htmlspecialchars($search) ?>">
                            <button class="btn-clear-search" onclick="clearSearch()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Filter Dropdown -->
            <div class="category-filter-wrapper">
                <select class="category-select" id="categoryFilter" onchange="changeFilter('category', this.value)">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori as $k): ?>
                        <option value="<?= htmlspecialchars($k['kategori']) ?>" 
                            <?= ($category === $k['kategori']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-overview">
            <div class="stats-chart">
                <div class="chart-bar income" style="flex: <?= $pemasukan_persen ?>%" data-bs-toggle="tooltip" title="Pemasukan: <?= number_format($pemasukan_persen, 1) ?>%">
                    <span class="chart-label"><?= number_format($pemasukan_persen, 1) ?>%</span>
                </div>
                <div class="chart-bar expense" style="flex: <?= $pengeluaran_persen ?>%" data-bs-toggle="tooltip" title="Pengeluaran: <?= number_format($pengeluaran_persen, 1) ?>%">
                    <span class="chart-label"><?= number_format($pengeluaran_persen, 1) ?>%</span>
                </div>
            </div>
            <div class="stats-text">
                <small class="text-muted">Rasio Pemasukan vs Pengeluaran (<?= $dateLabel ?>)</small>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card summary-card income">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="stats-label">Total Pemasukan (<?= $dateLabel ?>)</p>
                                <h3 class="stats-number">Rp <?= number_format($total['total_pemasukan'], 0, ',', '.') ?></h3>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 100%"></div>
                                </div>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card summary-card expense">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="stats-label">Total Pengeluaran (<?= $dateLabel ?>)</p>
                                <h3 class="stats-number">Rp <?= number_format($total['total_pengeluaran'], 0, ',', '.') ?></h3>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 100%"></div>
                                </div>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card summary-card balance pulse">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="stats-label">Saldo (<?= $dateLabel ?>)</p>
                                <h3 class="stats-number">Rp <?= number_format($saldo, 0, ',', '.') ?></h3>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 100%"></div>
                                </div>
                            </div>
                            <div class="summary-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Riwayat Transaksi (<?= $dateLabel ?>)</h5>
                <span class="badge bg-primary" id="transactionCount"><?= count($transaksi) ?> transaksi</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($transaksi)): ?>
                    <div class="empty-state">
                        <i class="fas fa-exchange-alt"></i>
                        <h5 class="text-muted mb-2">Belum Ada Transaksi</h5>
                        <p class="text-muted mb-3">Tidak ada transaksi <?= $jenis !== 'all' ? strtolower($jenis) : '' ?> pada periode <?= strtolower($dateLabel) ?>.</p>
                        <a href="create.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Tambah Transaksi
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Desktop Table View -->
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="transactionsTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kategori</th>
                                        <th>Jenis</th>
                                        <th>Jumlah</th>
                                        <th>Deskripsi</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transaksi as $t): ?>
                                        <tr class="transaction-row transaction-item" 
                                            data-type="<?= $t['jenis'] ?>" 
                                            data-category="<?= htmlspecialchars($t['kategori']) ?>"
                                            data-search="<?= htmlspecialchars(strtolower($t['kategori'] . ' ' . $t['deskripsi'] . ' ' . $t['metode_bayar'])) ?>"
                                            data-date="<?= $t['tanggal'] ?>">
                                            <td>
                                                <div class="transaction-date fw-bold"><?= date('d M Y', strtotime($t['tanggal'])) ?></div>
                                                <small class="text-muted"><?= date('H:i', strtotime($t['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge category-badge"><?= htmlspecialchars($t['kategori']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $t['jenis'] == 'pemasukan' ? 'badge-income' : 'badge-expense' ?>">
                                                    <i class="fas fa-<?= $t['jenis'] == 'pemasukan' ? 'arrow-down' : 'arrow-up' ?> me-1"></i>
                                                    <?= ucfirst($t['jenis']) ?>
                                                </span>
                                            </td>
                                            <td class="transaction-amount <?= $t['jenis'] == 'pemasukan' ? 'text-success' : 'text-danger' ?> fw-bold fs-6">
                                                <?= $t['jenis'] == 'pemasukan' ? '+' : '-' ?> 
                                                Rp <?= number_format($t['jumlah'], 0, ',', '.') ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?= !empty($t['deskripsi']) ? htmlspecialchars($t['deskripsi']) : '<span class="text-muted">-</span>' ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons d-flex gap-2">
                                                    <a href="update.php?id=<?= $t['id'] ?>" class="btn btn-outline-warning action-btn" 
                                                       data-bs-toggle="tooltip" title="Edit Transaksi">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger action-btn" 
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                            data-transaksi-id="<?= $t['id'] ?>"
                                                            data-transaksi-desc="<?= htmlspecialchars($t['kategori'] . ' - ' . ($t['deskripsi'] ?: 'Tanpa deskripsi')) ?>"
                                                            data-bs-tooltip="tooltip" title="Hapus Transaksi">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none p-3">
                        <?php foreach ($transaksi as $t): ?>
                            <div class="mobile-transaction-card transaction-item" 
                                 data-type="<?= $t['jenis'] ?>" 
                                 data-category="<?= htmlspecialchars($t['kategori']) ?>"
                                 data-search="<?= htmlspecialchars(strtolower($t['kategori'] . ' ' . $t['deskripsi'] . ' ' . $t['metode_bayar'])) ?>"
                                 data-date="<?= $t['tanggal'] ?>">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge <?= $t['jenis'] == 'pemasukan' ? 'badge-income' : 'badge-expense' ?> mb-2">
                                            <i class="fas fa-<?= $t['jenis'] == 'pemasukan' ? 'arrow-down' : 'arrow-up' ?> me-1"></i>
                                            <?= ucfirst($t['jenis']) ?>
                                        </span>
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($t['kategori']) ?></h6>
                                    </div>
                                    <span class="transaction-amount <?= $t['jenis'] == 'pemasukan' ? 'text-success' : 'text-danger' ?> fw-bold">
                                        <?= $t['jenis'] == 'pemasukan' ? '+' : '-' ?> 
                                        Rp <?= number_format($t['jumlah'], 0, ',', '.') ?>
                                    </span>
                                </div>
                                <p class="text-muted mb-2 small">
                                    <?= !empty($t['deskripsi']) ? htmlspecialchars($t['deskripsi']) : 'Tanpa deskripsi' ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="transaction-date"><?= date('d M Y H:i', strtotime($t['created_at'])) ?></span>
                                    <div class="action-buttons d-flex gap-1">
                                        <a href="update.php?id=<?= $t['id'] ?>" class="btn btn-outline-warning btn-sm action-btn">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm action-btn" 
                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-transaksi-id="<?= $t['id'] ?>"
                                                data-transaksi-desc="<?= htmlspecialchars($t['kategori'] . ' - ' . ($t['deskripsi'] ?: 'Tanpa deskripsi')) ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <a href="create.php" class="fab d-md-none">
        <i class="fas fa-plus"></i>
    </a>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: 1px solid var(--glass-border); background: var(--glass-bg); backdrop-filter: blur(20px);">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Apakah Anda yakin ingin menghapus transaksi ini?</p>
                    <p class="fw-bold text-primary" id="transaksi-desc"></p>
                    <p class="text-danger small mt-3"><i class="fas fa-info-circle me-1"></i>Tindakan ini tidak dapat dibatalkan</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 12px;">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <form method="POST" action="delete.php" id="deleteForm" style="display: inline;">
                        <input type="hidden" name="id" id="delete_transaksi_id">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-danger" style="border-radius: 12px;">
                            <i class="fas fa-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Delete Modal Handler
        var deleteModal = document.getElementById('deleteModal')
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var transaksiId = button.getAttribute('data-transaksi-id')
            var transaksiDesc = button.getAttribute('data-transaksi-desc')
            
            var modalBody = deleteModal.querySelector('#transaksi-desc')
            var deleteInput = deleteModal.querySelector('#delete_transaksi_id')
            
            modalBody.textContent = transaksiDesc
            deleteInput.value = transaksiId
        })

        // Debounce function untuk performance
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Global filter state
        let filterState = {
            period: '<?= $period ?>',
            jenis: '<?= $jenis ?>',
            category: '<?= $category ?>',
            search: '<?= $search ?>'
        };

        // Fungsi untuk mengubah filter dengan smooth transition
        function changeFilter(type, value) {
            // Tambahkan loading state
            const filterContainer = document.querySelector('.filter-container');
            filterContainer.style.opacity = '0.7';
            filterContainer.style.pointerEvents = 'none';
            
            // Update filter state
            filterState[type] = value;
            
            // Build URL dengan semua filter
            const params = new URLSearchParams({
                period: filterState.period,
                jenis: filterState.jenis,
            });
            
            if (filterState.category) {
                params.set('category', filterState.category);
            }
            
            if (filterState.search) {
                params.set('search', filterState.search);
            }
            
            // Redirect ke URL baru dengan smooth transition
            setTimeout(() => {
                window.location.href = `read.php?${params.toString()}`;
            }, 300);
        }

        // Fungsi untuk clear search
        function clearSearch() {
            const searchInput = document.getElementById('searchInput');
            searchInput.value = '';
            filterState.search = '';
            changeFilter('search', '');
        }

        // Fungsi untuk apply search dengan debounce
        const applySearch = debounce(() => {
            const searchValue = document.getElementById('searchInput').value;
            changeFilter('search', searchValue);
        }, 500);

        // Event listeners untuk filter
        document.addEventListener('DOMContentLoaded', function() {
            // Search input dengan debounce
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', applySearch);
            }
            
            // Tambahkan animasi loading ke progress bars
            document.querySelectorAll('.progress-fill').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 300);
            });
            
            // Tambahkan interaksi ke filter cards
            document.querySelectorAll('.filter-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (!e.target.closest('.filter-btn') && !e.target.closest('.search-input')) {
                        this.style.transform = 'scale(0.98)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 200);
                    }
                });
            });
            
            // Update transaction count dengan animasi
            const transactionCount = document.getElementById('transactionCount');
            if (transactionCount) {
                setTimeout(() => {
                    transactionCount.classList.add('pulse');
                    setTimeout(() => transactionCount.classList.remove('pulse'), 1000);
                }, 500);
            }
        });

        // Smooth scroll dengan offset
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offset = 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;
                    
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Parallax effect untuk background
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelector('body');
            if (parallax) {
                parallax.style.backgroundPosition = `center ${scrolled * 0.5}px`;
            }
        });
    </script>
</body>
</html>