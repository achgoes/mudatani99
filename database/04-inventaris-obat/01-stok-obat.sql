-- =====================================================================
-- mudatani99 - Inventaris Obat -> Stok Obat
-- File      : database/04-inventaris-obat/01-stok-obat.sql
-- Tabel     : pengelolaan_obat
-- Prasyarat : 01-master/02-obat-pupuk-tanaman.sql
-- Catatan   : Menggunakan kolom jumlah_kemasan (satuan pilihan Pcs / Botol).
-- =====================================================================

-- 1. Buat Tabel pengelolaan_obat (Stok Obat)
CREATE TABLE IF NOT EXISTS `pengelolaan_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `obat_id` INT(11) DEFAULT NULL,
    `nama_obat` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_obat_id` (`obat_id`),
    CONSTRAINT `fk_pengelolaan_obat_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrasi Otomatis Jika Tabel Lama Masih Menggunakan Kolom jumlah_karung
SET @has_old_karung := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pengelolaan_obat'
      AND COLUMN_NAME = 'jumlah_karung'
);
SET @sql_rename_karung := IF(
    @has_old_karung > 0,
    'ALTER TABLE `pengelolaan_obat` CHANGE COLUMN `jumlah_karung` `jumlah_kemasan` VARCHAR(50) NOT NULL',
    'SELECT 1'
);
PREPARE stmt_rename_karung FROM @sql_rename_karung;
EXECUTE stmt_rename_karung;
DEALLOCATE PREPARE stmt_rename_karung;

-- 3. Data Awal Stok Obat (Diambil dari master_produk jenis = 'Obat', Satuan Pcs / Botol)
INSERT INTO `pengelolaan_obat` (`id`, `obat_id`, `nama_obat`, `jumlah_kemasan`, `jumlah_berat`, `keterangan`) VALUES
(1, 4, 'Insektisida Virtako 300 Sc', '12 Botol', '12 Kg', 'Stok Obat Pengendali Hama Wereng Dan Sundep'),
(2, 5, 'Fungisida Score 250 Ec',    '15 Pcs',   '15 Kg', 'Stok Obat Pengendali Jamur Dan Bercak Daun')
ON DUPLICATE KEY UPDATE
    `obat_id` = VALUES(`obat_id`),
    `nama_obat` = VALUES(`nama_obat`),
    `jumlah_kemasan` = VALUES(`jumlah_kemasan`),
    `jumlah_berat` = VALUES(`jumlah_berat`),
    `keterangan` = VALUES(`keterangan`);
