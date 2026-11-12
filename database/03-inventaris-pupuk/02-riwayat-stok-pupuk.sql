-- =====================================================================
-- mudatani99 - Inventaris Pupuk -> Riwayat Stok Pupuk
-- File      : database/03-inventaris-pupuk/02-riwayat-stok-pupuk.sql
-- Tabel     : riwayat_stok_pupuk
-- Prasyarat : 03-inventaris-pupuk/01-stok-pupuk.sql
-- Catatan   : Mencatat seluruh aktivitas pengelolaan stok pupuk (tambah baru,
--             akumulasi, edit, hapus, penjualan, dan hutang piutang pupuk).
-- =====================================================================

-- 1. Buat Tabel riwayat_stok_pupuk
CREATE TABLE IF NOT EXISTS `riwayat_stok_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pengelolaan_pupuk_id` INT(11) DEFAULT NULL,
    `pupuk_id` INT(11) DEFAULT NULL,
    `nama_pupuk` VARCHAR(100) NOT NULL,
    `sumber` VARCHAR(50) NOT NULL DEFAULT 'Kelola Stok',
    `aksi` VARCHAR(60) NOT NULL DEFAULT 'Tambah Stok',
    `karung_sebelum` VARCHAR(50) NOT NULL DEFAULT '0 Karung',
    `berat_sebelum` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `perubahan_karung` VARCHAR(50) NOT NULL DEFAULT '0 Karung',
    `perubahan_berat` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `karung_sesudah` VARCHAR(50) NOT NULL DEFAULT '0 Karung',
    `berat_sesudah` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rsp_pengelolaan` (`pengelolaan_pupuk_id`),
    KEY `idx_rsp_pupuk` (`pupuk_id`),
    KEY `idx_rsp_sumber` (`sumber`),
    KEY `idx_rsp_created` (`created_at`),
    CONSTRAINT `fk_rsp_pengelolaan` FOREIGN KEY (`pengelolaan_pupuk_id`) REFERENCES `pengelolaan_pupuk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_rsp_master` FOREIGN KEY (`pupuk_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Backfill Stok Awal untuk Data pengelolaan_pupuk yang Sudah Ada (Jika Riwayat Kosong)
INSERT INTO `riwayat_stok_pupuk` (
    `pengelolaan_pupuk_id`, `pupuk_id`, `nama_pupuk`, `sumber`, `aksi`,
    `karung_sebelum`, `berat_sebelum`, `perubahan_karung`, `perubahan_berat`,
    `karung_sesudah`, `berat_sesudah`, `keterangan`, `dicatat_oleh`, `created_at`
)
SELECT
    p.`id`,
    p.`pupuk_id`,
    COALESCE(m.`nama_produk`, p.`nama_pupuk`),
    'Kelola Stok',
    'Stok Awal',
    '0 Karung',
    '0 Kg',
    CONCAT('+', p.`jumlah_karung`),
    CONCAT('+', p.`jumlah_berat`),
    p.`jumlah_karung`,
    p.`jumlah_berat`,
    COALESCE(NULLIF(p.`keterangan`, ''), '-'),
    'Sistem',
    p.`created_at`
FROM `pengelolaan_pupuk` p
LEFT JOIN `master_produk` m ON p.`pupuk_id` = m.`id` AND m.`jenis` = 'Pupuk'
WHERE NOT EXISTS (SELECT 1 FROM `riwayat_stok_pupuk` LIMIT 1);
