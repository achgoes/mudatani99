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
