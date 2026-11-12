<?php
/**
 * mudatani99 - Helper & Database Migration / Synchronization Pupuk
 * File: pupuk/helpers.php
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

// 3. Pastikan tabel pengelolaan_pupuk tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `pengelolaan_pupuk` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Cek apakah tabel hutang_pupuk baru pertama kali dibuat
$checkHutangTbl = $conn->query("SHOW TABLES LIKE 'hutang_pupuk'");
$isFirstTimeHutangTable = ($checkHutangTbl && $checkHutangTbl->num_rows === 0);

// 4. Pastikan tabel hutang_pupuk tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `hutang_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `pengelolaan_pupuk_id` INT(11) DEFAULT NULL,
    `pupuk_id` INT(11) DEFAULT NULL,
    `nama_pupuk` VARCHAR(100) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
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
    KEY `idx_hutang_anggota` (`anggota_id`),
    KEY `idx_hutang_pengelolaan_pupuk` (`pengelolaan_pupuk_id`),
    KEY `idx_hutang_master_pupuk` (`pupuk_id`),
    CONSTRAINT `fk_hutang_pupuk_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_pupuk_pengelolaan` FOREIGN KEY (`pengelolaan_pupuk_id`) REFERENCES `pengelolaan_pupuk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_hutang_pupuk_master` FOREIGN KEY (`pupuk_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 5. Pastikan tabel riwayat_hutang_pupuk tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `riwayat_hutang_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `hutang_id` INT(11) NOT NULL,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `pengelolaan_pupuk_id` INT(11) DEFAULT NULL,
    `pupuk_id` INT(11) DEFAULT NULL,
    `nama_pupuk` VARCHAR(100) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
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
    UNIQUE KEY `uniq_hutang_id` (`hutang_id`),
    KEY `idx_riwayat_anggota` (`anggota_id`),
    KEY `idx_riwayat_pupuk` (`pupuk_id`),
    CONSTRAINT `fk_riwayat_hutang_parent` FOREIGN KEY (`hutang_id`) REFERENCES `hutang_pupuk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Pastikan kolom harga, jumlah_hutang, sisa_hutang & tanggal tersedia (auto-migration)
$chkHrg = $conn->query("SHOW COLUMNS FROM `hutang_pupuk` LIKE 'harga'");
if ($chkHrg && $chkHrg->num_rows === 0) {
    $chkOldHrg = $conn->query("SHOW COLUMNS FROM `hutang_pupuk` LIKE 'jumlah_harga'");
    if ($chkOldHrg && $chkOldHrg->num_rows > 0) {
        $conn->query("ALTER TABLE `hutang_pupuk` CHANGE COLUMN `jumlah_harga` `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0'");
    } else {
        $conn->query("ALTER TABLE `hutang_pupuk` ADD COLUMN `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `jumlah_berat`");
    }
}
$chkJmlH = $conn->query("SHOW COLUMNS FROM `hutang_pupuk` LIKE 'jumlah_hutang'");
if ($chkJmlH && $chkJmlH->num_rows === 0) {
    $conn->query("ALTER TABLE `hutang_pupuk` ADD COLUMN `jumlah_hutang` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `harga`");
}
$chkSisaH = $conn->query("SHOW COLUMNS FROM `hutang_pupuk` LIKE 'sisa_hutang'");
if ($chkSisaH && $chkSisaH->num_rows === 0) {
    $conn->query("ALTER TABLE `hutang_pupuk` ADD COLUMN `sisa_hutang` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `jumlah_hutang`");
}
$chkTgl = $conn->query("SHOW COLUMNS FROM `hutang_pupuk` LIKE 'tanggal'");
if ($chkTgl && $chkTgl->num_rows === 0) {
    $conn->query("ALTER TABLE `hutang_pupuk` ADD COLUMN `tanggal` DATE DEFAULT NULL AFTER `keterangan`");
}

$chkHrgR = $conn->query("SHOW COLUMNS FROM `riwayat_hutang_pupuk` LIKE 'harga'");
if ($chkHrgR && $chkHrgR->num_rows === 0) {
    $chkOldHrgR = $conn->query("SHOW COLUMNS FROM `riwayat_hutang_pupuk` LIKE 'jumlah_harga'");
    if ($chkOldHrgR && $chkOldHrgR->num_rows > 0) {
        $conn->query("ALTER TABLE `riwayat_hutang_pupuk` CHANGE COLUMN `jumlah_harga` `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0'");
    } else {
        $conn->query("ALTER TABLE `riwayat_hutang_pupuk` ADD COLUMN `harga` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `jumlah_berat`");
    }
}
$chkJmlHR = $conn->query("SHOW COLUMNS FROM `riwayat_hutang_pupuk` LIKE 'jumlah_hutang'");
if ($chkJmlHR && $chkJmlHR->num_rows === 0) {
    $conn->query("ALTER TABLE `riwayat_hutang_pupuk` ADD COLUMN `jumlah_hutang` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `harga`");
}
$chkSisaHR = $conn->query("SHOW COLUMNS FROM `riwayat_hutang_pupuk` LIKE 'sisa_hutang'");
if ($chkSisaHR && $chkSisaHR->num_rows === 0) {
    $conn->query("ALTER TABLE `riwayat_hutang_pupuk` ADD COLUMN `sisa_hutang` VARCHAR(50) NOT NULL DEFAULT 'Rp 0' AFTER `jumlah_hutang`");
}
$chkTglR = $conn->query("SHOW COLUMNS FROM `riwayat_hutang_pupuk` LIKE 'tanggal'");
if ($chkTglR && $chkTglR->num_rows === 0) {
    $conn->query("ALTER TABLE `riwayat_hutang_pupuk` ADD COLUMN `tanggal` DATE DEFAULT NULL AFTER `keterangan`");
}

// 6. Pastikan tabel pembayaran_hutang_pupuk tersedia (1 baris = 1 kali pembayaran / cicilan)
$conn->query("CREATE TABLE IF NOT EXISTS `pembayaran_hutang_pupuk` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 7. Pastikan tabel hutang_pupuk_items tersedia (1 transaksi hutang bisa memiliki beberapa item pupuk)
$conn->query("CREATE TABLE IF NOT EXISTS `hutang_pupuk_items` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Pastikan kolom nama_pupuk cukup menampung gabungan beberapa item pupuk
foreach (['hutang_pupuk', 'riwayat_hutang_pupuk', 'pembayaran_hutang_pupuk'] as $tblNamaPupuk) {
    $chkCol = $conn->query("SHOW COLUMNS FROM `{$tblNamaPupuk}` LIKE 'nama_pupuk'");
    if ($chkCol && ($colRow = $chkCol->fetch_assoc())) {
        if (stripos((string)($colRow['Type'] ?? ''), 'varchar(100)') !== false) {
            $conn->query("ALTER TABLE `{$tblNamaPupuk}` MODIFY COLUMN `nama_pupuk` VARCHAR(255) NOT NULL");
        }
    }
}

// 8. Pastikan tabel penjualan_pupuk tersedia (berdiri sendiri, terpisah dari hutang piutang)
$conn->query("CREATE TABLE IF NOT EXISTS `penjualan_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `anggota_id` INT(11) DEFAULT NULL,
    `nama_pembeli` VARCHAR(100) NOT NULL,
    `alamat_pembeli` VARCHAR(255) DEFAULT '-',
    `nama_pupuk` VARCHAR(255) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `total_harga` BIGINT NOT NULL DEFAULT 0,
    `metode_bayar` ENUM('Tunai', 'Transfer', 'QRIS') NOT NULL DEFAULT 'Tunai',
    `tanggal_jual` DATE NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_kode_penjualan` (`kode_transaksi`),
    KEY `idx_penjualan_anggota` (`anggota_id`),
    KEY `idx_penjualan_tanggal` (`tanggal_jual`),
    CONSTRAINT `fk_penjualan_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 9. Pastikan tabel penjualan_pupuk_items tersedia (detail item pupuk terjual)
$conn->query("CREATE TABLE IF NOT EXISTS `penjualan_pupuk_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `penjualan_id` INT(11) NOT NULL,
    `pengelolaan_pupuk_id` INT(11) DEFAULT NULL,
    `pupuk_id` INT(11) DEFAULT NULL,
    `nama_pupuk` VARCHAR(100) NOT NULL,
    `jumlah_karung` VARCHAR(50) NOT NULL,
    `jumlah_berat` VARCHAR(50) NOT NULL,
    `harga_satuan` BIGINT NOT NULL DEFAULT 0,
    `subtotal` BIGINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ppi_penjualan` (`penjualan_id`),
    KEY `idx_ppi_pengelolaan` (`pengelolaan_pupuk_id`),
    KEY `idx_ppi_pupuk` (`pupuk_id`),
    CONSTRAINT `fk_ppi_penjualan` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan_pupuk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ppi_pengelolaan` FOREIGN KEY (`pengelolaan_pupuk_id`) REFERENCES `pengelolaan_pupuk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_ppi_master` FOREIGN KEY (`pupuk_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 10. Pastikan tabel riwayat_penjualan_pupuk tersedia (log aktivitas penjualan)
$conn->query("CREATE TABLE IF NOT EXISTS `riwayat_penjualan_pupuk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `penjualan_id` INT(11) DEFAULT NULL,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `aksi` ENUM('Terjual', 'Diperbarui', 'Dibatalkan') NOT NULL DEFAULT 'Terjual',
    `nama_pembeli` VARCHAR(100) NOT NULL,
    `rincian_item` TEXT NOT NULL,
    `total_karung` VARCHAR(50) NOT NULL,
    `total_berat` VARCHAR(50) NOT NULL,
    `total_harga` BIGINT NOT NULL DEFAULT 0,
    `metode_bayar` VARCHAR(30) NOT NULL DEFAULT 'Tunai',
    `tanggal_jual` DATE NOT NULL,
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rp_penjualan` (`penjualan_id`),
    KEY `idx_rp_tanggal` (`tanggal_jual`),
    KEY `idx_rp_aksi` (`aksi`),
    CONSTRAINT `fk_rp_penjualan` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan_pupuk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Pastikan kolom alamat_pembeli tersedia pada tabel penjualan_pupuk
$chkAlamatJual = $conn->query("SHOW COLUMNS FROM `penjualan_pupuk` LIKE 'alamat_pembeli'");
if ($chkAlamatJual && $chkAlamatJual->num_rows === 0) {
    $conn->query("ALTER TABLE `penjualan_pupuk` ADD COLUMN `alamat_pembeli` VARCHAR(255) DEFAULT '-' AFTER `nama_pembeli`");
}

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
 * Ekstrak nilai angka bulat dari string jumlah karung (contoh: "37 Karung" -> 37)
 */
