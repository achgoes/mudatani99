<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | mudatani99 - Sistem Informasi Kelompok Tani</title>

    <!-- Bootstrap CSS (Required) -->
    <link rel="stylesheet" href="/assets/bootstrap/bootstrap.min.css">
    <!-- Tailwind CSS (Required Local & CDN Fallback) -->
    <link rel="stylesheet" href="/assets/tailwind/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Modular CSS for Login -->
    <link rel="stylesheet" href="<?= appUrl('css/base.css') ?>">
    <link rel="stylesheet" href="<?= appUrl('auth/login.css') ?>">

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

    <div class="login-container">
        
        <!-- Left Banner: Visual, Brand & Info -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-7/12 login-hero-banner">
            <!-- Ambient Glowing Lights -->
            <div class="hero-glow-orb hero-glow-1"></div>
            <div class="hero-glow-orb hero-glow-2"></div>
            <div class="hero-glow-orb hero-glow-3"></div>

            <!-- Floating Agro Spores Canvas -->
            <canvas id="hero-canvas"></canvas>

            <!-- Decorative Leaf Pattern SVG -->
            <div class="absolute inset-0 opacity-10 pointer-events-none z-0">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                    <defs>
                        <pattern id="leaf-pattern" width="60" height="60" patternUnits="userSpaceOnUse">
                            <path d="M30 5 C45 5, 55 20, 55 35 C55 50, 40 55, 30 55 C15 55, 5 40, 5 25 C5 10, 20 5, 30 5 Z" fill="none" stroke="currentColor" stroke-width="1.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#leaf-pattern)"/>
                </svg>
            </div>

            <!-- Top Brand -->
            <div class="hero-content hero-brand">
                <div class="hero-logo-icon">
                    <i class="fa-solid fa-seedling text-2xl text-agro-300"></i>
                </div>
                <div>
                    <span class="text-2xl font-bold tracking-tight text-white block leading-none">mudatani99</span>
                    <span class="text-xs text-agro-200 mt-1 block">Sistem Informasi Kelompok Tani Modern</span>
                </div>
            </div>

            <!-- Center Headline & Feature Cards -->
            <div class="hero-content my-auto max-w-xl">
                <div class="hero-pill-tag">
                    <span class="w-2 h-2 rounded-full bg-agro-400 animate-pulse"></span>
                    Digitalisasi Pertanian Desa Terpadu
                </div>
                <h1 class="hero-title">
                    Kelola Anggota, Pupuk, Tanaman & Transaksi dalam Satu Genggaman.
                </h1>
                <p class="hero-desc">
                    Platform manajemen modern untuk mempermudah pengurus Kelompok Tani (Poktan) dalam memantau distribusi pupuk bersubsidi, jadwal tanam-panen komoditas, serta transparansi kas transaksi.
                </p>

                <!-- 2 Feature Cards -->
                <div class="hero-features">
                    <div class="hero-feature-card">
                        <div class="hero-feature-icon">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-white">Kontrol Stok Pupuk</h4>
                            <p class="text-xs text-agro-200 mt-0.5 leading-relaxed">Pantau kuota pupuk subsidi & non-subsidi secara akurat.</p>
                        </div>
                    </div>
                    <div class="hero-feature-card">
                        <div class="hero-feature-icon">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-white">Transparansi Transaksi</h4>
                            <p class="text-xs text-agro-200 mt-0.5 leading-relaxed">Pencatatan tebus pupuk & penjualan panen terintegrasi.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="hero-content flex items-center justify-between text-xs text-agro-200/80 border-t border-white/10 pt-6">
                <span>&copy; 2026 Kelompok Tani Harapan Makmur</span>
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-agro-400"></i> Versi 2.4.0 &bull; Terintegrasi Database MySQL
                </span>
            </div>
        </div>

        <!-- Right Side: Elevated Login Card with 3D Parallax -->
        <div class="login-form-side">
            <div class="auth-card">
                
                <!-- Mobile Brand Header -->
                <div class="flex lg:hidden items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-agro-600 flex items-center justify-center text-white shadow-md">
                        <i class="fa-solid fa-seedling text-lg"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-slate-900 block leading-none">mudatani99</span>
                        <span class="text-xs text-slate-500">Sistem Kelompok Tani</span>
                    </div>
                </div>

                <div class="auth-header">
                    <h2>Selamat Datang Kembali 👋</h2>
                    <p>Silakan masuk menggunakan akun <strong>Username</strong> atau <strong>NIK</strong> kelompok tani Anda.</p>
                </div>

                <!-- Alert Session Timeout -->
                <?php if (!empty($timeoutNotice) && empty($errorMessage)): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center gap-2.5 fade-in" role="alert">
                    <i class="fa-solid fa-clock-rotate-left text-amber-500 text-base shrink-0"></i>
                    <span><?= htmlspecialchars($timeoutNotice) ?></span>
                </div>
                <?php endif; ?>

                <!-- Alert Error Message -->
                <?php if (!empty($errorMessage)): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2.5 fade-in">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-base shrink-0"></i>
                    <span><?= htmlspecialchars($errorMessage) ?></span>
                </div>
                <?php endif; ?>

                <!-- Form Login (Koneksi Database Langsung) -->
                <form id="login-form" method="POST" action="<?= appUrl('index.php') ?>">
                    <!-- Username / NIK -->
                    <div class="form-group">
                        <label for="login-username" class="form-label">Username atau NIK (4 Digit)</label>
                        <div class="input-icon-group">
                            <span class="input-icon">
                                <i class="fa-regular fa-id-card"></i>
                            </span>
                            <input
                                type="text"
                                id="login-username"
                                name="username"
                                value="<?= htmlspecialchars($prevUsername) ?>"
                                required
                                class="custom-input"
                                placeholder="Contoh: admin atau 0001"
                                autocomplete="username"
                            />
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <div class="flex items-center justify-between mb-1">
                            <label for="login-password" class="form-label mb-0">Kata Sandi</label>
                            <a href="#" onclick="alert('Hubungi Ketua Kelompok Tani atau Administrator untuk reset kata sandi.'); return false;" class="text-xs font-semibold text-agro-600 hover:text-agro-700">
                                Lupa sandi?
                            </a>
                        </div>
                        <div class="input-icon-group">
                            <span class="input-icon">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input
                                type="password"
                                id="login-password"
                                name="password"
                                required
                                class="custom-input has-toggle"
                                placeholder="••••••••"
                                autocomplete="current-password"
                            />
                            <button type="button" onclick="togglePasswordVisibility()" class="btn-toggle-eye" aria-label="Tampilkan atau sembunyikan password">
                                <i id="password-eye-icon" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button with Spinner -->
                    <button type="submit" class="btn-submit-login" id="btn-login-submit">
                        <span class="spinner"></span>
                        <span class="btn-text-content flex items-center justify-center gap-2">
                            <span>Masuk ke Dashboard</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </span>
                    </button>
                </form>

                <!-- Footer Info -->
                <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-500">
                        Belum terdaftar sebagai anggota Poktan? 
                        <span class="font-semibold text-agro-700">Hubungi Pengurus Kelompok Tani</span>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS (Required) -->
    <script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>

    <!-- Login Page Script -->
    <script src="<?= appUrl('auth/login.js') ?>"></script>
</body>
</html>

