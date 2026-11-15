<?php
/**
 * mudatani99 - Cetak Bukti Transaksi Akhir
 * File: transaksi-akhir/print-transaksi-akhir.php
 */
require_once __DIR__ . '/../config/app.php';
requireLogin();
require_once ROOT_PATH . '/config/koneksi.php';
require_once __DIR__ . '/helpers.php';

$transaksiId    = (int)($_GET['id'] ?? 0);
$transaksi      = null;
$riwayatSetoran = [];

if ($transaksiId > 0) {
    $stmt = $conn->prepare("
        SELECT t.id, t.kode_transaksi, t.anggota_id,
               COALESCE(a.nama_petani, t.nama_petani) AS nama_petani,
               COALESCE(NULLIF(a.alamat, ''), '-') AS alamat_petani,
               t.tanaman_id,
               COALESCE(m.nama_produk, NULLIF(t.nama_tanaman, ''), '-') AS nama_tanaman,
               t.jumlah_setoran, t.rapaksi, t.berat_final, t.harga_per_kg, t.jumlah_uang,
               t.tanggal, t.keterangan, t.dicatat_oleh, t.created_at
        FROM transaksi_akhir t
        LEFT JOIN anggota_petani a ON t.anggota_id = a.id
        LEFT JOIN master_produk m ON t.tanaman_id = m.id AND m.jenis = 'Tanaman'
        WHERE t.id = ?
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param("i", $transaksiId);
        $stmt->execute();
        $res       = $stmt->get_result();
        $transaksi = $res ? $res->fetch_assoc() : null;
        $stmt->close();
    }

    if ($transaksi) {
        $rekap          = getRekapSetoranPerAnggota($conn);
        $anggotaId      = (int)$transaksi['anggota_id'];
        $riwayatSetoran = $rekap[$anggotaId]['setoran_list'] ?? [];
    }
}

$autoPrint = (($_GET['auto'] ?? '1') === '1');
$backUrl   = appUrl('transaksi-akhir/');

