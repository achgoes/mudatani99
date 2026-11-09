<script>
window.ANGGOTA_CONFIG = {
    activeView: <?= json_encode($activeView) ?>,
    viewParam: <?= json_encode($activeView === 'lahan' ? '?view=lahan' : '') ?>
};
<?php if (!empty($toastTitle)): ?>
window.ANGGOTA_TOAST = {
    title: <?= json_encode($toastTitle) ?>,
    message: <?= json_encode($toastMessage) ?>,
    type: <?= json_encode($toastType) ?>
};
<?php endif; ?>
</script>
