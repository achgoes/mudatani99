-- =====================================================================
-- mudatani99 - Inventaris Tanaman -> Stok Tanaman
-- File      : database/05-inventaris-tanaman/01-stok-tanaman.sql
-- Tabel     : pengelolaan_tanaman
-- Prasyarat : 01-master/02-obat-pupuk-tanaman.sql
-- Catatan   : Menggunakan kolom jumlah_kemasan (satuan: Pcs).
-- =====================================================================

-- 1. Buat Tabel pengelolaan_tanaman (Stok Tanaman)
CREATE TABLE IF NOT EXISTS `pengelolaan_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tanaman_inv_id` (`tanaman_id`),
    CONSTRAINT `fk_pengelolaan_tanaman_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrasi Otomatis Jika Tabel Lama Masih Menggunakan Kolom jumlah_karung
SET @has_old_karung_tanaman := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pengelolaan_tanaman'
      AND COLUMN_NAME = 'jumlah_karung'
);
SET @sql_rename_karung_tanaman := IF(
    @has_old_karung_tanaman > 0,
    'ALTER TABLE `pengelolaan_tanaman` CHANGE COLUMN `jumlah_karung` `jumlah_kemasan` VARCHAR(50) NOT NULL',
    'SELECT 1'
);
PREPARE stmt_rename_karung_tanaman FROM @sql_rename_karung_tanaman;
EXECUTE stmt_rename_karung_tanaman;
DEALLOCATE PREPARE stmt_rename_karung_tanaman;

-- 3. Data Awal Stok Tanaman (Diambil dari master_produk jenis = 'Tanaman', Satuan Pcs)
INSERT INTO `pengelolaan_tanaman` (`id`, `tanaman_id`, `nama_tanaman`, `jumlah_kemasan`, `jumlah_berat`, `keterangan`) VALUES
(1, 6,  'Padi Sawah',     '30 Pcs', '750 Kg', 'Stok Benih Padi Unggul Siap Tanam'),
(2, 7,  'Jagung Hibrida', '18 Pcs', '450 Kg', 'Stok Benih Jagung Hibrida Gudang Poktan'),
(3, 8,  'Cabai Merah',    '10 Pcs', '100 Kg', 'Bibit Unggul Cabai Merah Kelompok'),
(4, 9,  'Cabai Rawit',    '8 Pcs',  '80 Kg',  'Bibit Unggul Cabai Rawit Kelompok'),
(5, 10, 'Kacang Panjang', '12 Pcs', '120 Kg', 'Benih Kacang Panjang Musim Tanam')
ON DUPLICATE KEY UPDATE
    `tanaman_id` = VALUES(`tanaman_id`),
    `nama_tanaman` = VALUES(`nama_tanaman`),
    `jumlah_kemasan` = VALUES(`jumlah_kemasan`),
    `jumlah_berat` = VALUES(`jumlah_berat`),
    `keterangan` = VALUES(`keterangan`);
