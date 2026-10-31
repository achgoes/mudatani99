-- =====================================================================
-- mudatani99 - Database Setup & Query Script
-- Target Database: mudatani99
-- Table: users
-- Fields: id, nik (4 digit angka dimulai dari 0001), username, nama, password
-- =====================================================================

-- 1. Buat Tabel users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nik` VARCHAR(4) NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_nik` (`nik`),
    UNIQUE KEY `unique_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insert Data Awal Pengguna (NIK 4 digit dimulai dari 0001)
-- Catatan: Password mendukung pencocokan teks langsung maupun password_hash (Bcrypt)
INSERT INTO `users` (`id`, `nik`, `username`, `nama`, `password`) VALUES
(1, '0001', 'admin', 'Budi Santoso', 'admin123'),
(2, '0002', 'siti', 'Siti Aminah', 'admin123'),
(3, '0003', 'slamet', 'Slamet Riyadi', 'admin123'),
(4, '0004', 'hendra', 'Hendra Wijaya', 'admin123')
ON DUPLICATE KEY UPDATE 
    `nama` = VALUES(`nama`),
    `password` = VALUES(`password`);

-- =====================================================================
-- Table: anggota_petani
-- Fields: id, nama_petani, alamat, keterangan, created_at
-- =====================================================================

-- 3. Buat Tabel anggota_petani
CREATE TABLE IF NOT EXISTS `anggota_petani` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_petani` VARCHAR(100) NOT NULL,
    `alamat` VARCHAR(255) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Insert Data Awal Daftar Anggota Petani
INSERT INTO `anggota_petani` (`id`, `nama_petani`, `alamat`, `keterangan`) VALUES
(1, 'Budi Santoso', 'Dusun Krajan Rt 01 / Rw 02, Desa Makmur', 'Ketua Poktan / Lahan Blok Utara A-1 (2.5 Ha)'),
(2, 'Siti Aminah', 'Dusun Krajan Rt 03 / Rw 02, Desa Makmur', 'Bendahara / Lahan Blok Timur B-2 (1.2 Ha)'),
(3, 'Slamet Riyadi', 'Dusun Sukamaju Rt 02 / Rw 01, Desa Makmur', 'Anggota Aktif / Lahan Blok Selatan C-1 (1.75 Ha)'),
(4, 'Hendra Wijaya', 'Dusun Mekarsari Rt 04 / Rw 03, Desa Makmur', 'Penyuluh / Lahan Demplot Desa (0.5 Ha)'),
(5, 'Mulyono Tani', 'Dusun Sukamaju Rt 01 / Rw 01, Desa Makmur', 'Anggota Aktif / Lahan Blok Utara A-3 (2.0 Ha)')
ON DUPLICATE KEY UPDATE
    `nama_petani` = VALUES(`nama_petani`),
    `alamat` = VALUES(`alamat`),
    `keterangan` = VALUES(`keterangan`);

-- =====================================================================
-- Table: lahan_petani (Pemetaan & Luas Lahan)
-- Fields: id, anggota_id (FK ke anggota_petani), luas_lahan, ditanami, keterangan, created_at
-- =====================================================================

-- 5. Buat Tabel lahan_petani
CREATE TABLE IF NOT EXISTS `lahan_petani` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `anggota_id` INT(11) NOT NULL,
    `luas_lahan` VARCHAR(50) NOT NULL,
    `ditanami` VARCHAR(100) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_anggota_id` (`anggota_id`),
    CONSTRAINT `fk_lahan_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Insert Data Awal Pemetaan & Luas Lahan
