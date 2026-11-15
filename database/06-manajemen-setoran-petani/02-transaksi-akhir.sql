-- =====================================================================
-- mudatani99 - Manajemen Setoran Petani -> Transaksi Akhir
-- File      : database/06-manajemen-setoran-petani/02-transaksi-akhir.sql
-- Tabel     : transaksi_akhir
-- Prasyarat : 02-data-anggota/01-daftar-anggota-petani.sql,
--             06-manajemen-setoran-petani/01-setoran-petani.sql
-- Catatan   : Perhitungan transaksi akhir setoran petani berdasarkan
--             berat setoran, potongan rapaksi (Kg), berat akhir,
--             harga per Kg, dan total jumlah uang.
-- =====================================================================

-- 1. Buat Tabel transaksi_akhir
CREATE TABLE IF NOT EXISTS `transaksi_akhir` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `setoran_id` INT(11) NOT NULL,
    `anggota_id` INT(11) DEFAULT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `berat_setoran` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `rapaksi` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `berat_akhir` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `harga_per_kg` BIGINT NOT NULL DEFAULT 0,
    `jumlah_uang` BIGINT NOT NULL DEFAULT 0,
    `tanggal` DATE NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_kode_transaksi_akhir` (`kode_transaksi`),
    UNIQUE KEY `uniq_transaksi_akhir_setoran` (`setoran_id`),
    KEY `idx_ta_anggota` (`anggota_id`),
    KEY `idx_ta_tanggal` (`tanggal`),
    CONSTRAINT `fk_ta_setoran` FOREIGN KEY (`setoran_id`) REFERENCES `setoran_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ta_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Query Lanjutan: Penambahan Kolom Tanaman (Master Tanaman), Harga (/Kg), dan Penyesuaian Kolom Transaksi Akhir
--    (Aman dijalankan pada tabel dari query ke-1 maupun tabel transaksi_akhir yang sudah ada)

-- 2a. Jadikan setoran_id opsional (NULL) jika kolom setoran_id ada
SET @has_setoran_id := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'transaksi_akhir'
      AND COLUMN_NAME = 'setoran_id'
);
SET @sql_nullable_setoran := IF(
    @has_setoran_id > 0,
    'ALTER TABLE `transaksi_akhir` MODIFY COLUMN `setoran_id` INT(11) DEFAULT NULL',
    'SELECT 1'
);
PREPARE stmt_nullable_setoran FROM @sql_nullable_setoran;
EXECUTE stmt_nullable_setoran;
DEALLOCATE PREPARE stmt_nullable_setoran;

-- 2b. Hapus unique index uniq_transaksi_akhir_setoran jika ada
SET @has_uniq_setoran := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'transaksi_akhir'
      AND INDEX_NAME = 'uniq_transaksi_akhir_setoran'
);
SET @sql_drop_uniq_setoran := IF(
    @has_uniq_setoran > 0,
    'ALTER TABLE `transaksi_akhir` DROP INDEX `uniq_transaksi_akhir_setoran`',
    'SELECT 1'
);
PREPARE stmt_drop_uniq_setoran FROM @sql_drop_uniq_setoran;
EXECUTE stmt_drop_uniq_setoran;
DEALLOCATE PREPARE stmt_drop_uniq_setoran;

-- 2c. Tambah kolom tanaman_id (relasi ke master_produk) jika belum ada
SET @has_ta_tanaman_id := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'transaksi_akhir'
      AND COLUMN_NAME = 'tanaman_id'
);
SET @sql_add_ta_tanaman_id := IF(
    @has_ta_tanaman_id = 0,
    'ALTER TABLE `transaksi_akhir` ADD COLUMN `tanaman_id` INT(11) DEFAULT NULL AFTER `nama_petani`, ADD KEY `idx_ta_tanaman` (`tanaman_id`), ADD CONSTRAINT `fk_ta_tanaman` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE stmt_add_ta_tanaman_id FROM @sql_add_ta_tanaman_id;
EXECUTE stmt_add_ta_tanaman_id;
DEALLOCATE PREPARE stmt_add_ta_tanaman_id;

-- 2d. Tambah kolom nama_tanaman jika belum ada
SET @has_ta_nama_tanaman := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'transaksi_akhir'
      AND COLUMN_NAME = 'nama_tanaman'
);
SET @sql_add_ta_nama_tanaman := IF(
    @has_ta_nama_tanaman = 0,
    'ALTER TABLE `transaksi_akhir` ADD COLUMN `nama_tanaman` VARCHAR(100) NOT NULL DEFAULT ''-'' AFTER `tanaman_id`',
    'SELECT 1'
);
PREPARE stmt_add_ta_nama_tanaman FROM @sql_add_ta_nama_tanaman;
EXECUTE stmt_add_ta_nama_tanaman;
DEALLOCATE PREPARE stmt_add_ta_nama_tanaman;

