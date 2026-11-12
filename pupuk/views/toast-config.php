<script>
    const HUTANG_INFO_MAP = <?= json_encode($hutangInfoMap, JSON_UNESCAPED_UNICODE) ?>;
    const PEMBAYARAN_BY_HUTANG = <?= json_encode($pembayaranByHutang, JSON_UNESCAPED_UNICODE) ?>;
    const PUPUK_PRINT_BAYAR_URL = <?= json_encode(appUrl('pupuk/print-pembayaran.php')) ?>;
<?php if (!empty($toastTitle)): ?>
    window.PUPUK_TOAST = {
        title: <?= json_encode($toastTitle) ?>,
        message: <?= json_encode($toastMessage) ?>,
        type: <?= json_encode($toastType) ?>,
        duration: 1400,
        viewParam: <?= json_encode($activeView !== 'kelola' ? '?view=' . $activeView : '') ?>
    };
<?php endif; ?>
</script>
