-- =====================================================================
-- mudatani99 - Data Anggota -> Daftar Anggota Petani
-- File   : database/02-data-anggota/01-daftar-anggota-petani.sql
-- Tabel  : anggota_petani
-- Urutan : 3
-- =====================================================================

-- 1. Buat Tabel anggota_petani
CREATE TABLE IF NOT EXISTS `anggota_petani` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_petani` VARCHAR(100) NOT NULL,
    `alamat` VARCHAR(255) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Data Awal Daftar Anggota Petani
INSERT INTO `anggota_petani` (`id`, `nama_petani`, `alamat`, `keterangan`) VALUES
(1, 'Budi Santoso',  'Dusun Krajan Rt 01 / Rw 02, Desa Makmur',   'Ketua Poktan / Lahan Blok Utara A-1 (2.5 Ha)'),
(2, 'Siti Aminah',   'Dusun Krajan Rt 03 / Rw 02, Desa Makmur',   'Bendahara / Lahan Blok Timur B-2 (1.2 Ha)'),
(3, 'Slamet Riyadi', 'Dusun Sukamaju Rt 02 / Rw 01, Desa Makmur', 'Anggota Aktif / Lahan Blok Selatan C-1 (1.75 Ha)'),
(4, 'Hendra Wijaya', 'Dusun Mekarsari Rt 04 / Rw 03, Desa Makmur','Penyuluh / Lahan Demplot Desa (0.5 Ha)'),
(5, 'Mulyono Tani',  'Dusun Sukamaju Rt 01 / Rw 01, Desa Makmur', 'Anggota Aktif / Lahan Blok Utara A-3 (2.0 Ha)')
ON DUPLICATE KEY UPDATE
    `nama_petani` = VALUES(`nama_petani`),
    `alamat` = VALUES(`alamat`),
    `keterangan` = VALUES(`keterangan`);
