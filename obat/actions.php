<?php
$feedbackMessage   = "";
$feedbackType      = "";
$allowedSatuanObat = ['Pcs', 'Botol'];

// Proses Aksi Form POST (Tambah, Edit, Hapus Pengelolaan Obat)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TAMBAH PENGELOLAAN OBAT
    if ($action === 'add') {
        $obat_id        = (int)($_POST['obat_id'] ?? 0);
        $jumlah_angka   = trim($_POST['jumlah_angka'] ?? '');
        $satuan_kemasan = trim($_POST['satuan_kemasan'] ?? '');
        $jumlah_berat   = formatTitleCase($_POST['jumlah_berat'] ?? '');
        $keterangan     = formatTitleCase($_POST['keterangan'] ?? '');

        if ($obat_id <= 0 || $jumlah_angka === '' || !in_array($satuan_kemasan, $allowedSatuanObat, true) || empty($jumlah_berat)) {
            $feedbackMessage = "Nama Obat, Jumlah Kemasan (Pcs / Botol), dan Jumlah Berat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            // Ambil angka bersih lalu gabungkan dengan satuan pilihan (Pcs / Botol)
            $angkaBersih    = preg_replace('/[^0-9.,]/', '', $jumlah_angka);
            $jumlah_kemasan = ($angkaBersih !== '' ? $angkaBersih : $jumlah_angka) . ' ' . $satuan_kemasan;

            if (preg_match('/^[0-9]+([.,][0-9]+)?$/', $jumlah_berat)) {
                $jumlah_berat .= ' Kg';
            }

            // Pastikan obat_id valid di master_produk dengan jenis = 'Obat'
            $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Obat' LIMIT 1");
            $cekStmt->bind_param("i", $obat_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $obatRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            if (!$obatRow) {
                $feedbackMessage = "Nama obat yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $nama_obat = formatTitleCase($obatRow['nama_produk']);
                $stmt = $conn->prepare("INSERT INTO pengelolaan_obat (obat_id, nama_obat, jumlah_kemasan, jumlah_berat, keterangan) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("issss", $obat_id, $nama_obat, $jumlah_kemasan, $jumlah_berat, $keterangan);
                    if ($stmt->execute()) {
                        header('Location: ' . appUrl('obat/?status=added&name=' . urlencode($nama_obat)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal menambahkan data obat: " . $conn->error;
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    }

    // 2. EDIT PENGELOLAAN OBAT
    elseif ($action === 'edit') {
        $id             = (int)($_POST['id'] ?? 0);
        $obat_id        = (int)($_POST['obat_id'] ?? 0);
        $jumlah_angka   = trim($_POST['jumlah_angka'] ?? '');
        $satuan_kemasan = trim($_POST['satuan_kemasan'] ?? '');
        $jumlah_berat   = formatTitleCase($_POST['jumlah_berat'] ?? '');
        $keterangan     = formatTitleCase($_POST['keterangan'] ?? '');

        if ($id <= 0 || $obat_id <= 0 || $jumlah_angka === '' || !in_array($satuan_kemasan, $allowedSatuanObat, true) || empty($jumlah_berat)) {
            $feedbackMessage = "ID, Nama Obat, Jumlah Kemasan (Pcs / Botol), dan Jumlah Berat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $angkaBersih    = preg_replace('/[^0-9.,]/', '', $jumlah_angka);
            $jumlah_kemasan = ($angkaBersih !== '' ? $angkaBersih : $jumlah_angka) . ' ' . $satuan_kemasan;

            if (preg_match('/^[0-9]+([.,][0-9]+)?$/', $jumlah_berat)) {
                $jumlah_berat .= ' Kg';
            }

            // Pastikan obat_id valid di master_produk dengan jenis = 'Obat'
            $cekStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Obat' LIMIT 1");
            $cekStmt->bind_param("i", $obat_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $obatRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            if (!$obatRow) {
                $feedbackMessage = "Nama obat yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $nama_obat = formatTitleCase($obatRow['nama_produk']);
                $stmt = $conn->prepare("UPDATE pengelolaan_obat SET obat_id = ?, nama_obat = ?, jumlah_kemasan = ?, jumlah_berat = ?, keterangan = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("issssi", $obat_id, $nama_obat, $jumlah_kemasan, $jumlah_berat, $keterangan, $id);
                    if ($stmt->execute()) {
                        header('Location: ' . appUrl('obat/?status=updated&name=' . urlencode($nama_obat)));
                        exit;
                    } else {
                        $feedbackMessage = "Gagal memperbarui data obat: " . $conn->error;
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    }

    // 3. HAPUS PENGELOLAAN OBAT
    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM pengelolaan_obat WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    header('Location: ' . appUrl('obat/?status=deleted'));
                    exit;
                } else {
                    $feedbackMessage = "Gagal menghapus data obat: " . $conn->error;
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }
}

