-- =====================================================================
-- mudatani99 - Inventaris Tanaman -> Riwayat Stok Tanaman
-- File      : database/05-inventaris-tanaman/02-riwayat-stok-tanaman.sql
-- Tabel     : riwayat_stok_tanaman
-- Prasyarat : 05-inventaris-tanaman/01-stok-tanaman.sql
-- Catatan   : Mencatat seluruh aktivitas pengelolaan stok tanaman (tambah baru,
--             akumulasi, edit, hapus, penjualan, dan hutang piutang tanaman).
-- =====================================================================

-- 1. Buat Tabel riwayat_stok_tanaman
CREATE TABLE IF NOT EXISTS `riwayat_stok_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pengelolaan_tanaman_id` INT(11) DEFAULT NULL,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL,
    `sumber` VARCHAR(50) NOT NULL DEFAULT 'Kelola Stok',
    `aksi` VARCHAR(60) NOT NULL DEFAULT 'Tambah Stok',
    `kemasan_sebelum` VARCHAR(50) NOT NULL DEFAULT '0 Pcs',
    `berat_sebelum` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `perubahan_kemasan` VARCHAR(50) NOT NULL DEFAULT '0 Pcs',
    `perubahan_berat` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `kemasan_sesudah` VARCHAR(50) NOT NULL DEFAULT '0 Pcs',
    `berat_sesudah` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rst_pengelolaan` (`pengelolaan_tanaman_id`),
    KEY `idx_rst_tanaman` (`tanaman_id`),
    KEY `idx_rst_sumber` (`sumber`),
    KEY `idx_rst_created` (`created_at`),
    CONSTRAINT `fk_rst_pengelolaan` FOREIGN KEY (`pengelolaan_tanaman_id`) REFERENCES `pengelolaan_tanaman` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_rst_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Backfill Stok Awal untuk Data pengelolaan_tanaman yang Sudah Ada (Jika Riwayat Kosong)
INSERT INTO `riwayat_stok_tanaman` (
    `pengelolaan_tanaman_id`, `tanaman_id`, `nama_tanaman`, `sumber`, `aksi`,
    `kemasan_sebelum`, `berat_sebelum`, `perubahan_kemasan`, `perubahan_berat`,
    `kemasan_sesudah`, `berat_sesudah`, `keterangan`, `dicatat_oleh`, `created_at`
)
SELECT
    t.`id`,
    t.`tanaman_id`,
    COALESCE(m.`nama_produk`, t.`nama_tanaman`),
    'Kelola Stok',
    'Stok Awal',
    '0 Pcs',
    '0 Kg',
    CONCAT('+', t.`jumlah_kemasan`),
    CONCAT('+', t.`jumlah_berat`),
    t.`jumlah_kemasan`,
    t.`jumlah_berat`,
    COALESCE(NULLIF(t.`keterangan`, ''), '-'),
    'Sistem',
    t.`created_at`
FROM `pengelolaan_tanaman` t
LEFT JOIN `master_produk` m ON t.`tanaman_id` = m.`id` AND m.`jenis` = 'Tanaman'
WHERE NOT EXISTS (SELECT 1 FROM `riwayat_stok_tanaman` LIMIT 1);
