-- phpMyAdmin SQL Dump
-- Database: `finansialku`

CREATE DATABASE IF NOT EXISTS `finansialku`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `finansialku`;

-- ============================
-- TABEL ADMINS
-- ============================
CREATE TABLE `admins` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `level` ENUM('admin','superadmin') DEFAULT 'admin',
  `foto_profil` VARCHAR(255) DEFAULT NULL,
  `telepon` VARCHAR(20) DEFAULT NULL,
  `status` ENUM('aktif','nonaktif') DEFAULT 'aktif',
  `last_login` DATETIME DEFAULT NULL,
  `last_activity` DATETIME DEFAULT NULL,
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `reset_token` VARCHAR(100) DEFAULT NULL,
  `reset_expires` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_admin_creator` (`created_by`),
  KEY `idx_admins_email` (`email`),
  KEY `idx_admins_level` (`level`),
  KEY `idx_admins_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- TABEL USERS
-- ============================
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `foto_profil` VARCHAR(255) DEFAULT NULL,
  `telepon` VARCHAR(20) DEFAULT NULL,
  `alamat` TEXT,
  `email_verified` TINYINT(1) DEFAULT '0',
  `verification_token` VARCHAR(100) DEFAULT NULL,
  `reset_token` VARCHAR(100) DEFAULT NULL,
  `reset_expires` DATETIME DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `last_activity` DATETIME DEFAULT NULL,
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- TABEL BUDGET
-- ============================
CREATE TABLE `budget` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `nama_budget` VARCHAR(100) NOT NULL,
  `jumlah` DECIMAL(20,2) NOT NULL,
  `periode` ENUM('harian','mingguan','bulanan','tahunan') NOT NULL,
  `kategori` VARCHAR(50) NOT NULL,
  `deskripsi` TEXT,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_akhir` DATE NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_budget_user_periode` (`user_id`, `periode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- TABEL FINANCIAL GOAL
-- ============================
CREATE TABLE `financial_goal` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `nama_goal` VARCHAR(100) NOT NULL,
  `target_jumlah` DECIMAL(20,2) NOT NULL,
  `terkumpul` DECIMAL(20,2) DEFAULT '0.00',
  `tenggat_waktu` DATE NOT NULL,
  `deskripsi` TEXT,
  `status` ENUM('aktif','tercapai','dibatalkan') DEFAULT 'aktif',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_goal_user_status` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- TABEL TRANSAKSI
-- ============================
CREATE TABLE `transaksi` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `kategori` VARCHAR(50) NOT NULL,
  `jenis` ENUM('pemasukan','pengeluaran') NOT NULL,
  `jumlah` DECIMAL(20,2) NOT NULL,
  `deskripsi` TEXT,
  `tanggal` DATE NOT NULL,
  `metode_bayar` VARCHAR(50) DEFAULT NULL,
  `lokasi` VARCHAR(100) DEFAULT NULL,
  `bukti_transaksi` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transaksi_user_date` (`user_id`, `tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- FOREIGN KEY RELATIONS
-- ============================

ALTER TABLE `admins`
  ADD CONSTRAINT `fk_admin_creator`
  FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

ALTER TABLE `budget`
  ADD CONSTRAINT `fk_budget_user`
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `financial_goal`
  ADD CONSTRAINT `fk_goal_user`
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_user`
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
