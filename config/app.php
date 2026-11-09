<?php
/**
 * Konfigurasi aplikasi bersama.
 *
 * Wajib di-require paling awal oleh setiap halaman (index.php tiap folder menu):
 *   require_once __DIR__ . '/../config/app.php';          // folder 1 tingkat
 *   require_once __DIR__ . '/../../config/app.php';       // folder 2 tingkat (master/user)
 *
 * Menyediakan: ROOT_PATH, BASE_URL, APP_ROUTE, appUrl(), requireLogin().
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Path fisik folder proyek (tanpa trailing slash)
define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__)));

// URL dasar proyek terhadap document root, mis. '' (di root) atau '/mudatani99' (di subfolder)
$docRoot = rtrim(str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
define('BASE_URL', ($docRoot !== '' && stripos(ROOT_PATH, $docRoot) === 0) ? substr(ROOT_PATH, strlen($docRoot)) : '');
unset($docRoot);

// Rute folder halaman aktif relatif terhadap proyek, mis. 'pupuk', 'master/user', '' (root)
define('APP_ROUTE', trim(dirname(substr(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), strlen(BASE_URL))), '/\\.'));

/**
 * Membuat URL absolut-path dari path relatif proyek.
 * Contoh: appUrl('pupuk/') atau appUrl('pupuk/?view=hutang')
 */
function appUrl(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Proteksi sesi login. Mengalihkan ke halaman login jika belum login.
 */
function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . appUrl('index.php'));
        exit;
    }
}

