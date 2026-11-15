<?php
/**
 * mudatani99 - Helper & Database Migration Manajemen Transaksi Akhir
 * File: transaksi-akhir/helpers.php
 */

if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once ROOT_PATH . '/config/koneksi.php';
}
mysqli_report(MYSQLI_REPORT_OFF);

// Pastikan tabel dasar & helper setoran petani sudah dimuat
require_once ROOT_PATH . '/setoran/helpers.php';

// 1. Pastikan tabel transaksi_akhir tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `transaksi_akhir` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_transaksi` VARCHAR(30) NOT NULL,
    `anggota_id` INT(11) NOT NULL,
    `nama_petani` VARCHAR(100) NOT NULL,
    `tanaman_id` INT(11) DEFAULT NULL,
    `nama_tanaman` VARCHAR(100) NOT NULL DEFAULT '-',
    `jumlah_setoran` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `rapaksi` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `berat_final` VARCHAR(50) NOT NULL DEFAULT '0 Kg',
    `harga_per_kg` BIGINT NOT NULL DEFAULT 0,
    `jumlah_uang` BIGINT NOT NULL DEFAULT 0,
    `tanggal` DATE NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `dicatat_oleh` VARCHAR(100) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_kode_transaksi_akhir` (`kode_transaksi`),
    KEY `idx_ta_anggota` (`anggota_id`),
    KEY `idx_ta_tanaman` (`tanaman_id`),
    KEY `idx_ta_tanggal` (`tanggal`),
    CONSTRAINT `fk_ta_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota_petani` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ta_tanaman` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 2. Migrasi otomatis jika tabel transaksi_akhir sudah dibuat dari skema sebelumnya
$colSetoranId = $conn->query("SHOW COLUMNS FROM `transaksi_akhir` LIKE 'setoran_id'");
if ($colSetoranId && ($colSetoranRow = $colSetoranId->fetch_assoc())) {
    if (strtoupper((string)($colSetoranRow['Null'] ?? '')) === 'NO') {
        $conn->query("ALTER TABLE `transaksi_akhir` MODIFY COLUMN `setoran_id` INT(11) DEFAULT NULL");
    }
}
$idxUniqSetoran = $conn->query("SHOW INDEX FROM `transaksi_akhir` WHERE Key_name = 'uniq_transaksi_akhir_setoran'");
if ($idxUniqSetoran && $idxUniqSetoran->num_rows > 0) {
    $conn->query("ALTER TABLE `transaksi_akhir` DROP INDEX `uniq_transaksi_akhir_setoran`");
}

