<script>
    const HUTANG_OBAT_INFO_MAP = <?= json_encode($hutangInfoMap, JSON_UNESCAPED_UNICODE) ?>;
    const PEMBAYARAN_OBAT_BY_HUTANG = <?= json_encode($pembayaranByHutang, JSON_UNESCAPED_UNICODE) ?>;
    const PENJUALAN_OBAT_INFO_MAP = <?= json_encode($penjualanInfoMap, JSON_UNESCAPED_UNICODE) ?>;
    const OBAT_STOK_LIST = <?= json_encode(array_values($obatList), JSON_UNESCAPED_UNICODE) ?>;
    const OBAT_PRINT_BAYAR_URL = <?= json_encode(appUrl('obat/print-pembayaran.php')) ?>;
    const OBAT_PRINT_JUAL_URL = <?= json_encode(appUrl('obat/print-penjualan.php')) ?>;
<?php if (!empty($toastTitle)): ?>
    window.OBAT_TOAST = {
        title: <?= json_encode($toastTitle) ?>,
        message: <?= json_encode($toastMessage) ?>,
        type: <?= json_encode($toastType) ?>,
        duration: 1400,
        viewParam: <?= json_encode($activeView !== 'kelola' ? '?view=' . $activeView : '') ?>
    };
<?php endif; ?>
</script>
