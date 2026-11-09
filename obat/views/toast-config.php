<?php if (!empty($toastTitle)): ?>
<script>
window.OBAT_TOAST = {
    title: <?= json_encode($toastTitle) ?>,
    message: <?= json_encode($toastMessage) ?>,
    type: <?= json_encode($toastType) ?>
};
</script>
<?php endif; ?>

