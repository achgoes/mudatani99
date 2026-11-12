-- =====================================================================
-- mudatani99 - Inventaris Tanaman -> Hutang Piutang
-- File      : database/05-inventaris-tanaman/05-hutang-piutang-tanaman.sql
-- Tabel     : hutang_tanaman, hutang_tanaman_items
-- Prasyarat : 02-data-anggota/01-daftar-anggota-petani.sql,
--             05-inventaris-tanaman/01-stok-tanaman.sql
-- Catatan   : Mencatat transaksi hutang piutang tanaman/bibit anggota petani
--             (single / multi-item) yang tersinkronisasi dengan Stok Tanaman.
-- =====================================================================

-- 1. Buat Tabel hutang_tanaman
CREATE TABLE IF NOT EXISTS `hutang_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `pengelolaan_tanaman_id` INT(11) DEFAULT NULL,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(255) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
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
    KEY `idx_hutang_tanaman_anggota` (`anggota_id`),
    KEY `idx_hutang_tanaman_pengelolaan` (`pengelolaan_tanaman_id`),
    KEY `idx_hutang_tanaman_master` (`tanaman_id`),
    CONSTRAINT `fk_hutang_tanaman_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_tanaman_pengelolaan` FOREIGN KEY (`pengelolaan_tanaman_id`) REFERENCES `pengelolaan_tanaman` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_tanaman_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Buat Tabel hutang_tanaman_items (Detail Multi-Item Tanaman per Transaksi Hutang)
CREATE TABLE IF NOT EXISTS `hutang_tanaman_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `pengelolaan_tanaman_id` INT(11) DEFAULT NULL,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `subtotal` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_hti_hutang` (`hutang_id`),
    KEY `idx_hti_pengelolaan` (`pengelolaan_tanaman_id`),
    KEY `idx_hti_tanaman` (`tanaman_id`),
    CONSTRAINT `fk_hti_hutang` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_tanaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hti_pengelolaan` FOREIGN KEY (`pengelolaan_tanaman_id`) REFERENCES `pengelolaan_tanaman` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hti_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