function parseKarungInt(?string $val): int {
    if (preg_match('/([0-9]+([.,][0-9]+)?)/', (string)($val ?? ''), $m)) {
        return (int)round((float)str_replace(',', '.', $m[1]));
    }
    return 0;
}

/**
 * Ekstrak nilai float dari string jumlah berat (contoh: "1850 Kg" -> 1850.0)
 */
function parseBeratFloat(?string $val): float {
    if (preg_match('/([0-9]+([.,][0-9]+)?)/', (string)($val ?? ''), $m)) {
        return (float)str_replace(',', '.', $m[1]);
    }
    return 0.0;
}

/**
 * Format angka karung menjadi string bersatuan "Karung"
 */
function formatKarungStr(int $num): string {
    return max(0, $num) . ' Karung';
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
 * Sesuaikan stok jumlah_karung & jumlah_berat pada tabel pengelolaan_pupuk
 * $deltaKarung > 0 berarti menambah/mengembalikan stok, $deltaKarung < 0 berarti mengurangi stok
 */
function adjustStokPengelolaanPupuk(mysqli $conn, ?int $pengelolaanId, ?int $pupukId, int $deltaKarung, float $deltaBerat): bool {
    $targetRow = null;

    if ($pengelolaanId && $pengelolaanId > 0) {
        $stmt = $conn->prepare("SELECT id, jumlah_karung, jumlah_berat FROM pengelolaan_pupuk WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $pengelolaanId);
            $stmt->execute();
            $res = $stmt->get_result();
            $targetRow = $res ? $res->fetch_assoc() : null;
            $stmt->close();
        }
    }

    if (!$targetRow && $pupukId && $pupukId > 0) {
        $stmt = $conn->prepare("SELECT id, jumlah_karung, jumlah_berat FROM pengelolaan_pupuk WHERE pupuk_id = ? ORDER BY id ASC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $pupukId);
            $stmt->execute();
            $res = $stmt->get_result();
            $targetRow = $res ? $res->fetch_assoc() : null;
            $stmt->close();
        }
    }

    if (!$targetRow) {
        return false;
    }

    $currKarung = parseKarungInt($targetRow['jumlah_karung']);
    $currBerat  = parseBeratFloat($targetRow['jumlah_berat']);

    // Jika berat belum diisi tetapi karung berubah, hitung proporsional dari berat per karung
    if ($deltaBerat == 0.0 && $deltaKarung != 0 && $currKarung > 0) {
        $beratPerKarung = $currBerat / $currKarung;
        $deltaBerat = $deltaKarung * $beratPerKarung;
    }

    $newKarungStr = formatKarungStr($currKarung + $deltaKarung);
    $newBeratStr  = formatBeratStr($currBerat + $deltaBerat);
    $targetId     = (int)$targetRow['id'];

    $upd = $conn->prepare("UPDATE pengelolaan_pupuk SET jumlah_karung = ?, jumlah_berat = ? WHERE id = ?");
    if ($upd) {
        $upd->bind_param("ssi", $newKarungStr, $newBeratStr, $targetId);
        $ok = $upd->execute();
        $upd->close();
        return $ok;
    }
    return false;
}

/**
 * Mengambil seluruh baris item pupuk milik satu transaksi hutang dari hutang_pupuk_items.
 */
function getHutangItemsByHutangId(mysqli $conn, int $hutangId): array {
    $items = [];
    if ($hutangId <= 0) {
        return $items;
    }

    $stmt = $conn->prepare("
        SELECT i.id, i.hutang_id, i.pengelolaan_pupuk_id, i.pupuk_id,
               COALESCE(m.nama_produk, p.nama_pupuk, i.nama_pupuk) AS nama_pupuk,
               i.jumlah_karung, i.jumlah_berat,
               COALESCE(i.harga, 'Rp 0') AS harga,
               COALESCE(i.subtotal, 'Rp 0') AS subtotal
        FROM hutang_pupuk_items i
        LEFT JOIN pengelolaan_pupuk p ON i.pengelolaan_pupuk_id = p.id
        LEFT JOIN master_produk m ON COALESCE(p.pupuk_id, i.pupuk_id) = m.id AND m.jenis = 'Pupuk'
        WHERE i.hutang_id = ?
        ORDER BY i.id ASC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $hutangId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($res && ($row = $res->fetch_assoc())) {
            $items[] = normalizeHutangItemRow($row);
        }
        $stmt->close();
    }

    return $items;
}

/**
 * Mengambil seluruh item pupuk untuk semua transaksi hutang, dikelompokkan per hutang_id.
 */
function getAllHutangItemsGrouped(mysqli $conn): array {
    $grouped = [];
    $res = $conn->query("
        SELECT i.id, i.hutang_id, i.pengelolaan_pupuk_id, i.pupuk_id,
               COALESCE(m.nama_produk, p.nama_pupuk, i.nama_pupuk) AS nama_pupuk,
               i.jumlah_karung, i.jumlah_berat,
               COALESCE(i.harga, 'Rp 0') AS harga,
               COALESCE(i.subtotal, 'Rp 0') AS subtotal
        FROM hutang_pupuk_items i
        LEFT JOIN pengelolaan_pupuk p ON i.pengelolaan_pupuk_id = p.id
        LEFT JOIN master_produk m ON COALESCE(p.pupuk_id, i.pupuk_id) = m.id AND m.jenis = 'Pupuk'
        ORDER BY i.hutang_id ASC, i.id ASC
    ");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $hid = (int)$row['hutang_id'];
            $grouped[$hid][] = normalizeHutangItemRow($row);
        }
    }

    return $grouped;
}

