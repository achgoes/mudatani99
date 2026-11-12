<?php
/**
 * mudatani99 - Helper & Database Migration / Synchronization Obat
 * File: obat/helpers.php
 */

if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once ROOT_PATH . '/config/koneksi.php';
}
mysqli_report(MYSQLI_REPORT_OFF);

// 1. Pastikan tabel anggota_petani tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `anggota_petani` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_petani` VARCHAR(100) NOT NULL,
    `alamat` VARCHAR(255) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 2. Pastikan tabel master_produk tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `master_produk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_produk` VARCHAR(100) NOT NULL,
    `jenis` ENUM('Obat', 'Pupuk', 'Tanaman') NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 3. Pastikan tabel pengelolaan_obat tersedia di database dengan kolom jumlah_kemasan
$conn->query("CREATE TABLE IF NOT EXISTS `pengelolaan_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `obat_id` INT(11) DEFAULT NULL,
    `nama_obat` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_obat_id` (`obat_id`),
    CONSTRAINT `fk_pengelolaan_obat_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Migrasi otomatis jika tabel lama masih menggunakan kolom jumlah_karung
$colOld = $conn->query("SHOW COLUMNS FROM `pengelolaan_obat` LIKE 'jumlah_karung'");
if ($colOld && $colOld->num_rows > 0) {
    $conn->query("ALTER TABLE `pengelolaan_obat` CHANGE COLUMN `jumlah_karung` `jumlah_kemasan` VARCHAR(50) NOT NULL");
}

// 4. Pastikan tabel hutang_obat tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `hutang_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
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
    PRIMARY KEY (`id`),
    KEY `idx_hutang_obat_anggota` (`anggota_id`),
    KEY `idx_hutang_obat_pengelolaan` (`pengelolaan_obat_id`),
    KEY `idx_hutang_obat_master` (`obat_id`),
    CONSTRAINT `fk_hutang_obat_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_obat_pengelolaan` FOREIGN KEY (`pengelolaan_obat_id`) REFERENCES `pengelolaan_obat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_obat_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 5. Pastikan tabel riwayat_hutang_obat tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `riwayat_hutang_obat` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 6. Pastikan tabel pembayaran_hutang_obat tersedia (1 baris = 1 kali pembayaran / cicilan)
$conn->query("CREATE TABLE IF NOT EXISTS `pembayaran_hutang_obat` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 7. Pastikan tabel hutang_obat_items tersedia (1 transaksi hutang bisa memiliki beberapa item obat)
$conn->query("CREATE TABLE IF NOT EXISTS `hutang_obat_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `pengelolaan_obat_id` INT(11) DEFAULT NULL,
    `obat_id` INT(11) DEFAULT NULL,
    `nama_obat` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `subtotal` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_hoi_hutang` (`hutang_id`),
    KEY `idx_hoi_pengelolaan` (`pengelolaan_obat_id`),
    KEY `idx_hoi_obat` (`obat_id`),
    CONSTRAINT `fk_hoi_hutang` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_obat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hoi_pengelolaan` FOREIGN KEY (`pengelolaan_obat_id`) REFERENCES `pengelolaan_obat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hoi_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 8. Pastikan tabel penjualan_obat tersedia (berdiri sendiri, terpisah dari hutang piutang)
$conn->query("CREATE TABLE IF NOT EXISTS `penjualan_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `anggota_id` INT(11) DEFAULT NULL,
    `nama_pembeli` VARCHAR(100) NOT NULL,
    `alamat_pembeli` VARCHAR(255) DEFAULT '-',
    `nama_obat` VARCHAR(255) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `total_harga` BIGINT NOT NULL DEFAULT 0,
    `metode_bayar` ENUM('Tunai', 'Transfer', 'QRIS') NOT NULL DEFAULT 'Tunai',
    `tanggal_jual` DATE NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_kode_penjualan_obat` (`kode_transaksi`),
    KEY `idx_penjualan_obat_anggota` (`anggota_id`),
    KEY `idx_penjualan_obat_tanggal` (`tanggal_jual`),
    CONSTRAINT `fk_penjualan_obat_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 9. Pastikan tabel penjualan_obat_items tersedia (detail item obat terjual)
$conn->query("CREATE TABLE IF NOT EXISTS `penjualan_obat_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `penjualan_id` INT(11) NOT NULL,
    `pengelolaan_obat_id` INT(11) DEFAULT NULL,
    `obat_id` INT(11) DEFAULT NULL,
    `nama_obat` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga_satuan` BIGINT NOT NULL DEFAULT 0,
    `subtotal` BIGINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_poi_penjualan` (`penjualan_id`),
    KEY `idx_poi_pengelolaan` (`pengelolaan_obat_id`),
    KEY `idx_poi_obat` (`obat_id`),
    CONSTRAINT `fk_poi_penjualan` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan_obat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_poi_pengelolaan` FOREIGN KEY (`pengelolaan_obat_id`) REFERENCES `pengelolaan_obat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_poi_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 10. Pastikan tabel riwayat_penjualan_obat tersedia (log aktivitas penjualan)
$conn->query("CREATE TABLE IF NOT EXISTS `riwayat_penjualan_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `penjualan_id` INT(11) DEFAULT NULL,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `aksi` ENUM('Terjual', 'Diperbarui', 'Dibatalkan') NOT NULL DEFAULT 'Terjual',
    `nama_pembeli` VARCHAR(100) NOT NULL,
    `rincian_item` TEXT NOT NULL,
    `total_kemasan` VARCHAR(50) NOT NULL,
    `total_berat` VARCHAR(50) NOT NULL,
    `total_harga` BIGINT NOT NULL DEFAULT 0,
    `metode_bayar` VARCHAR(30) NOT NULL DEFAULT 'Tunai',
    `tanggal_jual` DATE NOT NULL,
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rpo_penjualan` (`penjualan_id`),
    KEY `idx_rpo_tanggal` (`tanggal_jual`),
    KEY `idx_rpo_aksi` (`aksi`),
    CONSTRAINT `fk_rpo_penjualan` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan_obat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 11. Pastikan tabel riwayat_stok_obat tersedia
$conn->query("CREATE TABLE IF NOT EXISTS `riwayat_stok_obat` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pengelolaan_obat_id` INT(11) DEFAULT NULL,
    `obat_id` INT(11) DEFAULT NULL,
    `nama_obat` VARCHAR(100) NOT NULL,
    `sumber` VARCHAR(50) NOT NULL DEFAULT 'Kelola Stok',
    `aksi` VARCHAR(60) NOT NULL DEFAULT 'Tambah Stok',
    `kemasan_sebelum` VARCHAR(50) NOT NULL DEFAULT '0 Kemasan',
    `berat_sebelum` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `perubahan_kemasan` VARCHAR(50) NOT NULL DEFAULT '0 Kemasan',
    `perubahan_berat` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `kemasan_sesudah` VARCHAR(50) NOT NULL DEFAULT '0 Kemasan',
    `berat_sesudah` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rso_pengelolaan` (`pengelolaan_obat_id`),
    KEY `idx_rso_obat` (`obat_id`),
    KEY `idx_rso_sumber` (`sumber`),
    KEY `idx_rso_created` (`created_at`),
    CONSTRAINT `fk_rso_pengelolaan` FOREIGN KEY (`pengelolaan_obat_id`) REFERENCES `pengelolaan_obat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_rso_master` FOREIGN KEY (`obat_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Backfill stok awal satu kali jika riwayat_stok_obat masih kosong
$conn->query("
    INSERT INTO `riwayat_stok_obat` (
        `pengelolaan_obat_id`, `obat_id`, `nama_obat`, `sumber`, `aksi`,
        `kemasan_sebelum`, `berat_sebelum`, `perubahan_kemasan`, `perubahan_berat`,
        `kemasan_sesudah`, `berat_sesudah`, `keterangan`, `dicatat_oleh`, `created_at`
    )
    SELECT
        o.`id`,
        o.`obat_id`,
        COALESCE(m.`nama_produk`, o.`nama_obat`),
        'Kelola Stok',
        'Stok Awal',
        '0 Kemasan',
        '0 Kg',
        CONCAT('+', o.`jumlah_kemasan`),
        CONCAT('+', o.`jumlah_berat`),
        o.`jumlah_kemasan`,
        o.`jumlah_berat`,
        COALESCE(NULLIF(o.`keterangan`, ''), '-'),
        'Sistem',
        o.`created_at`
    FROM `pengelolaan_obat` o
    LEFT JOIN `master_produk` m ON o.`obat_id` = m.`id` AND m.`jenis` = 'Obat'
    WHERE NOT EXISTS (SELECT 1 FROM `riwayat_stok_obat` LIMIT 1)
");

/**
 * Format teks otomatis menjadi Title Case (Huruf depan kapital, sisanya kecil)
 */
function formatTitleCase(?string $text): string {
    $clean = trim(preg_replace('/\s+/', ' ', (string)($text ?? '')));
    if ($clean === '' || $clean === '-') {
        return $clean;
    }
    return mb_convert_case(mb_strtolower($clean, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

/**
 * Ekstrak nilai angka bulat dari string jumlah kemasan (contoh: "12 Botol" -> 12)
 */
function parseKemasanInt(?string $val): int {
    if (preg_match('/([0-9]+([.,][0-9]+)?)/', (string)($val ?? ''), $m)) {
        return (int)round((float)str_replace(',', '.', $m[1]));
    }
    return 0;
}

/**
 * Ekstrak satuan kemasan dari string jumlah kemasan (Botol / Pcs / fallback)
 */
function parseSatuanKemasan(?string $val, string $default = 'Pcs'): string {
    $str = (string)($val ?? '');
    if (stripos($str, 'botol') !== false) {
        return 'Botol';
    }
    if (stripos($str, 'pcs') !== false) {
        return 'Pcs';
    }
    if (stripos($str, 'kemasan') !== false) {
        return 'Kemasan';
    }
    return $default;
}

/**
 * Menentukan label satuan ringkasan untuk kumpulan item obat.
 */
function resolveSummarySatuanKemasan(array $items, string $fallback = 'Kemasan'): string {
    $units = [];
    foreach ($items as $it) {
        $u = parseSatuanKemasan($it['jumlah_kemasan'] ?? '', '');
        if ($u !== '' && !in_array($u, $units, true)) {
            $units[] = $u;
        }
    }
    if (count($units) === 1) {
        return $units[0];
    }
    return $fallback;
}

/**
 * Ekstrak nilai float dari string jumlah berat (contoh: "12 Kg" -> 12.0)
 */
function parseBeratFloat(?string $val): float {
    if (preg_match('/([0-9]+([.,][0-9]+)?)/', (string)($val ?? ''), $m)) {
        return (float)str_replace(',', '.', $m[1]);
    }
    return 0.0;
}

/**
 * Format angka kemasan menjadi string bersatuan (Pcs / Botol / Kemasan)
 */
function formatKemasanStr(int $num, string $satuan = 'Kemasan'): string {
    return max(0, $num) . ' ' . $satuan;
}

/**
 * Format angka berat menjadi string bersatuan "Kg"
 */
function formatBeratStr(float $num): string {
    $clean = max(0.0, $num);
    if (floor($clean) == $clean) {
        return (int)$clean . ' Kg';
    }
    return rtrim(rtrim(number_format($clean, 2, '.', ''), '0'), '.') . ' Kg';
}

/**
 * Format string atau angka nominal rupiah (contoh: 750000 -> "Rp 750.000")
 */
function formatRupiahStr($val): string {
    if (empty($val)) return 'Rp 0';
    $num = preg_replace('/[^0-9]/', '', (string)$val);
    if ($num === '' || (int)$num === 0) return 'Rp 0';
    return 'Rp ' . number_format((float)$num, 0, ',', '.');
}

/**
 * Ekstrak angka float dari string nominal rupiah
 */
function parseRupiahFloat($val): float {
    $num = preg_replace('/[^0-9]/', '', (string)$val);
    return ($num !== '') ? (float)$num : 0.0;
}

/**
 * Format selisih kemasan bertanda (+/-) untuk log riwayat stok.
 */
function formatDeltaKemasanStr(int $delta, string $satuan = 'Kemasan'): string {
    if ($delta > 0) return '+' . $delta . ' ' . $satuan;
    if ($delta < 0) return $delta . ' ' . $satuan;
    return '0 ' . $satuan;
}

/**
 * Format selisih berat bertanda (+/-) untuk log riwayat stok.
 */
function formatDeltaBeratStr(float $delta): string {
    $abs = abs($delta);
    $numStr = (floor($abs) == $abs)
        ? (string)(int)$abs
        : rtrim(rtrim(number_format($abs, 2, '.', ''), '0'), '.');
    if ($delta > 0.0001) return '+' . $numStr . ' Kg';
    if ($delta < -0.0001) return '-' . $numStr . ' Kg';
    return '0 Kg';
}

/**
 * Mencatat aktivitas perubahan stok ke tabel riwayat_stok_obat.
 */
function recordRiwayatStokObat(
    mysqli $conn,
    ?int $pengelolaanId,
    ?int $obatId,
    string $namaObat,
    string $sumber,
    string $aksi,
    string $kemasanSebelum,
    string $beratSebelum,
    string $perubahanKemasan,
    string $perubahanBerat,
    string $kemasanSesudah,
    string $beratSesudah,
    string $keterangan = '-'
): void {
    $dicatatOleh = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));
    $cleanNama   = mb_substr(formatTitleCase($namaObat), 0, 100, 'UTF-8');
    $cleanKet    = mb_substr(trim($keterangan) !== '' ? formatTitleCase($keterangan) : '-', 0, 255, 'UTF-8');

    $stmt = $conn->prepare("
        INSERT INTO riwayat_stok_obat (
            pengelolaan_obat_id, obat_id, nama_obat, sumber, aksi,
            kemasan_sebelum, berat_sebelum, perubahan_kemasan, perubahan_berat,
            kemasan_sesudah, berat_sesudah, keterangan, dicatat_oleh
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if ($stmt) {
        $stmt->bind_param(
            "iisssssssssss",
            $pengelolaanId,
            $obatId,
            $cleanNama,
            $sumber,
            $aksi,
            $kemasanSebelum,
            $beratSebelum,
            $perubahanKemasan,
            $perubahanBerat,
            $kemasanSesudah,
            $beratSesudah,
            $cleanKet,
            $dicatatOleh
        );
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Sesuaikan stok jumlah_kemasan & jumlah_berat pada tabel pengelolaan_obat.
 * $deltaKemasan > 0 menambah/mengembalikan stok, $deltaKemasan < 0 mengurangi stok.
 */
function adjustStokPengelolaanObat(mysqli $conn, ?int $pengelolaanId, ?int $obatId, int $deltaKemasan, float $deltaBerat, array $logMeta = []): bool {
    $targetRow = null;

    if ($pengelolaanId && $pengelolaanId > 0) {
        $stmt = $conn->prepare("
            SELECT o.id, o.obat_id, COALESCE(m.nama_produk, o.nama_obat) AS nama_obat, o.jumlah_kemasan, o.jumlah_berat
            FROM pengelolaan_obat o
            LEFT JOIN master_produk m ON o.obat_id = m.id AND m.jenis = 'Obat'
            WHERE o.id = ? LIMIT 1
        ");
        if ($stmt) {
            $stmt->bind_param("i", $pengelolaanId);
            $stmt->execute();
            $res = $stmt->get_result();
            $targetRow = $res ? $res->fetch_assoc() : null;
            $stmt->close();
        }
    }

    if (!$targetRow && $obatId && $obatId > 0) {
        $stmt = $conn->prepare("
            SELECT o.id, o.obat_id, COALESCE(m.nama_produk, o.nama_obat) AS nama_obat, o.jumlah_kemasan, o.jumlah_berat
            FROM pengelolaan_obat o
            LEFT JOIN master_produk m ON o.obat_id = m.id AND m.jenis = 'Obat'
            WHERE o.obat_id = ? ORDER BY o.id ASC LIMIT 1
        ");
        if ($stmt) {
            $stmt->bind_param("i", $obatId);
            $stmt->execute();
            $res = $stmt->get_result();
            $targetRow = $res ? $res->fetch_assoc() : null;
            $stmt->close();
        }
    }

    if (!$targetRow) {
        return false;
    }

    $currKemasan = parseKemasanInt($targetRow['jumlah_kemasan']);
    $currSatuan  = parseSatuanKemasan($targetRow['jumlah_kemasan'], 'Pcs');
    $currBerat   = parseBeratFloat($targetRow['jumlah_berat']);

    // Jika berat belum diisi tetapi kemasan berubah, hitung proporsional dari berat per kemasan
    if ($deltaBerat == 0.0 && $deltaKemasan != 0 && $currKemasan > 0) {
        $beratPerKemasan = $currBerat / $currKemasan;
        $deltaBerat = $deltaKemasan * $beratPerKemasan;
    }

    $newKemasanNum = max(0, $currKemasan + $deltaKemasan);
    $newBeratNum   = max(0.0, $currBerat + $deltaBerat);
    $newKemasanStr = formatKemasanStr($newKemasanNum, $currSatuan);
    $newBeratStr   = formatBeratStr($newBeratNum);
    $targetId      = (int)$targetRow['id'];
    $targetObat    = !empty($targetRow['obat_id']) ? (int)$targetRow['obat_id'] : $obatId;

    $upd = $conn->prepare("UPDATE pengelolaan_obat SET jumlah_kemasan = ?, jumlah_berat = ? WHERE id = ?");
    if ($upd) {
        $upd->bind_param("ssi", $newKemasanStr, $newBeratStr, $targetId);
        $ok = $upd->execute();
        $upd->close();

        if ($ok && empty($logMeta['skip_log']) && ($deltaKemasan !== 0 || abs($deltaBerat) > 0.0001)) {
            $sumber = (string)($logMeta['sumber'] ?? 'Transaksi');
            $aksi   = (string)($logMeta['aksi'] ?? ($deltaKemasan < 0 ? 'Stok Keluar' : 'Stok Kembali'));
            $ket    = (string)($logMeta['keterangan'] ?? '-');
            recordRiwayatStokObat(
                $conn,
                $targetId,
                $targetObat,
                (string)$targetRow['nama_obat'],
                $sumber,
                $aksi,
                formatKemasanStr($currKemasan, $currSatuan),
                formatBeratStr($currBerat),
                formatDeltaKemasanStr($newKemasanNum - $currKemasan, $currSatuan),
                formatDeltaBeratStr($newBeratNum - $currBerat),
                $newKemasanStr,
                $newBeratStr,
                $ket
            );
        }

        return $ok;
    }
    return false;
}

/**
 * Mengambil seluruh baris item obat milik satu transaksi hutang dari hutang_obat_items.
 */
function getHutangObatItemsByHutangId(mysqli $conn, int $hutangId): array {
    $items = [];
    if ($hutangId <= 0) {
        return $items;
    }

    $stmt = $conn->prepare("
        SELECT i.id, i.hutang_id, i.pengelolaan_obat_id, i.obat_id,
               COALESCE(m.nama_produk, o.nama_obat, i.nama_obat) AS nama_obat,
               i.jumlah_kemasan, i.jumlah_berat,
               COALESCE(i.harga, 'Rp 0') AS harga,
               COALESCE(i.subtotal, 'Rp 0') AS subtotal
        FROM hutang_obat_items i
        LEFT JOIN pengelolaan_obat o ON i.pengelolaan_obat_id = o.id
        LEFT JOIN master_produk m ON COALESCE(o.obat_id, i.obat_id) = m.id AND m.jenis = 'Obat'
        WHERE i.hutang_id = ?
        ORDER BY i.id ASC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $hutangId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($res && ($row = $res->fetch_assoc())) {
            $items[] = normalizeHutangObatItemRow($row);
        }
        $stmt->close();
    }

    return $items;
}

/**
 * Mengambil seluruh item obat untuk semua transaksi hutang, dikelompokkan per hutang_id.
 */
function getAllHutangObatItemsGrouped(mysqli $conn): array {
    $grouped = [];
    $res = $conn->query("
        SELECT i.id, i.hutang_id, i.pengelolaan_obat_id, i.obat_id,
               COALESCE(m.nama_produk, o.nama_obat, i.nama_obat) AS nama_obat,
               i.jumlah_kemasan, i.jumlah_berat,
               COALESCE(i.harga, 'Rp 0') AS harga,
               COALESCE(i.subtotal, 'Rp 0') AS subtotal
        FROM hutang_obat_items i
        LEFT JOIN pengelolaan_obat o ON i.pengelolaan_obat_id = o.id
        LEFT JOIN master_produk m ON COALESCE(o.obat_id, i.obat_id) = m.id AND m.jenis = 'Obat'
        ORDER BY i.hutang_id ASC, i.id ASC
    ");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $hid = (int)$row['hutang_id'];
            $grouped[$hid][] = normalizeHutangObatItemRow($row);
        }
    }

    return $grouped;
}

/**
 * Menormalkan satu baris item hutang obat untuk tampilan dan kalkulasi.
 */
function normalizeHutangObatItemRow(array $row): array {
    $kNum     = parseKemasanInt($row['jumlah_kemasan'] ?? '');
    $satuan   = parseSatuanKemasan($row['jumlah_kemasan'] ?? '', 'Kemasan');
    $bNum     = parseBeratFloat($row['jumlah_berat'] ?? '');
    $hargaStr = formatRupiahStr($row['harga'] ?? 'Rp 0');
    $hargaNum = parseRupiahFloat($hargaStr);
    $subRaw   = parseRupiahFloat($row['subtotal'] ?? 'Rp 0');
    $subStr   = ($subRaw > 0) ? formatRupiahStr($subRaw) : formatRupiahStr($kNum * $hargaNum);

    return [
        'id'                  => (int)($row['id'] ?? 0),
        'hutang_id'           => (int)($row['hutang_id'] ?? 0),
        'pengelolaan_obat_id' => !empty($row['pengelolaan_obat_id']) ? (int)$row['pengelolaan_obat_id'] : null,
        'obat_id'             => !empty($row['obat_id']) ? (int)$row['obat_id'] : null,
        'nama_obat'           => formatTitleCase($row['nama_obat'] ?? '-'),
        'jumlah_kemasan'      => formatTitleCase($row['jumlah_kemasan'] ?? formatKemasanStr($kNum, $satuan)),
        'jumlah_berat'        => formatTitleCase($row['jumlah_berat'] ?? formatBeratStr($bNum)),
        'kemasan_num'         => $kNum,
        'satuan_kemasan'      => $satuan,
        'berat_num'           => $bNum,
        'harga'               => $hargaStr,
        'harga_num'           => $hargaNum,
        'subtotal'            => $subStr,
        'subtotal_num'        => parseRupiahFloat($subStr),
    ];
}

/**
 * Mengganti daftar item obat pada hutang_obat_items untuk transaksi tertentu.
 */
function saveHutangObatItems(mysqli $conn, int $hutangId, array $items): void {
    $del = $conn->prepare("DELETE FROM hutang_obat_items WHERE hutang_id = ?");
    if ($del) {
        $del->bind_param("i", $hutangId);
        $del->execute();
        $del->close();
    }

    $ins = $conn->prepare("
        INSERT INTO hutang_obat_items (
            hutang_id, pengelolaan_obat_id, obat_id,
            nama_obat, jumlah_kemasan, jumlah_berat,
            harga, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$ins) {
        return;
    }

    foreach ($items as $item) {
        $oId   = $item['pengelolaan_obat_id'];
        $mId   = $item['obat_id'];
        $nObat = $item['nama_obat'];
        $jKem  = $item['jumlah_kemasan'];
        $jBer  = $item['jumlah_berat'];
        $hrg   = $item['harga'];
        $sub   = $item['subtotal'];
        $ins->bind_param("iiisssss", $hutangId, $oId, $mId, $nObat, $jKem, $jBer, $hrg, $sub);
        $ins->execute();
    }
    $ins->close();
}

/**
 * Menyesuaikan stok pengelolaan_obat untuk sekumpulan item ($sign = -1 mengurangi, +1 mengembalikan).
 */
function adjustObatItemsStock(mysqli $conn, array $items, int $sign, array $logMeta = []): void {
    foreach ($items as $item) {
        $oId = !empty($item['pengelolaan_obat_id']) ? (int)$item['pengelolaan_obat_id'] : null;
        $mId = !empty($item['obat_id']) ? (int)$item['obat_id'] : null;
        $k   = (int)($item['kemasan_num'] ?? parseKemasanInt($item['jumlah_kemasan'] ?? ''));
        $b   = (float)($item['berat_num'] ?? parseBeratFloat($item['jumlah_berat'] ?? ''));
        if ($k > 0 || $b > 0) {
            adjustStokPengelolaanObat($conn, $oId, $mId, $sign * $k, $sign * $b, $logMeta);
        }
    }
}

/**
 * Menghitung kuota stok lama per pengelolaan_obat_id untuk validasi edit transaksi.
 */
function buildObatStockAllowanceFromItems(mysqli $conn, array $items): array {
    $allowance = [];
    foreach ($items as $oi) {
        $oId = !empty($oi['pengelolaan_obat_id']) ? (int)$oi['pengelolaan_obat_id'] : 0;
        if ($oId <= 0 && !empty($oi['obat_id'])) {
            $qOldStok = $conn->prepare("SELECT id FROM pengelolaan_obat WHERE obat_id = ? ORDER BY id ASC LIMIT 1");
            if ($qOldStok) {
                $mId = (int)$oi['obat_id'];
                $qOldStok->bind_param("i", $mId);
                $qOldStok->execute();
                $rOldStok = $qOldStok->get_result();
                if ($rOldStok && ($rowOldStok = $rOldStok->fetch_assoc())) {
                    $oId = (int)$rowOldStok['id'];
                }
                $qOldStok->close();
            }
        }
        if ($oId > 0) {
            $allowance[$oId] = [
                'kemasan' => ($allowance[$oId]['kemasan'] ?? 0) + (int)($oi['kemasan_num'] ?? 0),
                'berat'   => ($allowance[$oId]['berat'] ?? 0.0) + (float)($oi['berat_num'] ?? 0.0),
            ];
        }
    }
    return $allowance;
}

/**
 * Mem-parsing, memvalidasi, dan mengecek ketersediaan stok seluruh item obat pada form transaksi.
 */
function parseObatTransactionItems(mysqli $conn, array $post, array $oldStockAllowance = []): array {
    $rawIds      = $post['item_pengelolaan_obat_id'] ?? null;
    $rawKemasans = $post['item_jumlah_kemasan'] ?? null;
    $rawBerats   = $post['item_jumlah_berat'] ?? null;
    $rawHargas   = $post['item_harga'] ?? null;

    if (!is_array($rawIds)) {
        $rawIds      = [$post['pengelolaan_obat_id'] ?? ''];
        $rawKemasans = [$post['jumlah_kemasan'] ?? ''];
        $rawBerats   = [$post['jumlah_berat'] ?? ''];
        $rawHargas   = [$post['harga'] ?? ($post['jumlah_harga'] ?? '')];
    }

    $rowCount = count($rawIds);
    $items    = [];
    $emptyRes = [
        'error'             => '',
        'items'             => [],
        'total_kemasan'     => 0,
        'summary_satuan'    => 'Kemasan',
        'total_berat'       => 0.0,
        'total_nominal'     => 0.0,
        'summary_nama_obat' => '',
    ];

    if ($rowCount === 0) {
        $emptyRes['error'] = 'Minimal satu item obat wajib diisi.';
        return $emptyRes;
    }

    $stokCache   = [];
    $requestedBy = [];
    $stokStmt    = $conn->prepare("
        SELECT o.id, o.obat_id, COALESCE(m.nama_produk, o.nama_obat) AS nama_obat, o.jumlah_kemasan, o.jumlah_berat
        FROM pengelolaan_obat o
        LEFT JOIN master_produk m ON o.obat_id = m.id AND m.jenis = 'Obat'
        WHERE o.id = ? LIMIT 1
    ");

    for ($i = 0; $i < $rowCount; $i++) {
        $oId        = (int)($rawIds[$i] ?? 0);
        $rawKemStr  = (string)($rawKemasans[$i] ?? '');
        $reqKemasan = parseKemasanInt($rawKemStr);
        $reqBerat   = parseBeratFloat((string)($rawBerats[$i] ?? ''));
        $hargaStr   = formatRupiahStr($rawHargas[$i] ?? '');
        $hargaFloat = parseRupiahFloat($hargaStr);

        if ($oId <= 0 || $reqKemasan <= 0 || $reqBerat <= 0 || $hargaFloat <= 0) {
            if ($stokStmt) $stokStmt->close();
            $itemNo = $i + 1;
            $emptyRes['error'] = "Item obat ke-{$itemNo}: Nama Obat, Jumlah Kemasan (> 0), Jumlah Berat (> 0), dan Harga Satuan (> 0) wajib diisi dengan benar.";
            return $emptyRes;
        }

        if (!isset($stokCache[$oId])) {
            $stokRow = null;
            if ($stokStmt) {
                $stokStmt->bind_param("i", $oId);
                $stokStmt->execute();
                $stokRes = $stokStmt->get_result();
                $stokRow = $stokRes ? $stokRes->fetch_assoc() : null;
            }
            if (!$stokRow) {
                if ($stokStmt) $stokStmt->close();
                $itemNo = $i + 1;
                $emptyRes['error'] = "Item obat ke-{$itemNo}: Data obat tidak ditemukan pada tabel Stok Obat.";
                return $emptyRes;
            }
            $stokCache[$oId] = $stokRow;
        }

        $stokRow = $stokCache[$oId];
        $stokSatuan = parseSatuanKemasan($stokRow['jumlah_kemasan'], 'Pcs');
        $itemSatuan = parseSatuanKemasan($rawKemStr, $stokSatuan);

        $requestedBy[$oId] = [
            'kemasan' => ($requestedBy[$oId]['kemasan'] ?? 0) + $reqKemasan,
            'berat'   => ($requestedBy[$oId]['berat'] ?? 0.0) + $reqBerat,
        ];

        $subtotalFloat = $reqKemasan * $hargaFloat;
        $items[] = [
            'pengelolaan_obat_id' => $oId,
            'obat_id'             => $stokRow['obat_id'] ? (int)$stokRow['obat_id'] : null,
            'nama_obat'           => formatTitleCase($stokRow['nama_obat']),
            'jumlah_kemasan'      => formatKemasanStr($reqKemasan, $itemSatuan),
            'jumlah_berat'        => formatBeratStr($reqBerat),
            'kemasan_num'         => $reqKemasan,
            'satuan_kemasan'      => $itemSatuan,
            'berat_num'           => $reqBerat,
            'harga'               => $hargaStr,
            'harga_num'           => $hargaFloat,
            'subtotal'            => formatRupiahStr($subtotalFloat),
            'subtotal_float'      => $subtotalFloat,
        ];
    }

    if ($stokStmt) {
        $stokStmt->close();
    }

    foreach ($requestedBy as $oId => $req) {
        $stokRow     = $stokCache[$oId];
        $allowKem    = (int)($oldStockAllowance[$oId]['kemasan'] ?? 0);
        $allowBerat  = (float)($oldStockAllowance[$oId]['berat'] ?? 0.0);
        $maksKemasan = parseKemasanInt($stokRow['jumlah_kemasan']) + $allowKem;
        $stokSatuan  = parseSatuanKemasan($stokRow['jumlah_kemasan'], 'Pcs');
        $maksBerat   = parseBeratFloat($stokRow['jumlah_berat']) + $allowBerat;
        $namaObatLbl = formatTitleCase($stokRow['nama_obat']);

        if ($req['kemasan'] > $maksKemasan) {
            $emptyRes['error'] = "Stok kemasan untuk {$namaObatLbl} tidak mencukupi! Maksimal tersedia: {$maksKemasan} {$stokSatuan}, diminta: {$req['kemasan']} {$stokSatuan}.";
            return $emptyRes;
        }
        if ($req['berat'] > ($maksBerat + 0.01)) {
            $emptyRes['error'] = "Stok berat untuk {$namaObatLbl} tidak mencukupi! Maksimal tersedia: " . formatBeratStr($maksBerat) . ", diminta: " . formatBeratStr($req['berat']) . ".";
            return $emptyRes;
        }
    }

    $totalKemasan = 0;
    $totalBerat   = 0.0;
    $totalNominal = 0.0;
    $names        = [];
    foreach ($items as $it) {
        $totalKemasan += $it['kemasan_num'];
        $totalBerat   += $it['berat_num'];
        $totalNominal += $it['subtotal_float'];
        if (!in_array($it['nama_obat'], $names, true)) {
            $names[] = $it['nama_obat'];
        }
    }

    return [
        'error'             => null,
        'items'             => $items,
        'total_kemasan'     => $totalKemasan,
        'summary_satuan'    => resolveSummarySatuanKemasan($items, 'Kemasan'),
        'total_berat'       => $totalBerat,
        'total_nominal'     => $totalNominal,
        'summary_nama_obat' => mb_substr(implode(', ', $names), 0, 255, 'UTF-8'),
    ];
}

/**
 * Menormalkan satu baris item penjualan obat untuk tampilan dan kalkulasi.
 */
function normalizePenjualanObatItemRow(array $row): array {
    $kNum     = parseKemasanInt($row['jumlah_kemasan'] ?? '');
    $satuan   = parseSatuanKemasan($row['jumlah_kemasan'] ?? '', 'Kemasan');
    $bNum     = parseBeratFloat($row['jumlah_berat'] ?? '');
    $hargaInt = (int)($row['harga_satuan'] ?? 0);
    $subInt   = (int)($row['subtotal'] ?? ($kNum * $hargaInt));

    return [
        'id'                  => (int)($row['id'] ?? 0),
        'penjualan_id'        => (int)($row['penjualan_id'] ?? 0),
        'pengelolaan_obat_id' => !empty($row['pengelolaan_obat_id']) ? (int)$row['pengelolaan_obat_id'] : null,
        'obat_id'             => !empty($row['obat_id']) ? (int)$row['obat_id'] : null,
        'nama_obat'           => formatTitleCase($row['nama_obat'] ?? '-'),
        'jumlah_kemasan'      => formatTitleCase($row['jumlah_kemasan'] ?? formatKemasanStr($kNum, $satuan)),
        'jumlah_berat'        => formatTitleCase($row['jumlah_berat'] ?? formatBeratStr($bNum)),
        'kemasan_num'         => $kNum,
        'satuan_kemasan'      => $satuan,
        'berat_num'           => $bNum,
        'harga_satuan'        => $hargaInt,
        'harga'               => formatRupiahStr($hargaInt),
        'subtotal_num'        => $subInt,
        'subtotal'            => formatRupiahStr($subInt),
    ];
}

/**
 * Mengambil seluruh baris item obat milik satu transaksi penjualan.
 */
function getPenjualanObatItemsById(mysqli $conn, int $penjualanId): array {
    $items = [];
    if ($penjualanId <= 0) {
        return $items;
    }

    $stmt = $conn->prepare("
        SELECT i.id, i.penjualan_id, i.pengelolaan_obat_id, i.obat_id,
               COALESCE(m.nama_produk, o.nama_obat, i.nama_obat) AS nama_obat,
               i.jumlah_kemasan, i.jumlah_berat, i.harga_satuan, i.subtotal
        FROM penjualan_obat_items i
        LEFT JOIN pengelolaan_obat o ON i.pengelolaan_obat_id = o.id
        LEFT JOIN master_produk m ON COALESCE(o.obat_id, i.obat_id) = m.id AND m.jenis = 'Obat'
        WHERE i.penjualan_id = ?
        ORDER BY i.id ASC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $penjualanId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($res && ($row = $res->fetch_assoc())) {
            $items[] = normalizePenjualanObatItemRow($row);
        }
        $stmt->close();
    }

    return $items;
}

/**
 * Mengambil seluruh item penjualan obat dikelompokkan per penjualan_id.
 */
function getAllPenjualanObatItemsGrouped(mysqli $conn): array {
    $grouped = [];
    $res = $conn->query("
        SELECT i.id, i.penjualan_id, i.pengelolaan_obat_id, i.obat_id,
               COALESCE(m.nama_produk, o.nama_obat, i.nama_obat) AS nama_obat,
               i.jumlah_kemasan, i.jumlah_berat, i.harga_satuan, i.subtotal
        FROM penjualan_obat_items i
        LEFT JOIN pengelolaan_obat o ON i.pengelolaan_obat_id = o.id
        LEFT JOIN master_produk m ON COALESCE(o.obat_id, i.obat_id) = m.id AND m.jenis = 'Obat'
        ORDER BY i.penjualan_id ASC, i.id ASC
    ");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['penjualan_id'];
            $grouped[$pid][] = normalizePenjualanObatItemRow($row);
        }
    }

    return $grouped;
}

/**
 * Menyimpan ulang daftar item penjualan pada penjualan_obat_items.
 */
function savePenjualanObatItems(mysqli $conn, int $penjualanId, array $items): void {
    $del = $conn->prepare("DELETE FROM penjualan_obat_items WHERE penjualan_id = ?");
    if ($del) {
        $del->bind_param("i", $penjualanId);
        $del->execute();
        $del->close();
    }

    $ins = $conn->prepare("
        INSERT INTO penjualan_obat_items (
            penjualan_id, pengelolaan_obat_id, obat_id,
            nama_obat, jumlah_kemasan, jumlah_berat,
            harga_satuan, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$ins) {
        return;
    }

    foreach ($items as $item) {
        $oId   = $item['pengelolaan_obat_id'];
        $mId   = $item['obat_id'];
        $nObat = $item['nama_obat'];
        $jKem  = $item['jumlah_kemasan'];
        $jBer  = $item['jumlah_berat'];
        $hrg   = (int)round((float)($item['harga_num'] ?? 0));
        $sub   = (int)round((float)($item['subtotal_float'] ?? 0));
        $ins->bind_param("iiisssii", $penjualanId, $oId, $mId, $nObat, $jKem, $jBer, $hrg, $sub);
        $ins->execute();
    }
    $ins->close();
}

/**
 * Membuat kode transaksi penjualan obat unik (mis. PJO-20261112-0001).
 */
function generateKodePenjualanObat(mysqli $conn, string $tanggal): string {
    $tglCode = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ? str_replace('-', '', $tanggal) : date('Ymd');
    $prefix  = "PJO-{$tglCode}-";
    $like    = $prefix . '%';
    $nextSeq = 1;

    $stmt = $conn->prepare("SELECT kode_transaksi FROM penjualan_obat WHERE kode_transaksi LIKE ? ORDER BY id DESC LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && ($row = $res->fetch_assoc())) {
            $lastKode = (string)$row['kode_transaksi'];
            if (preg_match('/(\d+)$/', $lastKode, $m)) {
                $nextSeq = ((int)$m[1]) + 1;
            }
        }
        $stmt->close();
    }

    return sprintf('%s%04d', $prefix, $nextSeq);
}

/**
 * Mencatat jejak transaksi penjualan ke tabel riwayat_penjualan_obat.
 */
function recordRiwayatPenjualanObat(
    mysqli $conn,
    ?int $penjualanId,
    string $kodeTransaksi,
    string $aksi,
    string $namaPembeli,
    array $items,
    string $totalKemasan,
    string $totalBerat,
    int $totalHarga,
    string $metodeBayar,
    string $tanggalJual,
    string $keterangan
): void {
    $parts = [];
    foreach ($items as $it) {
        $hargaLabel = $it['harga'] ?? formatRupiahStr($it['harga_satuan'] ?? 0);
        $parts[] = "{$it['nama_obat']} ({$it['jumlah_kemasan']} / {$it['jumlah_berat']} @ {$hargaLabel})";
    }
    $rincianItem = !empty($parts) ? implode('; ', $parts) : '-';
    $dicatatOleh = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));

    $stmt = $conn->prepare("
        INSERT INTO riwayat_penjualan_obat (
            penjualan_id, kode_transaksi, aksi, nama_pembeli,
            rincian_item, total_kemasan, total_berat, total_harga,
            metode_bayar, tanggal_jual, dicatat_oleh, keterangan
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if ($stmt) {
        $stmt->bind_param(
            "issssssissss",
            $penjualanId,
            $kodeTransaksi,
            $aksi,
            $namaPembeli,
            $rincianItem,
            $totalKemasan,
            $totalBerat,
            $totalHarga,
            $metodeBayar,
            $tanggalJual,
            $dicatatOleh,
            $keterangan
        );
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Hapus jejak catatan sistem "Bayar: Rp ..." dari kolom keterangan hutang obat.
 */
function stripHutangObatPaymentNote(?string $keterangan): string {
    $clean = trim((string)($keterangan ?? ''));
    if ($clean === '' || $clean === '-') {
        return '-';
    }
    $parts = preg_split('/(?:^|\s*\|\s*)Bayar:\s*Rp\s*[0-9.]+/i', $clean, 2);
    $base  = trim((string)($parts[0] ?? ''), " \t\n\r\0\x0B|");
    return ($base === '') ? '-' : $base;
}

/**
 * Pastikan baris pembayaran_hutang_obat sinkron saat hutang diubah menjadi Lunas atau diedit.
 */
function syncPembayaranOnHutangObatUpdate(mysqli $conn, int $hutangId, int $anggotaId, string $namaPetani, string $namaObat, int $totalHutangInt, int $sisaHutangInt, string $status, string $ketCatatan = ''): void {
    $updB = $conn->prepare("UPDATE pembayaran_hutang_obat SET anggota_id = ?, nama_petani = ?, nama_obat = ?, total_hutang = ? WHERE hutang_id = ?");
    if ($updB) {
        $updB->bind_param("issii", $anggotaId, $namaPetani, $namaObat, $totalHutangInt, $hutangId);
        $updB->execute();
        $updB->close();
    }

    if ($status === 'Belum Lunas' && ($totalHutangInt <= 0 || $sisaHutangInt >= $totalHutangInt)) {
        $delB = $conn->prepare("DELETE FROM pembayaran_hutang_obat WHERE hutang_id = ?");
        if ($delB) {
            $delB->bind_param("i", $hutangId);
            $delB->execute();
            $delB->close();
        }
        return;
    }

    $qRows = $conn->prepare("SELECT id, pembayaran_ke, jumlah_bayar, sisa_hutang, status FROM pembayaran_hutang_obat WHERE hutang_id = ? ORDER BY pembayaran_ke ASC, id ASC");
    if (!$qRows) {
        return;
    }
    $qRows->bind_param("i", $hutangId);
    $qRows->execute();
    $resRows = $qRows->get_result();
    $rows = [];
    $sumBayar = 0;
    while ($resRows && ($r = $resRows->fetch_assoc())) {
        $rows[] = $r;
        $sumBayar += (int)$r['jumlah_bayar'];
    }
    $qRows->close();

    if (empty($rows)) {
        ensureBackfillPembayaranHutangObat($conn);
        return;
    }

    $lastRow = $rows[count($rows) - 1];
    $lastId  = (int)$lastRow['id'];
    $lastKe  = (int)$lastRow['pembayaran_ke'];

    if ($status === 'Lunas') {
        $kurangBayar = max(0, $totalHutangInt - $sumBayar);
        if ($kurangBayar > 0) {
            $nextKe      = $lastKe + 1;
            $tglBayar    = date('Y-m-d');
            $metode      = 'Tunai';
            $stLunas     = 'Lunas';
            $ketStep     = formatTitleCase($ketCatatan !== '' ? $ketCatatan : 'Pelunasan Hutang Obat');
            $dicatatOleh = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));

            $ins = $conn->prepare("
                INSERT INTO pembayaran_hutang_obat (
                    hutang_id, anggota_id, nama_petani, nama_obat, pembayaran_ke,
                    tanggal_bayar, total_hutang, sisa_sebelum, jumlah_bayar, sisa_hutang,
                    metode_bayar, status, keterangan, dicatat_oleh
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($ins) {
                $sisaNol = 0;
                $ins->bind_param(
                    "iissisiiiissss",
                    $hutangId, $anggotaId, $namaPetani, $namaObat, $nextKe,
                    $tglBayar, $totalHutangInt, $kurangBayar, $kurangBayar, $sisaNol,
                    $metode, $stLunas, $ketStep, $dicatatOleh
                );
                $ins->execute();
                $ins->close();
            }
        } else {
            $updLast = $conn->prepare("UPDATE pembayaran_hutang_obat SET sisa_hutang = 0, status = 'Lunas' WHERE id = ?");
            if ($updLast) {
                $updLast->bind_param("i", $lastId);
                $updLast->execute();
                $updLast->close();
            }
        }
    } else {
        $updLast = $conn->prepare("UPDATE pembayaran_hutang_obat SET sisa_hutang = ?, status = 'Belum Lunas' WHERE id = ?");
        if ($updLast) {
            $updLast->bind_param("ii", $sisaHutangInt, $lastId);
            $updLast->execute();
            $updLast->close();
        }
    }
}

/**
 * Sinkronkan baris hutang_obat ke tabel riwayat_hutang_obat.
 */
function syncRiwayatHutangObatRow(mysqli $conn, int $hutangId): void {
    $stmt = $conn->prepare("SELECT * FROM hutang_obat WHERE id = ? LIMIT 1");
    if (!$stmt) return;
    $stmt->bind_param("i", $hutangId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        $del = $conn->prepare("DELETE FROM riwayat_hutang_obat WHERE hutang_id = ?");
        if ($del) {
            $del->bind_param("i", $hutangId);
            $del->execute();
            $del->close();
        }
        return;
    }

    $harga        = $row['harga'] ?? 'Rp 0';
    $jumlahHutang = $row['jumlah_hutang'] ?? 'Rp 0';
    $sisaHutang   = $row['sisa_hutang'] ?? 'Rp 0';
    $tgl          = $row['tanggal'] ?? null;

    $sync = $conn->prepare("
        INSERT INTO riwayat_hutang_obat (
            hutang_id, anggota_id, nama_petani, pengelolaan_obat_id,
            obat_id, nama_obat, jumlah_kemasan, jumlah_berat, harga,
            jumlah_hutang, sisa_hutang,
            keterangan, tanggal, status, tanggal_lunas, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            anggota_id          = VALUES(anggota_id),
            nama_petani         = VALUES(nama_petani),
            pengelolaan_obat_id = VALUES(pengelolaan_obat_id),
            obat_id             = VALUES(obat_id),
            nama_obat           = VALUES(nama_obat),
            jumlah_kemasan      = VALUES(jumlah_kemasan),
            jumlah_berat        = VALUES(jumlah_berat),
            harga               = VALUES(harga),
            jumlah_hutang       = VALUES(jumlah_hutang),
            sisa_hutang         = VALUES(sisa_hutang),
            keterangan          = VALUES(keterangan),
            tanggal             = VALUES(tanggal),
            status              = VALUES(status),
            tanggal_lunas       = VALUES(tanggal_lunas)
    ");
    if ($sync) {
        $sync->bind_param(
            "iisiisssssssssss",
            $row['id'],
            $row['anggota_id'],
            $row['nama_petani'],
            $row['pengelolaan_obat_id'],
            $row['obat_id'],
            $row['nama_obat'],
            $row['jumlah_kemasan'],
            $row['jumlah_berat'],
            $harga,
            $jumlahHutang,
            $sisaHutang,
            $row['keterangan'],
            $tgl,
            $row['status'],
            $row['tanggal_lunas'],
            $row['created_at']
        );
        $sync->execute();
        $sync->close();
    }
}

/**
 * Sinkronkan jejak pembayaran otomatis untuk data hutang_obat yang sudah Lunas atau
 * sudah terbayar sebagian tetapi belum memiliki baris di pembayaran_hutang_obat.
 */
function ensureBackfillPembayaranHutangObat(mysqli $conn): void {
    $res = $conn->query("
        SELECT h.*
        FROM hutang_obat h
        LEFT JOIN pembayaran_hutang_obat b ON b.hutang_id = h.id
        WHERE b.id IS NULL
        ORDER BY h.id ASC
    ");
    if (!$res) return;

    while ($h = $res->fetch_assoc()) {
        $kNum     = parseKemasanInt($h['jumlah_kemasan'] ?? '');
        $hargaVal = parseRupiahFloat($h['harga'] ?? '');
        $totalVal = (int)round(parseRupiahFloat($h['jumlah_hutang'] ?? ''));
        if ($totalVal <= 0 && $kNum > 0 && $hargaVal > 0) {
            $totalVal = (int)round($kNum * $hargaVal);
        }
        $isLunas    = (($h['status'] ?? '') === 'Lunas');
        $rawSisaStr = trim((string)($h['sisa_hutang'] ?? ''));
        $sisaVal    = $isLunas ? 0 : (int)round(parseRupiahFloat($rawSisaStr));

        if (!$isLunas && ($rawSisaStr === '' || $sisaVal <= 0)) {
            $sisaVal = $totalVal;
        }

        if (!$isLunas && ($totalVal <= 0 || $sisaVal >= $totalVal)) {
            continue;
        }

        $hutangId    = (int)$h['id'];
        $anggotaId   = (int)$h['anggota_id'];
        $namaPetani  = formatTitleCase($h['nama_petani'] ?? '');
        $namaObat    = formatTitleCase($h['nama_obat'] ?? '');
        $defaultDate = !empty($h['tanggal_lunas'])
            ? substr((string)$h['tanggal_lunas'], 0, 10)
            : (!empty($h['tanggal']) ? (string)$h['tanggal'] : substr((string)($h['created_at'] ?? date('Y-m-d')), 0, 10));
        $dicatatOleh = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? 'Admin'));
        $ket         = (string)($h['keterangan'] ?? '');

        $nomBayar = max(0, $totalVal - $sisaVal);
        $sisaSblm = $totalVal;
        $stStep   = $isLunas ? 'Lunas' : 'Belum Lunas';
        $ketStep  = formatTitleCase(($ket !== '' && $ket !== '-') ? $ket : 'Pelunasan Hutang Obat');
        $metode   = 'Tunai';
        $ke       = 1;

        $ins = $conn->prepare("
            INSERT INTO pembayaran_hutang_obat (
                hutang_id, anggota_id, nama_petani, nama_obat, pembayaran_ke,
                tanggal_bayar, total_hutang, sisa_sebelum, jumlah_bayar, sisa_hutang,
                metode_bayar, status, keterangan, dicatat_oleh
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($ins) {
            $ins->bind_param(
                "iissisiiiissss",
                $hutangId, $anggotaId, $namaPetani, $namaObat, $ke,
                $defaultDate, $totalVal, $sisaSblm, $nomBayar, $sisaVal,
                $metode, $stStep, $ketStep, $dicatatOleh
            );
            $ins->execute();
            $ins->close();
        }
    }
}

// Pastikan seluruh baris hutang_obat selalu tersinkron di riwayat_hutang_obat
$conn->query("
    INSERT INTO riwayat_hutang_obat (
        hutang_id, anggota_id, nama_petani, pengelolaan_obat_id,
        obat_id, nama_obat, jumlah_kemasan, jumlah_berat,
        harga, jumlah_hutang, sisa_hutang,
        keterangan, tanggal, status, tanggal_lunas, created_at
    )
    SELECT
        id, anggota_id, nama_petani, pengelolaan_obat_id,
        obat_id, nama_obat, jumlah_kemasan, jumlah_berat,
        harga, jumlah_hutang, sisa_hutang,
        keterangan, tanggal, status, tanggal_lunas, created_at
    FROM hutang_obat
    ON DUPLICATE KEY UPDATE
        anggota_id          = VALUES(anggota_id),
        nama_petani         = VALUES(nama_petani),
        pengelolaan_obat_id = VALUES(pengelolaan_obat_id),
        obat_id             = VALUES(obat_id),
        nama_obat           = VALUES(nama_obat),
        jumlah_kemasan      = VALUES(jumlah_kemasan),
        jumlah_berat        = VALUES(jumlah_berat),
        harga               = VALUES(harga),
        jumlah_hutang       = VALUES(jumlah_hutang),
        sisa_hutang         = VALUES(sisa_hutang),
        keterangan          = VALUES(keterangan),
        tanggal             = VALUES(tanggal),
        status              = VALUES(status),
        tanggal_lunas       = VALUES(tanggal_lunas)
");

// Backfill item hutang lama ke tabel hutang_obat_items jika belum tercatat
$conn->query("
    INSERT INTO hutang_obat_items (
        hutang_id, pengelolaan_obat_id, obat_id,
        nama_obat, jumlah_kemasan, jumlah_berat,
        harga, subtotal, created_at
    )
    SELECT
        h.id, h.pengelolaan_obat_id, h.obat_id,
        COALESCE(m.nama_produk, o.nama_obat, h.nama_obat),
        h.jumlah_kemasan, h.jumlah_berat,
        COALESCE(h.harga, 'Rp 0'),
        COALESCE(h.jumlah_hutang, 'Rp 0'),
        h.created_at
    FROM hutang_obat h
    LEFT JOIN hutang_obat_items hoi ON hoi.hutang_id = h.id
    LEFT JOIN pengelolaan_obat o ON h.pengelolaan_obat_id = o.id
    LEFT JOIN master_produk m ON COALESCE(o.obat_id, h.obat_id) = m.id AND m.jenis = 'Obat'
    WHERE hoi.id IS NULL
");

ensureBackfillPembayaranHutangObat($conn);
