<?php
/**
 * mudatani99 - Helper & Database Migration Manajemen Setoran Petani
 * File: setoran/helpers.php
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

// 3. Pastikan tabel setoran_petani tersedia di database
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

// 4. Pastikan tabel setoran_petani_items tersedia di database (berat per item tanaman)
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

/**
 * Format teks menjadi Title Case.
 */
function formatTitleCaseSetoran(?string $text): string {
    $clean = trim(preg_replace('/\s+/', ' ', (string)($text ?? '')));
    if ($clean === '' || $clean === '-') {
        return $clean;
    }
    return mb_convert_case(mb_strtolower($clean, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

/**
 * Ekstrak angka float dari input berat (contoh: "25.5 Kg" atau "25,5" -> 25.5).
 */
function parseBeratSetoranFloat(?string $val): float {
    if (preg_match('/([0-9]+([.,][0-9]+)?)/', (string)($val ?? ''), $m)) {
        return (float)str_replace(',', '.', $m[1]);
    }
    return 0.0;
}

/**
 * Format angka berat menjadi string bersatuan "Kg".
 */
function formatBeratSetoranStr(float $num): string {
    $clean = max(0.0, round($num, 2));
    if (floor($clean) == $clean) {
        return (int)$clean . ' Kg';
    }
    return rtrim(rtrim(number_format($clean, 2, '.', ''), '0'), '.') . ' Kg';
}

/**
 * Membuat kode setoran petani unik (contoh: STR-20261113-0001).
 */
function generateKodeSetoranPetani(mysqli $conn, string $tanggal): string {
    $tglCode = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ? str_replace('-', '', $tanggal) : date('Ymd');
    $prefix  = "STR-{$tglCode}-";
    $like    = $prefix . '%';
    $nextSeq = 1;

    $stmt = $conn->prepare("SELECT kode_setoran FROM setoran_petani WHERE kode_setoran LIKE ? ORDER BY kode_setoran DESC LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && ($row = $res->fetch_assoc())) {
            $lastKode = (string)$row['kode_setoran'];
            if (preg_match('/(\d+)$/', $lastKode, $m)) {
                $nextSeq = ((int)$m[1]) + 1;
            }
        }
        $stmt->close();
    }

    return sprintf('%s%04d', $prefix, $nextSeq);
}

/**
 * Menormalkan satu baris item setoran petani untuk tampilan dan cetak.
 */
function normalizeSetoranItemRow(array $row): array {
    $beratNum = parseBeratSetoranFloat($row['berat'] ?? '');
    return [
        'id'           => (int)($row['id'] ?? 0),
        'setoran_id'   => (int)($row['setoran_id'] ?? 0),
        'tanaman_id'   => !empty($row['tanaman_id']) ? (int)$row['tanaman_id'] : null,
        'nama_tanaman' => formatTitleCaseSetoran($row['nama_tanaman'] ?? '-'),
        'berat'        => formatBeratSetoranStr($beratNum),
        'berat_num'    => $beratNum,
    ];
}

/**
 * Mengambil seluruh baris item setoran milik satu transaksi setoran_id.
 */
function getSetoranItemsBySetoranId(mysqli $conn, int $setoranId): array {
    $items = [];
    if ($setoranId <= 0) {
        return $items;
    }

    $stmt = $conn->prepare("
        SELECT i.id, i.setoran_id, i.tanaman_id,
               COALESCE(m.nama_produk, i.nama_tanaman) AS nama_tanaman,
               i.berat
        FROM setoran_petani_items i
        LEFT JOIN master_produk m ON i.tanaman_id = m.id AND m.jenis = 'Tanaman'
        WHERE i.setoran_id = ?
        ORDER BY i.id ASC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $setoranId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($res && ($row = $res->fetch_assoc())) {
            $items[] = normalizeSetoranItemRow($row);
        }
        $stmt->close();
    }

    return $items;
}

/**
 * Mengambil seluruh item setoran untuk semua setoran, dikelompokkan per setoran_id.
 */
function getAllSetoranItemsGrouped(mysqli $conn): array {
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
            $grouped[$sid][] = normalizeSetoranItemRow($row);
        }
    }

    return $grouped;
}

