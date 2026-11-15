<script>
    const TRANSAKSI_AKHIR_INFO_MAP = <?= json_encode($transaksiInfoMap, JSON_UNESCAPED_UNICODE) ?>;
    const TRANSAKSI_AKHIR_REKAP_SETORAN = <?= json_encode($rekapSetoranPerAnggota, JSON_UNESCAPED_UNICODE) ?>;
    const TRANSAKSI_AKHIR_MASTER_TANAMAN = <?= json_encode(array_values($masterTanamanList), JSON_UNESCAPED_UNICODE) ?>;
    const TRANSAKSI_AKHIR_PRINT_URL = <?= json_encode(appUrl('transaksi-akhir/print-transaksi-akhir.php')) ?>;
<?php if (!empty($toastTitle)): ?>
    window.TRANSAKSI_AKHIR_TOAST = {
        title: <?= json_encode($toastTitle) ?>,
        message: <?= json_encode($toastMessage) ?>,
        type: <?= json_encode($toastType) ?>,
        duration: 1600
    };
<?php endif; ?>
</script>
