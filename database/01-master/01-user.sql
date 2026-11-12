-- =====================================================================
-- mudatani99 - Master -> User
-- File   : database/01-master/01-user.sql
-- Tabel  : users
-- Urutan : 1 (Jalankan paling awal)
-- =====================================================================

-- 1. Buat Tabel users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nik` VARCHAR(4) NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_nik` (`nik`),
    UNIQUE KEY `unique_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Data Awal Pengguna (NIK 4 digit dimulai dari 0001)
-- Catatan: Aplikasi mendukung pencocokan teks langsung maupun password_hash (Bcrypt)
INSERT INTO `users` (`id`, `nik`, `username`, `nama`, `password`) VALUES
(1, '0001', 'admin', 'Budi Santoso', 'admin123'),
(2, '0002', 'siti', 'Siti Aminah', 'admin123'),
(3, '0003', 'slamet', 'Slamet Riyadi', 'admin123'),
(4, '0004', 'hendra', 'Hendra Wijaya', 'admin123')
ON DUPLICATE KEY UPDATE
    `nama` = VALUES(`nama`),
    `password` = VALUES(`password`);
