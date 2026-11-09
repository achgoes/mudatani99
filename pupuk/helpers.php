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
        $sisaVal = $isLunas ? 0 : (int)round(parseRupiahFloat($h['sisa_hutang'] ?? ''));

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
        $seedItems = [
            [1, 1, 'Budi Santoso', 1, 1, 'Pupuk Urea Bersubsidi', '5 Karung', '250 Kg', 'Pinjaman Pupuk Tahap 1 Lahan Blok Utara A-1', 'Belum Lunas', null],
            [2, 2, 'Siti Aminah', 2, 2, 'Npk Phonska Subsidi', '4 Karung', '200 Kg', 'Pinjaman Pupuk Musim Tanam Blok Timur B-2', 'Lunas', date('Y-m-d H:i:s')],
            [3, 3, 'Slamet Riyadi', 1, 1, 'Pupuk Urea Bersubsidi', '3 Karung', '150 Kg', 'Pinjaman Pupuk Sawah Tadah Hujan Blok C-1', 'Belum Lunas', null],
            [4, 5, 'Mulyono Tani', 3, 3, 'Pupuk Organik Petroganik', '4 Karung', '160 Kg', 'Pinjaman Pupuk Dasar Organik Blok Utara A-3', 'Lunas', date('Y-m-d H:i:s')],
        ];
        foreach ($seedItems as $item) {
            $ins = $conn->prepare("INSERT IGNORE INTO hutang_pupuk (id, anggota_id, nama_petani, pengelolaan_pupuk_id, pupuk_id, nama_pupuk, jumlah_karung, jumlah_berat, keterangan, status, tanggal_lunas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($ins) {
                $ins->bind_param("iisiissssss", $item[0], $item[1], $item[2], $item[3], $item[4], $item[5], $item[6], $item[7], $item[8], $item[9], $item[10]);
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

ensureBackfillPembayaranHutang($conn);
