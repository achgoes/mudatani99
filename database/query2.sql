-- =====================================================================
-- mudatani99 - Database Setup & Auto-Synchronization Script (query2.sql)
-- Target Database: mudatani99
-- Submenu Baru di Bawah Inventaris Pupuk:
--   1. Stok Pupuk (pengelolaan_pupuk)
--   2. Hutang Piutang (hutang_pupuk)
--   3. Riwayat Hutang / Pelunasan (riwayat_hutang_pupuk)
-- Fitur:
--   - Relasi Nama Petani dari tabel anggota_petani
--   - Relasi Nama Pupuk dari tabel pengelolaan_pupuk & master_produk
--   - Auto-Sinkronisasi Stok:
--       * Saat data ditambahkan di hutang_pupuk -> mengurangi jumlah_karung & jumlah_berat di pengelolaan_pupuk
--       * Saat data diubah di hutang_pupuk -> menyesuaikan selisih jumlah_karung & jumlah_berat di pengelolaan_pupuk
--       * Saat data dihapus di hutang_pupuk -> mengembalikan jumlah_karung & jumlah_berat ke pengelolaan_pupuk
--       * Sinkronisasi otomatis ke tabel riwayat_hutang_pupuk
-- =====================================================================

-- 1. Pastikan Tabel anggota_petani Tersedia
CREATE TABLE IF NOT EXISTS `anggota_petani` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_petani` VARCHAR(100) NOT NULL,
    `alamat` VARCHAR(255) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Pastikan Tabel master_produk Tersedia
CREATE TABLE IF NOT EXISTS `master_produk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_produk` VARCHAR(100) NOT NULL,
    `jenis` ENUM('Obat', 'Pupuk', 'Tanaman') NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Pastikan Tabel pengelolaan_pupuk Tersedia
CREATE TABLE IF NOT EXISTS `pengelolaan_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pupuk_id` INT(11) DEFAULT NULL,
    `nama_pupuk` VARCHAR(100) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pupuk_id` (`pupuk_id`),
    CONSTRAINT `fk_pengelolaan_pupuk_master` FOREIGN KEY (`pupuk_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Buat Tabel hutang_pupuk (Submenu: Hutang Piutang)
-- Kolom: id (Nomor), anggota_id & nama_petani (dari anggota_petani), pengelolaan_pupuk_id, pupuk_id & nama_pupuk,
--        jumlah_karung, jumlah_berat, keterangan, status (Lunas / Belum Lunas), tanggal_lunas, created_at
CREATE TABLE IF NOT EXISTS `hutang_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `pengelolaan_pupuk_id` INT(11) DEFAULT NULL,
    `pupuk_id` INT(11) DEFAULT NULL,
    `nama_pupuk` VARCHAR(100) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `status` ENUM('Belum Lunas', 'Lunas') NOT NULL DEFAULT 'Belum Lunas',
    `tanggal_lunas` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_hutang_anggota` (`anggota_id`),
    KEY `idx_hutang_pengelolaan_pupuk` (`pengelolaan_pupuk_id`),
    KEY `idx_hutang_master_pupuk` (`pupuk_id`),
    CONSTRAINT `fk_hutang_pupuk_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_pupuk_pengelolaan` FOREIGN KEY (`pengelolaan_pupuk_id`) REFERENCES `pengelolaan_pupuk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_pupuk_master` FOREIGN KEY (`pupuk_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Buat Tabel riwayat_hutang_pupuk (Submenu: Riwayat Hutang / Pelunasan)
-- Tersinkronisasi otomatis dengan tabel hutang_pupuk
CREATE TABLE IF NOT EXISTS `riwayat_hutang_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `pengelolaan_pupuk_id` INT(11) DEFAULT NULL,
    `pupuk_id` INT(11) DEFAULT NULL,
    `nama_pupuk` VARCHAR(100) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `status` ENUM('Belum Lunas', 'Lunas') NOT NULL DEFAULT 'Belum Lunas',
    `tanggal_lunas` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_hutang_id` (`hutang_id`),
    KEY `idx_riwayat_anggota` (`anggota_id`),
    KEY `idx_riwayat_pupuk` (`pupuk_id`),
    CONSTRAINT `fk_riwayat_hutang_parent` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_pupuk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 6. EDIT STRUKTUR TABEL (ALTER TABLE):
--    - Menambahkan kolom jumlah_harga di sebelah jumlah_berat
--    - Menambahkan kolom tanggal di sebelah keterangan
--    Catatan: Sesuai instruksi, tanpa contoh insert data pada database.
-- =====================================================================

-- A. Tambahkan kolom pada tabel hutang_pupuk
ALTER TABLE `hutang_pupuk`
    ADD COLUMN `jumlah_harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `jumlah_berat`,
    ADD COLUMN `tanggal` DATE DEFAULT NULL AFTER `keterangan`;

-- B. Tambahkan kolom pada tabel riwayat_hutang_pupuk
ALTER TABLE `riwayat_hutang_pupuk`
    ADD COLUMN `jumlah_harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `jumlah_berat`,
    ADD COLUMN `tanggal` DATE DEFAULT NULL AFTER `keterangan`;

-- =====================================================================
-- 7. EDIT STRUKTUR TABEL: HARGA SATUAN, JUMLAH HUTANG, DAN SISA HUTANG
--    - Mengganti kolom jumlah_harga menjadi harga (harga satuan per kantong)
--    - Menambahkan kolom jumlah_hutang (total hutang = jumlah karung * harga satuan)
--    - Menambahkan kolom sisa_hutang (sisa hutang yang belum dibayar)
-- =====================================================================

-- A. Perubahan struktur kolom pada tabel hutang_pupuk
ALTER TABLE `hutang_pupuk`
    CHANGE COLUMN `jumlah_harga` `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    ADD COLUMN `jumlah_hutang` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `harga`,
    ADD COLUMN `sisa_hutang` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `jumlah_hutang`;

-- B. Perubahan struktur kolom pada tabel riwayat_hutang_pupuk
ALTER TABLE `riwayat_hutang_pupuk`
    CHANGE COLUMN `jumlah_harga` `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    ADD COLUMN `jumlah_hutang` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `harga`,
    ADD COLUMN `sisa_hutang` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `jumlah_hutang`;

-- =====================================================================
-- 8. TABEL BARU: pembayaran_hutang_pupuk (Jejak / Riwayat Pembayaran)
--    - Satu baris = satu kali pembayaran, sehingga cicilan berkali-kali
--      tetap tercatat (riwayat_hutang_pupuk hanya 1 baris per hutang
--      karena UNIQUE KEY uniq_hutang_id)
--    - Nominal disimpan sebagai angka (BIGINT) agar bisa dijumlahkan
--    - Terhapus otomatis jika data hutang induknya dihapus (CASCADE)
-- =====================================================================
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



