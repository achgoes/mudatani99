<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default title if not provided
if (!isset($pageTitle)) {
    $pageTitle = "mudatani99 - Sistem Pengelolaan Kelompok Pertanian";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#052e16">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Bootstrap CSS (Required) -->
    <link rel="stylesheet" href="/assets/bootstrap/bootstrap.min.css">
    <!-- Tailwind CSS (Required Local & CDN Fallback) -->
    <link rel="stylesheet" href="/assets/tailwind/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Base & Modular CSS Files -->
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <?php if (isset($extraCss) && is_array($extraCss)): ?>
        <?php foreach ($extraCss as $cssFile): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($cssFile) ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        agro: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="text-slate-800 antialiased selection:bg-agro-600 selection:text-white bg-slate-50">
