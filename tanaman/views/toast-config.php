<script>
    const HUTANG_TANAMAN_INFO_MAP = <?= json_encode($hutangInfoMap, JSON_UNESCAPED_UNICODE) ?>;
    const PEMBAYARAN_TANAMAN_BY_HUTANG = <?= json_encode($pembayaranByHutang, JSON_UNESCAPED_UNICODE) ?>;
    const PENJUALAN_TANAMAN_INFO_MAP = <?= json_encode($penjualanInfoMap, JSON_UNESCAPED_UNICODE) ?>;
    const TANAMAN_STOK_LIST = <?= json_encode(array_values($tanamanList), JSON_UNESCAPED_UNICODE) ?>;
    const TANAMAN_PRINT_BAYAR_URL = <?= json_encode(appUrl('tanaman/print-pembayaran.php')) ?>;
    const TANAMAN_PRINT_JUAL_URL = <?= json_encode(appUrl('tanaman/print-penjualan.php')) ?>;
<?php if (!empty($toastTitle)): ?>
    window.TANAMAN_TOAST = {
        title: <?= json_encode($toastTitle) ?>,
        message: <?= json_encode($toastMessage) ?>,
        type: <?= json_encode($toastType) ?>,
        duration: 1400,
        viewParam: <?= json_encode($activeView !== 'kelola' ? '?view=' . $activeView : '') ?>
    };
<?php endif; ?>
</script>
