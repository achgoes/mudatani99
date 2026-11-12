-- =====================================================================
-- mudatani99 - Inventaris Tanaman -> Riwayat Penjualan
-- File      : database/05-inventaris-tanaman/04-riwayat-penjualan-tanaman.sql
-- Tabel     : riwayat_penjualan_tanaman
-- Prasyarat : 05-inventaris-tanaman/03-penjualan-tanaman.sql
-- Catatan   : Log aktivitas penjualan tanaman (Terjual, Diperbarui, Dibatalkan).
-- =====================================================================

-- 1. Buat Tabel riwayat_penjualan_tanaman
CREATE TABLE IF NOT EXISTS `riwayat_penjualan_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `penjualan_id` INT(11) DEFAULT NULL,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `aksi` ENUM('Terjual', 'Diperbarui', 'Dibatalkan') NOT NULL DEFAULT 'Terjual',
    `nama_pembeli` VARCHAR(100) NOT NULL,
    `rincian_item` TEXT NOT NULL,
    `total_kemasan` VARCHAR(50) NOT NULL,
    `total_berat` VARCHAR(50) NOT NULL,
    `total_harga` BIGINT NOT NULL DEFAULT 0,
    `metode_bayar` VARCHAR(30) NOT NULL DEFAULT 'Tunai',
    `tanggal_jual` DATE NOT NULL,
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rpt_penjualan` (`penjualan_id`),
    KEY `idx_rpt_tanggal` (`tanggal_jual`),
    KEY `idx_rpt_aksi` (`aksi`),
    CONSTRAINT `fk_rpt_penjualan` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan_tanaman` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
