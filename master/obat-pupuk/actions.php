<?php
// Pastikan tabel master_produk tersedia di database
$conn->query("CREATE TABLE IF NOT EXISTS `master_produk` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_produk` VARCHAR(100) NOT NULL,
    `jenis` ENUM('Obat', 'Pupuk', 'Tanaman') NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT '-',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$feedbackMessage = "";
$feedbackType    = "";
$allowedJenis    = ['Obat', 'Pupuk', 'Tanaman'];

// Proses Aksi Form POST (Tambah, Edit, Hapus Master Obat, Pupuk, dan Tanaman)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TAMBAH PRODUK MASTER
    if ($action === 'add') {
        $nama_produk = trim($_POST['nama_produk'] ?? '');
        $jenis       = trim($_POST['jenis'] ?? '');
        $keterangan  = trim($_POST['keterangan'] ?? '');

        if (empty($nama_produk) || !in_array($jenis, $allowedJenis, true)) {
            $feedbackMessage = "Nama Produk dan Jenis (Obat / Pupuk / Tanaman) wajib diisi dengan benar.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $stmt = $conn->prepare("INSERT INTO master_produk (nama_produk, jenis, keterangan) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $nama_produk, $jenis, $keterangan);
                if ($stmt->execute()) {
                    header('Location: ' . appUrl('master/obat-pupuk/?status=added&name=' . urlencode($nama_produk)));
                    exit;
                } else {
                    $feedbackMessage = "Gagal menambahkan produk: " . $conn->error;
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }

    // 2. EDIT PRODUK MASTER
    elseif ($action === 'edit') {
        $id          = (int)($_POST['id'] ?? 0);
        $nama_produk = trim($_POST['nama_produk'] ?? '');
        $jenis       = trim($_POST['jenis'] ?? '');
        $keterangan  = trim($_POST['keterangan'] ?? '');

        if ($id <= 0 || empty($nama_produk) || !in_array($jenis, $allowedJenis, true)) {
            $feedbackMessage = "ID, Nama Produk, dan Jenis (Obat / Pupuk / Tanaman) wajib diisi.";
            $feedbackType    = "danger";
        } else {
            if ($keterangan === '') {
                $keterangan = '-';
            }
            $stmt = $conn->prepare("UPDATE master_produk SET nama_produk = ?, jenis = ?, keterangan = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("sssi", $nama_produk, $jenis, $keterangan, $id);
                if ($stmt->execute()) {
                    header('Location: ' . appUrl('master/obat-pupuk/?status=updated&name=' . urlencode($nama_produk)));
                    exit;
                } else {
                    $feedbackMessage = "Gagal memperbarui produk: " . $conn->error;
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }

    // 3. HAPUS PRODUK MASTER
    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM master_produk WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    header('Location: ' . appUrl('master/obat-pupuk/?status=deleted'));
                    exit;
                } else {
                    $feedbackMessage = "Gagal menghapus produk: " . $conn->error;
                    $feedbackType    = "danger";
                }
                $stmt->close();
            }
        }
    }
}

