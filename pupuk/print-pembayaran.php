<?php
/**
 * mudatani99 - Cetak Bukti Pembayaran Hutang Pupuk
 * File: pupuk/print-pembayaran.php
 */
require_once __DIR__ . '/../config/app.php';
requireLogin();
require_once ROOT_PATH . '/config/koneksi.php';
require_once __DIR__ . '/helpers.php';

$bayarId = (int)($_GET['id'] ?? 0);
$bayar   = null;

if ($bayarId > 0) {
    $stmt = $conn->prepare("
        SELECT b.id, b.hutang_id, b.anggota_id,
               COALESCE(a.nama_petani, b.nama_petani) AS nama_petani,
               COALESCE(a.alamat, '-') AS alamat_petani,
               COALESCE(m.nama_produk, p.nama_pupuk, h.nama_pupuk, b.nama_pupuk) AS nama_pupuk,
               h.jumlah_karung, h.jumlah_berat, h.harga,
               h.tanggal AS tanggal_pinjam, h.tanggal_lunas, h.keterangan AS keterangan_pinjam,
               b.pembayaran_ke, b.tanggal_bayar,
               b.total_hutang, b.sisa_sebelum, b.jumlah_bayar, b.sisa_hutang,
               b.metode_bayar, b.status, b.keterangan AS keterangan_bayar, b.dicatat_oleh, b.created_at
        FROM pembayaran_hutang_pupuk b
        LEFT JOIN hutang_pupuk h ON b.hutang_id = h.id
        LEFT JOIN anggota_petani a ON b.anggota_id = a.id
        LEFT JOIN pengelolaan_pupuk p ON h.pengelolaan_pupuk_id = p.id
        LEFT JOIN master_produk m ON COALESCE(p.pupuk_id, h.pupuk_id) = m.id AND m.jenis = 'Pupuk'
        WHERE b.id = ?
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param("i", $bayarId);
        $stmt->execute();
        $res   = $stmt->get_result();
        $bayar = $res ? $res->fetch_assoc() : null;
        $stmt->close();
    }
}

$autoPrint = (($_GET['auto'] ?? '1') === '1');
$backUrl   = appUrl('pupuk/?view=riwayat');

