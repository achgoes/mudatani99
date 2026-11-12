<?php
/**
 * Endpoint sinkronisasi & pengecekan status sesi idle (JSON).
 *
 * - POST ?mode=ping  : Memperbarui waktu aktivitas terakhir sesi.
 * - GET  ?mode=check : Mengecek sisa waktu sesi tanpa memperpanjang masa aktif.
 */

$mode = $_GET['mode'] ?? ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'ping' : 'check');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $mode === 'check') {
    define('SKIP_SESSION_TOUCH', true);
}

require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'authenticated' => false,
        'reason'        => hasSessionTimedOut() ? 'timeout' : 'unauthenticated',
    ]);
    exit;
}

echo json_encode([
    'authenticated'    => true,
    'timeoutSeconds'   => SESSION_IDLE_TIMEOUT,
    'remainingSeconds' => getSessionRemainingSeconds(),
]);
