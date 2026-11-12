<?php
// Jika sudah login, langsung alihkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . appUrl('dashboard/'));
    exit;
}

$errorMessage  = "";
$timeoutNotice = "";
$prevUsername  = "";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && (hasSessionTimedOut() || (($_GET['timeout'] ?? '') === '1'))) {
    $timeoutNotice = "Sesi Anda berakhir karena tidak ada aktivitas selama 15 menit. Silakan login kembali.";
}

// Proses autentikasi saat form login disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput   = trim($_POST['username'] ?? '');
    $password     = trim($_POST['password'] ?? '');
    $prevUsername = $loginInput;

    if (empty($loginInput) || empty($password)) {
        $errorMessage = "Silakan masukkan Username / NIK dan kata sandi Anda.";
    } else {
        // Query database: verifikasi login menggunakan username ATAU nik
        $stmt = $conn->prepare("SELECT id, nik, username, nama, password FROM users WHERE username = ? OR nik = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("ss", $loginInput, $loginInput);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verifikasi password:
                // Mendukung password_hash Bcrypt, plain text, maupun MD5 untuk fleksibilitas testing
                $passwordValid = false;
                if (password_verify($password, $user['password']) || $password === $user['password'] || md5($password) === $user['password']) {
                    $passwordValid = true;
                }

                if ($passwordValid) {
                    session_regenerate_id(true);

                    // Set session user
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['nik']      = $user['nik'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama']     = $user['nama'];
                    touchSessionActivity();

                    header('Location: ' . appUrl('dashboard/'));
                    exit;
                } else {
                    $errorMessage = "Kata sandi yang Anda masukkan salah.";
                }
            } else {
                $errorMessage = "Username atau NIK tidak terdaftar dalam database.";
            }
            $stmt->close();
        } else {
            $errorMessage = "Terjadi gangguan pada query database: " . $conn->error;
        }
    }
}

