<?php
session_start();
require_once '../../php/config.php';
require_once '../../php/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        $db = new Database();
        $db->query("SELECT * FROM admins WHERE email = :email AND status = 'aktif'");
        $db->bind(':email', $email);
        $admin = $db->single();

        if ($admin && password_verify($password, $admin['password'])) {
            // Update last login
            $db->query("UPDATE admins SET last_login = NOW(), last_activity = NOW() WHERE id = :id");
            $db->bind(':id', $admin['id']);
            $db->execute();

            // Set session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nama'] = $admin['nama'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_level'] = $admin['level'];
            
            // Redirect based on level
            if ($admin['level'] === 'superadmin') {
                header('Location: ../../superadmin/dashboard/index.php');
            } else {
                header('Location: ../dashboard/index.php');
            }
            exit();
        } else {
            $_SESSION['error'] = "Email atau password salah!";
            header('Location: login.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        header('Location: login.php');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
?>