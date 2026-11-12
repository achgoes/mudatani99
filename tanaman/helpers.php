<?php
/**
 * mudatani99 - Helper & Database Migration / Synchronization Tanaman
 * File: tanaman/helpers.php
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

// 3. Pastikan tabel pengelolaan_tanaman tersedia di database dengan kolom jumlah_kemasan
$conn->query("CREATE TABLE IF NOT EXISTS `pengelolaan_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tanaman_inv_id` (`tanaman_id`),
    CONSTRAINT `fk_pengelolaan_tanaman_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Migrasi otomatis jika tabel lama masih menggunakan kolom jumlah_karung
$colOld = $conn->query("SHOW COLUMNS FROM `pengelolaan_tanaman` LIKE 'jumlah_karung'");
if ($colOld && $colOld->num_rows > 0) {
    $conn->query("ALTER TABLE `pengelolaan_tanaman` CHANGE COLUMN `jumlah_karung` `jumlah_kemasan` VARCHAR(50) NOT NULL");
}

// 4. Pastikan tabel hutang_tanaman tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `hutang_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
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
    PRIMARY KEY (`id`),
    KEY `idx_hutang_tanaman_anggota` (`anggota_id`),
    KEY `idx_hutang_tanaman_pengelolaan` (`pengelolaan_tanaman_id`),
    KEY `idx_hutang_tanaman_master` (`tanaman_id`),
    CONSTRAINT `fk_hutang_tanaman_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_tanaman_pengelolaan` FOREIGN KEY (`pengelolaan_tanaman_id`) REFERENCES `pengelolaan_tanaman` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_tanaman_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 5. Pastikan tabel riwayat_hutang_tanaman tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `riwayat_hutang_tanaman` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 6. Pastikan tabel pembayaran_hutang_tanaman tersedia (1 baris = 1 kali pembayaran / cicilan)
$conn->query("CREATE TABLE IF NOT EXISTS `pembayaran_hutang_tanaman` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 7. Pastikan tabel hutang_tanaman_items tersedia (1 transaksi hutang bisa memiliki beberapa item tanaman)
$conn->query("CREATE TABLE IF NOT EXISTS `hutang_tanaman_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `pengelolaan_tanaman_id` INT(11) DEFAULT NULL,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `subtotal` VARCHAR(50) NOT NULL DEFAULT 'Rp 0',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_hti_hutang` (`hutang_id`),
    KEY `idx_hti_pengelolaan` (`pengelolaan_tanaman_id`),
    KEY `idx_hti_tanaman` (`tanaman_id`),
    CONSTRAINT `fk_hti_hutang` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_tanaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hti_pengelolaan` FOREIGN KEY (`pengelolaan_tanaman_id`) REFERENCES `pengelolaan_tanaman` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hti_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 8. Pastikan tabel penjualan_tanaman tersedia (berdiri sendiri, terpisah dari hutang piutang)
$conn->query("CREATE TABLE IF NOT EXISTS `penjualan_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `anggota_id` INT(11) DEFAULT NULL,
    `nama_pembeli` VARCHAR(100) NOT NULL,
    `alamat_pembeli` VARCHAR(255) DEFAULT '-',
    `nama_tanaman` VARCHAR(255) NOT NULL,
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
    UNIQUE KEY `uniq_kode_penjualan_tanaman` (`kode_transaksi`),
    KEY `idx_penjualan_tanaman_anggota` (`anggota_id`),
    KEY `idx_penjualan_tanaman_tanggal` (`tanggal_jual`),
    CONSTRAINT `fk_penjualan_tanaman_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 9. Pastikan tabel penjualan_tanaman_items tersedia (detail item tanaman terjual)
$conn->query("CREATE TABLE IF NOT EXISTS `penjualan_tanaman_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `penjualan_id` INT(11) NOT NULL,
    `pengelolaan_tanaman_id` INT(11) DEFAULT NULL,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL,
    `jumlah_kemasan` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga_satuan` BIGINT NOT NULL DEFAULT 0,
    `subtotal` BIGINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pti_penjualan` (`penjualan_id`),
    KEY `idx_pti_pengelolaan` (`pengelolaan_tanaman_id`),
    KEY `idx_pti_tanaman` (`tanaman_id`),
    CONSTRAINT `fk_pti_penjualan` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan_tanaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pti_pengelolaan` FOREIGN KEY (`pengelolaan_tanaman_id`) REFERENCES `pengelolaan_tanaman` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_pti_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 10. Pastikan tabel riwayat_penjualan_tanaman tersedia (log aktivitas penjualan)
$conn->query("CREATE TABLE IF NOT EXISTS `riwayat_penjualan_tanaman` (
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
    KEY `idx_rpt_penjualan` (`penjualan_id`),
    KEY `idx_rpt_tanggal` (`tanggal_jual`),
    KEY `idx_rpt_aksi` (`aksi`),
    CONSTRAINT `fk_rpt_penjualan` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan_tanaman` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 11. Pastikan tabel riwayat_stok_tanaman tersedia
$conn->query("CREATE TABLE IF NOT EXISTS `riwayat_stok_tanaman` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pengelolaan_tanaman_id` INT(11) DEFAULT NULL,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL,
    `sumber` VARCHAR(50) NOT NULL DEFAULT 'Kelola Stok',
    `aksi` VARCHAR(60) NOT NULL DEFAULT 'Tambah Stok',
    `kemasan_sebelum` VARCHAR(50) NOT NULL DEFAULT '0 Pcs',
    `berat_sebelum` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `perubahan_kemasan` VARCHAR(50) NOT NULL DEFAULT '0 Pcs',
    `perubahan_berat` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `kemasan_sesudah` VARCHAR(50) NOT NULL DEFAULT '0 Pcs',
    `berat_sesudah` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rst_pengelolaan` (`pengelolaan_tanaman_id`),
    KEY `idx_rst_tanaman` (`tanaman_id`),
    KEY `idx_rst_sumber` (`sumber`),
    KEY `idx_rst_created` (`created_at`),
    CONSTRAINT `fk_rst_pengelolaan` FOREIGN KEY (`pengelolaan_tanaman_id`) REFERENCES `pengelolaan_tanaman` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_rst_master` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Backfill stok awal satu kali jika riwayat_stok_tanaman masih kosong
$conn->query("
    INSERT INTO `riwayat_stok_tanaman` (
        `pengelolaan_tanaman_id`, `tanaman_id`, `nama_tanaman`, `sumber`, `aksi`,
        `kemasan_sebelum`, `berat_sebelum`, `perubahan_kemasan`, `perubahan_berat`,
        `kemasan_sesudah`, `berat_sesudah`, `keterangan`, `dicatat_oleh`, `created_at`
    )
    SELECT
        t.`id`,
        t.`tanaman_id`,
        COALESCE(m.`nama_produk`, t.`nama_tanaman`),
        'Kelola Stok',
        'Stok Awal',
        '0 Pcs',
        '0 Kg',
        CONCAT('+', t.`jumlah_kemasan`),
        CONCAT('+', t.`jumlah_berat`),
        t.`jumlah_kemasan`,
        t.`jumlah_berat`,
        COALESCE(NULLIF(t.`keterangan`, ''), '-'),
        'Sistem',
        t.`created_at`
    FROM `pengelolaan_tanaman` t
    LEFT JOIN `master_produk` m ON t.`tanaman_id` = m.`id` AND m.`jenis` = 'Tanaman'
    WHERE NOT EXISTS (SELECT 1 FROM `riwayat_stok_tanaman` LIMIT 1)
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
 * Ekstrak nilai angka bulat dari string jumlah kemasan (contoh: "30 Pcs" -> 30)
 */
