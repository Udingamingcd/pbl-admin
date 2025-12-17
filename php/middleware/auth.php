<?php
// FILE: php/middleware/auth.php

/**
 * Middleware Authentication Checker
 * Versi Aman + Path Absolut (Tidak akan salah folder saat redirect)
 */

// Pastikan session start (dengan settings yang sama)
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../session/start-session.php';
}

// Load config & database
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../koneksi.php';

class AuthMiddleware {

    public static function checkAuth() {

        // Debug log (opsional)
        error_log("Auth Check - Session: " . json_encode([
            'user_id' => $_SESSION['user_id'] ?? 'not set',
            'logged_in' => $_SESSION['logged_in'] ?? 'not set',
            'last_activity' => $_SESSION['LAST_ACTIVITY'] ?? 'not set'
        ]));

        // Validasi session
        $isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
        $hasUserId  = isset($_SESSION['user_id']);
        $isValid    = $isLoggedIn && $hasUserId;

        // Jika session tidak valid → redirect ke root index
        if (!$isValid) {
            error_log("Auth failed → redirect ke /index.php");
            header("Location: /index.php");  
            exit();
        }

        // Opsional: Cek apakah user masih ada di database
        try {
            $db = new Database();
            $db->query("SELECT id, status FROM users WHERE id = :uid");
            $db->bind(":uid", $_SESSION['user_id']);
            $user = $db->single();

            if (!$user) {
                error_log("User tidak ditemukan → hapus session → redirect");
                session_destroy();
                header("Location: /index.php");
                exit();
            }

            // Update last activity
            $_SESSION['LAST_ACTIVITY'] = time();

        } catch (Exception $e) {
            error_log("DB Error (Auth) : " . $e->getMessage());
            $_SESSION['LAST_ACTIVITY'] = time();
        }
    }
}

// Jalankan middleware
AuthMiddleware::checkAuth();
