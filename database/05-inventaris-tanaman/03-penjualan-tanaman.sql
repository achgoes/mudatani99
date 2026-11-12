-- =====================================================================
-- mudatani99 - Inventaris Tanaman -> Penjualan
-- File      : database/05-inventaris-tanaman/03-penjualan-tanaman.sql
-- Tabel     : penjualan_tanaman, penjualan_tanaman_items
-- Prasyarat : 02-data-anggota/01-daftar-anggota-petani.sql,
--             05-inventaris-tanaman/01-stok-tanaman.sql
-- Catatan   : Transaksi penjualan langsung tanaman/bibit (Tunai / Transfer / QRIS),
--             berdiri sendiri dan otomatis mengurangi Stok Tanaman.
-- =====================================================================

-- 1. Buat Tabel penjualan_tanaman
CREATE TABLE IF NOT EXISTS `penjualan_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `anggota_id` INT(11) DEFAULT NULL,
    `nama_pembeli` VARCHAR(100) NOT NULL,
    `alamat_pembeli` VARCHAR(255) DEFAULT '-',
    `nama_tanaman` VARCHAR(255) NOT NULL,
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
    UNIQUE KEY `uniq_kode_penjualan_tanaman` (`kode_transaksi`),
    KEY `idx_penjualan_tanaman_anggota` (`anggota_id`),
    KEY `idx_penjualan_tanaman_tanggal` (`tanggal_jual`),
    CONSTRAINT `fk_penjualan_tanaman_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Buat Tabel penjualan_tanaman_items (Detail Multi-Item Tanaman per Nota)
CREATE TABLE IF NOT EXISTS `penjualan_tanaman_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `penjualan_id` INT(11) NOT NULL,
    `pengelolaan_tanaman_id` INT(11) DEFAULT NULL,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga_satuan` BIGINT NOT NULL DEFAULT 0,
    `subtotal` BIGINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pti_penjualan` (`penjualan_id`),
    KEY `idx_pti_pengelolaan` (`pengelolaan_tanaman_id`),
    KEY `idx_pti_tanaman` (`tanaman_id`),
    CONSTRAINT `fk_pti_penjualan` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan_tanaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pti_pengelolaan` FOREIGN KEY (`pengelolaan_tanaman_id`) REFERENCES `pengelolaan_tanaman` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pti_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
