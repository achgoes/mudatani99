<script>
window.OBAT_PUPUK_CONFIG = {
    sortCol: <?= json_encode($sortColParam === 'id' ? 'nomor' : $sortColParam) ?>,
    sortOrder: <?= json_encode($sortOrderParam) ?>,
    toast: <?= !empty($toastTitle) ? json_encode([
        'title'   => $toastTitle,
        'message' => $toastMessage,
        'type'    => $toastType,
    ]) : 'null' ?>
};
</script>

