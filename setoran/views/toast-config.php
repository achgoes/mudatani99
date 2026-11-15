<script>
    const SETORAN_INFO_MAP = <?= json_encode($setoranInfoMap, JSON_UNESCAPED_UNICODE) ?>;
    const SETORAN_MASTER_TANAMAN = <?= json_encode(array_values($masterTanamanList), JSON_UNESCAPED_UNICODE) ?>;
    const SETORAN_PRINT_URL = <?= json_encode(appUrl('setoran/print-setoran.php')) ?>;
<?php if (!empty($toastTitle)): ?>
    window.SETORAN_TOAST = {
        title: <?= json_encode($toastTitle) ?>,
        message: <?= json_encode($toastMessage) ?>,
        type: <?= json_encode($toastType) ?>,
        duration: 1600
    };
<?php endif; ?>
</script>
