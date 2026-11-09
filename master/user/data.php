<?php
$toastTitle   = "";
$toastMessage = "";
$toastType    = "";

// Flash message dari redirect URL
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'added') {
        $uName = htmlspecialchars($_GET['name'] ?? 'Pengguna');
        $uNik  = htmlspecialchars($_GET['nik'] ?? '');
        $toastTitle   = "Berhasil Ditambahkan!";
        $toastMessage = "Pengguna <b>$uName</b>" . ($uNik ? " (NIK: $uNik)" : "") . " berhasil disimpan.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'updated') {
        $uName = htmlspecialchars($_GET['name'] ?? 'Pengguna');
        $uNik  = htmlspecialchars($_GET['nik'] ?? '');
        $toastTitle   = "Berhasil Diperbarui!";
        $toastMessage = "Data pengguna <b>$uName</b>" . ($uNik ? " (NIK: $uNik)" : "") . " berhasil diperbarui.";
        $toastType    = "success";
    } elseif ($_GET['status'] === 'deleted') {
        $toastTitle   = "Berhasil Dihapus!";
        $toastMessage = "Data pengguna berhasil dihapus dari sistem.";
        $toastType    = "success";
    }
}

if (!empty($feedbackMessage) && $feedbackType === 'danger') {
    $toastTitle   = "Peringatan!";
    $toastMessage = htmlspecialchars($feedbackMessage);
    $toastType    = "danger";
}

// Ambil semua daftar pengguna dari database
$usersList = [];
$maxNikInt = 0;
$query = "SELECT id, nik, username, nama, created_at FROM users ORDER BY nik ASC, id ASC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['nama'] = formatTitleCase($row['nama']);
        $usersList[] = $row;
        $nikNum = (int)$row['nik'];
        if ($nikNum > $maxNikInt) {
            $maxNikInt = $nikNum;
        }
    }
}
$nextSuggestedNik = str_pad((string)($maxNikInt + 1), 4, '0', STR_PAD_LEFT);