/**
 * Mem-parsing dan memvalidasi item setoran tanaman dan berat per item dari POST.
 */
function parseSetoranItemsFromPost(mysqli $conn, array $post): array {
    $rawTanamanIds = $post['item_tanaman_id'] ?? null;
    $rawBerats     = $post['item_berat'] ?? null;

    if (!is_array($rawTanamanIds)) {
        $rawTanamanIds = [$post['tanaman_id'] ?? ''];
        $rawBerats     = [$post['berat'] ?? ''];
    }

    $rowCount = count($rawTanamanIds);
    $emptyRes = [
        'error'                => null,
        'items'                => [],
        'total_berat'          => 0.0,
        'summary_nama_tanaman' => '',
    ];

    if ($rowCount === 0) {
        $emptyRes['error'] = 'Minimal satu item tanaman setoran wajib diisi.';
        return $emptyRes;
    }

    $masterCache = [];
    $stmtMaster  = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
    $items       = [];
    $totalBerat  = 0.0;
    $names       = [];

    for ($i = 0; $i < $rowCount; $i++) {
        $tanamanId = (int)($rawTanamanIds[$i] ?? 0);
        $beratNum  = parseBeratSetoranFloat((string)($rawBerats[$i] ?? ''));
        $itemNo    = $i + 1;

        if ($tanamanId <= 0 || $beratNum <= 0) {
            if ($stmtMaster) $stmtMaster->close();
            $emptyRes['error'] = "Item setoran ke-{$itemNo}: Jenis Tanaman dan Berat (> 0 Kg) wajib diisi dengan benar.";
            return $emptyRes;
        }

        if (!isset($masterCache[$tanamanId])) {
            $mRow = null;
            if ($stmtMaster) {
                $stmtMaster->bind_param("i", $tanamanId);
                $stmtMaster->execute();
                $mRes = $stmtMaster->get_result();
                $mRow = $mRes ? $mRes->fetch_assoc() : null;
            }
            if (!$mRow) {
                if ($stmtMaster) $stmtMaster->close();
                $emptyRes['error'] = "Item setoran ke-{$itemNo}: Jenis tanaman tidak ditemukan pada Master Tanaman.";
                return $emptyRes;
            }
            $masterCache[$tanamanId] = $mRow;
        }

        $namaTanaman = formatTitleCaseSetoran($masterCache[$tanamanId]['nama_produk']);
        $totalBerat += $beratNum;

        if (!in_array($namaTanaman, $names, true)) {
            $names[] = $namaTanaman;
        }

        $items[] = [
            'tanaman_id'   => $tanamanId,
            'nama_tanaman' => mb_substr($namaTanaman, 0, 100, 'UTF-8'),
            'berat'        => formatBeratSetoranStr($beratNum),
            'berat_num'    => $beratNum,
        ];
    }

    if ($stmtMaster) {
        $stmtMaster->close();
    }

    return [
        'error'                => null,
        'items'                => $items,
        'total_berat'          => round($totalBerat, 2),
        'summary_nama_tanaman' => mb_substr(implode(', ', $names), 0, 255, 'UTF-8'),
    ];
}

/**
 * Menyimpan daftar item setoran pada setoran_petani_items.
 */
function saveSetoranPetaniItems(mysqli $conn, int $setoranId, array $items): bool {
    $ins = $conn->prepare("
        INSERT INTO setoran_petani_items (setoran_id, tanaman_id, nama_tanaman, berat)
        VALUES (?, ?, ?, ?)
    ");
    if (!$ins) {
        return false;
    }

    foreach ($items as $item) {
        $tanamanId   = $item['tanaman_id'];
        $namaTanaman = $item['nama_tanaman'];
        $beratStr    = $item['berat'];
        $ins->bind_param("iiss", $setoranId, $tanamanId, $namaTanaman, $beratStr);
        if (!$ins->execute()) {
            $ins->close();
            return false;
        }
    }

    $ins->close();
    return true;
}
