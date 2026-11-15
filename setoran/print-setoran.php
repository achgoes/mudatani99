<?php
/**
 * mudatani99 - Cetak Bukti Setoran Petani
 * File: setoran/print-setoran.php
 */
require_once __DIR__ . '/../config/app.php';
requireLogin();
require_once ROOT_PATH . '/config/koneksi.php';
require_once __DIR__ . '/helpers.php';

$setoranId = (int)($_GET['id'] ?? 0);
$setoran   = null;
$items     = [];

if ($setoranId > 0) {
    $stmt = $conn->prepare("
        SELECT s.id, s.kode_setoran, s.anggota_id,
               COALESCE(a.nama_petani, s.nama_petani) AS nama_petani,
               COALESCE(NULLIF(a.alamat, ''), '-') AS alamat_petani,
               s.nama_tanaman, s.jumlah_berat, s.tanggal,
               s.keterangan, s.dicatat_oleh, s.created_at
        FROM setoran_petani s
        LEFT JOIN anggota_petani a ON s.anggota_id = a.id
        WHERE s.id = ?
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param("i", $setoranId);
        $stmt->execute();
        $res     = $stmt->get_result();
        $setoran = $res ? $res->fetch_assoc() : null;
        $stmt->close();
    }

    if ($setoran) {
        $items = getSetoranItemsBySetoranId($conn, $setoranId);
        if (empty($items)) {
            $items[] = [
                'nama_tanaman' => formatTitleCaseSetoran($setoran['nama_tanaman']),
                'berat'        => formatBeratSetoranStr(parseBeratSetoranFloat($setoran['jumlah_berat'])),
            ];
        }
    }
}

$autoPrint = (($_GET['auto'] ?? '1') === '1');
$backUrl   = appUrl('setoran/');

