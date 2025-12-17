<?php
// FILE: ajax/cek-koneksi.php

// Matikan error display
ini_set('display_errors', 0);
error_reporting(0);

require_once '../php/config.php';
require_once '../php/koneksi.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    
    // Test connection dengan query yang lebih sederhana
    $db->query('SELECT 1 as connection_test');
    $result = $db->single();
    
    if ($result && isset($result['connection_test'])) {
        echo json_encode([
            'connected' => true,
            'message' => 'Koneksi database berhasil'
        ]);
    } else {
        throw new Exception('Query test gagal');
    }
} catch (Exception $e) {
    echo json_encode([
        'connected' => false,
        'message' => 'Koneksi database gagal'
    ]);
}
?>