INSERT INTO `lahan_petani` (`id`, `anggota_id`, `luas_lahan`, `ditanami`, `keterangan`) VALUES
(1, 1, '2.5 Ha', 'Padi Sawah', 'Blok Utara A-1, Irigasi Teknis'),
(2, 2, '1.2 Ha', 'Cabai Merah', 'Blok Timur B-2, Lahan Tegal'),
(3, 3, '1.75 Ha', 'Jagung Hibrida', 'Blok Selatan C-1, Sawah Tadah Hujan'),
(4, 4, '0.5 Ha', 'Cabai Rawit', 'Lahan Demplot Desa'),
(5, 5, '2.0 Ha', 'Kacang Panjang', 'Blok Utara A-3, Irigasi Teknis')
ON DUPLICATE KEY UPDATE
    `anggota_id` = VALUES(`anggota_id`),
    `luas_lahan` = VALUES(`luas_lahan`),
    `ditanami` = VALUES(`ditanami`),
    `keterangan` = VALUES(`keterangan`);

-- =====================================================================
-- Table: master_produk (Master Obat, Pupuk, dan Tanaman)
-- Fields: id, nama_produk, jenis (Obat / Pupuk / Tanaman), keterangan, created_at
-- =====================================================================

-- 7. Buat Tabel master_produk
CREATE TABLE IF NOT EXISTS `master_produk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_produk` VARCHAR(100) NOT NULL,
    `jenis` ENUM('Obat', 'Pupuk', 'Tanaman') NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Insert Data Awal Master Obat, Pupuk, dan Tanaman
INSERT INTO `master_produk` (`id`, `nama_produk`, `jenis`, `keterangan`) VALUES
(1, 'Pupuk Urea Bersubsidi', 'Pupuk', 'Pupuk Nitrogen 46% Untuk Fase Vegetatif Tanaman'),
(2, 'Npk Phonska Subsidi', 'Pupuk', 'Pupuk Majemuk N 15%, P 15%, K 15%'),
(3, 'Pupuk Organik Petroganik', 'Pupuk', 'Pupuk Organik Pembenah Kesuburan Tanah'),
(4, 'Insektisida Virtako 300 Sc', 'Obat', 'Obat Pengendali Hama Penggerek Batang Dan Wereng'),
(5, 'Fungisida Score 250 Ec', 'Obat', 'Obat Pengendali Jamur Potong Leher Dan Bercak Daun'),
(6, 'Padi Sawah', 'Tanaman', 'Varietas Bibit Padi Unggul Tahan Hama'),
(7, 'Jagung Hibrida', 'Tanaman', 'Bibit Jagung Hibrida Tongkol Besar'),
(8, 'Cabai Merah', 'Tanaman', 'Komoditas Hortikultura Produktivitas Tinggi')
ON DUPLICATE KEY UPDATE
    `nama_produk` = VALUES(`nama_produk`),
    `jenis` = VALUES(`jenis`),
    `keterangan` = VALUES(`keterangan`);

-- =====================================================================
-- Perubahan Tabel lahan_petani:
-- Relasi kolom ditanami / tanaman_id dengan master_produk (Filter: Jenis = 'Tanaman')
-- =====================================================================

-- 9. Tambah data komoditas Tanaman pelengkap di master_produk
INSERT INTO `master_produk` (`id`, `nama_produk`, `jenis`, `keterangan`) VALUES
(9, 'Cabai Rawit', 'Tanaman', 'Komoditas Cabai Rawit Unggul Tahan Cuaca'),
(10, 'Kacang Panjang', 'Tanaman', 'Komoditas Sayuran Kacang Panjang Produktif')
ON DUPLICATE KEY UPDATE
    `nama_produk` = VALUES(`nama_produk`),
    `jenis` = VALUES(`jenis`),
    `keterangan` = VALUES(`keterangan`);

