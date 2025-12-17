<?php
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Cek apakah user masih aktif di database
try {
    $db = new Database();
    $db->query("SELECT id, status FROM admins WHERE id = :id");
    $db->bind(':id', $_SESSION['admin_id']);
    $admin = $db->single();
    
    if (!$admin || $admin['status'] !== 'aktif') {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    // Update last activity
    $db->query("UPDATE admins SET last_activity = NOW() WHERE id = :id");
    $db->bind(':id', $_SESSION['admin_id']);
    $db->execute();
    
} catch (Exception $e) {
    error_log("Auth error: " . $e->getMessage());
    session_destroy();
    header('Location: login.php');
    exit();
}
?>