if ($transaksi) {
    $kodeTransaksi    = $transaksi['kode_transaksi'];
    $namaPetani       = formatTitleCaseSetoran($transaksi['nama_petani']);
    $alamatPetani     = formatTitleCaseSetoran(trim((string)($transaksi['alamat_petani'] ?? '-')) ?: '-');
    $namaTanaman      = formatTitleCaseSetoran($transaksi['nama_tanaman'] ?: '-');
    $tglRaw           = $transaksi['tanggal'] ?? '';
    $tglFormatted     = (!empty($tglRaw) && $tglRaw !== '0000-00-00')
        ? date('d/m/Y', strtotime($tglRaw))
        : '-';
    $keterangan       = formatTitleCaseSetoran($transaksi['keterangan'] ?: '-');
    $dicatatOleh      = formatTitleCaseSetoran($transaksi['dicatat_oleh'] ?: '-');
    $jumlahSetoranNum = round(parseBeratSetoranFloat($transaksi['jumlah_setoran']), 2);
    $rapaksiNum       = round(parseBeratSetoranFloat($transaksi['rapaksi']), 2);
    $beratFinalNum    = max(0.0, round($jumlahSetoranNum - $rapaksiNum, 2));
    $jumlahSetoran    = formatBeratSetoranStr($jumlahSetoranNum);
    $rapaksi          = formatBeratSetoranStr($rapaksiNum);
    $beratFinal       = formatBeratSetoranStr($beratFinalNum);
    $hargaPerKgNum    = max(0, (int)($transaksi['harga_per_kg'] ?? 0));
    $hargaPerKgFmt    = formatRupiahTransaksiAkhir($hargaPerKgNum) . ' /Kg';
    $jumlahUangRaw    = max(0, (int)($transaksi['jumlah_uang'] ?? 0));
    $jumlahUangNum    = ($jumlahUangRaw <= 0 && $beratFinalNum > 0 && $hargaPerKgNum > 0)
        ? (int)round($beratFinalNum * $hargaPerKgNum)
        : $jumlahUangRaw;
    $jumlahUangFmt    = formatRupiahTransaksiAkhir($jumlahUangNum);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $transaksi ? htmlspecialchars($kodeTransaksi . ' - Bukti Transaksi Akhir') : 'Bukti Transaksi Akhir Tidak Ditemukan' ?></title>
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
        .section-heading { font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #334155; margin: 0 0 0.5rem; }
        .payment-table { width: 100%; border-collapse: collapse; margin-bottom: 1.25rem; font-size: 0.85rem; }
        .payment-table th, .payment-table td { border: 1px solid #cbd5e1; padding: 0.65rem 0.85rem; text-align: left; }
        .payment-table th { background: #f1f5f9; color: #0f172a; font-weight: 700; }
        .payment-table td { color: #0f172a; font-weight: 600; }
        .amount-highlight { background: #ecfdf5; color: #065f46 !important; font-size: 1rem; font-weight: 800 !important; }
        .rapaksi-val { color: #92400e !important; background: #fffbeb; }
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
    <?php if (!$transaksi): ?>
        <div class="empty-state-box">
            <h1>Bukti Transaksi Akhir Tidak Ditemukan</h1>
            <p>Data transaksi akhir yang diminta tidak tersedia atau telah dihapus dari sistem.</p>
            <a href="<?= htmlspecialchars($backUrl) ?>" class="toolbar-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Transaksi Akhir
            </a>
        </div>
    <?php else: ?>
        <div class="print-toolbar">
            <a href="<?= htmlspecialchars($backUrl) ?>" class="toolbar-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Transaksi Akhir
            </a>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" class="toolbar-btn" onclick="window.close()">
                    <i class="fa-solid fa-xmark"></i>
                    Tutup Tab
                </button>
                <button type="button" class="toolbar-btn toolbar-btn-primary" onclick="window.print()">
                    <i class="fa-solid fa-print"></i>
                    Print Transaksi Akhir
                </button>
            </div>
        </div>

        <main class="receipt-sheet">
            <header class="receipt-header">
                <div>
                    <h1 class="brand-title">mudatani99</h1>
                    <p class="brand-subtitle">Sistem Pengelolaan Kelompok Pertanian (Manajemen Transaksi Akhir)</p>
                </div>
                <div class="doc-meta">
                    <p class="doc-label">Bukti Transaksi Akhir</p>
                    <p class="doc-number"><?= htmlspecialchars($kodeTransaksi) ?></p>
                    <p class="doc-date">Tanggal: <?= htmlspecialchars($tglFormatted) ?></p>
                </div>
            </header>

            <section class="meta-grid">
                <div class="meta-box">
                    <h2 class="meta-box-title">Identitas Anggota Petani</h2>
                    <div class="meta-row">
                        <span class="meta-key">Nama Petani</span>
                        <span class="meta-val"><?= htmlspecialchars($namaPetani) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">Alamat</span>
                        <span class="meta-val"><?= htmlspecialchars($alamatPetani) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">Tanaman</span>
                        <span class="meta-val"><?= htmlspecialchars($namaTanaman) ?></span>
                    </div>
                </div>

                <div class="meta-box">
                    <h2 class="meta-box-title">Ringkasan Transaksi</h2>
                    <div class="meta-row">
                        <span class="meta-key">Harga (/Kg)</span>
                        <span class="meta-val"><?= htmlspecialchars($hargaPerKgFmt) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">Total Uang</span>
                        <span class="meta-val"><?= htmlspecialchars($jumlahUangFmt) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">Tanggal</span>
                        <span class="meta-val"><?= htmlspecialchars($tglFormatted) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">Dicatat Oleh</span>
                        <span class="meta-val"><?= htmlspecialchars($dicatatOleh) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">Keterangan</span>
                        <span class="meta-val"><?= htmlspecialchars($keterangan) ?></span>
                    </div>
                </div>
            </section>

            <h2 class="section-heading">Perhitungan Berat Final &amp; Harga</h2>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">No</th>
                        <th style="width: 60%;">Komponen Perhitungan</th>
                        <th style="width: 30%;">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Tanaman (Dari Master Tanaman)</td>
                        <td><?= htmlspecialchars($namaTanaman) ?></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Jumlah Setoran (Dari Data Setoran Petani)</td>
                        <td><?= htmlspecialchars($jumlahSetoran) ?></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Rapaksi (Potongan)</td>
                        <td class="rapaksi-val">- <?= htmlspecialchars($rapaksi) ?></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Berat Final (Jumlah Setoran - Rapaksi)</td>
                        <td><strong><?= htmlspecialchars($beratFinal) ?></strong></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Harga (/Kg)</td>
                        <td><?= htmlspecialchars($hargaPerKgFmt) ?></td>
                    </tr>
                    <tr>
                        <th colspan="2" style="text-align: right;">Total Uang (Berat Final &times; Harga /Kg)</th>
                        <td class="amount-highlight"><?= htmlspecialchars($jumlahUangFmt) ?></td>
                    </tr>
                </tbody>
            </table>

            <?php if (!empty($riwayatSetoran)): ?>
            <h2 class="section-heading">Rincian Setoran Petani Terkait</h2>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">No</th>
                        <th style="width: 24%;">Kode Setoran</th>
                        <th style="width: 18%;">Tanggal</th>
                        <th style="width: 30%;">Tanaman Disetor</th>
                        <th style="width: 20%;">Berat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayatSetoran as $idx => $st): ?>
                    <?php
                        $stTgl = (!empty($st['tanggal']) && $st['tanggal'] !== '0000-00-00')
                            ? date('d/m/Y', strtotime($st['tanggal']))
                            : '-';
                    ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td><?= htmlspecialchars($st['kode_setoran']) ?></td>
                        <td><?= htmlspecialchars($stTgl) ?></td>
                        <td><?= htmlspecialchars($st['nama_tanaman']) ?></td>
                        <td><?= htmlspecialchars($st['jumlah_berat']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <section class="signature-grid">
                <div class="signature-box">
                    <div>Petani</div>
                    <div class="signature-space"></div>
                    <span class="signature-name"><?= htmlspecialchars($namaPetani) ?></span>
                </div>
                <div class="signature-box">
                    <div>Petugas Pencatat</div>
                    <div class="signature-space"></div>
                    <span class="signature-name"><?= htmlspecialchars($dicatatOleh !== '-' ? $dicatatOleh : 'Pengurus Poktan') ?></span>
                </div>
            </section>

            <footer class="receipt-footer-note">
                <span>Dicetak pada: <?= date('d/m/Y H:i') ?></span>
                <span>Kode Transaksi: <?= htmlspecialchars($kodeTransaksi) ?></span>
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
