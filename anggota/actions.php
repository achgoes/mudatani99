<?php
$feedbackMessage = "";
$feedbackType    = "";

// Proses Aksi Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // =====================================================================
    // A. CRUD DAFTAR ANGGOTA PETANI
    // =====================================================================
    if ($action === 'add') {
        $nama_petani = trim($_POST['nama_petani'] ?? '');
        $alamat      = trim($_POST['alamat'] ?? '');
        $keterangan  = trim($_POST['keterangan'] ?? '');

        if (empty($nama_petani) || empty($alamat)) {
            $feedbackMessage = "Nama Petani dan Alamat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $stmt = $conn->prepare("INSERT INTO anggota_petani (nama_petani, alamat, keterangan) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $nama_petani, $alamat, $keterangan);
                if ($stmt->execute()) {
                    header('Location: ' . appUrl('anggota/?status=added&name=' . urlencode($nama_petani)));
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('tambah anggota petani', $conn->error);
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'edit') {
        $id          = (int)($_POST['id'] ?? 0);
        $nama_petani = trim($_POST['nama_petani'] ?? '');
        $alamat      = trim($_POST['alamat'] ?? '');
        $keterangan  = trim($_POST['keterangan'] ?? '');

        if ($id <= 0 || empty($nama_petani) || empty($alamat)) {
            $feedbackMessage = "ID, Nama Petani, dan Alamat wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $stmt = $conn->prepare("UPDATE anggota_petani SET nama_petani = ?, alamat = ?, keterangan = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("sssi", $nama_petani, $alamat, $keterangan, $id);
                if ($stmt->execute()) {
                    header('Location: ' . appUrl('anggota/?status=updated&name=' . urlencode($nama_petani)));
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('perbarui anggota petani', $conn->error);
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM anggota_petani WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    header('Location: ' . appUrl('anggota/?status=deleted'));
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('hapus anggota petani', $conn->error);
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }

    // =====================================================================
    // B. CRUD PEMETAAN & LUAS LAHAN
    //    - Nama Petani diambil dari anggota_petani
    //    - Ditanami diambil dari master_produk (Filter: jenis = 'Tanaman')
    // =====================================================================
    elseif ($action === 'add_lahan') {
        $activeView = 'lahan';
        $anggota_id = (int)($_POST['anggota_id'] ?? 0);
        $luas_lahan = trim($_POST['luas_lahan'] ?? '');
        $tanaman_id = (int)($_POST['tanaman_id'] ?? 0);
        $keterangan = trim($_POST['keterangan'] ?? '');

        if ($anggota_id <= 0 || empty($luas_lahan) || $tanaman_id <= 0) {
            $feedbackMessage = "Nama Petani, Luas Lahan, dan Tanaman wajib dipilih.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            // Pastikan anggota_id valid
            $cekStmt = $conn->prepare("SELECT nama_petani FROM anggota_petani WHERE id = ? LIMIT 1");
            $cekStmt->bind_param("i", $anggota_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $petaniRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            // Pastikan tanaman_id valid di master_produk dengan jenis = 'Tanaman'
            $cekTnmStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
            $cekTnmStmt->bind_param("i", $tanaman_id);
            $cekTnmStmt->execute();
            $cekTnmRes = $cekTnmStmt->get_result();
            $tanamanRow = $cekTnmRes ? $cekTnmRes->fetch_assoc() : null;
            $cekTnmStmt->close();

            if (!$petaniRow) {
                $feedbackMessage = "Petani yang dipilih tidak ditemukan di daftar anggota.";
                $feedbackType    = "danger";
            } elseif (!$tanamanRow) {
                $feedbackMessage = "Komoditas tanaman yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $ditanami = $tanamanRow['nama_produk'];
                $stmt = $conn->prepare("INSERT INTO lahan_petani (anggota_id, luas_lahan, tanaman_id, ditanami, keterangan) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("isiss", $anggota_id, $luas_lahan, $tanaman_id, $ditanami, $keterangan);
                    if ($stmt->execute()) {
                        header('Location: ' . appUrl('anggota/?view=lahan&status=added&name=' . urlencode($petaniRow['nama_petani'])));
                        exit;
                    } else {
                        $feedbackMessage = dbErrorUntukPengguna('tambah data lahan', $conn->error);
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    } elseif ($action === 'edit_lahan') {
        $activeView = 'lahan';
        $id         = (int)($_POST['id'] ?? 0);
        $anggota_id = (int)($_POST['anggota_id'] ?? 0);
        $luas_lahan = trim($_POST['luas_lahan'] ?? '');
        $tanaman_id = (int)($_POST['tanaman_id'] ?? 0);
        $keterangan = trim($_POST['keterangan'] ?? '');

        if ($id <= 0 || $anggota_id <= 0 || empty($luas_lahan) || $tanaman_id <= 0) {
            $feedbackMessage = "ID, Nama Petani, Luas Lahan, dan Tanaman wajib dipilih.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $cekStmt = $conn->prepare("SELECT nama_petani FROM anggota_petani WHERE id = ? LIMIT 1");
            $cekStmt->bind_param("i", $anggota_id);
            $cekStmt->execute();
            $cekRes = $cekStmt->get_result();
            $petaniRow = $cekRes ? $cekRes->fetch_assoc() : null;
            $cekStmt->close();

            // Pastikan tanaman_id valid di master_produk dengan jenis = 'Tanaman'
            $cekTnmStmt = $conn->prepare("SELECT id, nama_produk FROM master_produk WHERE id = ? AND jenis = 'Tanaman' LIMIT 1");
            $cekTnmStmt->bind_param("i", $tanaman_id);
            $cekTnmStmt->execute();
            $cekTnmRes = $cekTnmStmt->get_result();
            $tanamanRow = $cekTnmRes ? $cekTnmRes->fetch_assoc() : null;
            $cekTnmStmt->close();

            if (!$petaniRow) {
                $feedbackMessage = "Petani yang dipilih tidak ditemukan di daftar anggota.";
                $feedbackType    = "danger";
            } elseif (!$tanamanRow) {
                $feedbackMessage = "Komoditas tanaman yang dipilih tidak ditemukan di Master Obat, Pupuk, dan Tanaman.";
                $feedbackType    = "danger";
            } else {
                $ditanami = $tanamanRow['nama_produk'];
                $stmt = $conn->prepare("UPDATE lahan_petani SET anggota_id = ?, luas_lahan = ?, tanaman_id = ?, ditanami = ?, keterangan = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("isisii", $anggota_id, $luas_lahan, $tanaman_id, $ditanami, $keterangan, $id);
                    if ($stmt->execute()) {
                        header('Location: ' . appUrl('anggota/?view=lahan&status=updated&name=' . urlencode($petaniRow['nama_petani'])));
                        exit;
                    } else {
                        $feedbackMessage = dbErrorUntukPengguna('perbarui data lahan', $conn->error);
                        $feedbackType    = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    } elseif ($action === 'delete_lahan') {
        $activeView = 'lahan';
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM lahan_petani WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    header('Location: ' . appUrl('anggota/?view=lahan&status=deleted'));
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('hapus data lahan', $conn->error);
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }
}
