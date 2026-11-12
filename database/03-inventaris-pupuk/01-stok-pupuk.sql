-- =====================================================================
-- mudatani99 - Inventaris Pupuk -> Stok Pupuk
-- File      : database/03-inventaris-pupuk/01-stok-pupuk.sql
-- Tabel     : pengelolaan_pupuk
-- Prasyarat : 01-master/02-obat-pupuk-tanaman.sql
-- =====================================================================

-- 1. Buat Tabel pengelolaan_pupuk (Stok Pupuk)
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

-- 2. Data Awal Stok Pupuk (Diambil dari master_produk jenis = 'Pupuk')
INSERT INTO `pengelolaan_pupuk` (`id`, `pupuk_id`, `nama_pupuk`, `jumlah_karung`, `jumlah_berat`, `keterangan`) VALUES
(1, 1, 'Pupuk Urea Bersubsidi',    '37 Karung', '1850 Kg', 'Stok Subsidi Tahap 1 Gudang Poktan'),
(2, 2, 'Npk Phonska Subsidi',      '28 Karung', '1400 Kg', 'Alokasi Kuota Subsidi Musim Tanam'),
(3, 3, 'Pupuk Organik Petroganik', '24 Karung', '960 Kg',  'Pupuk Organik Cadangan Kelompok')
ON DUPLICATE KEY UPDATE
    `pupuk_id` = VALUES(`pupuk_id`),
    `nama_pupuk` = VALUES(`nama_pupuk`),
    `jumlah_karung` = VALUES(`jumlah_karung`),
    `jumlah_berat` = VALUES(`jumlah_berat`),
    `keterangan` = VALUES(`keterangan`);
