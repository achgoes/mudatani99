-- =====================================================================
-- mudatani99 - Pembaruan & Sinkronisasi Query Riwayat Pembayaran Pupuk (query3.sql)
-- Target Database: mudatani99
-- Catatan:
--   - File ini berisi query lanjutan setelah query.sql dan query2.sql
--     (query sebelumnya pada query.sql & query2.sql tetap dipertahankan).
--   - Menjamin tabel pembayaran_hutang_pupuk tersedia (1 baris = 1 transaksi pembayaran),
--     membersihkan jejak " | Bayar: ..." dari kolom keterangan hutang_pupuk,
--     serta menyinkronkan data antar hutang_pupuk, riwayat_hutang_pupuk, dan pembayaran_hutang_pupuk.
-- =====================================================================

-- 1. Pastikan tabel pembayaran_hutang_pupuk tersedia
--    (Model riwayat: setiap transaksi pembayaran tercatat sebagai baris tersendiri, tidak digabung per anggota)
    CREATE TABLE IF NOT EXISTS `pembayaran_hutang_pupuk` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `hutang_id` INT(11) NOT NULL,
        `anggota_id` INT(11) NOT NULL,
        `nama_petani` VARCHAR(100) NOT NULL,
        `nama_pupuk` VARCHAR(100) NOT NULL,
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
        KEY `idx_bayar_hutang` (`hutang_id`),
        KEY `idx_bayar_anggota` (`anggota_id`),
        KEY `idx_bayar_tanggal` (`tanggal_bayar`),
        CONSTRAINT `fk_bayar_hutang_parent` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_pupuk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bersihkan catatan sistem " | Bayar: ..." dari kolom keterangan hutang_pupuk
--    agar kolom Keterangan di tabel Hutang Piutang hanya menyimpan keterangan awal pinjaman
UPDATE `hutang_pupuk`
SET `keterangan` = TRIM(TRAILING '|' FROM TRIM(SUBSTRING_INDEX(`keterangan`, ' | Bayar:', 1)))
WHERE `keterangan` LIKE '% | Bayar:%';

UPDATE `hutang_pupuk`
SET `keterangan` = '-'
WHERE `keterangan` REGEXP '^Bayar:[[:space:]]*Rp[[:space:]]*[0-9.]+';

UPDATE `hutang_pupuk`
SET `keterangan` = '-'
WHERE `keterangan` IS NULL OR TRIM(`keterangan`) = '';

-- 3. Normalisasi sisa_hutang pada transaksi Belum Lunas yang masih bernilai default 'Rp 0'
UPDATE `hutang_pupuk`
SET `sisa_hutang` = `jumlah_hutang`
WHERE `status` = 'Belum Lunas'
  AND (`sisa_hutang` IS NULL OR TRIM(`sisa_hutang`) = '' OR `sisa_hutang` = 'Rp 0')
  AND `jumlah_hutang` IS NOT NULL
  AND `jumlah_hutang` <> 'Rp 0';

-- 4. Sinkronkan ulang tabel riwayat_hutang_pupuk dengan hutang_pupuk terbaru
INSERT INTO `riwayat_hutang_pupuk` (
    `hutang_id`, `anggota_id`, `nama_petani`, `pengelolaan_pupuk_id`,
    `pupuk_id`, `nama_pupuk`, `jumlah_karung`, `jumlah_berat`,
    `harga`, `jumlah_hutang`, `sisa_hutang`,
    `keterangan`, `tanggal`, `status`, `tanggal_lunas`, `created_at`
)
SELECT
    `id`, `anggota_id`, `nama_petani`, `pengelolaan_pupuk_id`,
    `pupuk_id`, `nama_pupuk`, `jumlah_karung`, `jumlah_berat`,
    `harga`, `jumlah_hutang`, `sisa_hutang`,
    `keterangan`, `tanggal`, `status`, `tanggal_lunas`, `created_at`
