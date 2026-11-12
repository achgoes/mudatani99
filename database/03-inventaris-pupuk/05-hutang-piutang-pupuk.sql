-- =====================================================================
-- mudatani99 - Inventaris Pupuk -> Hutang Piutang
-- File      : database/03-inventaris-pupuk/05-hutang-piutang-pupuk.sql
-- Tabel     : hutang_pupuk, hutang_pupuk_items
-- Prasyarat : 02-data-anggota/01-daftar-anggota-petani.sql,
--             03-inventaris-pupuk/01-stok-pupuk.sql
-- Catatan   : Mencatat transaksi hutang piutang pupuk anggota petani
--             (single / multi-item) yang tersinkronisasi dengan Stok Pupuk.
-- =====================================================================

-- 1. Buat Tabel hutang_pupuk
CREATE TABLE IF NOT EXISTS `hutang_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
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
    PRIMARY KEY (`id`),
    KEY `idx_hutang_anggota` (`anggota_id`),
    KEY `idx_hutang_pengelolaan_pupuk` (`pengelolaan_pupuk_id`),
    KEY `idx_hutang_master_pupuk` (`pupuk_id`),
    CONSTRAINT `fk_hutang_pupuk_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_pupuk_pengelolaan` FOREIGN KEY (`pengelolaan_pupuk_id`) REFERENCES `pengelolaan_pupuk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_pupuk_master` FOREIGN KEY (`pupuk_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrasi Otomatis Struktur Kolom hutang_pupuk Lama (Jika Ada)
SET @has_hp_old_jml_harga := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hutang_pupuk' AND COLUMN_NAME = 'jumlah_harga'
);
SET @has_hp_harga := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hutang_pupuk' AND COLUMN_NAME = 'harga'
);
SET @sql_hp_harga := IF(
    @has_hp_harga = 0 AND @has_hp_old_jml_harga > 0,
    'ALTER TABLE `hutang_pupuk` CHANGE COLUMN `jumlah_harga` `harga` VARCHAR(50) NOT NULL DEFAULT ''Rp 0''',
    IF(
        @has_hp_harga = 0,
        'ALTER TABLE `hutang_pupuk` ADD COLUMN `harga` VARCHAR(50) NOT NULL DEFAULT ''Rp 0'' AFTER `jumlah_berat`',
        'SELECT 1'
    )
);
PREPARE stmt_hp_harga FROM @sql_hp_harga;
EXECUTE stmt_hp_harga;
DEALLOCATE PREPARE stmt_hp_harga;

SET @has_hp_jumlah_hutang := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hutang_pupuk' AND COLUMN_NAME = 'jumlah_hutang'
);
SET @sql_hp_jumlah_hutang := IF(
    @has_hp_jumlah_hutang = 0,
    'ALTER TABLE `hutang_pupuk` ADD COLUMN `jumlah_hutang` VARCHAR(50) NOT NULL DEFAULT ''Rp 0'' AFTER `harga`',
    'SELECT 1'
);
PREPARE stmt_hp_jumlah_hutang FROM @sql_hp_jumlah_hutang;
EXECUTE stmt_hp_jumlah_hutang;
DEALLOCATE PREPARE stmt_hp_jumlah_hutang;

SET @has_hp_sisa_hutang := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hutang_pupuk' AND COLUMN_NAME = 'sisa_hutang'
);
SET @sql_hp_sisa_hutang := IF(
    @has_hp_sisa_hutang = 0,
    'ALTER TABLE `hutang_pupuk` ADD COLUMN `sisa_hutang` VARCHAR(50) NOT NULL DEFAULT ''Rp 0'' AFTER `jumlah_hutang`',
    'SELECT 1'
);
PREPARE stmt_hp_sisa_hutang FROM @sql_hp_sisa_hutang;
EXECUTE stmt_hp_sisa_hutang;
DEALLOCATE PREPARE stmt_hp_sisa_hutang;

