<?php
session_start();

require_once '../../php/koneksi.php';

// Check if user is superadmin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_level'] !== 'superadmin') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$db = new Database();

// Get all admins
$db->query('SELECT * FROM admins ORDER BY level DESC, created_at DESC');
$admins = $db->resultSet();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=admins_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID', 
    'Nama', 
    'Email', 
    'Level', 
    'Status', 
    'Telepon', 
    'Login Terakhir',
    'Tanggal Dibuat',
    'Dibuat Oleh'
], ';');

// Add data rows
foreach ($admins as $admin) {
    // Get creator name if exists
    $creator = 'System';
    if ($admin['created_by']) {
        $db->query('SELECT nama FROM admins WHERE id = :id');
        $db->bind(':id', $admin['created_by']);
        $creator_data = $db->single();
        if ($creator_data) {
            $creator = $creator_data['nama'];
        }
    }
    
    fputcsv($output, [
        $admin['id'],
        $admin['nama'],
        $admin['email'],
        $admin['level'],
        $admin['status'],
        $admin['telepon'] ?? '',
        $admin['last_login'] ?? 'Belum login',
        $admin['created_at'],
        $creator
    ], ';');
}

fclose($output);
exit();
?>