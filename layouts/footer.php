    <!-- Bootstrap Bundle JS (Required) -->
    <script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Slider & Dropdown Submenu JavaScript -->
    <script src="<?= appUrl('js/sidebar.js') ?>"></script>

    <?php // $extraJs: daftar path JS relatif proyek, mis. ['pupuk/script.js'] ?>
    <?php if (isset($extraJs) && is_array($extraJs)): ?>
        <?php foreach ($extraJs as $jsFile): ?>
            <script src="<?= htmlspecialchars(appUrl($jsFile)) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