-- 10. Tambahkan kolom tanaman_id (FK ke master_produk) pada tabel lahan_petani
ALTER TABLE `lahan_petani`
    ADD COLUMN `tanaman_id` INT(11) DEFAULT NULL AFTER `luas_lahan`,
    ADD KEY `idx_tanaman_id` (`tanaman_id`),
    ADD CONSTRAINT `fk_lahan_tanaman` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 11. Sinkronkan data awal lahan_petani dengan master_produk (khusus jenis = 'Tanaman')
    UPDATE `lahan_petani` SET `tanaman_id` = 6,  `ditanami` = 'Padi Sawah' WHERE `id` = 1;
    UPDATE `lahan_petani` SET `tanaman_id` = 8,  `ditanami` = 'Cabai Merah' WHERE `id` = 2;
    UPDATE `lahan_petani` SET `tanaman_id` = 7,  `ditanami` = 'Jagung Hibrida' WHERE `id` = 3;
    UPDATE `lahan_petani` SET `tanaman_id` = 9,  `ditanami` = 'Cabai Rawit' WHERE `id` = 4;
    UPDATE `lahan_petani` SET `tanaman_id` = 10, `ditanami` = 'Kacang Panjang' WHERE `id` = 5;

-- =====================================================================
-- Table: pengelolaan_pupuk (Pengelolaan Inventaris Pupuk)
-- Fields: id, pupuk_id (FK ke master_produk jenis = 'Pupuk'), nama_pupuk, jumlah_karung, jumlah_berat, keterangan, created_at
-- =====================================================================

-- 12. Buat Tabel pengelolaan_pupuk
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

-- 13. Insert Data Awal Pengelolaan Pupuk (Diambil dari master_produk jenis = 'Pupuk')
INSERT INTO `pengelolaan_pupuk` (`id`, `pupuk_id`, `nama_pupuk`, `jumlah_karung`, `jumlah_berat`, `keterangan`) VALUES
(1, 1, 'Pupuk Urea Bersubsidi', '37 Karung', '1850 Kg', 'Stok Subsidi Tahap 1 Gudang Poktan'),
(2, 2, 'Npk Phonska Subsidi', '28 Karung', '1400 Kg', 'Alokasi Kuota Subsidi Musim Tanam'),
(3, 3, 'Pupuk Organik Petroganik', '24 Karung', '960 Kg', 'Pupuk Organik Cadangan Kelompok')
ON DUPLICATE KEY UPDATE
    `pupuk_id` = VALUES(`pupuk_id`),
    `nama_pupuk` = VALUES(`nama_pupuk`),
    `jumlah_karung` = VALUES(`jumlah_karung`),
    `jumlah_berat` = VALUES(`jumlah_berat`),
    `keterangan` = VALUES(`keterangan`);

-- =====================================================================
-- Table: pengelolaan_obat (Pengelolaan Inventaris Obat)
-- Fields: id, obat_id (FK ke master_produk jenis = 'Obat'), nama_obat, jumlah_karung, jumlah_berat, keterangan, created_at
-- =====================================================================

-- 14. Buat Tabel pengelolaan_obat
CREATE TABLE IF NOT EXISTS `pengelolaan_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `obat_id` INT(11) DEFAULT NULL,
    `nama_obat` VARCHAR(100) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_obat_id` (`obat_id`),
    CONSTRAINT `fk_pengelolaan_obat_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Insert Data Awal Pengelolaan Obat (Diambil dari master_produk jenis = 'Obat')
INSERT INTO `pengelolaan_obat` (`id`, `obat_id`, `nama_obat`, `jumlah_karung`, `jumlah_berat`, `keterangan`) VALUES
(1, 4, 'Insektisida Virtako 300 Sc', '12 Kemasan', '12 Kg', 'Stok Obat Pengendali Hama Wereng Dan Sundep'),
(2, 5, 'Fungisida Score 250 Ec', '15 Kemasan', '15 Kg', 'Stok Obat Pengendali Jamur Dan Bercak Daun')
ON DUPLICATE KEY UPDATE
    `obat_id` = VALUES(`obat_id`),
    `nama_obat` = VALUES(`nama_obat`),
    `jumlah_karung` = VALUES(`jumlah_karung`),
    `jumlah_berat` = VALUES(`jumlah_berat`),
    `keterangan` = VALUES(`keterangan`);

-- =====================================================================
-- Table: pengelolaan_tanaman (Pengelolaan Inventaris Tanaman)
-- Fields: id, tanaman_id (FK ke master_produk jenis = 'Tanaman'), nama_tanaman, jumlah_karung, jumlah_berat, keterangan, created_at
-- =====================================================================

-- 16. Buat Tabel pengelolaan_tanaman
CREATE TABLE IF NOT EXISTS `pengelolaan_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tanaman_inv_id` (`tanaman_id`),
    CONSTRAINT `fk_pengelolaan_tanaman_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Insert Data Awal Pengelolaan Tanaman (Diambil dari master_produk jenis = 'Tanaman')
