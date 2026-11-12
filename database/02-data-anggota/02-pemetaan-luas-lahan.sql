-- =====================================================================
-- mudatani99 - Data Anggota -> Pemetaan & Luas Lahan
-- File      : database/02-data-anggota/02-pemetaan-luas-lahan.sql
-- Tabel     : lahan_petani
-- Prasyarat : 01-master/02-obat-pupuk-tanaman.sql, 02-data-anggota/01-daftar-anggota-petani.sql
-- Urutan    : 4
-- =====================================================================

-- 1. Buat Tabel lahan_petani (Sudah Dilengkapi Relasi tanaman_id -> master_produk)
CREATE TABLE IF NOT EXISTS `lahan_petani` (
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
    CONSTRAINT `fk_lahan_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lahan_tanaman` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrasi Otomatis Jika Tabel Lama Belum Memiliki Kolom tanaman_id
SET @has_tanaman_id := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'lahan_petani'
      AND COLUMN_NAME = 'tanaman_id'
);
SET @sql_add_tanaman_id := IF(
    @has_tanaman_id = 0,
    'ALTER TABLE `lahan_petani` ADD COLUMN `tanaman_id` INT(11) DEFAULT NULL AFTER `luas_lahan`, ADD KEY `idx_tanaman_id` (`tanaman_id`), ADD CONSTRAINT `fk_lahan_tanaman` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE stmt_add_tanaman_id FROM @sql_add_tanaman_id;
EXECUTE stmt_add_tanaman_id;
DEALLOCATE PREPARE stmt_add_tanaman_id;

-- 3. Data Awal Pemetaan & Luas Lahan (Sinkron dengan master_produk jenis = 'Tanaman')
INSERT INTO `lahan_petani` (`id`, `anggota_id`, `luas_lahan`, `tanaman_id`, `ditanami`, `keterangan`) VALUES
(1, 1, '2.5 Ha',  6,  'Padi Sawah',     'Blok Utara A-1, Irigasi Teknis'),
(2, 2, '1.2 Ha',  8,  'Cabai Merah',    'Blok Timur B-2, Lahan Tegal'),
(3, 3, '1.75 Ha', 7,  'Jagung Hibrida', 'Blok Selatan C-1, Sawah Tadah Hujan'),
(4, 4, '0.5 Ha',  9,  'Cabai Rawit',    'Lahan Demplot Desa'),
(5, 5, '2.0 Ha',  10, 'Kacang Panjang', 'Blok Utara A-3, Irigasi Teknis')
ON DUPLICATE KEY UPDATE
    `anggota_id` = VALUES(`anggota_id`),
    `luas_lahan` = VALUES(`luas_lahan`),
    `tanaman_id` = VALUES(`tanaman_id`),
    `ditanami` = VALUES(`ditanami`),
    `keterangan` = VALUES(`keterangan`);
