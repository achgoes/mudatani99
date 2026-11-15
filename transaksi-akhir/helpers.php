<?php
/**
 * mudatani99 - Helper & Database Migration Transaksi Akhir
 * File: transaksi-akhir/helpers.php
 */

if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once ROOT_PATH . '/config/koneksi.php';
}
mysqli_report(MYSQLI_REPORT_OFF);

// 1. Pastikan tabel prasyarat tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `anggota_petani` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_petani` VARCHAR(100) NOT NULL,
    `alamat` VARCHAR(255) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->query("CREATE TABLE IF NOT EXISTS `master_produk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_produk` VARCHAR(100) NOT NULL,
    `jenis` ENUM('Obat', 'Pupuk', 'Tanaman') NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->query("CREATE TABLE IF NOT EXISTS `setoran_petani` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->query("CREATE TABLE IF NOT EXISTS `setoran_petani_items` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 2. Pastikan tabel transaksi_akhir tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `transaksi_akhir` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `setoran_id` INT(11) NOT NULL,
    `anggota_id` INT(11) DEFAULT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `berat_setoran` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `rapaksi` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `berat_akhir` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `harga_per_kg` BIGINT NOT NULL DEFAULT 0,
    `jumlah_uang` BIGINT NOT NULL DEFAULT 0,
    `tanggal` DATE NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_kode_transaksi_akhir` (`kode_transaksi`),
    UNIQUE KEY `uniq_transaksi_akhir_setoran` (`setoran_id`),
    KEY `idx_ta_anggota` (`anggota_id`),
    KEY `idx_ta_tanggal` (`tanggal`),
    CONSTRAINT `fk_ta_setoran` FOREIGN KEY (`setoran_id`) REFERENCES `setoran_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ta_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

/**
 * Format teks menjadi Title Case.
 */
function formatTitleCaseTa(?string $text): string {
    $clean = trim(preg_replace('/\s+/', ' ', (string)($text ?? '')));
    if ($clean === '' || $clean === '-') {
        return $clean;
    }
    return mb_convert_case(mb_strtolower($clean, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

/**
 * Ekstrak nilai desimal dari string/angka berat (contoh: "120.5 Kg" atau "12,5" -> 12.5).
 */
function parseDecimalTa($val): float {
    $raw = trim((string)($val ?? ''));
    if ($raw === '') {
        return 0.0;
    }
    if (preg_match('/([0-9]+([.,][0-9]+)?)/', $raw, $m)) {
        return (float)str_replace(',', '.', $m[1]);
    }
    return 0.0;
}

/**
 * Format angka berat menjadi string bersatuan "Kg".
 */
function formatBeratTaStr(float $num): string {
    $clean = max(0.0, round($num, 2));
    if (floor($clean) == $clean) {
        return number_format((int)$clean, 0, ',', '.') . ' Kg';
    }
    $dec = rtrim(rtrim(number_format($clean, 2, ',', '.'), '0'), ',');
    return $dec . ' Kg';
}

/**
 * Format angka berat polos tanpa pemisah ribuan untuk input angka.
 */
function formatBeratPlainTa(float $num): string {
    $clean = max(0.0, round($num, 2));
    if (floor($clean) == $clean) {
        return (string)(int)$clean;
    }
    return rtrim(rtrim(number_format($clean, 2, '.', ''), '0'), '.');
}

/**
 * Ekstrak angka bulat Rupiah dari input string (contoh: "Rp 5.500" -> 5500).
 */
function parseRupiahIntTa($val): int {
    $clean = preg_replace('/[^0-9]/', '', (string)($val ?? ''));
    return ($clean !== '') ? (int)$clean : 0;
}

/**
 * Format angka menjadi string Rupiah (contoh: 5500 -> "Rp 5.500").
 */
function formatRupiahTaStr($val): string {
    $num = is_numeric($val) ? (int)round((float)$val) : parseRupiahIntTa($val);
    return 'Rp ' . number_format(max(0, $num), 0, ',', '.');
}

/**
 * Membuat kode transaksi akhir unik (contoh: TA-20261113-0001).
 */
function generateKodeTransaksiAkhir(mysqli $conn, string $tanggal): string {
    $tglCode = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ? str_replace('-', '', $tanggal) : date('Ymd');
    $prefix  = "TA-{$tglCode}-";
    $like    = $prefix . '%';
    $nextSeq = 1;

    $stmt = $conn->prepare("SELECT kode_transaksi FROM transaksi_akhir WHERE kode_transaksi LIKE ? ORDER BY kode_transaksi DESC LIMIT 1");
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
 * Mengambil seluruh item tanaman per setoran_id untuk info detail & cetak.
 */
function getSetoranItemsGroupedForTa(mysqli $conn): array {
    $grouped = [];
    $res = $conn->query("
        SELECT i.id, i.setoran_id, i.tanaman_id,
               COALESCE(m.nama_produk, i.nama_tanaman) AS nama_tanaman,
               i.berat
        FROM setoran_petani_items i
        LEFT JOIN master_produk m ON i.tanaman_id = m.id AND m.jenis = 'Tanaman'
        ORDER BY i.setoran_id ASC, i.id ASC
    ");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $sid = (int)$row['setoran_id'];
            $bNum = parseDecimalTa($row['berat']);
            $grouped[$sid][] = [
                'nama_tanaman' => formatTitleCaseTa($row['nama_tanaman'] ?? '-'),
                'berat'        => formatBeratTaStr($bNum),
                'berat_num'    => $bNum,
            ];
        }
    }
    return $grouped;
}
