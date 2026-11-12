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

if (!defined('SESSION_IDLE_TIMEOUT')) {
    define('SESSION_IDLE_TIMEOUT', 15 * 60); // 15 menit tanpa aktivitas
}
if (!defined('SESSION_COOKIE_LIFETIME')) {
    define('SESSION_COOKIE_LIFETIME', 60 * 60 * 24 * 7); // 7 hari agar login tetap tersimpan saat browser ditutup
}

if (session_status() === PHP_SESSION_NONE) {
    $minGcLifetime = SESSION_IDLE_TIMEOUT + 300;
    if ((int) ini_get('session.gc_maxlifetime') < $minGcLifetime) {
        ini_set('session.gc_maxlifetime', (string) $minGcLifetime);
    }
    unset($minGcLifetime);

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);

    session_set_cookie_params([
        'lifetime' => SESSION_COOKIE_LIFETIME,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($isHttps);

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
 * Memperbarui masa berlaku cookie sesi agar tetap tersimpan saat browser ditutup.
 */
function refreshSessionCookie(): void
{
    if (headers_sent() || session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $params = session_get_cookie_params();
    setcookie(session_name(), session_id(), [
        'expires'  => time() + SESSION_COOKIE_LIFETIME,
        'path'     => $params['path'] ?? '/',
        'domain'   => $params['domain'] ?? '',
        'secure'   => (bool) ($params['secure'] ?? false),
        'httponly' => (bool) ($params['httponly'] ?? true),
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}

/**
 * Menghapus seluruh data sesi, cookie sesi, dan menghancurkan sesi di server.
 */
function destroyAuthSession(): void
{
    $_SESSION = [];

    if (!headers_sent() && ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $params['path'] ?? '/',
            'domain'   => $params['domain'] ?? '',
            'secure'   => (bool) ($params['secure'] ?? false),
            'httponly' => (bool) ($params['httponly'] ?? true),
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * Mengecek apakah sesi pengguna yang sedang aktif sudah melewati batas idle 15 menit.
 */
function isSessionExpired(): bool
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    if (!isset($_SESSION['last_activity'])) {
        return false;
    }

    return (time() - (int) $_SESSION['last_activity']) >= SESSION_IDLE_TIMEOUT;
}

/**
 * Mencatat waktu aktivitas terakhir pengguna pada sesi aktif.
 */
function touchSessionActivity(): void
{
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $_SESSION['last_activity'] = time();
    refreshSessionCookie();
}

/**
 * Menandakan apakah sesi pada request ini baru saja berakhir karena idle timeout.
 */
function hasSessionTimedOut(): bool
{
    return (bool) ($GLOBALS['__sessionTimedOut'] ?? false);
}

/**
 * Mengembalikan sisa waktu sesi aktif dalam detik sebelum idle timeout.
 */
function getSessionRemainingSeconds(): int
{
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }

    $lastActivity = (int) ($_SESSION['last_activity'] ?? time());
    return max(0, SESSION_IDLE_TIMEOUT - (time() - $lastActivity));
}

// Validasi timeout sesi pada setiap request
$GLOBALS['__sessionTimedOut'] = false;
if (isset($_SESSION['user_id'])) {
    if (isSessionExpired()) {
        $GLOBALS['__sessionTimedOut'] = true;
        destroyAuthSession();
    } elseif (!defined('SKIP_SESSION_TOUCH')) {
        touchSessionActivity();
    }
}

/**
 * Proteksi sesi login. Mengalihkan ke halaman login jika belum login atau sesi kedaluwarsa.
 */
function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        $loginPath = hasSessionTimedOut() ? 'index.php?timeout=1' : 'index.php';
        header('Location: ' . appUrl($loginPath));
        exit;
    }
}