-- 2e. Tambah kolom jumlah_setoran jika belum ada
SET @has_ta_jumlah_setoran := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'transaksi_akhir'
      AND COLUMN_NAME = 'jumlah_setoran'
);
SET @sql_add_ta_jumlah_setoran := IF(
    @has_ta_jumlah_setoran = 0,
    'ALTER TABLE `transaksi_akhir` ADD COLUMN `jumlah_setoran` VARCHAR(50) NOT NULL DEFAULT ''0 Kg'' AFTER `nama_tanaman`',
    'SELECT 1'
);
PREPARE stmt_add_ta_jumlah_setoran FROM @sql_add_ta_jumlah_setoran;
EXECUTE stmt_add_ta_jumlah_setoran;
DEALLOCATE PREPARE stmt_add_ta_jumlah_setoran;

-- 2f. Sesuaikan tipe kolom rapaksi menjadi VARCHAR(50) NOT NULL DEFAULT '0 Kg'
SET @has_ta_rapaksi := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'transaksi_akhir'
      AND COLUMN_NAME = 'rapaksi'
);
SET @sql_mod_ta_rapaksi := IF(
    @has_ta_rapaksi > 0,
    'ALTER TABLE `transaksi_akhir` MODIFY COLUMN `rapaksi` VARCHAR(50) NOT NULL DEFAULT ''0 Kg''',
    'ALTER TABLE `transaksi_akhir` ADD COLUMN `rapaksi` VARCHAR(50) NOT NULL DEFAULT ''0 Kg'' AFTER `jumlah_setoran`'
);
PREPARE stmt_mod_ta_rapaksi FROM @sql_mod_ta_rapaksi;
EXECUTE stmt_mod_ta_rapaksi;
DEALLOCATE PREPARE stmt_mod_ta_rapaksi;

-- 2g. Tambah kolom berat_final jika belum ada
SET @has_ta_berat_final := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'transaksi_akhir'
      AND COLUMN_NAME = 'berat_final'
);
SET @sql_add_ta_berat_final := IF(
    @has_ta_berat_final = 0,
    'ALTER TABLE `transaksi_akhir` ADD COLUMN `berat_final` VARCHAR(50) NOT NULL DEFAULT ''0 Kg'' AFTER `rapaksi`',
    'SELECT 1'
);
PREPARE stmt_add_ta_berat_final FROM @sql_add_ta_berat_final;
EXECUTE stmt_add_ta_berat_final;
DEALLOCATE PREPARE stmt_add_ta_berat_final;

-- 2h. Tambah kolom harga_per_kg jika belum ada
SET @has_ta_harga_per_kg := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'transaksi_akhir'
      AND COLUMN_NAME = 'harga_per_kg'
);
SET @sql_add_ta_harga_per_kg := IF(
    @has_ta_harga_per_kg = 0,
    'ALTER TABLE `transaksi_akhir` ADD COLUMN `harga_per_kg` BIGINT NOT NULL DEFAULT 0 AFTER `berat_final`',
    'SELECT 1'
);
PREPARE stmt_add_ta_harga_per_kg FROM @sql_add_ta_harga_per_kg;
EXECUTE stmt_add_ta_harga_per_kg;
DEALLOCATE PREPARE stmt_add_ta_harga_per_kg;

-- 2i. Tambah kolom jumlah_uang (Total Uang = Berat Final x Harga /Kg) jika belum ada
SET @has_ta_jumlah_uang := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'transaksi_akhir'
      AND COLUMN_NAME = 'jumlah_uang'
);
SET @sql_add_ta_jumlah_uang := IF(
    @has_ta_jumlah_uang = 0,
    'ALTER TABLE `transaksi_akhir` ADD COLUMN `jumlah_uang` BIGINT NOT NULL DEFAULT 0 AFTER `harga_per_kg`',
    'SELECT 1'
);
PREPARE stmt_add_ta_jumlah_uang FROM @sql_add_ta_jumlah_uang;
EXECUTE stmt_add_ta_jumlah_uang;
DEALLOCATE PREPARE stmt_add_ta_jumlah_uang;