/**
 * Menormalkan satu baris item hutang pupuk untuk tampilan dan kalkulasi.
 */
function normalizeHutangItemRow(array $row): array {
    $kNum     = parseKarungInt($row['jumlah_karung'] ?? '');
    $bNum     = parseBeratFloat($row['jumlah_berat'] ?? '');
    $hargaStr = formatRupiahStr($row['harga'] ?? 'Rp 0');
    $hargaNum = parseRupiahFloat($hargaStr);
    $subRaw   = parseRupiahFloat($row['subtotal'] ?? 'Rp 0');
    $subStr   = ($subRaw > 0) ? formatRupiahStr($subRaw) : formatRupiahStr($kNum * $hargaNum);

    return [
        'id'                   => (int)($row['id'] ?? 0),
        'hutang_id'            => (int)($row['hutang_id'] ?? 0),
        'pengelolaan_pupuk_id' => !empty($row['pengelolaan_pupuk_id']) ? (int)$row['pengelolaan_pupuk_id'] : null,
        'pupuk_id'             => !empty($row['pupuk_id']) ? (int)$row['pupuk_id'] : null,
        'nama_pupuk'           => formatTitleCase($row['nama_pupuk'] ?? '-'),
        'jumlah_karung'        => formatTitleCase($row['jumlah_karung'] ?? formatKarungStr($kNum)),
        'jumlah_berat'         => formatTitleCase($row['jumlah_berat'] ?? formatBeratStr($bNum)),
        'karung_num'           => $kNum,
        'berat_num'            => $bNum,
        'harga'                => $hargaStr,
        'harga_num'            => $hargaNum,
        'subtotal'             => $subStr,
        'subtotal_num'         => parseRupiahFloat($subStr),
    ];
}

/**
 * Mengganti daftar item pupuk pada hutang_pupuk_items untuk transaksi tertentu.
 */
