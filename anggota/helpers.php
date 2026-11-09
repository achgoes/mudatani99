<?php
// Pastikan tabel anggota_petani tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `anggota_petani` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_petani` VARCHAR(100) NOT NULL,
    `alamat` VARCHAR(255) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Pastikan tabel master_produk tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `master_produk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_produk` VARCHAR(100) NOT NULL,
    `jenis` ENUM('Obat', 'Pupuk', 'Tanaman') NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Pastikan tabel lahan_petani tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `lahan_petani` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `anggota_id` INT(11) NOT NULL,
    `luas_lahan` VARCHAR(50) NOT NULL,
    `tanaman_id` INT(11) DEFAULT NULL,
    `ditanami` VARCHAR(100) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_anggota_id` (`anggota_id`),
    KEY `idx_tanaman_id` (`tanaman_id`),
    CONSTRAINT `fk_lahan_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Pastikan kolom tanaman_id ada pada tabel lahan_petani lama
$colCheck = $conn->query("SHOW COLUMNS FROM `lahan_petani` LIKE 'tanaman_id'");
if ($colCheck && $colCheck->num_rows === 0) {
    $conn->query("ALTER TABLE `lahan_petani` ADD COLUMN `tanaman_id` INT(11) DEFAULT NULL AFTER `luas_lahan`, ADD KEY `idx_tanaman_id` (`tanaman_id`)");
}

// Tentukan tampilan aktif: 'daftar' (default) atau 'lahan' (Pemetaan & Luas Lahan)
$activeView = (isset($_GET['view']) && $_GET['view'] === 'lahan') ? 'lahan' : 'daftar';
