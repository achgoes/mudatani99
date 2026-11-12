-- =====================================================================
-- mudatani99 - Inventaris Pupuk -> Penjualan
-- File      : database/03-inventaris-pupuk/03-penjualan-pupuk.sql
-- Tabel     : penjualan_pupuk, penjualan_pupuk_items
-- Prasyarat : 02-data-anggota/01-daftar-anggota-petani.sql,
--             03-inventaris-pupuk/01-stok-pupuk.sql
-- Catatan   : Transaksi penjualan langsung pupuk (Tunai / Transfer / QRIS),
--             berdiri sendiri dan otomatis mengurangi Stok Pupuk.
-- =====================================================================

-- 1. Buat Tabel penjualan_pupuk
CREATE TABLE IF NOT EXISTS `penjualan_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `anggota_id` INT(11) DEFAULT NULL,
    `nama_pembeli` VARCHAR(100) NOT NULL,
    `alamat_pembeli` VARCHAR(255) DEFAULT '-',
    `nama_pupuk` VARCHAR(255) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `total_harga` BIGINT NOT NULL DEFAULT 0,
    `metode_bayar` ENUM('Tunai', 'Transfer', 'QRIS') NOT NULL DEFAULT 'Tunai',
    `tanggal_jual` DATE NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_kode_penjualan` (`kode_transaksi`),
    KEY `idx_penjualan_anggota` (`anggota_id`),
    KEY `idx_penjualan_tanggal` (`tanggal_jual`),
    CONSTRAINT `fk_penjualan_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrasi Otomatis Jika Tabel Lama Belum Memiliki Kolom alamat_pembeli
SET @has_alamat_pembeli := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'penjualan_pupuk'
      AND COLUMN_NAME = 'alamat_pembeli'
);
SET @sql_add_alamat_pembeli := IF(
    @has_alamat_pembeli = 0,
    'ALTER TABLE `penjualan_pupuk` ADD COLUMN `alamat_pembeli` VARCHAR(255) DEFAULT ''-'' AFTER `nama_pembeli`',
    'SELECT 1'
);
PREPARE stmt_add_alamat_pembeli FROM @sql_add_alamat_pembeli;
EXECUTE stmt_add_alamat_pembeli;
DEALLOCATE PREPARE stmt_add_alamat_pembeli;

-- 3. Buat Tabel penjualan_pupuk_items (Detail Multi-Item Pupuk per Nota)
CREATE TABLE IF NOT EXISTS `penjualan_pupuk_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `penjualan_id` INT(11) NOT NULL,
    `pengelolaan_pupuk_id` INT(11) DEFAULT NULL,
    `pupuk_id` INT(11) DEFAULT NULL,
    `nama_pupuk` VARCHAR(100) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga_satuan` BIGINT NOT NULL DEFAULT 0,
    `subtotal` BIGINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ppi_penjualan` (`penjualan_id`),
    KEY `idx_ppi_pengelolaan` (`pengelolaan_pupuk_id`),
    KEY `idx_ppi_pupuk` (`pupuk_id`),
    CONSTRAINT `fk_ppi_penjualan` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan_pupuk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ppi_pengelolaan` FOREIGN KEY (`pengelolaan_pupuk_id`) REFERENCES `pengelolaan_pupuk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_ppi_master` FOREIGN KEY (`pupuk_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
