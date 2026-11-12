-- =====================================================================
-- mudatani99 - Master -> Obat, Pupuk, dan Tanaman
-- File   : database/01-master/02-obat-pupuk-tanaman.sql
-- Tabel  : master_produk
-- Urutan : 2
-- =====================================================================

-- 1. Buat Tabel master_produk
CREATE TABLE IF NOT EXISTS `master_produk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_produk` VARCHAR(100) NOT NULL,
    `jenis` ENUM('Obat', 'Pupuk', 'Tanaman') NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Data Awal Master Obat, Pupuk, dan Tanaman
INSERT INTO `master_produk` (`id`, `nama_produk`, `jenis`, `keterangan`) VALUES
(1,  'Pupuk Urea Bersubsidi',    'Pupuk',   'Pupuk Nitrogen 46% Untuk Fase Vegetatif Tanaman'),
(2,  'Npk Phonska Subsidi',      'Pupuk',   'Pupuk Majemuk N 15%, P 15%, K 15%'),
(3,  'Pupuk Organik Petroganik', 'Pupuk',   'Pupuk Organik Pembenah Kesuburan Tanah'),
(4,  'Insektisida Virtako 300 Sc','Obat',   'Obat Pengendali Hama Penggerek Batang Dan Wereng'),
(5,  'Fungisida Score 250 Ec',   'Obat',    'Obat Pengendali Jamur Potong Leher Dan Bercak Daun'),
(6,  'Padi Sawah',               'Tanaman', 'Varietas Bibit Padi Unggul Tahan Hama'),
(7,  'Jagung Hibrida',           'Tanaman', 'Bibit Jagung Hibrida Tongkol Besar'),
(8,  'Cabai Merah',              'Tanaman', 'Komoditas Hortikultura Produktivitas Tinggi'),
(9,  'Cabai Rawit',              'Tanaman', 'Komoditas Cabai Rawit Unggul Tahan Cuaca'),
(10, 'Kacang Panjang',           'Tanaman', 'Komoditas Sayuran Kacang Panjang Produktif')
ON DUPLICATE KEY UPDATE
    `nama_produk` = VALUES(`nama_produk`),
    `jenis` = VALUES(`jenis`),
    `keterangan` = VALUES(`keterangan`);