$colTanamanId = $conn->query("SHOW COLUMNS FROM `transaksi_akhir` LIKE 'tanaman_id'");
if ($colTanamanId && $colTanamanId->num_rows === 0) {
    $conn->query("ALTER TABLE `transaksi_akhir` ADD COLUMN `tanaman_id` INT(11) DEFAULT NULL AFTER `nama_petani`, ADD KEY `idx_ta_tanaman` (`tanaman_id`), ADD CONSTRAINT `fk_ta_tanaman` FOREIGN KEY (`tanaman_id`) REFERENCES `master_produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
}

$colNamaTanaman = $conn->query("SHOW COLUMNS FROM `transaksi_akhir` LIKE 'nama_tanaman'");
if ($colNamaTanaman && $colNamaTanaman->num_rows === 0) {
    $conn->query("ALTER TABLE `transaksi_akhir` ADD COLUMN `nama_tanaman` VARCHAR(100) NOT NULL DEFAULT '-' AFTER `tanaman_id`");
}

$colJumlahSetoran = $conn->query("SHOW COLUMNS FROM `transaksi_akhir` LIKE 'jumlah_setoran'");
if ($colJumlahSetoran && $colJumlahSetoran->num_rows === 0) {
    $conn->query("ALTER TABLE `transaksi_akhir` ADD COLUMN `jumlah_setoran` VARCHAR(50) NOT NULL DEFAULT '0 Kg' AFTER `nama_tanaman`");
}

$colRapaksi = $conn->query("SHOW COLUMNS FROM `transaksi_akhir` LIKE 'rapaksi'");
if ($colRapaksi && ($colRapaksiRow = $colRapaksi->fetch_assoc())) {
    if (stripos((string)($colRapaksiRow['Type'] ?? ''), 'varchar') === false) {
        $conn->query("ALTER TABLE `transaksi_akhir` MODIFY COLUMN `rapaksi` VARCHAR(50) NOT NULL DEFAULT '0 Kg'");
    }
}

$colBeratFinal = $conn->query("SHOW COLUMNS FROM `transaksi_akhir` LIKE 'berat_final'");
if ($colBeratFinal && $colBeratFinal->num_rows === 0) {
    $conn->query("ALTER TABLE `transaksi_akhir` ADD COLUMN `berat_final` VARCHAR(50) NOT NULL DEFAULT '0 Kg' AFTER `rapaksi`");
}

$colHargaKg = $conn->query("SHOW COLUMNS FROM `transaksi_akhir` LIKE 'harga_per_kg'");
if ($colHargaKg && $colHargaKg->num_rows === 0) {
    $conn->query("ALTER TABLE `transaksi_akhir` ADD COLUMN `harga_per_kg` BIGINT NOT NULL DEFAULT 0 AFTER `berat_final`");
}

$colJumlahUang = $conn->query("SHOW COLUMNS FROM `transaksi_akhir` LIKE 'jumlah_uang'");
if ($colJumlahUang && $colJumlahUang->num_rows === 0) {
    $conn->query("ALTER TABLE `transaksi_akhir` ADD COLUMN `jumlah_uang` BIGINT NOT NULL DEFAULT 0 AFTER `harga_per_kg`");
}

/**
 * Mem-parsing input angka berat/rapaksi secara ketat.
 * Mengembalikan null jika input kosong atau bukan angka non-negatif yang valid.
 */
function parseAngkaNonNegatifStrict(?string $raw): ?float {
    $clean = trim((string)($raw ?? ''));
    if ($clean === '') {
        return null;
    }

    // Izinkan satuan "Kg" opsional di akhir agar kompatibel jika dikirim bersama satuan
    $clean = preg_replace('/\s*kg$/i', '', $clean);
    $clean = str_replace(',', '.', trim($clean));

    if (!preg_match('/^\d+(\.\d+)?$/', $clean)) {
        return null;
    }

    return round((float)$clean, 2);
}

/**
 * Mem-parsing input harga (/Kg) menjadi integer rupiah (> 0), atau null jika kosong / <= 0.
 */
function parseHargaPerKgInt(?string $raw): ?int {
    $digits = preg_replace('/[^0-9]/', '', (string)($raw ?? ''));
    if ($digits === '') {
        return null;
    }
    $val = (int)$digits;
    return ($val > 0) ? $val : null;
}

/**
 * Format angka rupiah (contoh: 12500 -> "Rp 12.500").
 */
function formatRupiahTransaksiAkhir($nominal): string {
    $digits = preg_replace('/[^0-9]/', '', (string)($nominal ?? '0'));
    $num    = ($digits !== '') ? (int)$digits : 0;
    return 'Rp ' . number_format($num, 0, ',', '.');
}

/**
 * Membuat kode transaksi akhir unik (contoh: TRX-20261115-0001).
 */
function generateKodeTransaksiAkhir(mysqli $conn, string $tanggal): string {
    $tglCode = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ? str_replace('-', '', $tanggal) : date('Ymd');
    $prefix  = "TRX-{$tglCode}-";
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
 * Menghitung sisa berat setoran, rincian sisa berat per tanaman_id (dikurangi transaksi akhir yang sudah tercatat),
 * serta daftar nota dari setoran_petani per anggota_id.
 */
function getRekapSetoranPerAnggota(mysqli $conn, ?int $excludeTransaksiId = null): array {
    $itemsGrouped    = getAllSetoranItemsGrouped($conn);
    $rekap           = [];
    $tanamanIdByName = [];

    $resTanamanMap = $conn->query("SELECT id, nama_produk FROM master_produk WHERE jenis = 'Tanaman'");
    if ($resTanamanMap) {
        while ($tRow = $resTanamanMap->fetch_assoc()) {
            $tNameKey = mb_strtolower(trim((string)$tRow['nama_produk']), 'UTF-8');
            if ($tNameKey !== '' && !isset($tanamanIdByName[$tNameKey])) {
                $tanamanIdByName[$tNameKey] = (int)$tRow['id'];
            }
        }
    }

    $res = $conn->query("
        SELECT id, kode_setoran, anggota_id, nama_tanaman, jumlah_berat, tanggal, keterangan, created_at
        FROM setoran_petani
        ORDER BY tanggal DESC, id DESC
    ");

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $sid       = (int)$row['id'];
            $anggotaId = (int)$row['anggota_id'];
            $rowItems  = $itemsGrouped[$sid] ?? [];
            $itemNames = [];
            $sumBerat  = 0.0;

            foreach ($rowItems as $it) {
                if (!in_array($it['nama_tanaman'], $itemNames, true)) {
                    $itemNames[] = $it['nama_tanaman'];
                }
                $sumBerat += (float)$it['berat_num'];
            }

            if (!empty($rowItems)) {
                $beratNum  = round($sumBerat, 2);
                $namaTanam = implode(', ', $itemNames);
            } else {
                $beratNum  = round(parseBeratSetoranFloat($row['jumlah_berat']), 2);
                $namaTanam = formatTitleCaseSetoran($row['nama_tanaman']);
                $fallbackTidKey = mb_strtolower(trim($namaTanam), 'UTF-8');
                $rowItems[] = [
                    'id'           => 0,
                    'setoran_id'   => $sid,
                    'tanaman_id'   => $tanamanIdByName[$fallbackTidKey] ?? null,
                    'nama_tanaman' => $namaTanam,
                    'berat'        => formatBeratSetoranStr($beratNum),
                    'berat_num'    => $beratNum,
                ];
            }

            if (!isset($rekap[$anggotaId])) {
                $rekap[$anggotaId] = [
                    'anggota_id'            => $anggotaId,
                    'raw_total_berat'       => 0.0,
                    'used_total_berat'      => 0.0,
                    'total_berat'           => 0.0,
                    'total_berat_str'       => '0 Kg',
                    'jumlah_nota'           => 0,
                    'default_tanaman_id'    => null,
                    'raw_berat_per_tanaman' => [],
                    'berat_per_tanaman'     => [],
                    'setoran_list'          => [],
                ];
            }

            foreach ($rowItems as $it) {
                $tid = !empty($it['tanaman_id']) ? (int)$it['tanaman_id'] : 0;
                if ($tid <= 0 && !empty($it['nama_tanaman'])) {
                    $itNameKey = mb_strtolower(trim((string)$it['nama_tanaman']), 'UTF-8');
                    $tid       = (int)($tanamanIdByName[$itNameKey] ?? 0);
                }
                if ($tid > 0) {
                    $prevT = (float)($rekap[$anggotaId]['raw_berat_per_tanaman'][$tid] ?? 0.0);
                    $rekap[$anggotaId]['raw_berat_per_tanaman'][$tid] = round($prevT + (float)$it['berat_num'], 2);
                }
            }

            $tgl = !empty($row['tanggal']) ? $row['tanggal'] : substr((string)$row['created_at'], 0, 10);

            $rekap[$anggotaId]['raw_total_berat'] = round($rekap[$anggotaId]['raw_total_berat'] + $beratNum, 2);
            $rekap[$anggotaId]['jumlah_nota']++;
            $rekap[$anggotaId]['setoran_list'][] = [
                'id'           => $sid,
                'kode_setoran' => (string)$row['kode_setoran'],
                'tanggal'      => $tgl,
                'nama_tanaman' => $namaTanam,
                'jumlah_berat' => formatBeratSetoranStr($beratNum),
                'berat_num'    => $beratNum,
                'keterangan'   => formatTitleCaseSetoran($row['keterangan'] ?: '-'),
                'items'        => $rowItems,
            ];
        }
    }

    // Inisialisasi sisa setoran dari total setoran awal sebelum pengurangan transaksi akhir
    foreach ($rekap as $aid => &$rData) {
        $rData['total_berat']       = $rData['raw_total_berat'];
        $rData['total_berat_str']   = formatBeratSetoranStr($rData['raw_total_berat']);
        $rData['berat_per_tanaman'] = $rData['raw_berat_per_tanaman'];
    }
    unset($rData);

    // Kurangi sisa setoran petani & sisa per tanaman berdasarkan data yang sudah diinput di transaksi_akhir
    $taSql = "SELECT id, anggota_id, tanaman_id, nama_tanaman, jumlah_setoran FROM transaksi_akhir";
    if ($excludeTransaksiId !== null && $excludeTransaksiId > 0) {
        $taSql .= " WHERE id <> " . (int)$excludeTransaksiId;
    }

    $resTa = $conn->query($taSql);
    if ($resTa) {
        while ($taRow = $resTa->fetch_assoc()) {
            $aid       = (int)($taRow['anggota_id'] ?? 0);
            $usedBerat = round(parseBeratSetoranFloat($taRow['jumlah_setoran'] ?? '0'), 2);
            if ($aid <= 0 || $usedBerat <= 0 || !isset($rekap[$aid])) {
                continue;
            }

            $rekap[$aid]['used_total_berat'] = round($rekap[$aid]['used_total_berat'] + $usedBerat, 2);
            $rekap[$aid]['total_berat']      = max(0.0, round($rekap[$aid]['raw_total_berat'] - $rekap[$aid]['used_total_berat'], 2));
            $rekap[$aid]['total_berat_str']  = formatBeratSetoranStr($rekap[$aid]['total_berat']);

            $taTid = !empty($taRow['tanaman_id']) ? (int)$taRow['tanaman_id'] : 0;
            if ($taTid <= 0 && !empty($taRow['nama_tanaman'])) {
                $taNameKey = mb_strtolower(trim((string)$taRow['nama_tanaman']), 'UTF-8');
                $taTid     = (int)($tanamanIdByName[$taNameKey] ?? 0);
            }

            $rem = $usedBerat;
            if ($taTid > 0 && isset($rekap[$aid]['berat_per_tanaman'][$taTid])) {
                $avail  = (float)$rekap[$aid]['berat_per_tanaman'][$taTid];
                $deduct = min($avail, $rem);
                $rekap[$aid]['berat_per_tanaman'][$taTid] = max(0.0, round($avail - $deduct, 2));
                $rem    = round($rem - $deduct, 2);
            }

            // Jika transaksi lama tidak punya tanaman_id spesifik atau melebihi saldo satu tanaman, kurangi dari tanaman lain milik petani
            if ($rem > 0) {
                foreach ($rekap[$aid]['berat_per_tanaman'] as $tidKey => $availVal) {
                    if ($rem <= 0) {
                        break;
                    }
                    $availFloat = (float)$availVal;
                    if ($availFloat <= 0) {
                        continue;
                    }
                    $deduct = min($availFloat, $rem);
                    $rekap[$aid]['berat_per_tanaman'][$tidKey] = max(0.0, round($availFloat - $deduct, 2));
                    $rem = round($rem - $deduct, 2);
                }
            }
        }
    }

    // Tentukan default_tanaman_id pertama yang masih memiliki sisa setoran > 0
    foreach ($rekap as $aid => &$rData) {
        $firstTid    = null;
        $selectedTid = null;
        foreach ($rData['berat_per_tanaman'] as $tidKey => $sisaVal) {
            if ($firstTid === null) {
                $firstTid = (int)$tidKey;
            }
            if ((float)$sisaVal > 0 && $selectedTid === null) {
                $selectedTid = (int)$tidKey;
                break;
            }
        }
        $rData['default_tanaman_id'] = $selectedTid ?? $firstTid;
    }
    unset($rData);

    return $rekap;
}
