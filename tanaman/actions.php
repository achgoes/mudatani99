<?php
$feedbackMessage = "";
$feedbackType    = "";

// Proses Aksi Form POST (Tambah, Edit, Hapus Pengelolaan Tanaman)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TAMBAH PENGELOLAAN TANAMAN
    if ($action === 'add') {
        $tanaman_id   = (int)($_POST['tanaman_id'] ?? 0);
        $jumlah_angka = trim($_POST['jumlah_angka'] ?? '');
        $jumlah_berat = formatTitleCase($_POST['jumlah_berat'] ?? '');
        $keterangan   = formatTitleCase($_POST['keterangan'] ?? '');

        if ($tanaman_id <= 0 || $jumlah_angka === '' || empty($jumlah_berat)) {
            $feedbackMessage = "Nama Tanaman, Jumlah Kemasan (Pcs), dan Jumlah Berat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            // Satuan kemasan untuk inventaris tanaman adalah Pcs saja
            $angkaBersih    = preg_replace('/[^0-9.,]/', '', $jumlah_angka);
            $jumlah_kemasan = ($angkaBersih !== '' ? $angkaBersih : $jumlah_angka) . ' Pcs';

            if (preg_match('/^[0-9]+([.,][0-9]+)?$/', $jumlah_berat)) {
                $jumlah_berat .= ' Kg';
            }

            // Pastikan tanaman_id valid di master_produk dengan jenis = 'Tanaman'
            $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
            $cekStmt->bind_param("i", $tanaman_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $tanamanRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            if (!$tanamanRow) {
                $feedbackMessage = "Nama tanaman yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $nama_tanaman = formatTitleCase($tanamanRow['nama_produk']);
                $stmt = $conn->prepare("INSERT INTO pengelolaan_tanaman (tanaman_id, nama_tanaman, jumlah_kemasan, jumlah_berat, keterangan) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("issss", $tanaman_id, $nama_tanaman, $jumlah_kemasan, $jumlah_berat, $keterangan);
                    if ($stmt->execute()) {
                        header('Location: ' . appUrl('tanaman/?status=added&name=' . urlencode($nama_tanaman)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal menambahkan data tanaman: " . $conn->error;
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    }

    // 2. EDIT PENGELOLAAN TANAMAN
    elseif ($action === 'edit') {
        $id           = (int)($_POST['id'] ?? 0);
        $tanaman_id   = (int)($_POST['tanaman_id'] ?? 0);
        $jumlah_angka = trim($_POST['jumlah_angka'] ?? '');
        $jumlah_berat = formatTitleCase($_POST['jumlah_berat'] ?? '');
        $keterangan   = formatTitleCase($_POST['keterangan'] ?? '');

        if ($id <= 0 || $tanaman_id <= 0 || $jumlah_angka === '' || empty($jumlah_berat)) {
            $feedbackMessage = "ID, Nama Tanaman, Jumlah Kemasan (Pcs), dan Jumlah Berat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $angkaBersih    = preg_replace('/[^0-9.,]/', '', $jumlah_angka);
            $jumlah_kemasan = ($angkaBersih !== '' ? $angkaBersih : $jumlah_angka) . ' Pcs';

            if (preg_match('/^[0-9]+([.,][0-9]+)?$/', $jumlah_berat)) {
                $jumlah_berat .= ' Kg';
            }

            // Pastikan tanaman_id valid di master_produk dengan jenis = 'Tanaman'
            $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
            $cekStmt->bind_param("i", $tanaman_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $tanamanRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            if (!$tanamanRow) {
                $feedbackMessage = "Nama tanaman yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $nama_tanaman = formatTitleCase($tanamanRow['nama_produk']);
                $stmt = $conn->prepare("UPDATE pengelolaan_tanaman SET tanaman_id = ?, nama_tanaman = ?, jumlah_kemasan = ?, jumlah_berat = ?, keterangan = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("issssi", $tanaman_id, $nama_tanaman, $jumlah_kemasan, $jumlah_berat, $keterangan, $id);
                    if ($stmt->execute()) {
                        header('Location: ' . appUrl('tanaman/?status=updated&name=' . urlencode($nama_tanaman)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal memperbarui data tanaman: " . $conn->error;
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    }

    // 3. HAPUS PENGELOLAAN TANAMAN
    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM pengelolaan_tanaman WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    header('Location: ' . appUrl('tanaman/?status=deleted'));
                    exit;
                } else {
                    $feedbackMessage = "Gagal menghapus data tanaman: " . $conn->error;
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }
}