FROM `hutang_pupuk`
ON DUPLICATE KEY UPDATE
    `anggota_id`           = VALUES(`anggota_id`),
    `nama_petani`          = VALUES(`nama_petani`),
    `pengelolaan_pupuk_id` = VALUES(`pengelolaan_pupuk_id`),
    `pupuk_id`             = VALUES(`pupuk_id`),
    `nama_pupuk`           = VALUES(`nama_pupuk`),
    `jumlah_karung`        = VALUES(`jumlah_karung`),
    `jumlah_berat`         = VALUES(`jumlah_berat`),
    `harga`                = VALUES(`harga`),
    `jumlah_hutang`        = VALUES(`jumlah_hutang`),
    `sisa_hutang`          = VALUES(`sisa_hutang`),
    `keterangan`           = VALUES(`keterangan`),
    `tanggal`              = VALUES(`tanggal`),
    `status`               = VALUES(`status`),
    `tanggal_lunas`        = VALUES(`tanggal_lunas`);

-- 5. Sinkronkan nama_petani dan nama_pupuk pada pembayaran_hutang_pupuk dengan data master & hutang induk
UPDATE `pembayaran_hutang_pupuk` b
INNER JOIN `hutang_pupuk` h ON b.`hutang_id` = h.`id`
LEFT JOIN `anggota_petani` a ON h.`anggota_id` = a.`id`
LEFT JOIN `pengelolaan_pupuk` p ON h.`pengelolaan_pupuk_id` = p.`id`
LEFT JOIN `master_produk` m ON COALESCE(p.`pupuk_id`, h.`pupuk_id`) = m.`id` AND m.`jenis` = 'Pupuk'
SET
    b.`anggota_id`  = h.`anggota_id`,
    b.`nama_petani` = COALESCE(a.`nama_petani`, h.`nama_petani`),
    b.`nama_pupuk`  = COALESCE(m.`nama_produk`, p.`nama_pupuk`, h.`nama_pupuk`);

-- 6. Pastikan kolom nama_pupuk cukup panjang menampung gabungan beberapa item pupuk
ALTER TABLE `hutang_pupuk`
    MODIFY COLUMN `nama_pupuk` VARCHAR(255) NOT NULL;

ALTER TABLE `riwayat_hutang_pupuk`
    MODIFY COLUMN `nama_pupuk` VARCHAR(255) NOT NULL;

ALTER TABLE `pembayaran_hutang_pupuk`
    MODIFY COLUMN `nama_pupuk` VARCHAR(255) NOT NULL;

-- 7. Tabel detail item pupuk per transaksi hutang (1 transaksi dapat memiliki beberapa item pupuk)
CREATE TABLE IF NOT EXISTS `hutang_pupuk_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `pengelolaan_pupuk_id` INT(11) DEFAULT NULL,
    `pupuk_id` INT(11) DEFAULT NULL,
    `nama_pupuk` VARCHAR(100) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `subtotal` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_hpi_hutang` (`hutang_id`),
    KEY `idx_hpi_pengelolaan` (`pengelolaan_pupuk_id`),
    KEY `idx_hpi_pupuk` (`pupuk_id`),
    CONSTRAINT `fk_hpi_hutang` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_pupuk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hpi_pengelolaan` FOREIGN KEY (`pengelolaan_pupuk_id`) REFERENCES `pengelolaan_pupuk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hpi_master` FOREIGN KEY (`pupuk_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Backfill item hutang lama ke tabel hutang_pupuk_items jika belum tercatat
INSERT INTO `hutang_pupuk_items` (
    `hutang_id`, `pengelolaan_pupuk_id`, `pupuk_id`,
    `nama_pupuk`, `jumlah_karung`, `jumlah_berat`,
    `harga`, `subtotal`, `created_at`
)
SELECT
    h.`id`, h.`pengelolaan_pupuk_id`, h.`pupuk_id`,
    COALESCE(m.`nama_produk`, p.`nama_pupuk`, h.`nama_pupuk`),
    h.`jumlah_karung`, h.`jumlah_berat`,
    COALESCE(h.`harga`, 'Rp 0'),
    COALESCE(h.`jumlah_hutang`, 'Rp 0'),
    h.`created_at`
FROM `hutang_pupuk` h
LEFT JOIN `hutang_pupuk_items` hpi ON hpi.`hutang_id` = h.`id`
LEFT JOIN `pengelolaan_pupuk` p ON h.`pengelolaan_pupuk_id` = p.`id`
LEFT JOIN `master_produk` m ON COALESCE(p.`pupuk_id`, h.`pupuk_id`) = m.`id` AND m.`jenis` = 'Pupuk'
WHERE hpi.`id` IS NULL;