function parseKemasanInt(?string $val): int {
    if (preg_match('/([0-9]+([.,][0-9]+)?)/', (string)($val ?? ''), $m)) {
        return (int)round((float)str_replace(',', '.', $m[1]));
    }
    return 0;
}

/**
 * Ekstrak nilai float dari string jumlah berat (contoh: "750 Kg" -> 750.0)
 */
function parseBeratFloat(?string $val): float {
    if (preg_match('/([0-9]+([.,][0-9]+)?)/', (string)($val ?? ''), $m)) {
        return (float)str_replace(',', '.', $m[1]);
    }
    return 0.0;
}

/**
 * Format angka kemasan tanaman menjadi string bersatuan "Pcs"
 */
function formatKemasanStr(int $num): string {
    return max(0, $num) . ' Pcs';
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
 * Format selisih kemasan bertanda (+/-) untuk log riwayat stok tanaman.
 */
function formatDeltaKemasanStr(int $delta): string {
    if ($delta > 0) return '+' . $delta . ' Pcs';
    if ($delta < 0) return $delta . ' Pcs';
    return '0 Pcs';
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
 * Mencatat aktivitas perubahan stok ke tabel riwayat_stok_tanaman.
 */
function recordRiwayatStokTanaman(
    mysqli $conn,
    ?int $pengelolaanId,
    ?int $tanamanId,
    string $namaTanaman,
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
    $cleanNama   = mb_substr(formatTitleCase($namaTanaman), 0, 100, 'UTF-8');
    $cleanKet    = mb_substr(trim($keterangan) !== '' ? formatTitleCase($keterangan) : '-', 0, 255, 'UTF-8');

    $stmt = $conn->prepare("
        INSERT INTO riwayat_stok_tanaman (
            pengelolaan_tanaman_id, tanaman_id, nama_tanaman, sumber, aksi,
            kemasan_sebelum, berat_sebelum, perubahan_kemasan, perubahan_berat,
            kemasan_sesudah, berat_sesudah, keterangan, dicatat_oleh
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if ($stmt) {
        $stmt->bind_param(
            "iisssssssssss",
            $pengelolaanId,
            $tanamanId,
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
 * Sesuaikan stok jumlah_kemasan & jumlah_berat pada tabel pengelolaan_tanaman.
 * $deltaKemasan > 0 menambah/mengembalikan stok, $deltaKemasan < 0 mengurangi stok.
 */
function adjustStokPengelolaanTanaman(mysqli $conn, ?int $pengelolaanId, ?int $tanamanId, int $deltaKemasan, float $deltaBerat, array $logMeta = []): bool {
    $targetRow = null;

    if ($pengelolaanId && $pengelolaanId > 0) {
        $stmt = $conn->prepare("
            SELECT t.id, t.tanaman_id, COALESCE(m.nama_produk, t.nama_tanaman) AS nama_tanaman, t.jumlah_kemasan, t.jumlah_berat
            FROM pengelolaan_tanaman t
            LEFT JOIN master_produk m ON t.tanaman_id = m.id AND m.jenis = 'Tanaman'
            WHERE t.id = ? LIMIT 1
        ");
        if ($stmt) {
            $stmt->bind_param("i", $pengelolaanId);
            $stmt->execute();
            $res = $stmt->get_result();
            $targetRow = $res ? $res->fetch_assoc() : null;
            $stmt->close();
        }
    }

    if (!$targetRow && $tanamanId && $tanamanId > 0) {
        $stmt = $conn->prepare("
            SELECT t.id, t.tanaman_id, COALESCE(m.nama_produk, t.nama_tanaman) AS nama_tanaman, t.jumlah_kemasan, t.jumlah_berat
            FROM pengelolaan_tanaman t
            LEFT JOIN master_produk m ON t.tanaman_id = m.id AND m.jenis = 'Tanaman'
            WHERE t.tanaman_id = ? ORDER BY t.id ASC LIMIT 1
        ");
        if ($stmt) {
            $stmt->bind_param("i", $tanamanId);
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
    $currBerat   = parseBeratFloat($targetRow['jumlah_berat']);

    // Jika berat belum diisi tetapi kemasan berubah, hitung proporsional dari berat per kemasan
    if ($deltaBerat == 0.0 && $deltaKemasan != 0 && $currKemasan > 0) {
        $beratPerKemasan = $currBerat / $currKemasan;
        $deltaBerat = $deltaKemasan * $beratPerKemasan;
    }

    $newKemasanNum = max(0, $currKemasan + $deltaKemasan);
    $newBeratNum   = max(0.0, $currBerat + $deltaBerat);
    $newKemasanStr = formatKemasanStr($newKemasanNum);
    $newBeratStr   = formatBeratStr($newBeratNum);
    $targetId      = (int)$targetRow['id'];
    $targetTanaman = !empty($targetRow['tanaman_id']) ? (int)$targetRow['tanaman_id'] : $tanamanId;

    $upd = $conn->prepare("UPDATE pengelolaan_tanaman SET jumlah_kemasan = ?, jumlah_berat = ? WHERE id = ?");
    if ($upd) {
        $upd->bind_param("ssi", $newKemasanStr, $newBeratStr, $targetId);
        $ok = $upd->execute();
        $upd->close();

        if ($ok && empty($logMeta['skip_log']) && ($deltaKemasan !== 0 || abs($deltaBerat) > 0.0001)) {
            $sumber = (string)($logMeta['sumber'] ?? 'Transaksi');
            $aksi   = (string)($logMeta['aksi'] ?? ($deltaKemasan < 0 ? 'Stok Keluar' : 'Stok Kembali'));
            $ket    = (string)($logMeta['keterangan'] ?? '-');
            recordRiwayatStokTanaman(
                $conn,
                $targetId,
                $targetTanaman,
                (string)$targetRow['nama_tanaman'],
                $sumber,
                $aksi,
                formatKemasanStr($currKemasan),
                formatBeratStr($currBerat),
                formatDeltaKemasanStr($newKemasanNum - $currKemasan),
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
 * Mengambil seluruh baris item tanaman milik satu transaksi hutang dari hutang_tanaman_items.
 */
function getHutangTanamanItemsByHutangId(mysqli $conn, int $hutangId): array {
    $items = [];
    if ($hutangId <= 0) {
        return $items;
    }

    $stmt = $conn->prepare("
        SELECT i.id, i.hutang_id, i.pengelolaan_tanaman_id, i.tanaman_id,
               COALESCE(m.nama_produk, t.nama_tanaman, i.nama_tanaman) AS nama_tanaman,
               i.jumlah_kemasan, i.jumlah_berat,
               COALESCE(i.harga, 'Rp 0') AS harga,
               COALESCE(i.subtotal, 'Rp 0') AS subtotal
        FROM hutang_tanaman_items i
        LEFT JOIN pengelolaan_tanaman t ON i.pengelolaan_tanaman_id = t.id
        LEFT JOIN master_produk m ON COALESCE(t.tanaman_id, i.tanaman_id) = m.id AND m.jenis = 'Tanaman'
        WHERE i.hutang_id = ?
        ORDER BY i.id ASC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $hutangId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($res && ($row = $res->fetch_assoc())) {
            $items[] = normalizeHutangTanamanItemRow($row);
        }
        $stmt->close();
    }

    return $items;
}

/**
 * Mengambil seluruh item tanaman untuk semua transaksi hutang, dikelompokkan per hutang_id.
 */
function getAllHutangTanamanItemsGrouped(mysqli $conn): array {
    $grouped = [];
    $res = $conn->query("
        SELECT i.id, i.hutang_id, i.pengelolaan_tanaman_id, i.tanaman_id,
               COALESCE(m.nama_produk, t.nama_tanaman, i.nama_tanaman) AS nama_tanaman,
               i.jumlah_kemasan, i.jumlah_berat,
               COALESCE(i.harga, 'Rp 0') AS harga,
               COALESCE(i.subtotal, 'Rp 0') AS subtotal
        FROM hutang_tanaman_items i
        LEFT JOIN pengelolaan_tanaman t ON i.pengelolaan_tanaman_id = t.id
        LEFT JOIN master_produk m ON COALESCE(t.tanaman_id, i.tanaman_id) = m.id AND m.jenis = 'Tanaman'
        ORDER BY i.hutang_id ASC, i.id ASC
    ");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $hid = (int)$row['hutang_id'];
            $grouped[$hid][] = normalizeHutangTanamanItemRow($row);
        }
    }

    return $grouped;
}

/**
 * Menormalkan satu baris item hutang tanaman untuk tampilan dan kalkulasi.
 */
function normalizeHutangTanamanItemRow(array $row): array {
    $kNum     = parseKemasanInt($row['jumlah_kemasan'] ?? '');
    $bNum     = parseBeratFloat($row['jumlah_berat'] ?? '');
    $hargaStr = formatRupiahStr($row['harga'] ?? 'Rp 0');
    $hargaNum = parseRupiahFloat($hargaStr);
    $subRaw   = parseRupiahFloat($row['subtotal'] ?? 'Rp 0');
    $subStr   = ($subRaw > 0) ? formatRupiahStr($subRaw) : formatRupiahStr($kNum * $hargaNum);

    return [
        'id'                     => (int)($row['id'] ?? 0),
        'hutang_id'              => (int)($row['hutang_id'] ?? 0),
        'pengelolaan_tanaman_id' => !empty($row['pengelolaan_tanaman_id']) ? (int)$row['pengelolaan_tanaman_id'] : null,
        'tanaman_id'             => !empty($row['tanaman_id']) ? (int)$row['tanaman_id'] : null,
        'nama_tanaman'           => formatTitleCase($row['nama_tanaman'] ?? '-'),
        'jumlah_kemasan'         => formatTitleCase($row['jumlah_kemasan'] ?? formatKemasanStr($kNum)),
        'jumlah_berat'           => formatTitleCase($row['jumlah_berat'] ?? formatBeratStr($bNum)),
        'kemasan_num'            => $kNum,
        'berat_num'              => $bNum,
        'harga'                  => $hargaStr,
        'harga_num'              => $hargaNum,
        'subtotal'               => $subStr,
        'subtotal_num'           => parseRupiahFloat($subStr),
    ];
}

/**
 * Mengganti daftar item tanaman pada hutang_tanaman_items untuk transaksi tertentu.
 */
function saveHutangTanamanItems(mysqli $conn, int $hutangId, array $items): void {
    $del = $conn->prepare("DELETE FROM hutang_tanaman_items WHERE hutang_id = ?");
    if ($del) {
        $del->bind_param("i", $hutangId);
        $del->execute();
        $del->close();
    }

    $ins = $conn->prepare("
        INSERT INTO hutang_tanaman_items (
            hutang_id, pengelolaan_tanaman_id, tanaman_id,
            nama_tanaman, jumlah_kemasan, jumlah_berat,
            harga, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$ins) {
        return;
    }

    foreach ($items as $item) {
        $tId      = $item['pengelolaan_tanaman_id'];
        $mId      = $item['tanaman_id'];
        $nTanaman = $item['nama_tanaman'];
        $jKem     = $item['jumlah_kemasan'];
        $jBer     = $item['jumlah_berat'];
        $hrg      = $item['harga'];
        $sub      = $item['subtotal'];
        $ins->bind_param("iiisssss", $hutangId, $tId, $mId, $nTanaman, $jKem, $jBer, $hrg, $sub);
        $ins->execute();
    }
    $ins->close();
}

/**
 * Menyesuaikan stok pengelolaan_tanaman untuk sekumpulan item ($sign = -1 mengurangi, +1 mengembalikan).
 */
function adjustTanamanItemsStock(mysqli $conn, array $items, int $sign, array $logMeta = []): void {
    foreach ($items as $item) {
        $tId = !empty($item['pengelolaan_tanaman_id']) ? (int)$item['pengelolaan_tanaman_id'] : null;
        $mId = !empty($item['tanaman_id']) ? (int)$item['tanaman_id'] : null;
        $k   = (int)($item['kemasan_num'] ?? parseKemasanInt($item['jumlah_kemasan'] ?? ''));
        $b   = (float)($item['berat_num'] ?? parseBeratFloat($item['jumlah_berat'] ?? ''));
        if ($k > 0 || $b > 0) {
            adjustStokPengelolaanTanaman($conn, $tId, $mId, $sign * $k, $sign * $b, $logMeta);
        }
    }
}

/**
 * Menghitung kuota stok lama per pengelolaan_tanaman_id untuk validasi edit transaksi.
 */
function buildTanamanStockAllowanceFromItems(mysqli $conn, array $items): array {
    $allowance = [];
    foreach ($items as $ti) {
        $tId = !empty($ti['pengelolaan_tanaman_id']) ? (int)$ti['pengelolaan_tanaman_id'] : 0;
        if ($tId <= 0 && !empty($ti['tanaman_id'])) {
            $qOldStok = $conn->prepare("SELECT id FROM pengelolaan_tanaman WHERE tanaman_id = ? ORDER BY id ASC LIMIT 1");
            if ($qOldStok) {
                $mId = (int)$ti['tanaman_id'];
                $qOldStok->bind_param("i", $mId);
                $qOldStok->execute();
                $rOldStok = $qOldStok->get_result();
                if ($rOldStok && ($rowOldStok = $rOldStok->fetch_assoc())) {
                    $tId = (int)$rowOldStok['id'];
                }
                $qOldStok->close();
            }
        }
        if ($tId > 0) {
            $allowance[$tId] = [
                'kemasan' => ($allowance[$tId]['kemasan'] ?? 0) + (int)($ti['kemasan_num'] ?? 0),
                'berat'   => ($allowance[$tId]['berat'] ?? 0.0) + (float)($ti['berat_num'] ?? 0.0),
            ];
        }
    }
    return $allowance;
}

/**
 * Mem-parsing, memvalidasi, dan mengecek ketersediaan stok seluruh item tanaman pada form transaksi.
 */
function parseTanamanTransactionItems(mysqli $conn, array $post, array $oldStockAllowance = []): array {
    $rawIds      = $post['item_pengelolaan_tanaman_id'] ?? null;
    $rawKemasans = $post['item_jumlah_kemasan'] ?? null;
    $rawBerats   = $post['item_jumlah_berat'] ?? null;
    $rawHargas   = $post['item_harga'] ?? null;

    if (!is_array($rawIds)) {
        $rawIds      = [$post['pengelolaan_tanaman_id'] ?? ''];
        $rawKemasans = [$post['jumlah_kemasan'] ?? ''];
        $rawBerats   = [$post['jumlah_berat'] ?? ''];
        $rawHargas   = [$post['harga'] ?? ($post['jumlah_harga'] ?? '')];
    }

    $rowCount = count($rawIds);
    $items    = [];
    $emptyRes = [
        'error'                => '',
        'items'                => [],
        'total_kemasan'        => 0,
        'total_berat'          => 0.0,
        'total_nominal'        => 0.0,
        'summary_nama_tanaman' => '',
    ];

    if ($rowCount === 0) {
        $emptyRes['error'] = 'Minimal satu item tanaman wajib diisi.';
        return $emptyRes;
    }

    $stokCache   = [];
    $requestedBy = [];
    $stokStmt    = $conn->prepare("
        SELECT t.id, t.tanaman_id, COALESCE(m.nama_produk, t.nama_tanaman) AS nama_tanaman, t.jumlah_kemasan, t.jumlah_berat
        FROM pengelolaan_tanaman t
        LEFT JOIN master_produk m ON t.tanaman_id = m.id AND m.jenis = 'Tanaman'
        WHERE t.id = ? LIMIT 1
    ");

    for ($i = 0; $i < $rowCount; $i++) {
        $tId        = (int)($rawIds[$i] ?? 0);
        $reqKemasan = parseKemasanInt((string)($rawKemasans[$i] ?? ''));
        $reqBerat   = parseBeratFloat((string)($rawBerats[$i] ?? ''));
        $hargaStr   = formatRupiahStr($rawHargas[$i] ?? '');
        $hargaFloat = parseRupiahFloat($hargaStr);

        if ($tId <= 0 || $reqKemasan <= 0 || $reqBerat <= 0 || $hargaFloat <= 0) {
            if ($stokStmt) $stokStmt->close();
            $itemNo = $i + 1;
            $emptyRes['error'] = "Item tanaman ke-{$itemNo}: Nama Tanaman, Jumlah Kemasan (> 0 Pcs), Jumlah Berat (> 0), dan Harga Satuan (> 0) wajib diisi dengan benar.";
            return $emptyRes;
        }

        if (!isset($stokCache[$tId])) {
            $stokRow = null;
            if ($stokStmt) {
                $stokStmt->bind_param("i", $tId);
                $stokStmt->execute();
                $stokRes = $stokStmt->get_result();
                $stokRow = $stokRes ? $stokRes->fetch_assoc() : null;
            }
            if (!$stokRow) {
                if ($stokStmt) $stokStmt->close();
                $itemNo = $i + 1;
                $emptyRes['error'] = "Item tanaman ke-{$itemNo}: Data tanaman tidak ditemukan pada tabel Stok Tanaman.";
                return $emptyRes;
            }
            $stokCache[$tId] = $stokRow;
        }

        $stokRow = $stokCache[$tId];
        $requestedBy[$tId] = [
            'kemasan' => ($requestedBy[$tId]['kemasan'] ?? 0) + $reqKemasan,
            'berat'   => ($requestedBy[$tId]['berat'] ?? 0.0) + $reqBerat,
        ];

        $subtotalFloat = $reqKemasan * $hargaFloat;
        $items[] = [
            'pengelolaan_tanaman_id' => $tId,
            'tanaman_id'             => $stokRow['tanaman_id'] ? (int)$stokRow['tanaman_id'] : null,
            'nama_tanaman'           => formatTitleCase($stokRow['nama_tanaman']),
            'jumlah_kemasan'         => formatKemasanStr($reqKemasan),
            'jumlah_berat'           => formatBeratStr($reqBerat),
            'kemasan_num'            => $reqKemasan,
            'berat_num'              => $reqBerat,
            'harga'                  => $hargaStr,
            'harga_num'              => $hargaFloat,
            'subtotal'               => formatRupiahStr($subtotalFloat),
            'subtotal_float'         => $subtotalFloat,
        ];
    }

    if ($stokStmt) {
        $stokStmt->close();
    }

    foreach ($requestedBy as $tId => $req) {
        $stokRow        = $stokCache[$tId];
        $allowKem       = (int)($oldStockAllowance[$tId]['kemasan'] ?? 0);
        $allowBerat     = (float)($oldStockAllowance[$tId]['berat'] ?? 0.0);
        $maksKemasan    = parseKemasanInt($stokRow['jumlah_kemasan']) + $allowKem;
        $maksBerat      = parseBeratFloat($stokRow['jumlah_berat']) + $allowBerat;
        $namaTanamanLbl = formatTitleCase($stokRow['nama_tanaman']);

        if ($req['kemasan'] > $maksKemasan) {
            $emptyRes['error'] = "Stok kemasan untuk {$namaTanamanLbl} tidak mencukupi! Maksimal tersedia: {$maksKemasan} Pcs, diminta: {$req['kemasan']} Pcs.";
            return $emptyRes;
        }
        if ($req['berat'] > ($maksBerat + 0.01)) {
            $emptyRes['error'] = "Stok berat untuk {$namaTanamanLbl} tidak mencukupi! Maksimal tersedia: " . formatBeratStr($maksBerat) . ", diminta: " . formatBeratStr($req['berat']) . ".";
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
        if (!in_array($it['nama_tanaman'], $names, true)) {
            $names[] = $it['nama_tanaman'];
        }
    }

    return [
        'error'                => null,
        'items'                => $items,
        'total_kemasan'        => $totalKemasan,
        'total_berat'          => $totalBerat,
        'total_nominal'        => $totalNominal,
        'summary_nama_tanaman' => mb_substr(implode(', ', $names), 0, 255, 'UTF-8'),
    ];
}

/**
 * Menormalkan satu baris item penjualan tanaman untuk tampilan dan kalkulasi.
 */
function normalizePenjualanTanamanItemRow(array $row): array {
    $kNum     = parseKemasanInt($row['jumlah_kemasan'] ?? '');
    $bNum     = parseBeratFloat($row['jumlah_berat'] ?? '');
    $hargaInt = (int)($row['harga_satuan'] ?? 0);
    $subInt   = (int)($row['subtotal'] ?? ($kNum * $hargaInt));

    return [
        'id'                     => (int)($row['id'] ?? 0),
        'penjualan_id'           => (int)($row['penjualan_id'] ?? 0),
        'pengelolaan_tanaman_id' => !empty($row['pengelolaan_tanaman_id']) ? (int)$row['pengelolaan_tanaman_id'] : null,
        'tanaman_id'             => !empty($row['tanaman_id']) ? (int)$row['tanaman_id'] : null,
        'nama_tanaman'           => formatTitleCase($row['nama_tanaman'] ?? '-'),
        'jumlah_kemasan'         => formatTitleCase($row['jumlah_kemasan'] ?? formatKemasanStr($kNum)),
        'jumlah_berat'           => formatTitleCase($row['jumlah_berat'] ?? formatBeratStr($bNum)),
        'kemasan_num'            => $kNum,
        'berat_num'              => $bNum,
        'harga_satuan'           => $hargaInt,
        'harga'                  => formatRupiahStr($hargaInt),
        'subtotal_num'           => $subInt,
        'subtotal'               => formatRupiahStr($subInt),
    ];
}

/**
 * Mengambil seluruh baris item tanaman milik satu transaksi penjualan.
 */
function getPenjualanTanamanItemsById(mysqli $conn, int $penjualanId): array {
    $items = [];
    if ($penjualanId <= 0) {
        return $items;
    }

    $stmt = $conn->prepare("
        SELECT i.id, i.penjualan_id, i.pengelolaan_tanaman_id, i.tanaman_id,
               COALESCE(m.nama_produk, t.nama_tanaman, i.nama_tanaman) AS nama_tanaman,
               i.jumlah_kemasan, i.jumlah_berat, i.harga_satuan, i.subtotal
        FROM penjualan_tanaman_items i
        LEFT JOIN pengelolaan_tanaman t ON i.pengelolaan_tanaman_id = t.id
        LEFT JOIN master_produk m ON COALESCE(t.tanaman_id, i.tanaman_id) = m.id AND m.jenis = 'Tanaman'
        WHERE i.penjualan_id = ?
        ORDER BY i.id ASC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $penjualanId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($res && ($row = $res->fetch_assoc())) {
            $items[] = normalizePenjualanTanamanItemRow($row);
        }
        $stmt->close();
    }

    return $items;
}

/**
 * Mengambil seluruh item penjualan tanaman dikelompokkan per penjualan_id.
 */
function getAllPenjualanTanamanItemsGrouped(mysqli $conn): array {
    $grouped = [];
    $res = $conn->query("
        SELECT i.id, i.penjualan_id, i.pengelolaan_tanaman_id, i.tanaman_id,
               COALESCE(m.nama_produk, t.nama_tanaman, i.nama_tanaman) AS nama_tanaman,
               i.jumlah_kemasan, i.jumlah_berat, i.harga_satuan, i.subtotal
        FROM penjualan_tanaman_items i
        LEFT JOIN pengelolaan_tanaman t ON i.pengelolaan_tanaman_id = t.id
        LEFT JOIN master_produk m ON COALESCE(t.tanaman_id, i.tanaman_id) = m.id AND m.jenis = 'Tanaman'
        ORDER BY i.penjualan_id ASC, i.id ASC
    ");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['penjualan_id'];
            $grouped[$pid][] = normalizePenjualanTanamanItemRow($row);
        }
    }

    return $grouped;
}

/**
 * Menyimpan ulang daftar item penjualan pada penjualan_tanaman_items.
 */
function savePenjualanTanamanItems(mysqli $conn, int $penjualanId, array $items): void {
    $del = $conn->prepare("DELETE FROM penjualan_tanaman_items WHERE penjualan_id = ?");
    if ($del) {
        $del->bind_param("i", $penjualanId);
        $del->execute();
        $del->close();
    }

    $ins = $conn->prepare("
        INSERT INTO penjualan_tanaman_items (
            penjualan_id, pengelolaan_tanaman_id, tanaman_id,
            nama_tanaman, jumlah_kemasan, jumlah_berat,
            harga_satuan, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$ins) {
        return;
    }

    foreach ($items as $item) {
        $tId      = $item['pengelolaan_tanaman_id'];
        $mId      = $item['tanaman_id'];
        $nTanaman = $item['nama_tanaman'];
        $jKem     = $item['jumlah_kemasan'];
        $jBer     = $item['jumlah_berat'];
        $hrg      = (int)round((float)($item['harga_num'] ?? 0));
        $sub      = (int)round((float)($item['subtotal_float'] ?? 0));
        $ins->bind_param("iiisssii", $penjualanId, $tId, $mId, $nTanaman, $jKem, $jBer, $hrg, $sub);
        $ins->execute();
    }
    $ins->close();
}

/**
 * Membuat kode transaksi penjualan tanaman unik (mis. PJT-20261112-0001).
 */
function generateKodePenjualanTanaman(mysqli $conn, string $tanggal): string {
    $tglCode = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ? str_replace('-', '', $tanggal) : date('Ymd');
    $prefix  = "PJT-{$tglCode}-";
    $like    = $prefix . '%';
    $nextSeq = 1;

    $stmt = $conn->prepare("SELECT kode_transaksi FROM penjualan_tanaman WHERE kode_transaksi LIKE ? ORDER BY id DESC LIMIT 1");
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
 * Mencatat jejak transaksi penjualan ke tabel riwayat_penjualan_tanaman.
 */
function recordRiwayatPenjualanTanaman(
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
        $parts[] = "{$it['nama_tanaman']} ({$it['jumlah_kemasan']} / {$it['jumlah_berat']} @ {$hargaLabel})";
    }
    $rincianItem = !empty($parts) ? implode('; ', $parts) : '-';
    $dicatatOleh = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));

    $stmt = $conn->prepare("
        INSERT INTO riwayat_penjualan_tanaman (
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
 * Hapus jejak catatan sistem "Bayar: Rp ..." dari kolom keterangan hutang tanaman.
 */
function stripHutangTanamanPaymentNote(?string $keterangan): string {
    $clean = trim((string)($keterangan ?? ''));
    if ($clean === '' || $clean === '-') {
        return '-';
    }
    $parts = preg_split('/(?:^|\s*\|\s*)Bayar:\s*Rp\s*[0-9.]+/i', $clean, 2);
    $base  = trim((string)($parts[0] ?? ''), " \t\n\r\0\x0B|");
    return ($base === '') ? '-' : $base;
}

/**
 * Pastikan baris pembayaran_hutang_tanaman sinkron saat hutang diubah menjadi Lunas atau diedit.
 */
function syncPembayaranOnHutangTanamanUpdate(mysqli $conn, int $hutangId, int $anggotaId, string $namaPetani, string $namaTanaman, int $totalHutangInt, int $sisaHutangInt, string $status, string $ketCatatan = ''): void {
    $updB = $conn->prepare("UPDATE pembayaran_hutang_tanaman SET anggota_id = ?, nama_petani = ?, nama_tanaman = ?, total_hutang = ? WHERE hutang_id = ?");
    if ($updB) {
        $updB->bind_param("issii", $anggotaId, $namaPetani, $namaTanaman, $totalHutangInt, $hutangId);
        $updB->execute();
        $updB->close();
    }

    if ($status === 'Belum Lunas' && ($totalHutangInt <= 0 || $sisaHutangInt >= $totalHutangInt)) {
        $delB = $conn->prepare("DELETE FROM pembayaran_hutang_tanaman WHERE hutang_id = ?");
        if ($delB) {
            $delB->bind_param("i", $hutangId);
            $delB->execute();
            $delB->close();
        }
        return;
    }

    $qRows = $conn->prepare("SELECT id, pembayaran_ke, jumlah_bayar, sisa_hutang, status FROM pembayaran_hutang_tanaman WHERE hutang_id = ? ORDER BY pembayaran_ke ASC, id ASC");
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
        ensureBackfillPembayaranHutangTanaman($conn);
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
            $ketStep     = formatTitleCase($ketCatatan !== '' ? $ketCatatan : 'Pelunasan Hutang Tanaman');
            $dicatatOleh = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));

            $ins = $conn->prepare("
                INSERT INTO pembayaran_hutang_tanaman (
                    hutang_id, anggota_id, nama_petani, nama_tanaman, pembayaran_ke,
                    tanggal_bayar, total_hutang, sisa_sebelum, jumlah_bayar, sisa_hutang,
                    metode_bayar, status, keterangan, dicatat_oleh
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($ins) {
                $sisaNol = 0;
                $ins->bind_param(
                    "iissisiiiissss",
                    $hutangId, $anggotaId, $namaPetani, $namaTanaman, $nextKe,
                    $tglBayar, $totalHutangInt, $kurangBayar, $kurangBayar, $sisaNol,
                    $metode, $stLunas, $ketStep, $dicatatOleh
                );
                $ins->execute();
                $ins->close();
            }
        } else {
            $updLast = $conn->prepare("UPDATE pembayaran_hutang_tanaman SET sisa_hutang = 0, status = 'Lunas' WHERE id = ?");
            if ($updLast) {
                $updLast->bind_param("i", $lastId);
                $updLast->execute();
                $updLast->close();
            }
        }
    } else {
        $updLast = $conn->prepare("UPDATE pembayaran_hutang_tanaman SET sisa_hutang = ?, status = 'Belum Lunas' WHERE id = ?");
        if ($updLast) {
            $updLast->bind_param("ii", $sisaHutangInt, $lastId);
            $updLast->execute();
            $updLast->close();
        }
    }
}

/**
 * Sinkronkan baris hutang_tanaman ke tabel riwayat_hutang_tanaman.
 */
function syncRiwayatHutangTanamanRow(mysqli $conn, int $hutangId): void {
    $stmt = $conn->prepare("SELECT * FROM hutang_tanaman WHERE id = ? LIMIT 1");
    if (!$stmt) return;
    $stmt->bind_param("i", $hutangId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        $del = $conn->prepare("DELETE FROM riwayat_hutang_tanaman WHERE hutang_id = ?");
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
        INSERT INTO riwayat_hutang_tanaman (
            hutang_id, anggota_id, nama_petani, pengelolaan_tanaman_id,
            tanaman_id, nama_tanaman, jumlah_kemasan, jumlah_berat, harga,
            jumlah_hutang, sisa_hutang,
            keterangan, tanggal, status, tanggal_lunas, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            anggota_id             = VALUES(anggota_id),
            nama_petani            = VALUES(nama_petani),
            pengelolaan_tanaman_id = VALUES(pengelolaan_tanaman_id),
            tanaman_id             = VALUES(tanaman_id),
            nama_tanaman           = VALUES(nama_tanaman),
            jumlah_kemasan         = VALUES(jumlah_kemasan),
            jumlah_berat           = VALUES(jumlah_berat),
            harga                  = VALUES(harga),
            jumlah_hutang          = VALUES(jumlah_hutang),
            sisa_hutang            = VALUES(sisa_hutang),
            keterangan             = VALUES(keterangan),
            tanggal                = VALUES(tanggal),
            status                 = VALUES(status),
            tanggal_lunas          = VALUES(tanggal_lunas)
    ");
    if ($sync) {
        $sync->bind_param(
            "iisiisssssssssss",
            $row['id'],
            $row['anggota_id'],
            $row['nama_petani'],
            $row['pengelolaan_tanaman_id'],
            $row['tanaman_id'],
            $row['nama_tanaman'],
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
 * Sinkronkan jejak pembayaran otomatis untuk data hutang_tanaman yang sudah Lunas atau
 * sudah terbayar sebagian tetapi belum memiliki baris di pembayaran_hutang_tanaman.
 */
function ensureBackfillPembayaranHutangTanaman(mysqli $conn): void {
    $res = $conn->query("
        SELECT h.*
        FROM hutang_tanaman h
        LEFT JOIN pembayaran_hutang_tanaman b ON b.hutang_id = h.id
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
        $namaTanaman = formatTitleCase($h['nama_tanaman'] ?? '');
        $defaultDate = !empty($h['tanggal_lunas'])
            ? substr((string)$h['tanggal_lunas'], 0, 10)
            : (!empty($h['tanggal']) ? (string)$h['tanggal'] : substr((string)($h['created_at'] ?? date('Y-m-d')), 0, 10));
        $dicatatOleh = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? 'Admin'));
        $ket         = (string)($h['keterangan'] ?? '');

        $nomBayar = max(0, $totalVal - $sisaVal);
        $sisaSblm = $totalVal;
        $stStep   = $isLunas ? 'Lunas' : 'Belum Lunas';
        $ketStep  = formatTitleCase(($ket !== '' && $ket !== '-') ? $ket : 'Pelunasan Hutang Tanaman');
        $metode   = 'Tunai';
        $ke       = 1;

        $ins = $conn->prepare("
            INSERT INTO pembayaran_hutang_tanaman (
                hutang_id, anggota_id, nama_petani, nama_tanaman, pembayaran_ke,
                tanggal_bayar, total_hutang, sisa_sebelum, jumlah_bayar, sisa_hutang,
                metode_bayar, status, keterangan, dicatat_oleh
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($ins) {
            $ins->bind_param(
                "iissisiiiissss",
                $hutangId, $anggotaId, $namaPetani, $namaTanaman, $ke,
                $defaultDate, $totalVal, $sisaSblm, $nomBayar, $sisaVal,
                $metode, $stStep, $ketStep, $dicatatOleh
            );
            $ins->execute();
            $ins->close();
        }
    }
}

// Pastikan seluruh baris hutang_tanaman selalu tersinkron di riwayat_hutang_tanaman
$conn->query("
    INSERT INTO riwayat_hutang_tanaman (
        hutang_id, anggota_id, nama_petani, pengelolaan_tanaman_id,
        tanaman_id, nama_tanaman, jumlah_kemasan, jumlah_berat,
        harga, jumlah_hutang, sisa_hutang,
        keterangan, tanggal, status, tanggal_lunas, created_at
    )
    SELECT
        id, anggota_id, nama_petani, pengelolaan_tanaman_id,
        tanaman_id, nama_tanaman, jumlah_kemasan, jumlah_berat,
        harga, jumlah_hutang, sisa_hutang,
        keterangan, tanggal, status, tanggal_lunas, created_at
    FROM hutang_tanaman
    ON DUPLICATE KEY UPDATE
        anggota_id             = VALUES(anggota_id),
        nama_petani            = VALUES(nama_petani),
        pengelolaan_tanaman_id = VALUES(pengelolaan_tanaman_id),
        tanaman_id             = VALUES(tanaman_id),
        nama_tanaman           = VALUES(nama_tanaman),
        jumlah_kemasan         = VALUES(jumlah_kemasan),
        jumlah_berat           = VALUES(jumlah_berat),
        harga                  = VALUES(harga),
        jumlah_hutang          = VALUES(jumlah_hutang),
        sisa_hutang            = VALUES(sisa_hutang),
        keterangan             = VALUES(keterangan),
        tanggal                = VALUES(tanggal),
        status                 = VALUES(status),
        tanggal_lunas          = VALUES(tanggal_lunas)
");

// Backfill item hutang lama ke tabel hutang_tanaman_items jika belum tercatat
$conn->query("
    INSERT INTO hutang_tanaman_items (
        hutang_id, pengelolaan_tanaman_id, tanaman_id,
        nama_tanaman, jumlah_kemasan, jumlah_berat,
        harga, subtotal, created_at
    )
    SELECT
        h.id, h.pengelolaan_tanaman_id, h.tanaman_id,
        COALESCE(m.nama_produk, t.nama_tanaman, h.nama_tanaman),
        h.jumlah_kemasan, h.jumlah_berat,
        COALESCE(h.harga, 'Rp 0'),
        COALESCE(h.jumlah_hutang, 'Rp 0'),
        h.created_at
    FROM hutang_tanaman h
    LEFT JOIN hutang_tanaman_items hti ON hti.hutang_id = h.id
    LEFT JOIN pengelolaan_tanaman t ON h.pengelolaan_tanaman_id = t.id
    LEFT JOIN master_produk m ON COALESCE(t.tanaman_id, h.tanaman_id) = m.id AND m.jenis = 'Tanaman'
    WHERE hti.id IS NULL
");

ensureBackfillPembayaranHutangTanaman($conn);
