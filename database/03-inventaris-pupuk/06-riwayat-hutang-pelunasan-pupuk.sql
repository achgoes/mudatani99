-- =====================================================================
-- mudatani99 - Inventaris Pupuk -> Riwayat Hutang / Pelunasan
-- File      : database/03-inventaris-pupuk/06-riwayat-hutang-pelunasan-pupuk.sql
-- Tabel     : riwayat_hutang_pupuk, pembayaran_hutang_pupuk
-- Prasyarat : 03-inventaris-pupuk/05-hutang-piutang-pupuk.sql
-- Catatan   :
--   - riwayat_hutang_pupuk: ringkasan sinkron 1 baris per hutang_pupuk.
--   - pembayaran_hutang_pupuk: jejak setiap pembayaran/cicilan (1 baris = 1 kali bayar).
-- =====================================================================

-- 1. Buat Tabel riwayat_hutang_pupuk
CREATE TABLE IF NOT EXISTS `riwayat_hutang_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `pengelolaan_pupuk_id` INT(11) DEFAULT NULL,
    `pupuk_id` INT(11) DEFAULT NULL,
    `nama_pupuk` VARCHAR(255) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `jumlah_hutang` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `sisa_hutang` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `keterangan` VARCHAR(255) DEFAULT '-',
    `tanggal` DATE DEFAULT NULL,
    `status` ENUM('Belum Lunas', 'Lunas') NOT NULL DEFAULT 'Belum Lunas',
    `tanggal_lunas` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_hutang_id` (`hutang_id`),
    KEY `idx_riwayat_anggota` (`anggota_id`),
    KEY `idx_riwayat_pupuk` (`pupuk_id`),
    CONSTRAINT `fk_riwayat_hutang_parent` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_pupuk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrasi Otomatis Struktur Kolom riwayat_hutang_pupuk Lama (Jika Ada)
SET @has_rhp_old_jml_harga := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'riwayat_hutang_pupuk' AND COLUMN_NAME = 'jumlah_harga'
);
SET @has_rhp_harga := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'riwayat_hutang_pupuk' AND COLUMN_NAME = 'harga'
);
SET @sql_rhp_harga := IF(
    @has_rhp_harga = 0 AND @has_rhp_old_jml_harga > 0,
    'ALTER TABLE `riwayat_hutang_pupuk` CHANGE COLUMN `jumlah_harga` `harga` VARCHAR(50) NOT NULL DEFAULT ''Rp 0''',
    IF(
        @has_rhp_harga = 0,
        'ALTER TABLE `riwayat_hutang_pupuk` ADD COLUMN `harga` VARCHAR(50) NOT NULL DEFAULT ''Rp 0'' AFTER `jumlah_berat`',
        'SELECT 1'
    )
);
PREPARE stmt_rhp_harga FROM @sql_rhp_harga;
EXECUTE stmt_rhp_harga;
DEALLOCATE PREPARE stmt_rhp_harga;

SET @has_rhp_jumlah_hutang := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'riwayat_hutang_pupuk' AND COLUMN_NAME = 'jumlah_hutang'
);
SET @sql_rhp_jumlah_hutang := IF(
    @has_rhp_jumlah_hutang = 0,
    'ALTER TABLE `riwayat_hutang_pupuk` ADD COLUMN `jumlah_hutang` VARCHAR(50) NOT NULL DEFAULT ''Rp 0'' AFTER `harga`',
    'SELECT 1'
);
PREPARE stmt_rhp_jumlah_hutang FROM @sql_rhp_jumlah_hutang;
EXECUTE stmt_rhp_jumlah_hutang;
DEALLOCATE PREPARE stmt_rhp_jumlah_hutang;

SET @has_rhp_sisa_hutang := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'riwayat_hutang_pupuk' AND COLUMN_NAME = 'sisa_hutang'
);
SET @sql_rhp_sisa_hutang := IF(
    @has_rhp_sisa_hutang = 0,
    'ALTER TABLE `riwayat_hutang_pupuk` ADD COLUMN `sisa_hutang` VARCHAR(50) NOT NULL DEFAULT ''Rp 0'' AFTER `jumlah_hutang`',
    'SELECT 1'
);
PREPARE stmt_rhp_sisa_hutang FROM @sql_rhp_sisa_hutang;
EXECUTE stmt_rhp_sisa_hutang;
DEALLOCATE PREPARE stmt_rhp_sisa_hutang;

SET @has_rhp_tanggal := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'riwayat_hutang_pupuk' AND COLUMN_NAME = 'tanggal'
);
SET @sql_rhp_tanggal := IF(
    @has_rhp_tanggal = 0,
    'ALTER TABLE `riwayat_hutang_pupuk` ADD COLUMN `tanggal` DATE DEFAULT NULL AFTER `keterangan`',
    'SELECT 1'
);
PREPARE stmt_rhp_tanggal FROM @sql_rhp_tanggal;
EXECUTE stmt_rhp_tanggal;
DEALLOCATE PREPARE stmt_rhp_tanggal;

