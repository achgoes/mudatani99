<?php
/**
 * mudatani99 - CRUD Manajemen Transaksi Akhir
 * File: transaksi-akhir/actions.php
 */

$feedbackMessage = "";
$feedbackType    = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_transaksi_akhir') {
        $anggotaId   = (int)($_POST['anggota_id'] ?? 0);
        $tanamanId   = (int)($_POST['tanaman_id'] ?? 0);
        $tanggalRaw  = trim($_POST['tanggal'] ?? '');
        $rapaksiNum  = parseAngkaNonNegatifStrict($_POST['rapaksi'] ?? '');
        $hargaPerKg  = parseHargaPerKgInt($_POST['harga_per_kg'] ?? '');
        $keterangan  = formatTitleCaseSetoran($_POST['keterangan'] ?? '');

        $tanggal = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalRaw) ? $tanggalRaw : date('Y-m-d');

        if ($anggotaId <= 0) {
            $feedbackMessage = "Nama Petani wajib dipilih dari daftar Anggota Petani.";
            $feedbackType    = "danger";
        } elseif ($tanamanId <= 0) {
            $feedbackMessage = "Tanaman wajib dipilih dari Master Tanaman.";
            $feedbackType    = "danger";
        } elseif ($rapaksiNum === null) {
            $feedbackMessage = "Nilai Rapaksi wajib diisi dengan angka minimal 0 Kg.";
            $feedbackType    = "danger";
        } elseif ($hargaPerKg === null) {
            $feedbackMessage = "Harga (/Kg) wajib diisi dengan nominal lebih dari Rp 0.";
            $feedbackType    = "danger";
        } else {
            $petaniRow  = null;
            $cekAnggota = $conn->prepare("SELECT id, nama_petani FROM anggota_petani WHERE id = ? LIMIT 1");
            if ($cekAnggota) {
                $cekAnggota->bind_param("i", $anggotaId);
                $cekAnggota->execute();
                $resAnggota = $cekAnggota->get_result();
                $petaniRow  = $resAnggota ? $resAnggota->fetch_assoc() : null;
                $cekAnggota->close();
            }

            $tanamanRow = null;
            $cekTanaman = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
            if ($cekTanaman) {
                $cekTanaman->bind_param("i", $tanamanId);
                $cekTanaman->execute();
                $resTanaman = $cekTanaman->get_result();
                $tanamanRow = $resTanaman ? $resTanaman->fetch_assoc() : null;
                $cekTanaman->close();
            }

            if (!$petaniRow) {
                $feedbackMessage = "Anggota petani yang dipilih tidak ditemukan pada Daftar Anggota Petani.";
                $feedbackType    = "danger";
            } elseif (!$tanamanRow) {
                $feedbackMessage = "Tanaman yang dipilih tidak ditemukan pada Master Tanaman.";
                $feedbackType    = "danger";
            } else {
                $rekapSetoran     = getRekapSetoranPerAnggota($conn);
                $rekapPetani      = $rekapSetoran[$anggotaId] ?? null;
                $beratPerTanaman  = $rekapPetani['berat_per_tanaman'] ?? [];
                $jumlahSetoranNum = isset($beratPerTanaman[$tanamanId])
                    ? round((float)$beratPerTanaman[$tanamanId], 2)
                    : 0.0;

                if ($jumlahSetoranNum <= 0) {
                    $namaTanamErr = formatTitleCaseSetoran($tanamanRow['nama_produk']);
                    $feedbackMessage = "Sisa setoran tanaman {$namaTanamErr} untuk petani yang dipilih adalah 0 Kg (belum ada setoran atau sudah habis tercatat pada Transaksi Akhir).";
                    $feedbackType    = "danger";
                } elseif ($rapaksiNum > $jumlahSetoranNum) {
                    $feedbackMessage = "Nilai rapaksi (" . formatBeratSetoranStr($rapaksiNum) . ") tidak boleh melebihi jumlah setoran (" . formatBeratSetoranStr($jumlahSetoranNum) . ").";
                    $feedbackType    = "danger";
                } else {
                    if ($keterangan === '') {
                        $keterangan = '-';
                    }
                    $keterangan       = mb_substr($keterangan, 0, 255, 'UTF-8');
                    $namaPetani       = mb_substr(formatTitleCaseSetoran($petaniRow['nama_petani']), 0, 100, 'UTF-8');
                    $namaTanaman      = mb_substr(formatTitleCaseSetoran($tanamanRow['nama_produk']), 0, 100, 'UTF-8');
                    $jumlahSetoranStr = formatBeratSetoranStr($jumlahSetoranNum);
                    $rapaksiStr       = formatBeratSetoranStr($rapaksiNum);
                    $beratFinalNum    = max(0.0, round($jumlahSetoranNum - $rapaksiNum, 2));
                    $beratFinalStr    = formatBeratSetoranStr($beratFinalNum);
                    $jumlahUang       = (int)round($beratFinalNum * $hargaPerKg);
                    $kodeTransaksi    = generateKodeTransaksiAkhir($conn, $tanggal);
                    $dicatatOleh      = mb_substr(formatTitleCaseSetoran($_SESSION['nama'] ?? ($_SESSION['username'] ?? '-')), 0, 100, 'UTF-8');

                    $insStmt = $conn->prepare("
                        INSERT INTO transaksi_akhir (
                            kode_transaksi, anggota_id, nama_petani, tanaman_id, nama_tanaman,
                            jumlah_setoran, rapaksi, berat_final, harga_per_kg, jumlah_uang,
                            tanggal, keterangan, dicatat_oleh
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    if ($insStmt) {
                        $insStmt->bind_param(
                            "sisissssiisss",
                            $kodeTransaksi,
                            $anggotaId,
                            $namaPetani,
                            $tanamanId,
                            $namaTanaman,
                            $jumlahSetoranStr,
                            $rapaksiStr,
                            $beratFinalStr,
                            $hargaPerKg,
                            $jumlahUang,
                            $tanggal,
                            $keterangan,
                            $dicatatOleh
                        );

                        if ($insStmt->execute()) {
                            $insStmt->close();
                            header('Location: ' . appUrl('transaksi-akhir/?status=added&name=' . urlencode($namaPetani) . '&berat=' . urlencode($beratFinalStr)));
                            exit;
                        } else {
                            $feedbackMessage = dbErrorUntukPengguna('simpan transaksi akhir', $conn->error);
                            $feedbackType    = "danger";
                            $insStmt->close();
                        }
                    } else {
                        $feedbackMessage = dbErrorUntukPengguna('siapkan query tambah transaksi akhir', $conn->error);
                        $feedbackType    = "danger";
                    }
                }
            }
        }
    } elseif ($action === 'edit_transaksi_akhir') {
        $id          = (int)($_POST['id'] ?? 0);
        $anggotaId   = (int)($_POST['anggota_id'] ?? 0);
        $tanamanId   = (int)($_POST['tanaman_id'] ?? 0);
        $tanggalRaw  = trim($_POST['tanggal'] ?? '');
        $rapaksiNum  = parseAngkaNonNegatifStrict($_POST['rapaksi'] ?? '');
        $hargaPerKg  = parseHargaPerKgInt($_POST['harga_per_kg'] ?? '');
        $keterangan  = formatTitleCaseSetoran($_POST['keterangan'] ?? '');

        $tanggal = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalRaw) ? $tanggalRaw : date('Y-m-d');

        if ($id <= 0) {
            $feedbackMessage = "ID transaksi akhir tidak valid.";
            $feedbackType    = "danger";
        } elseif ($anggotaId <= 0) {
            $feedbackMessage = "Nama Petani wajib dipilih dari daftar Anggota Petani.";
            $feedbackType    = "danger";
        } elseif ($tanamanId <= 0) {
            $feedbackMessage = "Tanaman wajib dipilih dari Master Tanaman.";
            $feedbackType    = "danger";
        } elseif ($rapaksiNum === null) {
            $feedbackMessage = "Nilai Rapaksi wajib diisi dengan angka minimal 0 Kg.";
            $feedbackType    = "danger";
        } elseif ($hargaPerKg === null) {
            $feedbackMessage = "Harga (/Kg) wajib diisi dengan nominal lebih dari Rp 0.";
            $feedbackType    = "danger";
        } else {
            $existing    = null;
            $cekExisting = $conn->prepare("SELECT id, anggota_id, tanaman_id, jumlah_setoran FROM transaksi_akhir WHERE id = ? LIMIT 1");
            if ($cekExisting) {
                $cekExisting->bind_param("i", $id);
                $cekExisting->execute();
                $resExisting = $cekExisting->get_result();
                $existing    = $resExisting ? $resExisting->fetch_assoc() : null;
                $cekExisting->close();
            }

            if (!$existing) {
                $feedbackMessage = "Data transaksi akhir yang akan diubah tidak ditemukan.";
                $feedbackType    = "danger";
            } else {
                $petaniRow  = null;
                $cekAnggota = $conn->prepare("SELECT id, nama_petani FROM anggota_petani WHERE id = ? LIMIT 1");
                if ($cekAnggota) {
                    $cekAnggota->bind_param("i", $anggotaId);
                    $cekAnggota->execute();
                    $resAnggota = $cekAnggota->get_result();
                    $petaniRow  = $resAnggota ? $resAnggota->fetch_assoc() : null;
                    $cekAnggota->close();
                }

                $tanamanRow = null;
                $cekTanaman = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
                if ($cekTanaman) {
                    $cekTanaman->bind_param("i", $tanamanId);
                    $cekTanaman->execute();
                    $resTanaman = $cekTanaman->get_result();
                    $tanamanRow = $resTanaman ? $resTanaman->fetch_assoc() : null;
                    $cekTanaman->close();
                }

                if (!$petaniRow) {
                    $feedbackMessage = "Anggota petani yang dipilih tidak ditemukan pada Daftar Anggota Petani.";
                    $feedbackType    = "danger";
                } elseif (!$tanamanRow) {
                    $feedbackMessage = "Tanaman yang dipilih tidak ditemukan pada Master Tanaman.";
                    $feedbackType    = "danger";
                } else {
                    $rekapSetoran     = getRekapSetoranPerAnggota($conn, $id);
                    $rekapPetani      = $rekapSetoran[$anggotaId] ?? null;
                    $beratPerTanaman  = $rekapPetani['berat_per_tanaman'] ?? [];
                    $jumlahSetoranNum = isset($beratPerTanaman[$tanamanId])
                        ? round((float)$beratPerTanaman[$tanamanId], 2)
                        : 0.0;

                    // Fallback ke nilai setoran tersimpan jika petani & tanaman tetap sama dan data setoran kosong
                    if (
                        $jumlahSetoranNum <= 0
                        && $anggotaId === (int)$existing['anggota_id']
                        && $tanamanId === (int)($existing['tanaman_id'] ?? 0)
                    ) {
                        $jumlahSetoranNum = round(parseBeratSetoranFloat($existing['jumlah_setoran']), 2);
                    }

                    if ($jumlahSetoranNum <= 0) {
                        $namaTanamErr = formatTitleCaseSetoran($tanamanRow['nama_produk']);
                        $feedbackMessage = "Sisa setoran tanaman {$namaTanamErr} untuk petani yang dipilih adalah 0 Kg.";
                        $feedbackType    = "danger";
                    } elseif ($rapaksiNum > $jumlahSetoranNum) {
                        $feedbackMessage = "Nilai rapaksi (" . formatBeratSetoranStr($rapaksiNum) . ") tidak boleh melebihi jumlah setoran (" . formatBeratSetoranStr($jumlahSetoranNum) . ").";
                        $feedbackType    = "danger";
                    } else {
                        if ($keterangan === '') {
                            $keterangan = '-';
                        }
                        $keterangan       = mb_substr($keterangan, 0, 255, 'UTF-8');
                        $namaPetani       = mb_substr(formatTitleCaseSetoran($petaniRow['nama_petani']), 0, 100, 'UTF-8');
                        $namaTanaman      = mb_substr(formatTitleCaseSetoran($tanamanRow['nama_produk']), 0, 100, 'UTF-8');
                        $jumlahSetoranStr = formatBeratSetoranStr($jumlahSetoranNum);
                        $rapaksiStr       = formatBeratSetoranStr($rapaksiNum);
                        $beratFinalNum    = max(0.0, round($jumlahSetoranNum - $rapaksiNum, 2));
                        $beratFinalStr    = formatBeratSetoranStr($beratFinalNum);
                        $jumlahUang       = (int)round($beratFinalNum * $hargaPerKg);

                        $updStmt = $conn->prepare("
                            UPDATE transaksi_akhir
                            SET anggota_id = ?, nama_petani = ?, tanaman_id = ?, nama_tanaman = ?,
                                jumlah_setoran = ?, rapaksi = ?, berat_final = ?, harga_per_kg = ?, jumlah_uang = ?,
                                tanggal = ?, keterangan = ?
                            WHERE id = ?
                        ");

                        if ($updStmt) {
                            $updStmt->bind_param(
                                "isissssiissi",
                                $anggotaId,
                                $namaPetani,
                                $tanamanId,
                                $namaTanaman,
                                $jumlahSetoranStr,
                                $rapaksiStr,
                                $beratFinalStr,
                                $hargaPerKg,
                                $jumlahUang,
                                $tanggal,
                                $keterangan,
                                $id
                            );

                            if ($updStmt->execute()) {
                                $updStmt->close();
                                header('Location: ' . appUrl('transaksi-akhir/?status=updated&name=' . urlencode($namaPetani) . '&berat=' . urlencode($beratFinalStr)));
                                exit;
                            } else {
                                $feedbackMessage = dbErrorUntukPengguna('perbarui transaksi akhir', $conn->error);
                                $feedbackType    = "danger";
                                $updStmt->close();
                            }
                        } else {
                            $feedbackMessage = dbErrorUntukPengguna('siapkan query edit transaksi akhir', $conn->error);
                            $feedbackType    = "danger";
                        }
                    }
                }
            }
        }
    } elseif ($action === 'delete_transaksi_akhir') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $delStmt = $conn->prepare("DELETE FROM transaksi_akhir WHERE id = ?");
            if ($delStmt) {
                $delStmt->bind_param("i", $id);
                if ($delStmt->execute()) {
                    $delStmt->close();
                    header('Location: ' . appUrl('transaksi-akhir/?status=deleted'));
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('hapus data transaksi akhir', $conn->error);
                    $feedbackType    = "danger";
                    $delStmt->close();
                }
            } else {
                $feedbackMessage = dbErrorUntukPengguna('siapkan query hapus transaksi akhir', $conn->error);
                $feedbackType    = "danger";
            }
        }
    }
}
