    <!-- Bootstrap Bundle JS (Required) -->
    <script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Slider & Dropdown Submenu JavaScript -->
    <script src="<?= appUrl('js/sidebar.js') ?>"></script>

    <?php if (isset($_SESSION['user_id'])): ?>
    <script>
        window.MUDATANI_SESSION_CONFIG = <?= json_encode([
            'userId'           => (string) $_SESSION['user_id'],
            'timeoutSeconds'   => SESSION_IDLE_TIMEOUT,
            'remainingSeconds' => getSessionRemainingSeconds(),
            'pingUrl'          => appUrl('auth/session-ping.php'),
            'logoutUrl'        => appUrl('logout.php?reason=timeout'),
            'loginUrl'         => appUrl('index.php?timeout=1'),
        ], JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="<?= appUrl('js/session-timeout.js') ?>"></script>
    <?php endif; ?>

    <?php // $extraJs: daftar path JS relatif proyek, mis. ['pupuk/script.js'] ?>
    <?php if (isset($extraJs) && is_array($extraJs)): ?>
        <?php foreach ($extraJs as $jsFile): ?>
            <script src="<?= htmlspecialchars(appUrl($jsFile)) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