if ($setoran) {
    $kodeSetoran  = $setoran['kode_setoran'];
    $namaPetani   = formatTitleCaseSetoran($setoran['nama_petani']);
    $alamatPetani = formatTitleCaseSetoran(trim((string)($setoran['alamat_petani'] ?? '-')) ?: '-');
    $tglRaw       = $setoran['tanggal'] ?? '';
    $tglFormatted = (!empty($tglRaw) && $tglRaw !== '0000-00-00')
        ? date('d/m/Y', strtotime($tglRaw))
        : '-';
    $keterangan   = formatTitleCaseSetoran($setoran['keterangan'] ?: '-');
    $dicatatOleh  = formatTitleCaseSetoran($setoran['dicatat_oleh'] ?: '-');
    $jumlahBerat  = formatBeratSetoranStr(parseBeratSetoranFloat($setoran['jumlah_berat']));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $setoran ? htmlspecialchars($kodeSetoran . ' - Bukti Setoran Petani') : 'Bukti Setoran Tidak Ditemukan' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 1.5rem 1rem 2.5rem;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            line-height: 1.5;
        }
        .print-toolbar {
            max-width: 760px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.6rem 1rem;
            border-radius: 0.55rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            min-height: 44px;
        }
        .toolbar-btn:hover { background: #f8fafc; }
        .toolbar-btn-primary {
            background: #15803d;
            border-color: #15803d;
            color: #ffffff;
        }
        .toolbar-btn-primary:hover { background: #166534; }
        .receipt-sheet {
            max-width: 760px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            padding: 2rem;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
        }
        .receipt-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 1.25rem;
            border-bottom: 2px solid #0f172a;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
        }
        .brand-title { font-size: 1.25rem; font-weight: 800; color: #14532d; margin: 0; }
        .brand-subtitle { font-size: 0.8rem; color: #334155; margin: 0.2rem 0 0; }
        .doc-meta { text-align: right; }
        .doc-label { font-size: 0.75rem; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: #15803d; margin: 0 0 0.25rem; }
        .doc-number { font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0; }
        .doc-date { font-size: 0.78rem; color: #334155; margin: 0.15rem 0 0; }
        .meta-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
        .meta-box { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.85rem 1rem; background: #f8fafc; }
        .meta-box-title { font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #334155; margin: 0 0 0.5rem; }
        .meta-row { display: flex; justify-content: space-between; gap: 0.75rem; font-size: 0.82rem; padding: 0.2rem 0; }
        .meta-key { color: #475569; flex-shrink: 0; }
        .meta-val { color: #0f172a; font-weight: 700; text-align: right; word-break: break-word; }
        .payment-table { width: 100%; border-collapse: collapse; margin-bottom: 1.25rem; font-size: 0.85rem; }
        .payment-table th, .payment-table td { border: 1px solid #cbd5e1; padding: 0.65rem 0.85rem; text-align: left; }
        .payment-table th { background: #f1f5f9; color: #0f172a; font-weight: 700; }
        .payment-table td { color: #0f172a; font-weight: 600; }
        .amount-highlight { background: #ecfdf5; color: #065f46 !important; font-size: 1rem; font-weight: 800 !important; }
        .signature-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2rem; margin-top: 2rem; padding-top: 0.5rem; }
        .signature-box { text-align: center; font-size: 0.82rem; color: #334155; }
        .signature-space { height: 64px; }
        .signature-name { font-weight: 700; color: #0f172a; border-top: 1px solid #64748b; display: inline-block; min-width: 170px; padding-top: 0.35rem; }
        .receipt-footer-note { margin-top: 1.5rem; padding-top: 0.85rem; border-top: 1px dashed #cbd5e1; font-size: 0.75rem; color: #475569; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; }
        .empty-state-box { max-width: 520px; margin: 3rem auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 0.75rem; padding: 2rem; text-align: center; }
        @media (max-width: 640px) {
            .receipt-sheet { padding: 1.25rem; }
            .doc-meta { text-align: left; }
            .meta-grid, .signature-grid { grid-template-columns: 1fr; gap: 0.85rem; }
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .print-toolbar { display: none !important; }
            .receipt-sheet { border: none; border-radius: 0; box-shadow: none; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <?php if (!$setoran): ?>
        <div class="empty-state-box">
            <h1>Bukti Setoran Tidak Ditemukan</h1>
            <p>Data setoran petani yang diminta tidak tersedia atau telah dihapus dari sistem.</p>
            <a href="<?= htmlspecialchars($backUrl) ?>" class="toolbar-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Setoran Petani
            </a>
        </div>
    <?php else: ?>
        <div class="print-toolbar">
            <a href="<?= htmlspecialchars($backUrl) ?>" class="toolbar-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Setoran Petani
            </a>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" class="toolbar-btn" onclick="window.close()">
                    <i class="fa-solid fa-xmark"></i>
                    Tutup Tab
                </button>
                <button type="button" class="toolbar-btn toolbar-btn-primary" onclick="window.print()">
                    <i class="fa-solid fa-print"></i>
                    Print Bukti Setoran
                </button>
            </div>
        </div>

        <main class="receipt-sheet">
            <header class="receipt-header">
                <div>
                    <h1 class="brand-title">mudatani99</h1>
                    <p class="brand-subtitle">Sistem Pengelolaan Kelompok Pertanian (Manajemen Setoran Petani)</p>
                </div>
                <div class="doc-meta">
                    <p class="doc-label">Bukti Setoran Petani</p>
                    <p class="doc-number"><?= htmlspecialchars($kodeSetoran) ?></p>
                    <p class="doc-date">Tanggal: <?= htmlspecialchars($tglFormatted) ?></p>
                </div>
            </header>

            <section class="meta-grid">
                <div class="meta-box">
                    <h2 class="meta-box-title">Identitas Petani Penyetor</h2>
                    <div class="meta-row">
                        <span class="meta-key">Nama Petani</span>
                        <span class="meta-val"><?= htmlspecialchars($namaPetani) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">Alamat</span>
                        <span class="meta-val"><?= htmlspecialchars($alamatPetani) ?></span>
                    </div>
                </div>

                <div class="meta-box">
                    <h2 class="meta-box-title">Informasi Setoran</h2>
                    <div class="meta-row">
                        <span class="meta-key">Jumlah Item</span>
                        <span class="meta-val"><?= count($items) ?> Item</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">Total Berat</span>
                        <span class="meta-val"><?= htmlspecialchars($jumlahBerat) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">Keterangan</span>
                        <span class="meta-val"><?= htmlspecialchars($keterangan) ?></span>
                    </div>
                </div>
            </section>

            <table class="payment-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">No</th>
                        <th style="width: 58%;">Setor Tanaman</th>
                        <th style="width: 30%;">Berat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $idx => $it): ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td><?= htmlspecialchars($it['nama_tanaman']) ?></td>
                        <td><?= htmlspecialchars($it['berat']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th colspan="2" style="text-align: right;">Jumlah Berat Total</th>
                        <td class="amount-highlight"><?= htmlspecialchars($jumlahBerat) ?></td>
                    </tr>
                </tbody>
            </table>

            <section class="signature-grid">
                <div class="signature-box">
                    <div>Petani Penyetor</div>
                    <div class="signature-space"></div>
                    <span class="signature-name"><?= htmlspecialchars($namaPetani) ?></span>
                </div>
                <div class="signature-box">
                    <div>Petugas Penerima</div>
                    <div class="signature-space"></div>
                    <span class="signature-name"><?= htmlspecialchars($dicatatOleh !== '-' ? $dicatatOleh : 'Pengurus Poktan') ?></span>
                </div>
            </section>

            <footer class="receipt-footer-note">
                <span>Dicetak pada: <?= date('d/m/Y H:i') ?></span>
                <span>Kode Setoran: <?= htmlspecialchars($kodeSetoran) ?></span>
            </footer>
        </main>

        <?php if ($autoPrint): ?>
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 200);
            });
        </script>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
