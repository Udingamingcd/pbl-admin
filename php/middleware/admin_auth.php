<?php
// php/middleware/admin_auth.php
// Middleware untuk autentikasi admin

class AdminAuth {
    
    /**
     * Cek apakah admin sudah login
     * Jika belum, redirect ke halaman login
     */
    public static function check() {
        session_start();
        if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_level'])) {
            // Simpan URL yang diminta untuk redirect setelah login
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            
            if (isset($_SESSION['admin_level']) && $_SESSION['admin_level'] === 'superadmin') {
                header('Location: /finansialku/superadmin/auth/login.php');
            } else {
                header('Location: /finansialku/admin/auth/login.php');
            }
            exit();
        }
    }
    
    /**
     * Cek apakah user adalah superadmin
     * Jika bukan, redirect ke dashboard admin biasa
     */
    public static function checkSuperAdmin() {
        self::check();
        if ($_SESSION['admin_level'] !== 'superadmin') {
            header('Location: /finansialku/admin/dashboard/index.php');
            exit();
        }
    }
    
    /**
     * Cek apakah admin sudah login (untuk halaman guest)
     * Jika sudah login, redirect ke dashboard sesuai level
     */
    public static function guest() {
        session_start();
        if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_level'])) {
            if ($_SESSION['admin_level'] === 'superadmin') {
                header('Location: /finansialku/superadmin/dashboard/index.php');
            } else {
                header('Location: /finansialku/admin/dashboard/index.php');
            }
            exit();
        }
    }
    
    /**
     * Dapatkan data admin yang sedang login
     */
    public static function user() {
        session_start();
        if (isset($_SESSION['admin_id'])) {
            return [
                'id' => $_SESSION['admin_id'],
                'nama' => $_SESSION['admin_nama'] ?? '',
                'email' => $_SESSION['admin_email'] ?? '',
                'level' => $_SESSION['admin_level'] ?? ''
            ];
        }
        return null;
    }
    
    /**
     * Logout admin
     */
    public static function logout() {
        session_start();
        
        // Hapus semua data session
        $_SESSION = array();
        
        // Hapus session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Hancurkan session
        session_destroy();
        
        // Hapus remember cookie
        setcookie('admin_remember', '', time() - 3600, '/');
    }
}
?>