ALTER TABLE `riwayat_hutang_pupuk`
    MODIFY COLUMN `nama_pupuk` VARCHAR(255) NOT NULL;

-- 3. Buat Tabel pembayaran_hutang_pupuk (1 Baris = 1 Transaksi Pembayaran / Cicilan)
CREATE TABLE IF NOT EXISTS `pembayaran_hutang_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `nama_pupuk` VARCHAR(255) NOT NULL,
    `pembayaran_ke` INT(11) NOT NULL DEFAULT 1,
    `tanggal_bayar` DATE NOT NULL,
    `total_hutang` BIGINT NOT NULL DEFAULT 0,
    `sisa_sebelum` BIGINT NOT NULL DEFAULT 0,
    `jumlah_bayar` BIGINT NOT NULL DEFAULT 0,
    `sisa_hutang` BIGINT NOT NULL DEFAULT 0,
    `metode_bayar` ENUM('Tunai', 'Transfer', 'Potong Hasil Panen') NOT NULL DEFAULT 'Tunai',
    `status` ENUM('Belum Lunas', 'Lunas') NOT NULL DEFAULT 'Belum Lunas',
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_bayar_hutang` (`hutang_id`),
    KEY `idx_bayar_anggota` (`anggota_id`),
    KEY `idx_bayar_tanggal` (`tanggal_bayar`),
    CONSTRAINT `fk_bayar_hutang_parent` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_pupuk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `pembayaran_hutang_pupuk`
    MODIFY COLUMN `nama_pupuk` VARCHAR(255) NOT NULL;

-- 4. Sinkronkan riwayat_hutang_pupuk dari hutang_pupuk
INSERT INTO `riwayat_hutang_pupuk` (
    `hutang_id`, `anggota_id`, `nama_petani`, `pengelolaan_pupuk_id`,
    `pupuk_id`, `nama_pupuk`, `jumlah_karung`, `jumlah_berat`,
    `harga`, `jumlah_hutang`, `sisa_hutang`,
    `keterangan`, `tanggal`, `status`, `tanggal_lunas`, `created_at`
)
SELECT
    `id`, `anggota_id`, `nama_petani`, `pengelolaan_pupuk_id`,
    `pupuk_id`, `nama_pupuk`, `jumlah_karung`, `jumlah_berat`,
    `harga`, `jumlah_hutang`, `sisa_hutang`,
    `keterangan`, `tanggal`, `status`, `tanggal_lunas`, `created_at`
FROM `hutang_pupuk`
ON DUPLICATE KEY UPDATE
    `anggota_id`           = VALUES(`anggota_id`),
    `nama_petani`          = VALUES(`nama_petani`),
    `pengelolaan_pupuk_id` = VALUES(`pengelolaan_pupuk_id`),
    `pupuk_id`             = VALUES(`pupuk_id`),
    `nama_pupuk`           = VALUES(`nama_pupuk`),
    `jumlah_karung`        = VALUES(`jumlah_karung`),
    `jumlah_berat`         = VALUES(`jumlah_berat`),
    `harga`                = VALUES(`harga`),
    `jumlah_hutang`        = VALUES(`jumlah_hutang`),
    `sisa_hutang`          = VALUES(`sisa_hutang`),
    `keterangan`           = VALUES(`keterangan`),
    `tanggal`              = VALUES(`tanggal`),
    `status`               = VALUES(`status`),
    `tanggal_lunas`        = VALUES(`tanggal_lunas`);

-- 5. Sinkronkan nama_petani dan nama_pupuk pada pembayaran_hutang_pupuk dengan Data Master & Hutang Induk
UPDATE `pembayaran_hutang_pupuk` b
INNER JOIN `hutang_pupuk` h ON b.`hutang_id` = h.`id`
LEFT JOIN `anggota_petani` a ON h.`anggota_id` = a.`id`
LEFT JOIN `pengelolaan_pupuk` p ON h.`pengelolaan_pupuk_id` = p.`id`
LEFT JOIN `master_produk` m ON COALESCE(p.`pupuk_id`, h.`pupuk_id`) = m.`id` AND m.`jenis` = 'Pupuk'
SET
    b.`anggota_id`  = h.`anggota_id`,
    b.`nama_petani` = COALESCE(a.`nama_petani`, h.`nama_petani`),
    b.`nama_pupuk`  = COALESCE(m.`nama_produk`, p.`nama_pupuk`, h.`nama_pupuk`);
