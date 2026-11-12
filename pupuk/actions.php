<?php
/**
 * mudatani99 - Form Action Dispatcher (CRUD Stok Pupuk, Hutang Piutang & Pembayaran)
 * File: pupuk/actions.php
 */

// Tentukan tampilan submenu aktif: 'kelola' (default), 'penjualan', 'riwayat_penjualan', 'hutang', atau 'riwayat'
$viewParam  = $_GET['view'] ?? 'kelola';
$activeView = in_array($viewParam, ['penjualan', 'riwayat_penjualan', 'hutang', 'riwayat'], true) ? $viewParam : 'kelola';

$feedbackMessage = "";
$feedbackType    = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nonaktifkan trigger sesi MySQL agar sinkronisasi PHP yang tervalidasi tidak mengeksekusi pengurangan 2 kali
    $conn->query("SET @disable_hutang_trigger = 1");

    $action     = $_POST['action'] ?? '';
    $returnView = $_POST['return_view'] ?? $activeView;
    if (!in_array($returnView, ['kelola', 'penjualan', 'riwayat_penjualan', 'hutang', 'riwayat'], true)) {
        $returnView = 'kelola';
    }
    $viewQuery = ($returnView !== 'kelola') ? "view=" . urlencode($returnView) . "&" : "";

    if (in_array($action, ['add', 'edit', 'delete'], true)) {
        require __DIR__ . '/actions/stok.php';
    } elseif (in_array($action, ['add_penjualan', 'edit_penjualan', 'delete_penjualan'], true)) {
        require __DIR__ . '/actions/penjualan.php';
    } elseif (in_array($action, ['add_hutang', 'edit_hutang', 'delete_hutang'], true)) {
        require __DIR__ . '/actions/hutang.php';
    } elseif (in_array($action, ['toggle_status_hutang', 'pay_hutang'], true)) {
        require __DIR__ . '/actions/pembayaran.php';
    }
}
