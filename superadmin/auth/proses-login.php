<?php
session_start();
header('Content-Type: application/json');

require_once '../../php/koneksi.php';
$db = new Database();

$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Method tidak diizinkan';
    echo json_encode($response);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password'])) {
    $response['message'] = 'Email dan password harus diisi';
    echo json_encode($response);
    exit();
}

$email = trim($data['email']);
$password = $data['password'];
$remember = isset($data['remember']) ? true : false;

try {
    // Cari admin dengan level superadmin
    $db->query('SELECT * FROM admins WHERE email = :email AND level = "superadmin" AND status = "aktif"');
    $db->bind(':email', $email);
    $admin = $db->single();
    
    if ($admin) {
        if (password_verify($password, $admin['password'])) {
            // Set session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nama'] = $admin['nama'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_level'] = $admin['level'];
            
            // Update last login
            $db->query('UPDATE admins SET last_login = NOW() WHERE id = :id');
            $db->bind(':id', $admin['id']);
            $db->execute();
            
            // Set cookie jika remember me dipilih
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (30 * 24 * 60 * 60);
                
                $db->query('UPDATE admins SET remember_token = :token WHERE id = :id');
                $db->bind(':token', $token);
                $db->bind(':id', $admin['id']);
                $db->execute();
                
                setcookie('admin_remember', $token, $expiry, '/', '', false, true);
            }
            
            // Track session
            $db->query('INSERT INTO admin_sessions (admin_id, session_id, ip_address, user_agent) 
                       VALUES (:admin_id, :session_id, :ip, :agent)');
            $db->bind(':admin_id', $admin['id']);
            $db->bind(':session_id', session_id());
            $db->bind(':ip', $_SERVER['REMOTE_ADDR']);
            $db->bind(':agent', $_SERVER['HTTP_USER_AGENT']);
            $db->execute();
            
            $response['success'] = true;
            $response['message'] = 'Login berhasil';
            $response['redirect'] = '../dashboard/index.php';
            
        } else {
            $response['message'] = 'Password salah';
        }
    } else {
        $response['message'] = 'Email tidak terdaftar atau akun bukan superadmin';
    }
} catch (Exception $e) {
    $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
}

echo json_encode($response);
?>