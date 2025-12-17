<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $db = new Database();
    $db->query('SELECT id, nama, email, password FROM users WHERE email = :email');
    $db->bind(':email', $email);
    $row = $db->single();

    if ($row) {
        if (password_verify($password, $row->password)) {
            $_SESSION['user_id'] = $row->id;
            $_SESSION['user_nama'] = $row->nama;
            $_SESSION['user_email'] = $row->email;
            $_SESSION['logged_in'] = true;
            $_SESSION['LAST_ACTIVITY'] = time();
            echo json_encode(['status' => 'success', 'message' => 'Login berhasil!']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Password salah!']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email tidak ditemukan!']);
        exit;
    }
}
?>