-- =====================================================================
-- mudatani99 - Inventaris Obat -> Riwayat Stok Obat
-- File      : database/04-inventaris-obat/02-riwayat-stok-obat.sql
-- Tabel     : riwayat_stok_obat
-- Prasyarat : 04-inventaris-obat/01-stok-obat.sql
-- Catatan   : Mencatat seluruh aktivitas pengelolaan stok obat (tambah baru,
--             akumulasi, edit, hapus, penjualan, dan hutang piutang obat).
-- =====================================================================

-- 1. Buat Tabel riwayat_stok_obat
CREATE TABLE IF NOT EXISTS `riwayat_stok_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pengelolaan_obat_id` INT(11) DEFAULT NULL,
    `obat_id` INT(11) DEFAULT NULL,
    `nama_obat` VARCHAR(100) NOT NULL,
    `sumber` VARCHAR(50) NOT NULL DEFAULT 'Kelola Stok',
    `aksi` VARCHAR(60) NOT NULL DEFAULT 'Tambah Stok',
    `kemasan_sebelum` VARCHAR(50) NOT NULL DEFAULT '0 Kemasan',
    `berat_sebelum` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `perubahan_kemasan` VARCHAR(50) NOT NULL DEFAULT '0 Kemasan',
    `perubahan_berat` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `kemasan_sesudah` VARCHAR(50) NOT NULL DEFAULT '0 Kemasan',
    `berat_sesudah` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rso_pengelolaan` (`pengelolaan_obat_id`),
    KEY `idx_rso_obat` (`obat_id`),
    KEY `idx_rso_sumber` (`sumber`),
    KEY `idx_rso_created` (`created_at`),
    CONSTRAINT `fk_rso_pengelolaan` FOREIGN KEY (`pengelolaan_obat_id`) REFERENCES `pengelolaan_obat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_rso_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Backfill Stok Awal untuk Data pengelolaan_obat yang Sudah Ada (Jika Riwayat Kosong)
INSERT INTO `riwayat_stok_obat` (
    `pengelolaan_obat_id`, `obat_id`, `nama_obat`, `sumber`, `aksi`,
    `kemasan_sebelum`, `berat_sebelum`, `perubahan_kemasan`, `perubahan_berat`,
    `kemasan_sesudah`, `berat_sesudah`, `keterangan`, `dicatat_oleh`, `created_at`
)
SELECT
    o.`id`,
    o.`obat_id`,
    COALESCE(m.`nama_produk`, o.`nama_obat`),
    'Kelola Stok',
    'Stok Awal',
    '0 Kemasan',
    '0 Kg',
    CONCAT('+', o.`jumlah_kemasan`),
    CONCAT('+', o.`jumlah_berat`),
    o.`jumlah_kemasan`,
    o.`jumlah_berat`,
    COALESCE(NULLIF(o.`keterangan`, ''), '-'),
    'Sistem',
    o.`created_at`
FROM `pengelolaan_obat` o
LEFT JOIN `master_produk` m ON o.`obat_id` = m.`id` AND m.`jenis` = 'Obat'
WHERE NOT EXISTS (SELECT 1 FROM `riwayat_stok_obat` LIMIT 1);