INSERT INTO `pengelolaan_tanaman` (`id`, `tanaman_id`, `nama_tanaman`, `jumlah_karung`, `jumlah_berat`, `keterangan`) VALUES
(1, 6, 'Padi Sawah', '30 Karung', '750 Kg', 'Stok Benih Padi Unggul Siap Tanam'),
(2, 7, 'Jagung Hibrida', '18 Karung', '450 Kg', 'Stok Benih Jagung Hibrida Gudang Poktan'),
(3, 8, 'Cabai Merah', '10 Karung', '100 Kg', 'Bibit Unggul Cabai Merah Kelompok'),
(4, 9, 'Cabai Rawit', '8 Karung', '80 Kg', 'Bibit Unggul Cabai Rawit Kelompok'),
(5, 10, 'Kacang Panjang', '12 Karung', '120 Kg', 'Benih Kacang Panjang Musim Tanam')
ON DUPLICATE KEY UPDATE
    `tanaman_id` = VALUES(`tanaman_id`),
    `nama_tanaman` = VALUES(`nama_tanaman`),
    `jumlah_karung` = VALUES(`jumlah_karung`),
    `jumlah_berat` = VALUES(`jumlah_berat`),
    `keterangan` = VALUES(`keterangan`);

-- =====================================================================
-- Perubahan Tabel pengelolaan_obat & pengelolaan_tanaman:
-- Ubah kolom jumlah_karung menjadi jumlah_kemasan
-- - pengelolaan_obat: satuan kemasan pilihan Pcs / Botol
-- - pengelolaan_tanaman: satuan kemasan Pcs saja
-- =====================================================================

-- 18. Ubah kolom jumlah_karung menjadi jumlah_kemasan pada tabel pengelolaan_obat
ALTER TABLE `pengelolaan_obat`
    CHANGE COLUMN `jumlah_karung` `jumlah_kemasan` VARCHAR(50) NOT NULL;

-- 19. Update data awal pengelolaan_obat menggunakan satuan Pcs / Botol
UPDATE `pengelolaan_obat` SET `jumlah_kemasan` = '12 Botol' WHERE `id` = 1;
UPDATE `pengelolaan_obat` SET `jumlah_kemasan` = '15 Pcs' WHERE `id` = 2;

-- 20. Ubah kolom jumlah_karung menjadi jumlah_kemasan pada tabel pengelolaan_tanaman
ALTER TABLE `pengelolaan_tanaman`
    CHANGE COLUMN `jumlah_karung` `jumlah_kemasan` VARCHAR(50) NOT NULL;

-- 21. Update data awal pengelolaan_tanaman menggunakan satuan Pcs saja
UPDATE `pengelolaan_tanaman` SET `jumlah_kemasan` = '30 Pcs' WHERE `id` = 1;
UPDATE `pengelolaan_tanaman` SET `jumlah_kemasan` = '18 Pcs' WHERE `id` = 2;
UPDATE `pengelolaan_tanaman` SET `jumlah_kemasan` = '10 Pcs' WHERE `id` = 3;
UPDATE `pengelolaan_tanaman` SET `jumlah_kemasan` = '8 Pcs' WHERE `id` = 4;
UPDATE `pengelolaan_tanaman` SET `jumlah_kemasan` = '12 Pcs' WHERE `id` = 5;




