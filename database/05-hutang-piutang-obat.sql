-- =====================================================================
-- mudatani99 - Query Submenu 5: Hutang Piutang Obat (05-hutang-piutang-obat.sql)
-- Target Database: mudatani99
-- Menu: Inventaris Obat -> Hutang Piutang (hutang_obat, hutang_obat_items)
-- Prasyarat: Jalankan 01-stok-obat.sql dan 03-penjualan-obat.sql (anggota_petani) terlebih dahulu.
-- Catatan:
--   - Mencatat transaksi hutang piutang obat anggota petani (single / multi-item).
--   - Tersinkronisasi otomatis dengan stok pada pengelolaan_obat.
-- =====================================================================

-- 1. Buat Tabel hutang_obat
CREATE TABLE IF NOT EXISTS `hutang_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `pengelolaan_obat_id` INT(11) DEFAULT NULL,
    `obat_id` INT(11) DEFAULT NULL,
    `nama_obat` VARCHAR(255) NOT NULL,
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
    KEY `idx_hutang_obat_anggota` (`anggota_id`),
    KEY `idx_hutang_obat_pengelolaan` (`pengelolaan_obat_id`),
    KEY `idx_hutang_obat_master` (`obat_id`),
    CONSTRAINT `fk_hutang_obat_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_obat_pengelolaan` FOREIGN KEY (`pengelolaan_obat_id`) REFERENCES `pengelolaan_obat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_obat_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Buat Tabel hutang_obat_items (Detail Multi-Item Obat per Transaksi Hutang)
CREATE TABLE IF NOT EXISTS `hutang_obat_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `pengelolaan_obat_id` INT(11) DEFAULT NULL,
    `obat_id` INT(11) DEFAULT NULL,
    `nama_obat` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `subtotal` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_hoi_hutang` (`hutang_id`),
    KEY `idx_hoi_pengelolaan` (`pengelolaan_obat_id`),
    KEY `idx_hoi_obat` (`obat_id`),
    CONSTRAINT `fk_hoi_hutang` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_obat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hoi_pengelolaan` FOREIGN KEY (`pengelolaan_obat_id`) REFERENCES `pengelolaan_obat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hoi_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