function saveHutangItems(mysqli $conn, int $hutangId, array $items): void {
    $del = $conn->prepare("DELETE FROM hutang_pupuk_items WHERE hutang_id = ?");
    if ($del) {
        $del->bind_param("i", $hutangId);
        $del->execute();
        $del->close();
    }

    $ins = $conn->prepare("
        INSERT INTO hutang_pupuk_items (
            hutang_id, pengelolaan_pupuk_id, pupuk_id,
            nama_pupuk, jumlah_karung, jumlah_berat,
            harga, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$ins) {
        return;
    }

    foreach ($items as $item) {
        $pId    = $item['pengelolaan_pupuk_id'];
        $mId    = $item['pupuk_id'];
        $nPupuk = $item['nama_pupuk'];
        $jKar   = $item['jumlah_karung'];
        $jBer   = $item['jumlah_berat'];
        $hrg    = $item['harga'];
        $sub    = $item['subtotal'];
        $ins->bind_param("iiisssss", $hutangId, $pId, $mId, $nPupuk, $jKar, $jBer, $hrg, $sub);
        $ins->execute();
    }
    $ins->close();
}

/**
 * Menyesuaikan stok pengelolaan_pupuk untuk sekumpulan item ($sign = -1 mengurangi, +1 mengembalikan).
 */
function adjustHutangItemsStock(mysqli $conn, array $items, int $sign): void {
    foreach ($items as $item) {
        $pId = !empty($item['pengelolaan_pupuk_id']) ? (int)$item['pengelolaan_pupuk_id'] : null;
        $mId = !empty($item['pupuk_id']) ? (int)$item['pupuk_id'] : null;
        $k   = (int)($item['karung_num'] ?? parseKarungInt($item['jumlah_karung'] ?? ''));
        $b   = (float)($item['berat_num'] ?? parseBeratFloat($item['jumlah_berat'] ?? ''));
        if ($k > 0 || $b > 0) {
            adjustStokPengelolaanPupuk($conn, $pId, $mId, $sign * $k, $sign * $b);
        }
    }
}

/**
 * Menghitung kuota stok lama per pengelolaan_pupuk_id untuk validasi edit transaksi.
 */
function buildStockAllowanceFromItems(mysqli $conn, array $items): array {
    $allowance = [];
    foreach ($items as $oi) {
        $pId = !empty($oi['pengelolaan_pupuk_id']) ? (int)$oi['pengelolaan_pupuk_id'] : 0;
        if ($pId <= 0 && !empty($oi['pupuk_id'])) {
            $qOldStok = $conn->prepare("SELECT id FROM pengelolaan_pupuk WHERE pupuk_id = ? ORDER BY id ASC LIMIT 1");
            if ($qOldStok) {
                $mId = (int)$oi['pupuk_id'];
                $qOldStok->bind_param("i", $mId);
                $qOldStok->execute();
                $rOldStok = $qOldStok->get_result();
                if ($rOldStok && ($rowOldStok = $rOldStok->fetch_assoc())) {
                    $pId = (int)$rowOldStok['id'];
                }
                $qOldStok->close();
            }
        }
        if ($pId > 0) {
            $allowance[$pId] = [
                'karung' => ($allowance[$pId]['karung'] ?? 0) + (int)($oi['karung_num'] ?? 0),
                'berat'  => ($allowance[$pId]['berat'] ?? 0.0) + (float)($oi['berat_num'] ?? 0.0),
            ];
        }
    }
    return $allowance;
}

/**
 * Mem-parsing, memvalidasi, dan mengecek ketersediaan stok seluruh item pupuk pada form transaksi.
 */
function parsePupukTransactionItems(mysqli $conn, array $post, array $oldStockAllowance = []): array {
    $rawIds     = $post['item_pengelolaan_pupuk_id'] ?? null;
    $rawKarungs = $post['item_jumlah_karung'] ?? null;
    $rawBerats  = $post['item_jumlah_berat'] ?? null;
    $rawHargas  = $post['item_harga'] ?? null;

    if (!is_array($rawIds)) {
        $rawIds     = [$post['pengelolaan_pupuk_id'] ?? ''];
        $rawKarungs = [$post['jumlah_karung'] ?? ''];
        $rawBerats  = [$post['jumlah_berat'] ?? ''];
        $rawHargas  = [$post['harga'] ?? ($post['jumlah_harga'] ?? '')];
    }

    $rowCount = count($rawIds);
    $items    = [];
    $emptyRes = [
        'error'              => '',
        'items'              => [],
        'total_karung'       => 0,
        'total_berat'        => 0.0,
        'total_nominal'      => 0.0,
        'summary_nama_pupuk' => '',
    ];

    if ($rowCount === 0) {
        $emptyRes['error'] = 'Minimal satu item pupuk wajib diisi.';
        return $emptyRes;
    }

    $stokCache   = [];
    $requestedBy = [];
    $stokStmt    = $conn->prepare("
        SELECT p.id, p.pupuk_id, COALESCE(m.nama_produk, p.nama_pupuk) AS nama_pupuk, p.jumlah_karung, p.jumlah_berat
        FROM pengelolaan_pupuk p
        LEFT JOIN master_produk m ON p.pupuk_id = m.id AND m.jenis = 'Pupuk'
        WHERE p.id = ? LIMIT 1
    ");

    for ($i = 0; $i < $rowCount; $i++) {
        $pId        = (int)($rawIds[$i] ?? 0);
        $reqKarung  = parseKarungInt((string)($rawKarungs[$i] ?? ''));
        $reqBerat   = parseBeratFloat((string)($rawBerats[$i] ?? ''));
        $hargaStr   = formatRupiahStr($rawHargas[$i] ?? '');
        $hargaFloat = parseRupiahFloat($hargaStr);

        if ($pId <= 0 || $reqKarung <= 0 || $reqBerat <= 0 || $hargaFloat <= 0) {
            if ($stokStmt) $stokStmt->close();
            $itemNo = $i + 1;
            $emptyRes['error'] = "Item pupuk ke-{$itemNo}: Nama Pupuk, Jumlah Karung (> 0), Jumlah Berat (> 0), dan Harga Satuan (> 0) wajib diisi dengan benar.";
            return $emptyRes;
        }

        if (!isset($stokCache[$pId])) {
            $stokRow = null;
            if ($stokStmt) {
                $stokStmt->bind_param("i", $pId);
                $stokStmt->execute();
                $stokRes = $stokStmt->get_result();
                $stokRow = $stokRes ? $stokRes->fetch_assoc() : null;
            }
            if (!$stokRow) {
                if ($stokStmt) $stokStmt->close();
                $itemNo = $i + 1;
                $emptyRes['error'] = "Item pupuk ke-{$itemNo}: Data pupuk tidak ditemukan pada tabel Stok Pupuk.";
                return $emptyRes;
            }
            $stokCache[$pId] = $stokRow;
        }

        $stokRow = $stokCache[$pId];
        $requestedBy[$pId] = [
            'karung' => ($requestedBy[$pId]['karung'] ?? 0) + $reqKarung,
            'berat'  => ($requestedBy[$pId]['berat'] ?? 0.0) + $reqBerat,
        ];

        $subtotalFloat = $reqKarung * $hargaFloat;
        $items[] = [
            'pengelolaan_pupuk_id' => $pId,
            'pupuk_id'             => $stokRow['pupuk_id'] ? (int)$stokRow['pupuk_id'] : null,
            'nama_pupuk'           => formatTitleCase($stokRow['nama_pupuk']),
            'jumlah_karung'        => formatKarungStr($reqKarung),
            'jumlah_berat'         => formatBeratStr($reqBerat),
            'karung_num'           => $reqKarung,
            'berat_num'            => $reqBerat,
            'harga'                => $hargaStr,
            'harga_num'            => $hargaFloat,
            'subtotal'             => formatRupiahStr($subtotalFloat),
            'subtotal_float'       => $subtotalFloat,
        ];
    }

    if ($stokStmt) {
        $stokStmt->close();
    }

    foreach ($requestedBy as $pId => $req) {
        $stokRow      = $stokCache[$pId];
        $allowKarung  = (int)($oldStockAllowance[$pId]['karung'] ?? 0);
        $allowBerat   = (float)($oldStockAllowance[$pId]['berat'] ?? 0.0);
        $maksKarung   = parseKarungInt($stokRow['jumlah_karung']) + $allowKarung;
        $maksBerat    = parseBeratFloat($stokRow['jumlah_berat']) + $allowBerat;
        $namaPupukLbl = formatTitleCase($stokRow['nama_pupuk']);

        if ($req['karung'] > $maksKarung) {
            $emptyRes['error'] = "Stok karung untuk {$namaPupukLbl} tidak mencukupi! Maksimal tersedia: {$maksKarung} Karung, diminta: {$req['karung']} Karung.";
            return $emptyRes;
        }
        if ($req['berat'] > ($maksBerat + 0.01)) {
            $emptyRes['error'] = "Stok berat untuk {$namaPupukLbl} tidak mencukupi! Maksimal tersedia: " . formatBeratStr($maksBerat) . ", diminta: " . formatBeratStr($req['berat']) . ".";
            return $emptyRes;
        }
    }

    $totalKarung  = 0;
    $totalBerat   = 0.0;
    $totalNominal = 0.0;
    $names        = [];
    foreach ($items as $it) {
        $totalKarung  += $it['karung_num'];
        $totalBerat   += $it['berat_num'];
        $totalNominal += $it['subtotal_float'];
        if (!in_array($it['nama_pupuk'], $names, true)) {
            $names[] = $it['nama_pupuk'];
        }
    }

    return [
        'error'              => null,
        'items'              => $items,
        'total_karung'       => $totalKarung,
        'total_berat'        => $totalBerat,
        'total_nominal'      => $totalNominal,
        'summary_nama_pupuk' => mb_substr(implode(', ', $names), 0, 255, 'UTF-8'),
    ];
}

/**
 * Menormalkan satu baris item penjualan pupuk untuk tampilan dan kalkulasi.
 */
function normalizePenjualanItemRow(array $row): array {
    $kNum     = parseKarungInt($row['jumlah_karung'] ?? '');
    $bNum     = parseBeratFloat($row['jumlah_berat'] ?? '');
    $hargaInt = (int)($row['harga_satuan'] ?? 0);
    $subInt   = (int)($row['subtotal'] ?? ($kNum * $hargaInt));

    return [
        'id'                   => (int)($row['id'] ?? 0),
        'penjualan_id'         => (int)($row['penjualan_id'] ?? 0),
        'pengelolaan_pupuk_id' => !empty($row['pengelolaan_pupuk_id']) ? (int)$row['pengelolaan_pupuk_id'] : null,
        'pupuk_id'             => !empty($row['pupuk_id']) ? (int)$row['pupuk_id'] : null,
        'nama_pupuk'           => formatTitleCase($row['nama_pupuk'] ?? '-'),
        'jumlah_karung'        => formatTitleCase($row['jumlah_karung'] ?? formatKarungStr($kNum)),
        'jumlah_berat'         => formatTitleCase($row['jumlah_berat'] ?? formatBeratStr($bNum)),
        'karung_num'           => $kNum,
        'berat_num'            => $bNum,
        'harga_satuan'         => $hargaInt,
        'harga'                => formatRupiahStr($hargaInt),
        'subtotal_num'         => $subInt,
        'subtotal'             => formatRupiahStr($subInt),
    ];
}

/**
 * Mengambil seluruh baris item pupuk milik satu transaksi penjualan.
 */
function getPenjualanItemsById(mysqli $conn, int $penjualanId): array {
    $items = [];
    if ($penjualanId <= 0) {
        return $items;
    }

    $stmt = $conn->prepare("
        SELECT i.id, i.penjualan_id, i.pengelolaan_pupuk_id, i.pupuk_id,
               COALESCE(m.nama_produk, p.nama_pupuk, i.nama_pupuk) AS nama_pupuk,
               i.jumlah_karung, i.jumlah_berat, i.harga_satuan, i.subtotal
        FROM penjualan_pupuk_items i
        LEFT JOIN pengelolaan_pupuk p ON i.pengelolaan_pupuk_id = p.id
        LEFT JOIN master_produk m ON COALESCE(p.pupuk_id, i.pupuk_id) = m.id AND m.jenis = 'Pupuk'
        WHERE i.penjualan_id = ?
        ORDER BY i.id ASC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $penjualanId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($res && ($row = $res->fetch_assoc())) {
            $items[] = normalizePenjualanItemRow($row);
        }
        $stmt->close();
    }

    return $items;
}

/**
 * Mengambil seluruh item penjualan pupuk dikelompokkan per penjualan_id.
 */
function getAllPenjualanItemsGrouped(mysqli $conn): array {
    $grouped = [];
    $res = $conn->query("
        SELECT i.id, i.penjualan_id, i.pengelolaan_pupuk_id, i.pupuk_id,
               COALESCE(m.nama_produk, p.nama_pupuk, i.nama_pupuk) AS nama_pupuk,
               i.jumlah_karung, i.jumlah_berat, i.harga_satuan, i.subtotal
        FROM penjualan_pupuk_items i
        LEFT JOIN pengelolaan_pupuk p ON i.pengelolaan_pupuk_id = p.id
        LEFT JOIN master_produk m ON COALESCE(p.pupuk_id, i.pupuk_id) = m.id AND m.jenis = 'Pupuk'
        ORDER BY i.penjualan_id ASC, i.id ASC
    ");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['penjualan_id'];
            $grouped[$pid][] = normalizePenjualanItemRow($row);
        }
    }

    return $grouped;
}

/**
 * Menyimpan ulang daftar item penjualan pada penjualan_pupuk_items.
 */
function savePenjualanItems(mysqli $conn, int $penjualanId, array $items): void {
    $del = $conn->prepare("DELETE FROM penjualan_pupuk_items WHERE penjualan_id = ?");
    if ($del) {
        $del->bind_param("i", $penjualanId);
        $del->execute();
        $del->close();
    }

    $ins = $conn->prepare("
        INSERT INTO penjualan_pupuk_items (
            penjualan_id, pengelolaan_pupuk_id, pupuk_id,
            nama_pupuk, jumlah_karung, jumlah_berat,
            harga_satuan, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$ins) {
        return;
    }

    foreach ($items as $item) {
        $pId    = $item['pengelolaan_pupuk_id'];
        $mId    = $item['pupuk_id'];
        $nPupuk = $item['nama_pupuk'];
        $jKar   = $item['jumlah_karung'];
        $jBer   = $item['jumlah_berat'];
        $hrg    = (int)round((float)($item['harga_num'] ?? 0));
        $sub    = (int)round((float)($item['subtotal_float'] ?? 0));
        $ins->bind_param("iiisssii", $penjualanId, $pId, $mId, $nPupuk, $jKar, $jBer, $hrg, $sub);
        $ins->execute();
    }
    $ins->close();
}

/**
 * Membuat kode transaksi penjualan unik (mis. PJL-20261112-0001).
 */
function generateKodePenjualan(mysqli $conn, string $tanggal): string {
    $tglCode = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ? str_replace('-', '', $tanggal) : date('Ymd');
    $prefix  = "PJL-{$tglCode}-";
    $like    = $prefix . '%';
    $nextSeq = 1;

    $stmt = $conn->prepare("SELECT kode_transaksi FROM penjualan_pupuk WHERE kode_transaksi LIKE ? ORDER BY id DESC LIMIT 1");
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
 * Mencatat jejak transaksi penjualan ke tabel riwayat_penjualan_pupuk.
 */
function recordRiwayatPenjualan(
    mysqli $conn,
    ?int $penjualanId,
    string $kodeTransaksi,
    string $aksi,
    string $namaPembeli,
    array $items,
    string $totalKarung,
    string $totalBerat,
    int $totalHarga,
    string $metodeBayar,
    string $tanggalJual,
    string $keterangan
): void {
    $parts = [];
    foreach ($items as $it) {
        $hargaLabel = $it['harga'] ?? formatRupiahStr($it['harga_satuan'] ?? 0);
        $parts[] = "{$it['nama_pupuk']} ({$it['jumlah_karung']} / {$it['jumlah_berat']} @ {$hargaLabel})";
    }
    $rincianItem = !empty($parts) ? implode('; ', $parts) : '-';
    $dicatatOleh = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));

    $stmt = $conn->prepare("
        INSERT INTO riwayat_penjualan_pupuk (
            penjualan_id, kode_transaksi, aksi, nama_pembeli,
            rincian_item, total_karung, total_berat, total_harga,
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
            $totalKarung,
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
 * Hapus jejak catatan sistem "Bayar: Rp ..." dari kolom keterangan hutang
 */
function stripHutangPaymentNote(?string $keterangan): string {
    $clean = trim((string)($keterangan ?? ''));
    if ($clean === '' || $clean === '-') {
        return '-';
    }
    $parts = preg_split('/(?:^|\s*\|\s*)Bayar:\s*Rp\s*[0-9.]+/i', $clean, 2);
    $base  = trim((string)($parts[0] ?? ''), " \t\n\r\0\x0B|");
    return ($base === '') ? '-' : $base;
}

/**
 * Pastikan baris pembayaran_hutang_pupuk sinkron saat hutang diubah menjadi Lunas atau diedit
 */
function syncPembayaranOnHutangUpdate(mysqli $conn, int $hutangId, int $anggotaId, string $namaPetani, string $namaPupuk, int $totalHutangInt, int $sisaHutangInt, string $status, string $ketCatatan = ''): void {
    $updB = $conn->prepare("UPDATE pembayaran_hutang_pupuk SET anggota_id = ?, nama_petani = ?, nama_pupuk = ?, total_hutang = ? WHERE hutang_id = ?");
    if ($updB) {
        $updB->bind_param("issii", $anggotaId, $namaPetani, $namaPupuk, $totalHutangInt, $hutangId);
        $updB->execute();
        $updB->close();
    }

    if ($status === 'Belum Lunas' && ($totalHutangInt <= 0 || $sisaHutangInt >= $totalHutangInt)) {
        $delB = $conn->prepare("DELETE FROM pembayaran_hutang_pupuk WHERE hutang_id = ?");
        if ($delB) {
            $delB->bind_param("i", $hutangId);
            $delB->execute();
            $delB->close();
        }
        return;
    }

    $qRows = $conn->prepare("SELECT id, pembayaran_ke, jumlah_bayar, sisa_hutang, status FROM pembayaran_hutang_pupuk WHERE hutang_id = ? ORDER BY pembayaran_ke ASC, id ASC");
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
        ensureBackfillPembayaranHutang($conn);
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
            $ketStep     = formatTitleCase($ketCatatan !== '' ? $ketCatatan : 'Pelunasan Hutang Pupuk');
            $dicatatOleh = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-'));

            $ins = $conn->prepare("
                INSERT INTO pembayaran_hutang_pupuk (
                    hutang_id, anggota_id, nama_petani, nama_pupuk, pembayaran_ke,
                    tanggal_bayar, total_hutang, sisa_sebelum, jumlah_bayar, sisa_hutang,
                    metode_bayar, status, keterangan, dicatat_oleh
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($ins) {
                $sisaNol = 0;
                $ins->bind_param(
                    "iissisiiiissss",
                    $hutangId, $anggotaId, $namaPetani, $namaPupuk, $nextKe,
                    $tglBayar, $totalHutangInt, $kurangBayar, $kurangBayar, $sisaNol,
                    $metode, $stLunas, $ketStep, $dicatatOleh
                );
                $ins->execute();
                $ins->close();
            }
        } else {
            $updLast = $conn->prepare("UPDATE pembayaran_hutang_pupuk SET sisa_hutang = 0, status = 'Lunas' WHERE id = ?");
            if ($updLast) {
                $updLast->bind_param("i", $lastId);
                $updLast->execute();
                $updLast->close();
            }
        }
    } else {
        $updLast = $conn->prepare("UPDATE pembayaran_hutang_pupuk SET sisa_hutang = ?, status = 'Belum Lunas' WHERE id = ?");
        if ($updLast) {
            $updLast->bind_param("ii", $sisaHutangInt, $lastId);
            $updLast->execute();
            $updLast->close();
        }
    }
}

/**
 * Sinkronkan baris hutang_pupuk ke tabel riwayat_hutang_pupuk
 */
function syncRiwayatHutangRow(mysqli $conn, int $hutangId): void {
    $stmt = $conn->prepare("SELECT * FROM hutang_pupuk WHERE id = ? LIMIT 1");
    if (!$stmt) return;
    $stmt->bind_param("i", $hutangId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        $del = $conn->prepare("DELETE FROM riwayat_hutang_pupuk WHERE hutang_id = ?");
        if ($del) {
            $del->bind_param("i", $hutangId);
            $del->execute();
            $del->close();
        }
        return;
    }

    $harga        = $row['harga'] ?? ($row['jumlah_harga'] ?? 'Rp 0');
    $jumlahHutang = $row['jumlah_hutang'] ?? 'Rp 0';
    $sisaHutang   = $row['sisa_hutang'] ?? 'Rp 0';
    $tgl          = $row['tanggal'] ?? null;

    $sync = $conn->prepare("
        INSERT INTO riwayat_hutang_pupuk (
            hutang_id, anggota_id, nama_petani, pengelolaan_pupuk_id,
            pupuk_id, nama_pupuk, jumlah_karung, jumlah_berat, harga,
            jumlah_hutang, sisa_hutang,
            keterangan, tanggal, status, tanggal_lunas, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            anggota_id           = VALUES(anggota_id),
            nama_petani          = VALUES(nama_petani),
            pengelolaan_pupuk_id = VALUES(pengelolaan_pupuk_id),
            pupuk_id             = VALUES(pupuk_id),
            nama_pupuk           = VALUES(nama_pupuk),
            jumlah_karung        = VALUES(jumlah_karung),
            jumlah_berat         = VALUES(jumlah_berat),
            harga                = VALUES(harga),
            jumlah_hutang        = VALUES(jumlah_hutang),
            sisa_hutang          = VALUES(sisa_hutang),
            keterangan           = VALUES(keterangan),
            tanggal              = VALUES(tanggal),
            status               = VALUES(status),
            tanggal_lunas        = VALUES(tanggal_lunas)
    ");
    if ($sync) {
        $sync->bind_param(
            "iisiisssssssssss",
            $row['id'],
            $row['anggota_id'],
            $row['nama_petani'],
            $row['pengelolaan_pupuk_id'],
            $row['pupuk_id'],
            $row['nama_pupuk'],
            $row['jumlah_karung'],
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
 * Sinkronkan jejak pembayaran otomatis untuk data hutang_pupuk yang sudah Lunas atau
 * sudah terbayar sebagian tetapi belum memiliki baris di pembayaran_hutang_pupuk
 */
function ensureBackfillPembayaranHutang(mysqli $conn): void {
    $res = $conn->query("
        SELECT h.*
        FROM hutang_pupuk h
        LEFT JOIN pembayaran_hutang_pupuk b ON b.hutang_id = h.id
        WHERE b.id IS NULL
        ORDER BY h.id ASC
    ");
    if (!$res) return;

    while ($h = $res->fetch_assoc()) {
        $kNum     = parseKarungInt($h['jumlah_karung'] ?? '');
        $hargaVal = parseRupiahFloat($h['harga'] ?? '');
        $totalVal = (int)round(parseRupiahFloat($h['jumlah_hutang'] ?? ''));
        if ($totalVal <= 0 && $kNum > 0 && $hargaVal > 0) {
            $totalVal = (int)round($kNum * $hargaVal);
        }
        $isLunas = (($h['status'] ?? '') === 'Lunas');
        $rawSisaStr = trim((string)($h['sisa_hutang'] ?? ''));
        $sisaVal = $isLunas ? 0 : (int)round(parseRupiahFloat($rawSisaStr));

        // Status Belum Lunas dengan sisa_hutang kosong/'Rp 0' menandakan belum ada pembayaran (default skema)
        if (!$isLunas && ($rawSisaStr === '' || $sisaVal <= 0)) {
            $sisaVal = $totalVal;
        }

        if (!$isLunas && ($totalVal <= 0 || $sisaVal >= $totalVal)) {
            continue;
        }

        $hutangId    = (int)$h['id'];
        $anggotaId   = (int)$h['anggota_id'];
        $namaPetani  = formatTitleCase($h['nama_petani'] ?? '');
        $namaPupuk   = formatTitleCase($h['nama_pupuk'] ?? '');
        $defaultDate = !empty($h['tanggal_lunas'])
            ? substr((string)$h['tanggal_lunas'], 0, 10)
            : (!empty($h['tanggal']) ? (string)$h['tanggal'] : substr((string)($h['created_at'] ?? date('Y-m-d')), 0, 10));
        $dicatatOleh = formatTitleCase($_SESSION['nama'] ?? ($_SESSION['username'] ?? 'Admin'));

        $ket = (string)($h['keterangan'] ?? '');
        $matches = [];
        if (preg_match_all('/Bayar:\s*Rp\s*([0-9.]+)\s*\(([^)]*)\)/i', $ket, $matches, PREG_SET_ORDER)) {
            $sumBayar = 0;
            foreach ($matches as $m) {
                $sumBayar += (int)preg_replace('/[^0-9]/', '', $m[1]);
            }
            if ($totalVal <= 0) {
                $totalVal = $sumBayar + $sisaVal;
            }
            $runningSisa = $totalVal;
            $ke = 1;
            $totalSteps = count($matches);
            foreach ($matches as $idx => $m) {
                $nomBayar = (int)preg_replace('/[^0-9]/', '', $m[1]);
                if ($nomBayar <= 0) continue;
                $sisaSblm = $runningSisa;
                $runningSisa = max(0, $runningSisa - $nomBayar);
                if ($idx === $totalSteps - 1 && $isLunas) {
                    $runningSisa = 0;
                }
                $stStep = ($runningSisa <= 0) ? 'Lunas' : 'Belum Lunas';
                $noteRaw = trim($m[2]);
                $metode = 'Tunai';
                if (stripos($noteRaw, 'Transfer') !== false) {
                    $metode = 'Transfer';
                } elseif (stripos($noteRaw, 'Panen') !== false || stripos($noteRaw, 'Potong') !== false) {
                    $metode = 'Potong Hasil Panen';
                }
                $ketStep = formatTitleCase($noteRaw !== '' ? $noteRaw : 'Pembayaran Hutang Pupuk');

                $ins = $conn->prepare("
                    INSERT INTO pembayaran_hutang_pupuk (
                        hutang_id, anggota_id, nama_petani, nama_pupuk, pembayaran_ke,
                        tanggal_bayar, total_hutang, sisa_sebelum, jumlah_bayar, sisa_hutang,
                        metode_bayar, status, keterangan, dicatat_oleh
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if ($ins) {
                    $ins->bind_param(
                        "iissisiiiissss",
                        $hutangId, $anggotaId, $namaPetani, $namaPupuk, $ke,
                        $defaultDate, $totalVal, $sisaSblm, $nomBayar, $runningSisa,
                        $metode, $stStep, $ketStep, $dicatatOleh
                    );
                    $ins->execute();
                    $ins->close();
                }
                $ke++;
            }
        } else {
            $nomBayar = max(0, $totalVal - $sisaVal);
            $sisaSblm = $totalVal;
            $stStep   = $isLunas ? 'Lunas' : 'Belum Lunas';
            $ketStep  = formatTitleCase(($ket !== '' && $ket !== '-') ? $ket : 'Pelunasan Hutang Pupuk');
            $metode   = 'Tunai';
            $ke       = 1;

            $ins = $conn->prepare("
                INSERT INTO pembayaran_hutang_pupuk (
                    hutang_id, anggota_id, nama_petani, nama_pupuk, pembayaran_ke,
                    tanggal_bayar, total_hutang, sisa_sebelum, jumlah_bayar, sisa_hutang,
                    metode_bayar, status, keterangan, dicatat_oleh
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($ins) {
                $ins->bind_param(
                    "iissisiiiissss",
                    $hutangId, $anggotaId, $namaPetani, $namaPupuk, $ke,
                    $defaultDate, $totalVal, $sisaSblm, $nomBayar, $sisaVal,
                    $metode, $stStep, $ketStep, $dicatatOleh
                );
                $ins->execute();
                $ins->close();
            }
        }
    }
}

// Jika tabel hutang_pupuk baru pertama kali dibuat dan anggota_petani serta pengelolaan_pupuk sudah ada,
// masukkan data awal otomatis dan sinkronkan pengurangan stok ke pengelolaan_pupuk
if ($isFirstTimeHutangTable) {
    $cekAnggotaCount = $conn->query("SELECT COUNT(*) AS cnt FROM anggota_petani");
    $cekPupukCount   = $conn->query("SELECT COUNT(*) AS cnt FROM pengelolaan_pupuk");
    $hasAnggota = ($cekAnggotaCount && ($rA = $cekAnggotaCount->fetch_assoc()) && (int)$rA['cnt'] >= 4);
    $hasPupuk   = ($cekPupukCount && ($rP = $cekPupukCount->fetch_assoc()) && (int)$rP['cnt'] >= 3);

    if ($hasAnggota && $hasPupuk) {
        $conn->query("SET @disable_hutang_trigger = 1");
        $todayDate = date('Y-m-d');
        $seedItems = [
            [1, 1, 'Budi Santoso', 1, 1, 'Pupuk Urea Bersubsidi', '5 Karung', '250 Kg', 'Rp 0', 'Rp 0', 'Rp 0', 'Pinjaman Pupuk Tahap 1 Lahan Blok Utara A-1', $todayDate, 'Belum Lunas', null],
            [2, 2, 'Siti Aminah', 2, 2, 'Npk Phonska Subsidi', '4 Karung', '200 Kg', 'Rp 0', 'Rp 0', 'Rp 0', 'Pinjaman Pupuk Musim Tanam Blok Timur B-2', $todayDate, 'Lunas', date('Y-m-d H:i:s')],
            [3, 3, 'Slamet Riyadi', 1, 1, 'Pupuk Urea Bersubsidi', '3 Karung', '150 Kg', 'Rp 0', 'Rp 0', 'Rp 0', 'Pinjaman Pupuk Sawah Tadah Hujan Blok C-1', $todayDate, 'Belum Lunas', null],
            [4, 5, 'Mulyono Tani', 3, 3, 'Pupuk Organik Petroganik', '4 Karung', '160 Kg', 'Rp 0', 'Rp 0', 'Rp 0', 'Pinjaman Pupuk Dasar Organik Blok Utara A-3', $todayDate, 'Lunas', date('Y-m-d H:i:s')],
        ];
        foreach ($seedItems as $item) {
            $ins = $conn->prepare("INSERT IGNORE INTO hutang_pupuk (id, anggota_id, nama_petani, pengelolaan_pupuk_id, pupuk_id, nama_pupuk, jumlah_karung, jumlah_berat, harga, jumlah_hutang, sisa_hutang, keterangan, tanggal, status, tanggal_lunas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($ins) {
                $ins->bind_param("iisiissssssssss", $item[0], $item[1], $item[2], $item[3], $item[4], $item[5], $item[6], $item[7], $item[8], $item[9], $item[10], $item[11], $item[12], $item[13], $item[14]);
                if ($ins->execute() && $ins->affected_rows > 0) {
                    adjustStokPengelolaanPupuk($conn, $item[3], $item[4], -parseKarungInt($item[6]), -parseBeratFloat($item[7]));
                    syncRiwayatHutangRow($conn, $item[0]);
                }
                $ins->close();
            }
        }
    }
}

// Pastikan seluruh baris hutang_pupuk selalu tersinkron di riwayat_hutang_pupuk
$conn->query("
    INSERT INTO riwayat_hutang_pupuk (
        hutang_id, anggota_id, nama_petani, pengelolaan_pupuk_id,
        pupuk_id, nama_pupuk, jumlah_karung, jumlah_berat,
        harga, jumlah_hutang, sisa_hutang,
        keterangan, tanggal, status, tanggal_lunas, created_at
    )
    SELECT
        id, anggota_id, nama_petani, pengelolaan_pupuk_id,
        pupuk_id, nama_pupuk, jumlah_karung, jumlah_berat,
        harga, jumlah_hutang, sisa_hutang,
        keterangan, tanggal, status, tanggal_lunas, created_at
    FROM hutang_pupuk
    ON DUPLICATE KEY UPDATE
        anggota_id           = VALUES(anggota_id),
        nama_petani          = VALUES(nama_petani),
        pengelolaan_pupuk_id = VALUES(pengelolaan_pupuk_id),
        pupuk_id             = VALUES(pupuk_id),
        nama_pupuk           = VALUES(nama_pupuk),
        jumlah_karung        = VALUES(jumlah_karung),
        jumlah_berat         = VALUES(jumlah_berat),
        harga                = VALUES(harga),
        jumlah_hutang        = VALUES(jumlah_hutang),
        sisa_hutang          = VALUES(sisa_hutang),
        keterangan           = VALUES(keterangan),
        tanggal              = VALUES(tanggal),
        status               = VALUES(status),
        tanggal_lunas        = VALUES(tanggal_lunas)
");

// Backfill item hutang lama ke tabel hutang_pupuk_items jika belum tercatat
$conn->query("
    INSERT INTO hutang_pupuk_items (
        hutang_id, pengelolaan_pupuk_id, pupuk_id,
        nama_pupuk, jumlah_karung, jumlah_berat,
        harga, subtotal, created_at
    )
    SELECT
        h.id, h.pengelolaan_pupuk_id, h.pupuk_id,
        COALESCE(m.nama_produk, p.nama_pupuk, h.nama_pupuk),
        h.jumlah_karung, h.jumlah_berat,
        COALESCE(h.harga, 'Rp 0'),
        COALESCE(h.jumlah_hutang, 'Rp 0'),
        h.created_at
    FROM hutang_pupuk h
    LEFT JOIN hutang_pupuk_items hpi ON hpi.hutang_id = h.id
    LEFT JOIN pengelolaan_pupuk p ON h.pengelolaan_pupuk_id = p.id
    LEFT JOIN master_produk m ON COALESCE(p.pupuk_id, h.pupuk_id) = m.id AND m.jenis = 'Pupuk'
    WHERE hpi.id IS NULL
");

ensureBackfillPembayaranHutang($conn);
