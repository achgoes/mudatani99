<?php
$feedbackMessage = "";
$feedbackType    = ""; // 'success' atau 'danger'

// Proses Aksi Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ==========================================
    // 1. AKSI TAMBAH USER
    // ==========================================
    if ($action === 'add') {
        $nik      = trim($_POST['nik'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $nama     = formatTitleCase($_POST['nama'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validasi input
        if (empty($nik) || empty($username) || empty($nama) || empty($password)) {
            $feedbackMessage = "Semua bidang (NIK, Username, Nama, dan Password) wajib diisi.";
            $feedbackType    = "danger";
        } elseif (!preg_match('/^[0-9]{4}$/', $nik)) {
            $feedbackMessage = "NIK harus berupa 4 digit angka (contoh: 0001, 0005).";
            $feedbackType    = "danger";
        } else {
            // Cek keunikan NIK atau Username
            $checkStmt = $conn->prepare("SELECT id, nik, username FROM users WHERE nik = ? OR username = ? LIMIT 1");
            $checkStmt->bind_param("ss", $nik, $username);
            $checkStmt->execute();
            $checkRes = $checkStmt->get_result();

            if ($checkRes && $checkRes->num_rows > 0) {
                $existing = $checkRes->fetch_assoc();
                if ($existing['nik'] === $nik) {
                    $feedbackMessage = "NIK '$nik' sudah terdaftar pada pengguna lain.";
                } else {
                    $feedbackMessage = "Username '$username' sudah digunakan. Silakan gunakan username lain.";
                }
                $feedbackType = "danger";
            } else {
                // Simpan user baru (menggunakan password_hash untuk keamanan)
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $conn->prepare("INSERT INTO users (nik, username, nama, password) VALUES (?, ?, ?, ?)");
                $insertStmt->bind_param("ssss", $nik, $username, $nama, $hashedPassword);

                if ($insertStmt->execute()) {
                    header('Location: ' . appUrl('master/user/?status=added&name=' . urlencode($nama) . '&nik=' . urlencode($nik)));
                    exit;
                } else {
                    $feedbackMessage = dbErrorUntukPengguna('tambah user', $conn->error);
                    $feedbackType    = "danger";
                }
                $insertStmt->close();
            }
            $checkStmt->close();
        }
    }

    // ==========================================
    // 2. AKSI EDIT USER
    // ==========================================
    elseif ($action === 'edit') {
        $id       = (int)($_POST['id'] ?? 0);
        $nik      = trim($_POST['nik'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $nama     = formatTitleCase($_POST['nama'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($id <= 0 || empty($nik) || empty($username) || empty($nama)) {
            $feedbackMessage = "ID, NIK, Username, dan Nama lengkap wajib diisi.";
            $feedbackType    = "danger";
        } elseif (!preg_match('/^[0-9]{4}$/', $nik)) {
            $feedbackMessage = "NIK harus berupa 4 digit angka (contoh: 0001, 0005).";
            $feedbackType    = "danger";
        } else {
            // Cek duplikasi NIK atau Username pada user LAIN
            $checkStmt = $conn->prepare("SELECT id, nik, username FROM users WHERE (nik = ? OR username = ?) AND id != ? LIMIT 1");
            $checkStmt->bind_param("ssi", $nik, $username, $id);
            $checkStmt->execute();
            $checkRes = $checkStmt->get_result();

            if ($checkRes && $checkRes->num_rows > 0) {
                $existing = $checkRes->fetch_assoc();
                if ($existing['nik'] === $nik) {
                    $feedbackMessage = "NIK '$nik' telah digunakan oleh pengguna lain.";
                } else {
                    $feedbackMessage = "Username '$username' telah digunakan oleh pengguna lain.";
                }
                $feedbackType = "danger";
            } else {
                // Update dengan atau tanpa ganti password
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE users SET nik = ?, username = ?, nama = ?, password = ? WHERE id = ?");
                    $updateStmt->bind_param("ssssi", $nik, $username, $nama, $hashedPassword, $id);
                } else {
                    $updateStmt = $conn->prepare("UPDATE users SET nik = ?, username = ?, nama = ? WHERE id = ?");
                    $updateStmt->bind_param("sssi", $nik, $username, $nama, $id);
                }

                if ($updateStmt->execute()) {
                    // Jika mengedit akun sendiri yang sedang login, update sesi
                    if ($id === (int)$_SESSION['user_id']) {
                        $_SESSION['nama']     = $nama;
                        $_SESSION['username'] = $username;
                        $_SESSION['nik']      = $nik;
                    }
                    header('Location: ' . appUrl('master/user/?status=updated&name=' . urlencode($nama) . '&nik=' . urlencode($nik)));
                    exit;
                } else {
                    $feedbackMessage = "Gagal memperbarui data user: " . $conn->error;
                    $feedbackType    = "danger";
                }
                $updateStmt->close();
            }
            $checkStmt->close();
        }
    }

    // ==========================================
    // 3. AKSI HAPUS USER
    // ==========================================
    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id === (int)$_SESSION['user_id']) {
            $feedbackMessage = "Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.";
            $feedbackType    = "danger";
        } else {
            $delStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $delStmt->bind_param("i", $id);
            if ($delStmt->execute()) {
                header('Location: ' . appUrl('master/user/?status=deleted'));
                exit;
            } else {
                $feedbackMessage = "Gagal menghapus user: " . $conn->error;
                $feedbackType    = "danger";
            }
            $delStmt->close();
        }
    }
}