SET @has_hp_tanggal := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hutang_pupuk' AND COLUMN_NAME = 'tanggal'
);
SET @sql_hp_tanggal := IF(
    @has_hp_tanggal = 0,
    'ALTER TABLE `hutang_pupuk` ADD COLUMN `tanggal` DATE DEFAULT NULL AFTER `keterangan`',
    'SELECT 1'
);
PREPARE stmt_hp_tanggal FROM @sql_hp_tanggal;
EXECUTE stmt_hp_tanggal;
DEALLOCATE PREPARE stmt_hp_tanggal;

ALTER TABLE `hutang_pupuk`
    MODIFY COLUMN `nama_pupuk` VARCHAR(255) NOT NULL;

-- 3. Bersihkan Jejak Catatan Pembayaran Lama dari Kolom keterangan & Normalisasi sisa_hutang
UPDATE `hutang_pupuk`
SET `keterangan` = TRIM(TRAILING '|' FROM TRIM(SUBSTRING_INDEX(`keterangan`, ' | Bayar:', 1)))
WHERE `keterangan` LIKE '% | Bayar:%';

UPDATE `hutang_pupuk`
SET `keterangan` = '-'
WHERE `keterangan` REGEXP '^Bayar:[[:space:]]*Rp[[:space:]]*[0-9.]+';

UPDATE `hutang_pupuk`
SET `keterangan` = '-'
WHERE `keterangan` IS NULL OR TRIM(`keterangan`) = '';

UPDATE `hutang_pupuk`
SET `sisa_hutang` = `jumlah_hutang`
WHERE `status` = 'Belum Lunas'
  AND (`sisa_hutang` IS NULL OR TRIM(`sisa_hutang`) = '' OR `sisa_hutang` = 'Rp 0')
  AND `jumlah_hutang` IS NOT NULL
  AND `jumlah_hutang` <> 'Rp 0';

-- 4. Buat Tabel hutang_pupuk_items (Detail Multi-Item Pupuk per Transaksi Hutang)
CREATE TABLE IF NOT EXISTS `hutang_pupuk_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `pengelolaan_pupuk_id` INT(11) DEFAULT NULL,
    `pupuk_id` INT(11) DEFAULT NULL,
    `nama_pupuk` VARCHAR(100) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `subtotal` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_hpi_hutang` (`hutang_id`),
    KEY `idx_hpi_pengelolaan` (`pengelolaan_pupuk_id`),
    KEY `idx_hpi_pupuk` (`pupuk_id`),
    CONSTRAINT `fk_hpi_hutang` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_pupuk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hpi_pengelolaan` FOREIGN KEY (`pengelolaan_pupuk_id`) REFERENCES `pengelolaan_pupuk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hpi_master` FOREIGN KEY (`pupuk_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Backfill Item Hutang Lama ke hutang_pupuk_items Jika Belum Tercatat
INSERT INTO `hutang_pupuk_items` (
    `hutang_id`, `pengelolaan_pupuk_id`, `pupuk_id`,
    `nama_pupuk`, `jumlah_karung`, `jumlah_berat`,
    `harga`, `subtotal`, `created_at`
)
SELECT
    h.`id`, h.`pengelolaan_pupuk_id`, h.`pupuk_id`,
    COALESCE(m.`nama_produk`, p.`nama_pupuk`, h.`nama_pupuk`),
    h.`jumlah_karung`, h.`jumlah_berat`,
    COALESCE(h.`harga`, 'Rp 0'),
    COALESCE(h.`jumlah_hutang`, 'Rp 0'),
    h.`created_at`
FROM `hutang_pupuk` h
LEFT JOIN `hutang_pupuk_items` hpi ON hpi.`hutang_id` = h.`id`
LEFT JOIN `pengelolaan_pupuk` p ON h.`pengelolaan_pupuk_id` = p.`id`
LEFT JOIN `master_produk` m ON COALESCE(p.`pupuk_id`, h.`pupuk_id`) = m.`id` AND m.`jenis` = 'Pupuk'
WHERE hpi.`id` IS NULL;
