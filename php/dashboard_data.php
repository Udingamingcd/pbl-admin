<?php
require_once 'koneksi.php';

function getDashboardData($user_id) {
    $db = new Database();
    $current_month = date('Y-m');
    $previous_month = date('Y-m', strtotime('-1 month'));
    
    try {
        // Data statistik utama
        $db->query('SELECT 
                    COALESCE(SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE 0 END), 0) as total_pemasukan,
                    COALESCE(SUM(CASE WHEN jenis = "pengeluaran" THEN jumlah ELSE 0 END), 0) as total_pengeluaran
                    FROM transaksi 
                    WHERE user_id = :user_id 
                    AND DATE_FORMAT(tanggal, "%Y-%m") = :current_month');
        $db->bind(':user_id', $user_id);
        $db->bind(':current_month', $current_month);
        $current_stats = $db->single();
        
        // Data bulan sebelumnya untuk perbandingan
        $db->query('SELECT 
                    COALESCE(SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE 0 END), 0) as total_pemasukan,
                    COALESCE(SUM(CASE WHEN jenis = "pengeluaran" THEN jumlah ELSE 0 END), 0) as total_pengeluaran
                    FROM transaksi 
                    WHERE user_id = :user_id 
                    AND DATE_FORMAT(tanggal, "%Y-%m") = :previous_month');
        $db->bind(':user_id', $user_id);
        $db->bind(':previous_month', $previous_month);
        $previous_stats = $db->single();
        
        // Hitung saldo dan perubahan persentase
        $saldo_bulan_ini = $current_stats['total_pemasukan'] - $current_stats['total_pengeluaran'];
        $saldo_bulan_lalu = $previous_stats['total_pemasukan'] - $previous_stats['total_pengeluaran'];
        
        $pemasukan_change = $previous_stats['total_pemasukan'] > 0 ? 
            (($current_stats['total_pemasukan'] - $previous_stats['total_pemasukan']) / $previous_stats['total_pemasukan']) * 100 : 0;
        
        $pengeluaran_change = $previous_stats['total_pengeluaran'] > 0 ? 
            (($current_stats['total_pengeluaran'] - $previous_stats['total_pengeluaran']) / $previous_stats['total_pengeluaran']) * 100 : 0;
        
        $saldo_change = $saldo_bulan_lalu != 0 ? 
            (($saldo_bulan_ini - $saldo_bulan_lalu) / abs($saldo_bulan_lalu)) * 100 : 0;
        
        // Data target
        $db->query('SELECT 
                    COUNT(*) as total_goals, 
                    SUM(CASE WHEN status = "tercapai" THEN 1 ELSE 0 END) as achieved_goals,
                    SUM(CASE WHEN status = "aktif" THEN 1 ELSE 0 END) as active_goals
                    FROM financial_goal 
                    WHERE user_id = :user_id');
        $db->bind(':user_id', $user_id);
        $goals = $db->single();
        
        $target_tercapai = $goals['total_goals'] > 0 ? 
            round(($goals['achieved_goals'] / $goals['total_goals']) * 100) : 0;
        
        // Total transaksi
        $db->query('SELECT COUNT(*) as total_transactions FROM transaksi WHERE user_id = :user_id');
        $db->bind(':user_id', $user_id);
        $transactions_count = $db->single();
        
        // Transaksi terbaru
        $db->query('SELECT deskripsi, jumlah, jenis, tanggal, kategori 
                    FROM transaksi 
                    WHERE user_id = :user_id 
                    ORDER BY tanggal DESC, created_at DESC 
                    LIMIT 5');
        $db->bind(':user_id', $user_id);
        $recent_transactions = $db->resultSet();
        
        // Data budget aktif
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
                    LIMIT 3');
        $db->bind(':user_id', $user_id);
        $db->bind(':current_date', $current_date);
        $budget_data = $db->resultSet();
        
        // Data target aktif
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
                    LIMIT 3');
        $db->bind(':user_id', $user_id);
        $goal_data = $db->resultSet();
        
        return [
            'success' => true,
            'saldo_bulan_ini' => $saldo_bulan_ini,
            'total_pemasukan' => $current_stats['total_pemasukan'],
            'total_pengeluaran' => $current_stats['total_pengeluaran'],
            'target_tercapai' => $target_tercapai,
            'perbandingan' => [
                'pemasukan_change' => round($pemasukan_change, 1),
                'pengeluaran_change' => round($pengeluaran_change, 1),
                'saldo_change' => round($saldo_change, 1)
            ],
            'summary' => [
                'total_goals' => $goals['total_goals'],
                'achieved_goals' => $goals['achieved_goals'],
                'active_goals' => $goals['active_goals'],
                'total_transactions' => $transactions_count['total_transactions']
            ],
            'recent_transactions' => $recent_transactions,
            'budget_data' => $budget_data,
            'goal_data' => $goal_data
        ];
        
    } catch (Exception $e) {
        error_log("Dashboard data error: " . $e->getMessage());
        return [
            'success' => false,
            'saldo_bulan_ini' => 0,
            'total_pemasukan' => 0,
            'total_pengeluaran' => 0,
            'target_tercapai' => 0,
            'perbandingan' => [
                'pemasukan_change' => 0,
                'pengeluaran_change' => 0,
                'saldo_change' => 0
            ],
            'summary' => [
                'total_goals' => 0,
                'achieved_goals' => 0,
                'active_goals' => 0,
                'total_transactions' => 0
            ],
            'recent_transactions' => [],
            'budget_data' => [],
            'goal_data' => []
        ];
    }
}
?>