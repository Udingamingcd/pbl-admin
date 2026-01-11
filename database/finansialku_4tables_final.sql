-- ===============================
-- DATABASE: finansialku
-- ===============================
CREATE DATABASE IF NOT EXISTS finansialku
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE finansialku;

-- ===============================
-- TABLE: users
-- ===============================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    foto_profil VARCHAR(255) DEFAULT NULL,
    telepon VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(100) DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    last_activity DATETIME DEFAULT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

-- ===============================
-- TABLE: budget
-- ===============================
CREATE TABLE IF NOT EXISTS budget (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_budget VARCHAR(100) NOT NULL,
    jumlah DECIMAL(20,2) NOT NULL,
    periode ENUM('harian', 'mingguan', 'bulanan', 'tahunan') NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_akhir DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_budget_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- ===============================
-- TABLE: transaksi
-- ===============================
CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    jenis ENUM('pemasukan', 'pengeluaran') NOT NULL,
    jumlah DECIMAL(20,2) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    tanggal DATE NOT NULL,
    metode_bayar VARCHAR(50) DEFAULT NULL,
    lokasi VARCHAR(100) DEFAULT NULL,
    bukti_transaksi VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaksi_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- ===============================
-- TABLE: financial_goal
-- ===============================
CREATE TABLE IF NOT EXISTS financial_goal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_goal VARCHAR(100) NOT NULL,
    target_jumlah DECIMAL(20,2) NOT NULL,
    terkumpul DECIMAL(20,2) DEFAULT 0.00,
    tenggat_waktu DATE NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    status ENUM('aktif', 'tercapai', 'dibatalkan') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_goal_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- ===============================
-- TABLE: admins
-- ===============================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    level ENUM('admin', 'superadmin') DEFAULT 'admin',
    foto_profil VARCHAR(255) DEFAULT NULL,
    telepon VARCHAR(20) DEFAULT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    last_login DATETIME DEFAULT NULL,
    last_activity DATETIME DEFAULT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL,
    CONSTRAINT fk_admin_creator
        FOREIGN KEY (created_by)
        REFERENCES admins(id)
        ON DELETE SET NULL
);

-- ===============================
-- INDEXES (PERFORMA)
-- ===============================
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_last_activity ON users(last_activity);

CREATE INDEX idx_budget_user_periode
ON budget(user_id, periode);

CREATE INDEX idx_transaksi_user_date
ON transaksi(user_id, tanggal);

CREATE INDEX idx_goal_user_status
ON financial_goal(user_id, status);

CREATE INDEX idx_admins_email ON admins(email);
CREATE INDEX idx_admins_level ON admins(level);
CREATE INDEX idx_admins_last_activity ON admins(last_activity);

-- ===============================
-- OTP & EMAIL VERIFICATION COLUMNS
-- ===============================
ALTER TABLE users
ADD otp_code VARCHAR(6) DEFAULT NULL,
ADD otp_expires DATETIME DEFAULT NULL;
