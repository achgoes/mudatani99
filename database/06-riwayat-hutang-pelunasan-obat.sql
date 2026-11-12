-- =====================================================================
-- mudatani99 - Query Submenu 6: Riwayat Hutang / Pelunasan Obat (06-riwayat-hutang-pelunasan-obat.sql)
-- Target Database: mudatani99
-- Menu: Inventaris Obat -> Riwayat Hutang / Pelunasan (riwayat_hutang_obat, pembayaran_hutang_obat)
-- Prasyarat: Jalankan 05-hutang-piutang-obat.sql terlebih dahulu.
-- Catatan:
--   - riwayat_hutang_obat: ringkasan sinkron 1 baris per hutang_obat.
--   - pembayaran_hutang_obat: jejak setiap pembayaran/cicilan (1 baris = 1 kali bayar).
-- =====================================================================

-- 1. Buat Tabel riwayat_hutang_obat
CREATE TABLE IF NOT EXISTS `riwayat_hutang_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
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
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_hutang_obat_id` (`hutang_id`),
    KEY `idx_riwayat_obat_anggota` (`anggota_id`),
    KEY `idx_riwayat_obat_id` (`obat_id`),
    CONSTRAINT `fk_riwayat_hutang_obat_parent` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_obat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Buat Tabel pembayaran_hutang_obat
CREATE TABLE IF NOT EXISTS `pembayaran_hutang_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `nama_obat` VARCHAR(255) NOT NULL,
    `pembayaran_ke` INT(11) NOT NULL DEFAULT 1,
    `tanggal_bayar` DATE NOT NULL,
    `total_hutang` BIGINT NOT NULL DEFAULT 0,
    `sisa_sebelum` BIGINT NOT NULL DEFAULT 0,
    `jumlah_bayar` BIGINT NOT NULL DEFAULT 0,
    `sisa_hutang` BIGINT NOT NULL DEFAULT 0,
    `metode_bayar` ENUM('Tunai', 'Transfer', 'Potong Hasil Panen') NOT NULL DEFAULT 'Tunai',
    `status` ENUM('Belum Lunas', 'Lunas') NOT NULL DEFAULT 'Belum Lunas',
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_bayar_obat_hutang` (`hutang_id`),
    KEY `idx_bayar_obat_anggota` (`anggota_id`),
    KEY `idx_bayar_obat_tanggal` (`tanggal_bayar`),
    CONSTRAINT `fk_bayar_hutang_obat_parent` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_obat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Sinkronkan Data riwayat_hutang_obat dari hutang_obat (Jika Sudah Ada Data Hutang Obat)
INSERT INTO `riwayat_hutang_obat` (
    `hutang_id`, `anggota_id`, `nama_petani`, `pengelolaan_obat_id`,
    `obat_id`, `nama_obat`, `jumlah_kemasan`, `jumlah_berat`,
    `harga`, `jumlah_hutang`, `sisa_hutang`,
    `keterangan`, `tanggal`, `status`, `tanggal_lunas`, `created_at`
)
SELECT
    `id`, `anggota_id`, `nama_petani`, `pengelolaan_obat_id`,
    `obat_id`, `nama_obat`, `jumlah_kemasan`, `jumlah_berat`,
    `harga`, `jumlah_hutang`, `sisa_hutang`,
    `keterangan`, `tanggal`, `status`, `tanggal_lunas`, `created_at`
FROM `hutang_obat`
ON DUPLICATE KEY UPDATE
    `anggota_id`          = VALUES(`anggota_id`),
    `nama_petani`         = VALUES(`nama_petani`),
    `pengelolaan_obat_id` = VALUES(`pengelolaan_obat_id`),
    `obat_id`             = VALUES(`obat_id`),
    `nama_obat`           = VALUES(`nama_obat`),
    `jumlah_kemasan`      = VALUES(`jumlah_kemasan`),
    `jumlah_berat`        = VALUES(`jumlah_berat`),
    `harga`               = VALUES(`harga`),
    `jumlah_hutang`       = VALUES(`jumlah_hutang`),
    `sisa_hutang`         = VALUES(`sisa_hutang`),
    `keterangan`          = VALUES(`keterangan`),
    `tanggal`             = VALUES(`tanggal`),
    `status`              = VALUES(`status`),
    `tanggal_lunas`       = VALUES(`tanggal_lunas`);
