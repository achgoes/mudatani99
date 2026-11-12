-- =====================================================================
-- mudatani99 - Inventaris Obat -> Penjualan
-- File      : database/04-inventaris-obat/03-penjualan-obat.sql
-- Tabel     : penjualan_obat, penjualan_obat_items
-- Prasyarat : 02-data-anggota/01-daftar-anggota-petani.sql,
--             04-inventaris-obat/01-stok-obat.sql
-- Catatan   : Transaksi penjualan langsung obat (Tunai / Transfer / QRIS),
--             berdiri sendiri dan otomatis mengurangi Stok Obat.
-- =====================================================================

-- 1. Buat Tabel penjualan_obat
CREATE TABLE IF NOT EXISTS `penjualan_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `anggota_id` INT(11) DEFAULT NULL,
    `nama_pembeli` VARCHAR(100) NOT NULL,
    `alamat_pembeli` VARCHAR(255) DEFAULT '-',
    `nama_obat` VARCHAR(255) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `total_harga` BIGINT NOT NULL DEFAULT 0,
    `metode_bayar` ENUM('Tunai', 'Transfer', 'QRIS') NOT NULL DEFAULT 'Tunai',
    `tanggal_jual` DATE NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_kode_penjualan_obat` (`kode_transaksi`),
    KEY `idx_penjualan_obat_anggota` (`anggota_id`),
    KEY `idx_penjualan_obat_tanggal` (`tanggal_jual`),
    CONSTRAINT `fk_penjualan_obat_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Buat Tabel penjualan_obat_items (Detail Multi-Item Obat per Nota)
CREATE TABLE IF NOT EXISTS `penjualan_obat_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `penjualan_id` INT(11) NOT NULL,
    `pengelolaan_obat_id` INT(11) DEFAULT NULL,
    `obat_id` INT(11) DEFAULT NULL,
    `nama_obat` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga_satuan` BIGINT NOT NULL DEFAULT 0,
    `subtotal` BIGINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_poi_penjualan` (`penjualan_id`),
    KEY `idx_poi_pengelolaan` (`pengelolaan_obat_id`),
    KEY `idx_poi_obat` (`obat_id`),
    CONSTRAINT `fk_poi_penjualan` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan_obat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_poi_pengelolaan` FOREIGN KEY (`pengelolaan_obat_id`) REFERENCES `pengelolaan_obat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_poi_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
