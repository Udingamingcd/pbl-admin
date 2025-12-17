<?php
require_once '../config.php';
require_once '../koneksi.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    $db = new Database();
    $user_id = $_SESSION['user_id'];

    switch ($action) {
        case 'get_financial_chart':
            $period = $_GET['period'] ?? 'weekly';
            getFinancialChart($db, $user_id, $period);
            break;
            
        case 'get_budget_data':
            getBudgetData($db, $user_id);
            break;

        case 'get_goal_data':
            getGoalData($db, $user_id);
            break;
            
        case 'get_recent_transactions':
            getRecentTransactions($db, $user_id);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Dashboard API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}

function getBudgetData($db, $user_id) {
    $current_date = date('Y-m-d');
    
    $db->query('SELECT 
                b.id,
                b.nama_budget, 
                b.jumlah,
                b.kategori,
                b.tanggal_mulai,
                b.tanggal_akhir,
                COALESCE(SUM(t.jumlah), 0) as terpakai
                FROM budget b
                LEFT JOIN transaksi t ON b.user_id = t.user_id 
                    AND t.jenis = "pengeluaran" 
                    AND t.tanggal BETWEEN b.tanggal_mulai AND b.tanggal_akhir
                    AND t.kategori = b.kategori
                WHERE b.user_id = :user_id 
                AND b.tanggal_akhir >= :current_date
                GROUP BY b.id, b.nama_budget, b.jumlah, b.kategori, b.tanggal_mulai, b.tanggal_akhir
                ORDER BY b.tanggal_akhir ASC
                LIMIT 5');
    $db->bind(':user_id', $user_id);
    $db->bind(':current_date', $current_date);
    $budgets = $db->resultSet();
    
    echo json_encode([
        'success' => true,
        'data' => $budgets
    ]);
}

function getGoalData($db, $user_id) {
    $db->query('SELECT 
                id,
                nama_goal, 
                target_jumlah, 
                terkumpul,
                tenggat_waktu,
                status
                FROM financial_goal 
                WHERE user_id = :user_id 
                AND status = "aktif"
                ORDER BY tenggat_waktu ASC
                LIMIT 5');
    $db->bind(':user_id', $user_id);
    $goals = $db->resultSet();
    
    echo json_encode([
        'success' => true,
        'data' => $goals
    ]);
}

function getRecentTransactions($db, $user_id) {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    
    $db->query('SELECT * FROM transaksi 
                WHERE user_id = :user_id 
                ORDER BY tanggal DESC, created_at DESC 
                LIMIT :limit');
    $db->bind(':user_id', $user_id);
    $db->bind(':limit', $limit);
    $transactions = $db->resultSet();
    
    echo json_encode([
        'success' => true,
        'data' => $transactions
    ]);
}

function getFinancialChart($db, $user_id, $period = 'weekly') {
    $labels = [];
    $pemasukan_data = [];
    $pengeluaran_data = [];
    
    switch ($period) {
        case 'daily':
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $label = date('d M', strtotime($date));
                $labels[] = $label;
                
                $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                            WHERE user_id = :user_id AND jenis = "pemasukan" 
                            AND tanggal = :date');
                $db->bind(':user_id', $user_id);
                $db->bind(':date', $date);
                $pemasukan = $db->single();
                $pemasukan_data[] = (float)$pemasukan['total'];
                
                $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                            WHERE user_id = :user_id AND jenis = "pengeluaran" 
                            AND tanggal = :date');
                $db->bind(':user_id', $user_id);
                $db->bind(':date', $date);
                $pengeluaran = $db->single();
                $pengeluaran_data[] = (float)$pengeluaran['total'];
            }
            break;
            
        case 'weekly':
            for ($i = 7; $i >= 0; $i--) {
                $week_start = date('Y-m-d', strtotime("-$i weeks"));
                $week_end = date('Y-m-d', strtotime("-$i weeks +6 days"));
                $label = date('d M', strtotime($week_start));
                $labels[] = $label;
                
                $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                            WHERE user_id = :user_id AND jenis = "pemasukan" 
                            AND tanggal BETWEEN :start_date AND :end_date');
                $db->bind(':user_id', $user_id);
                $db->bind(':start_date', $week_start);
                $db->bind(':end_date', $week_end);
                $pemasukan = $db->single();
                $pemasukan_data[] = (float)$pemasukan['total'];
                
                $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                            WHERE user_id = :user_id AND jenis = "pengeluaran" 
                            AND tanggal BETWEEN :start_date AND :end_date');
                $db->bind(':user_id', $user_id);
                $db->bind(':start_date', $week_start);
                $db->bind(':end_date', $week_end);
                $pengeluaran = $db->single();
                $pengeluaran_data[] = (float)$pengeluaran['total'];
            }
            break;
            
        case 'monthly':
            for ($i = 5; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i months"));
                $month_name = date('M Y', strtotime($month));
                $labels[] = $month_name;
                
                $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                            WHERE user_id = :user_id AND jenis = "pemasukan" 
                            AND DATE_FORMAT(tanggal, "%Y-%m") = :month');
                $db->bind(':user_id', $user_id);
                $db->bind(':month', $month);
                $pemasukan = $db->single();
                $pemasukan_data[] = (float)$pemasukan['total'];
                
                $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                            WHERE user_id = :user_id AND jenis = "pengeluaran" 
                            AND DATE_FORMAT(tanggal, "%Y-%m") = :month');
                $db->bind(':user_id', $user_id);
                $db->bind(':month', $month);
                $pengeluaran = $db->single();
                $pengeluaran_data[] = (float)$pengeluaran['total'];
            }
            break;
            
        case 'yearly':
            for ($i = 4; $i >= 0; $i--) {
                $year = date('Y', strtotime("-$i years"));
                $labels[] = $year;
                
                $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                            WHERE user_id = :user_id AND jenis = "pemasukan" 
                            AND DATE_FORMAT(tanggal, "%Y") = :year');
                $db->bind(':user_id', $user_id);
                $db->bind(':year', $year);
                $pemasukan = $db->single();
                $pemasukan_data[] = (float)$pemasukan['total'];
                
                $db->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi 
                            WHERE user_id = :user_id AND jenis = "pengeluaran" 
                            AND DATE_FORMAT(tanggal, "%Y") = :year');
                $db->bind(':user_id', $user_id);
                $db->bind(':year', $year);
                $pengeluaran = $db->single();
                $pengeluaran_data[] = (float)$pengeluaran['total'];
            }
            break;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'labels' => $labels,
            'pemasukan' => $pemasukan_data,
            'pengeluaran' => $pengeluaran_data
        ]
    ]);
}
?>