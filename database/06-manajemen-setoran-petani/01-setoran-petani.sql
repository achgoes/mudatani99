-- =====================================================================
-- mudatani99 - Manajemen Setoran Petani -> Setoran Petani
-- File      : database/06-manajemen-setoran-petani/01-setoran-petani.sql
-- Tabel     : setoran_petani, setoran_petani_items
-- Prasyarat : 01-master/02-obat-pupuk-tanaman.sql,
--             02-data-anggota/01-daftar-anggota-petani.sql
-- Catatan   : Pencatatan setoran hasil panen/tanaman anggota petani.
--             Setiap transaksi dapat memuat satu atau lebih item tanaman
--             beserta berat per item dan akumulasi jumlah berat (Kg).
-- =====================================================================

-- 1. Buat Tabel setoran_petani (Header Setoran Petani)
CREATE TABLE IF NOT EXISTS `setoran_petani` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_setoran` VARCHAR(30) NOT NULL,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `nama_tanaman` VARCHAR(255) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `tanggal` DATE NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_kode_setoran_petani` (`kode_setoran`),
    KEY `idx_setoran_anggota` (`anggota_id`),
    KEY `idx_setoran_tanggal` (`tanggal`),
    CONSTRAINT `fk_setoran_petani_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Buat Tabel setoran_petani_items (Detail Item Tanaman & Berat per Item)
CREATE TABLE IF NOT EXISTS `setoran_petani_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setoran_id` INT(11) NOT NULL,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL,
    `berat` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_spi_setoran` (`setoran_id`),
    KEY `idx_spi_tanaman` (`tanaman_id`),
    CONSTRAINT `fk_spi_setoran` FOREIGN KEY (`setoran_id`) REFERENCES `setoran_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_spi_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
