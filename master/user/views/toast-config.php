<?php if (!empty($toastTitle)): ?>
<script>
// Konfigurasi toast notifikasi dari PHP, ditampilkan oleh script.js saat halaman dimuat
window.USER_TOAST = {
    title: <?= json_encode($toastTitle) ?>,
    message: <?= json_encode($toastMessage) ?>,
    type: <?= json_encode($toastType) ?>
};
</script>
<?php endif; ?>
