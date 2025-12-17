<?php
require_once '../config.php';
require_once '../koneksi.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    $db = new Database();
    $user_id = $_SESSION['user_id'];

    switch ($action) {
        case 'get_recent':
            getRecentTransactions($db, $user_id);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Transaksi API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}

function getRecentTransactions($db, $user_id) {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
    
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
?>