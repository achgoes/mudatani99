-- =====================================================================
-- mudatani99 - Inventaris Tanaman -> Riwayat Hutang / Pelunasan
-- File      : database/05-inventaris-tanaman/06-riwayat-hutang-pelunasan-tanaman.sql
-- Tabel     : riwayat_hutang_tanaman, pembayaran_hutang_tanaman
-- Prasyarat : 05-inventaris-tanaman/05-hutang-piutang-tanaman.sql
-- Catatan   :
--   - riwayat_hutang_tanaman: ringkasan sinkron 1 baris per hutang_tanaman.
--   - pembayaran_hutang_tanaman: jejak setiap pembayaran/cicilan (1 baris = 1 kali bayar).
-- =====================================================================

-- 1. Buat Tabel riwayat_hutang_tanaman
CREATE TABLE IF NOT EXISTS `riwayat_hutang_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
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
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_hutang_tanaman_id` (`hutang_id`),
    KEY `idx_riwayat_tanaman_anggota` (`anggota_id`),
    KEY `idx_riwayat_tanaman_id` (`tanaman_id`),
    CONSTRAINT `fk_riwayat_hutang_tanaman_parent` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_tanaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Buat Tabel pembayaran_hutang_tanaman (1 Baris = 1 Transaksi Pembayaran / Cicilan)
CREATE TABLE IF NOT EXISTS `pembayaran_hutang_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `nama_tanaman` VARCHAR(255) NOT NULL,
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
    KEY `idx_bayar_tanaman_hutang` (`hutang_id`),
    KEY `idx_bayar_tanaman_anggota` (`anggota_id`),
    KEY `idx_bayar_tanaman_tanggal` (`tanggal_bayar`),
    CONSTRAINT `fk_bayar_hutang_tanaman_parent` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_tanaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Sinkronkan riwayat_hutang_tanaman dari hutang_tanaman
INSERT INTO `riwayat_hutang_tanaman` (
    `hutang_id`, `anggota_id`, `nama_petani`, `pengelolaan_tanaman_id`,
    `tanaman_id`, `nama_tanaman`, `jumlah_kemasan`, `jumlah_berat`,
    `harga`, `jumlah_hutang`, `sisa_hutang`,
    `keterangan`, `tanggal`, `status`, `tanggal_lunas`, `created_at`
)
SELECT
    `id`, `anggota_id`, `nama_petani`, `pengelolaan_tanaman_id`,
    `tanaman_id`, `nama_tanaman`, `jumlah_kemasan`, `jumlah_berat`,
    `harga`, `jumlah_hutang`, `sisa_hutang`,
    `keterangan`, `tanggal`, `status`, `tanggal_lunas`, `created_at`
FROM `hutang_tanaman`
ON DUPLICATE KEY UPDATE
    `anggota_id`             = VALUES(`anggota_id`),
    `nama_petani`            = VALUES(`nama_petani`),
    `pengelolaan_tanaman_id` = VALUES(`pengelolaan_tanaman_id`),
    `tanaman_id`             = VALUES(`tanaman_id`),
    `nama_tanaman`           = VALUES(`nama_tanaman`),
    `jumlah_kemasan`         = VALUES(`jumlah_kemasan`),
    `jumlah_berat`           = VALUES(`jumlah_berat`),
    `harga`                  = VALUES(`harga`),
    `jumlah_hutang`          = VALUES(`jumlah_hutang`),
    `sisa_hutang`            = VALUES(`sisa_hutang`),
    `keterangan`             = VALUES(`keterangan`),
    `tanggal`                = VALUES(`tanggal`),
    `status`                 = VALUES(`status`),
    `tanggal_lunas`          = VALUES(`tanggal_lunas`);
