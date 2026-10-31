<?php
/**
 * Mencatat detail error database ke file log server, lalu mengembalikan
 * pesan umum yang aman ditampilkan kepada pengguna.
 *
 * Detail teknis (struktur tabel, nama kolom, dsb.) tidak pernah dikirim ke browser.
 */
function dbErrorUntukPengguna(string $konteks, string $detail = ''): string
{
    $logDir  = __DIR__ . '/../logs';
    $logFile = $logDir . '/db-error.log';

    // Buat folder log jika belum ada. Kegagalan di sini tidak boleh menghentikan program.
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $baris = '[' . date('Y-m-d H:i:s') . '] ' . $konteks
        . ($detail !== '' ? ' | ' . $detail : '')
        . PHP_EOL;

    // Jika penulisan file gagal, catat ke log default PHP agar error tetap tercatat.
    if (@error_log($baris, 3, $logFile) === false) {
        error_log('[mudatani99] ' . trim($baris));
    }

    return 'Terjadi kesalahan pada database. Silakan coba lagi, atau hubungi administrator jika masalah berlanjut.';
}