if ($bayar) {
    $namaPetani   = formatTitleCase($bayar['nama_petani']);
    $alamatPetani = trim((string)($bayar['alamat_petani'] ?? '-')) ?: '-';
    $namaPupuk    = formatTitleCase($bayar['nama_pupuk']);
    $tglBayarRaw  = $bayar['tanggal_bayar'] ?? '';
    $tglBayar     = (!empty($tglBayarRaw) && $tglBayarRaw !== '0000-00-00')
        ? date('d/m/Y', strtotime($tglBayarRaw))
        : '-';
    $tglKode      = (!empty($tglBayarRaw) && $tglBayarRaw !== '0000-00-00')
        ? date('Ymd', strtotime($tglBayarRaw))
        : date('Ymd');
    $noBukti      = sprintf('BKP-%s-%04d', $tglKode, (int)$bayar['id']);

    $tglPinjamRaw = $bayar['tanggal_pinjam'] ?? '';
    $tglPinjam    = (!empty($tglPinjamRaw) && $tglPinjamRaw !== '0000-00-00')
        ? date('d/m/Y', strtotime($tglPinjamRaw))
        : '-';

    $ketPinjam    = stripHutangPaymentNote($bayar['keterangan_pinjam'] ?? '-');
    $ketBayar     = formatTitleCase($bayar['keterangan_bayar'] ?: '-');
    $dicatatOleh  = formatTitleCase($bayar['dicatat_oleh'] ?: '-');
    $isLunas      = (($bayar['status'] ?? '') === 'Lunas');

    $hutangItems = getHutangItemsByHutangId($conn, (int)$bayar['hutang_id']);
    if (!empty($hutangItems)) {
        $itemNames = [];
        $sumK = 0;
        $sumB = 0.0;
        foreach ($hutangItems as $hi) {
            if (!in_array($hi['nama_pupuk'], $itemNames, true)) {
                $itemNames[] = $hi['nama_pupuk'];
            }
            $sumK += (int)$hi['karung_num'];
            $sumB += (float)$hi['berat_num'];
        }
        $namaPupuk    = implode(', ', $itemNames);
        $kuantitasStr = formatKarungStr($sumK) . ' / ' . formatBeratStr($sumB);
    } else {
        $kuantitasParts = [];
        if (!empty($bayar['jumlah_karung'])) $kuantitasParts[] = formatTitleCase($bayar['jumlah_karung']);
        if (!empty($bayar['jumlah_berat'])) $kuantitasParts[] = formatTitleCase($bayar['jumlah_berat']);
        $kuantitasStr = !empty($kuantitasParts) ? implode(' / ', $kuantitasParts) : '-';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $bayar ? htmlspecialchars($noBukti . ' - Bukti Pembayaran Hutang Pupuk') : 'Bukti Pembayaran Tidak Ditemukan' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
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
        .toolbar-btn:hover {
            background: #f8fafc;
        }
        .toolbar-btn:focus-visible {
            outline: 3px solid #16a34a;
            outline-offset: 2px;
        }
        .toolbar-btn-primary {
            background: #15803d;
            border-color: #15803d;
            color: #ffffff;
        }
        .toolbar-btn-primary:hover {
            background: #166534;
        }
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
        .brand-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #14532d;
            margin: 0;
            letter-spacing: -0.01em;
        }
        .brand-subtitle {
            font-size: 0.8rem;
            color: #334155;
            margin: 0.2rem 0 0;
        }
        .doc-meta {
            text-align: right;
        }
        .doc-label {
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #15803d;
            margin: 0 0 0.25rem;
        }
        .doc-number {
            font-size: 0.95rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }
        .doc-date {
            font-size: 0.78rem;
            color: #334155;
            margin: 0.15rem 0 0;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .meta-box {
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.85rem 1rem;
            background: #f8fafc;
        }
        .meta-box-title {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #334155;
            margin: 0 0 0.5rem;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.82rem;
            padding: 0.2rem 0;
        }
        .meta-key {
            color: #475569;
            flex-shrink: 0;
        }
        .meta-val {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
            word-break: break-word;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
        }
        .payment-table th,
        .payment-table td {
            border: 1px solid #cbd5e1;
            padding: 0.65rem 0.85rem;
            text-align: left;
        }
        .payment-table th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            width: 45%;
        }
        .payment-table td {
            color: #0f172a;
            font-weight: 600;
        }
        .amount-highlight {
            background: #ecfdf5;
            color: #065f46 !important;
            font-size: 1rem;
            font-weight: 800 !important;
        }
        .status-pill {
            display: inline-block;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 800;
            border: 1px solid transparent;
        }
        .status-lunas {
            background: #ecfdf5;
            color: #065f46;
            border-color: #6ee7b7;
        }
        .status-belum {
            background: #fff1f2;
            color: #9f1239;
            border-color: #fda4af;
        }
        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 2rem;
            margin-top: 2rem;
            padding-top: 0.5rem;
        }
        .signature-box {
            text-align: center;
            font-size: 0.82rem;
            color: #334155;
        }
        .signature-space {
            height: 64px;
        }
        .signature-name {
            font-weight: 700;
            color: #0f172a;
            border-top: 1px solid #64748b;
            display: inline-block;
            min-width: 170px;
            padding-top: 0.35rem;
        }
        .receipt-footer-note {
            margin-top: 1.5rem;
            padding-top: 0.85rem;
            border-top: 1px dashed #cbd5e1;
            font-size: 0.75rem;
            color: #475569;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .empty-state-box {
            max-width: 520px;
            margin: 3rem auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            padding: 2rem;
            text-align: center;
        }
        .empty-state-box h1 {
            font-size: 1.1rem;
            margin: 0 0 0.5rem;
            color: #0f172a;
        }
        .empty-state-box p {
            font-size: 0.88rem;
            color: #475569;
            margin: 0 0 1.25rem;
        }
        @media (max-width: 640px) {
            .receipt-sheet {
                padding: 1.25rem;
            }
            .doc-meta {
                text-align: left;
            }
            .meta-grid,
            .signature-grid {
                grid-template-columns: 1fr;
                gap: 0.85rem;
            }
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-toolbar {
                display: none !important;
            }
            .receipt-sheet {
                border: none;
                border-radius: 0;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php if (!$bayar): ?>
        <div class="empty-state-box">
            <h1>Data Bukti Pembayaran Tidak Ditemukan</h1>
            <p>Transaksi pembayaran yang diminta tidak tersedia atau telah dihapus dari sistem.</p>
            <a href="<?= htmlspecialchars($backUrl) ?>" class="toolbar-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Riwayat Pembayaran
            </a>
        </div>
    <?php else: ?>
        <div class="print-toolbar">
            <a href="<?= htmlspecialchars($backUrl) ?>" class="toolbar-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Riwayat
            </a>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" class="toolbar-btn" onclick="window.close()">
                    <i class="fa-solid fa-xmark"></i>
                    Tutup Tab
                </button>
                <button type="button" class="toolbar-btn toolbar-btn-primary" onclick="window.print()">
                    <i class="fa-solid fa-print"></i>
                    Print Bukti Pembayaran
                </button>
            </div>
        </div>

        <main class="receipt-sheet">
            <header class="receipt-header">
                <div>
                    <h1 class="brand-title">mudatani99</h1>
                    <p class="brand-subtitle">Sistem Pengelolaan Kelompok Pertanian (Inventaris Pupuk)</p>
                </div>
                <div class="doc-meta">
                    <p class="doc-label">Bukti Pembayaran Hutang Pupuk</p>
                    <p class="doc-number"><?= htmlspecialchars($noBukti) ?></p>
                    <p class="doc-date">Tanggal Bayar: <?= htmlspecialchars($tglBayar) ?> (Tahap ke-<?= (int)$bayar['pembayaran_ke'] ?>)</p>
                </div>
            </header>

            <section class="meta-grid">
                <div class="meta-box">
                    <h2 class="meta-box-title">Identitas Penghutang</h2>
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
                    <h2 class="meta-box-title">Detail Pinjaman Pupuk</h2>
                    <div class="meta-row">
                        <span class="meta-key">Nama Pupuk</span>
                        <span class="meta-val"><?= htmlspecialchars($namaPupuk) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">Kuantitas</span>
                        <span class="meta-val"><?= htmlspecialchars($kuantitasStr) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-key">Tanggal Pinjam</span>
                        <span class="meta-val"><?= htmlspecialchars($tglPinjam) ?></span>
                    </div>
                </div>
            </section>

            <?php if (!empty($hutangItems)): ?>
            <table class="payment-table" aria-label="Rincian Item Pupuk">
                <thead>
                    <tr>
                        <th style="width: 34%;">Item Pupuk</th>
                        <th style="width: 16%;">Karung</th>
                        <th style="width: 16%;">Berat</th>
                        <th style="width: 17%;">Harga</th>
                        <th style="width: 17%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hutangItems as $hi): ?>
                    <tr>
                        <td><?= htmlspecialchars($hi['nama_pupuk']) ?></td>
                        <td><?= htmlspecialchars($hi['jumlah_karung']) ?></td>
                        <td><?= htmlspecialchars($hi['jumlah_berat']) ?></td>
                        <td><?= htmlspecialchars($hi['harga']) ?></td>
                        <td><?= htmlspecialchars($hi['subtotal']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <table class="payment-table">
                <tbody>
                    <tr>
                        <th>Total Hutang Awal</th>
                        <td><?= htmlspecialchars(formatRupiahStr($bayar['total_hutang'])) ?></td>
                    </tr>
                    <tr>
                        <th>Sisa Hutang Sebelum Pembayaran</th>
                        <td><?= htmlspecialchars(formatRupiahStr($bayar['sisa_sebelum'])) ?></td>
                    </tr>
                    <tr>
                        <th>Jumlah Dibayar (Tahap ke-<?= (int)$bayar['pembayaran_ke'] ?>)</th>
                        <td class="amount-highlight"><?= htmlspecialchars(formatRupiahStr($bayar['jumlah_bayar'])) ?></td>
                    </tr>
                    <tr>
                        <th>Sisa Hutang Setelah Pembayaran</th>
                        <td><?= htmlspecialchars(formatRupiahStr($bayar['sisa_hutang'])) ?></td>
                    </tr>
                    <tr>
                        <th>Metode Pembayaran</th>
                        <td><?= htmlspecialchars($bayar['metode_bayar']) ?></td>
                    </tr>
                    <tr>
                        <th>Status Hutang</th>
                        <td>
                            <span class="status-pill <?= $isLunas ? 'status-lunas' : 'status-belum' ?>">
                                <?= htmlspecialchars($bayar['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Keterangan Pembayaran</th>
                        <td><?= htmlspecialchars($ketBayar) ?></td>
                    </tr>
                    <tr>
                        <th>Catatan Pinjaman Awal</th>
                        <td><?= htmlspecialchars($ketPinjam) ?></td>
                    </tr>
                </tbody>
            </table>

            <section class="signature-grid">
                <div class="signature-box">
                    <div>Penghutang / Anggota Petani</div>
                    <div class="signature-space"></div>
                    <span class="signature-name"><?= htmlspecialchars($namaPetani) ?></span>
                </div>
                <div class="signature-box">
                    <div>Diterima &amp; Dicatat Oleh</div>
                    <div class="signature-space"></div>
                    <span class="signature-name"><?= htmlspecialchars($dicatatOleh !== '-' ? $dicatatOleh : 'Bendahara Poktan') ?></span>
                </div>
            </section>

            <footer class="receipt-footer-note">
                <span>Dicetak pada: <?= date('d/m/Y H:i') ?></span>
                <span>ID Transaksi: #<?= (int)$bayar['id'] ?> (Hutang #<?= (int)$bayar['hutang_id'] ?>)</span>
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
