/**
 * mudatani99 - Core Application Logic & Interactive UI/UX JavaScript
 * Controls: Data state, CRUD handlers, Gemini AI integration,
 * 3D Parallax Tilt, Particle Canvas, Animated Counters, and Toast Alerts.
 */

// --- INITIAL MOCK DATA KELOMPOK TANI ---
let currentUser = {
    name: 'Budi Santoso',
    role: 'Ketua Poktan (Admin)',
    initials: 'BS'
};

let usersData = [
    { id: 'USR-01', name: 'Budi Santoso', nik: '3507120405780001', role: 'Ketua Poktan', phone: '0812-3456-7801', land: 2.5, block: 'Blok Utara A-1', status: 'Aktif' },
    { id: 'USR-02', name: 'Siti Aminah', nik: '3507121508820002', role: 'Bendahara', phone: '0813-5566-7788', land: 1.2, block: 'Blok Timur B-2', status: 'Aktif' },
    { id: 'USR-03', name: 'Slamet Riyadi', nik: '3507122103750004', role: 'Petani Anggota', phone: '0857-9012-3456', land: 1.75, block: 'Blok Selatan C-1', status: 'Aktif' },
    { id: 'USR-04', name: 'Hendra Wijaya, SP', nik: '3507121110880003', role: 'Penyuluh', phone: '0811-2233-4455', land: 0.5, block: 'Lahan Demplot Desa', status: 'Aktif' },
    { id: 'USR-05', name: 'Mulyono Tani', nik: '3507120901700007', role: 'Petani Anggota', phone: '0821-8899-0011', land: 2.0, block: 'Blok Utara A-3', status: 'Aktif' },
    { id: 'USR-06', name: 'Kartini Sari', nik: '3507123004850009', role: 'Petani Anggota', phone: '0852-3344-5566', land: 0.8, block: 'Blok Barat D-4', status: 'Non-Aktif' }
];

let fertilizersData = [
    { id: 'PPK-01', code: 'PPK-001', name: 'Pupuk Urea Bersubsidi', category: 'Subsidi', stock: 1850, quota: 2500, price: 2250, desc: 'Nitrogen 46% (Kujang/Pupuk Indonesia)' },
    { id: 'PPK-02', code: 'PPK-002', name: 'NPK Phonska Subsidi', category: 'Subsidi', stock: 1420, quota: 2000, price: 2300, desc: 'N 15%, P 15%, K 15%' },
    { id: 'PPK-03', code: 'PPK-003', name: 'Pupuk Organik Petroganik', category: 'Organik', stock: 950, quota: 1500, price: 800, desc: 'C-Organik Tinggi Remah Tanah' },
    { id: 'PPK-04', code: 'PPK-004', name: 'Pupuk ZA Non-Subsidi', category: 'Non-Subsidi', stock: 240, quota: 1000, price: 6500, desc: 'Amonium Sulfat + Sulfur' },
    { id: 'PPK-05', code: 'PPK-005', name: 'NPK Mutiara 16-16-16', category: 'Non-Subsidi', stock: 310, quota: 800, price: 14500, desc: 'Pupuk Majemuk Cepat Larut' }
];

let cropsData = [
    { id: 'TNM-01', name: 'Padi Sawah', variety: 'Inpari 32 HDB', farmer: 'Budi Santoso', area: 2.5, plantDate: '2026-08-05', harvestDate: '2026-11-15', phase: 'Generatif / Berbunga' },
    { id: 'TNM-02', name: 'Jagung Hibrida', variety: 'Bisi-18 Super', farmer: 'Slamet Riyadi', area: 1.75, plantDate: '2026-07-12', harvestDate: '2026-10-25', phase: 'Siap Panen' },
    { id: 'TNM-03', name: 'Padi Sawah', variety: 'Ciherang Wangi', farmer: 'Mulyono Tani', area: 2.0, plantDate: '2026-09-10', harvestDate: '2026-12-20', phase: 'Vegetatif' },
    { id: 'TNM-04', name: 'Cabai Merah Keriting', variety: 'TM 999', farmer: 'Siti Aminah', area: 1.2, plantDate: '2026-07-01', harvestDate: '2026-10-20', phase: 'Siap Panen' },
    { id: 'TNM-05', name: 'Kedelai Unggul', variety: 'Anjasmoro', farmer: 'Hendra Wijaya, SP', area: 0.5, plantDate: '2026-10-02', harvestDate: '2026-12-28', phase: 'Penyemaian / Bibit' }
];

let transactionsData = [
    { id: 'TRX-26101', date: '2026-10-16', member: 'Slamet Riyadi', category: 'Tebus Pupuk', desc: 'Tebus Pupuk Urea Bersubsidi (150 Kg)', flow: 'IN', amount: 337500 },
    { id: 'TRX-26102', date: '2026-10-15', member: 'Mulyono Tani', category: 'Tebus Pupuk', desc: 'Tebus NPK Phonska Subsidi (200 Kg)', flow: 'IN', amount: 460000 },
    { id: 'TRX-26103', date: '2026-10-12', member: 'KUD Tani Makmur (Pemasok)', category: 'Belanja Gudang', desc: 'Penebusan DO Kuota Pupuk Organik 500 Kg', flow: 'OUT', amount: 350000 },
    { id: 'TRX-26104', date: '2026-10-10', member: 'Budi Santoso', category: 'Penjualan Panen', desc: 'Kontribusi Kas Hasil Panen Gabah Kering Giling', flow: 'IN', amount: 2450000 },
    { id: 'TRX-26105', date: '2026-10-05', member: 'Siti Aminah', category: 'Iuran Poktan', desc: 'Setoran Kas Wajib Anggota Bulan Oktober 2026', flow: 'IN', amount: 600000 }
];

// --- HELPER UTILITIES ---
function formatRupiah(num) {
    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
}

function formatDateIndo(dateStr) {
    if (!dateStr) return '-';
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    return `${parseInt(parts[2])} ${months[parseInt(parts[1]) - 1]} ${parts[0]}`;
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    const colorMap = {
        success: 'bg-emerald-800 text-white border-emerald-600 shadow-emerald-900/30',
        info: 'bg-slate-900 text-white border-slate-700 shadow-slate-900/40',
        error: 'bg-rose-800 text-white border-rose-600 shadow-rose-900/40'
    };
    const iconMap = {
        success: 'fa-circle-check text-emerald-300',
        info: 'fa-circle-info text-blue-300',
        error: 'fa-circle-exclamation text-rose-300'
    };

    toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl border text-xs sm:text-sm font-medium fade-in ${colorMap[type] || colorMap.success}`;
    toast.innerHTML = `
        <i class="fa-solid ${iconMap[type] || iconMap.success} text-lg shrink-0"></i>
        <span class="flex-1">${message}</span>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(30px)';
        toast.style.transition = 'all 0.35s ease';
        setTimeout(() => toast.remove(), 350);
    }, 3200);
}

// --- COOL EFFECT 1: 1-CLICK DEMO ACCOUNT SELECTOR ---
function fillDemoAccount(role) {
    const userInp = document.getElementById('login-username');
    const passInp = document.getElementById('login-password');
    if (!userInp || !passInp) return;

    if (role === 'admin') {
        userInp.value = 'admin';
        passInp.value = 'admin123';
        showToast('Akun demo: Ketua Poktan (Budi Santoso) dipilih', 'info');
    } else if (role === 'bendahara') {
        userInp.value = 'Siti Aminah';
        passInp.value = 'bendahara123';
        showToast('Akun demo: Bendahara (Siti Aminah) dipilih', 'info');
    } else if (role === 'petani') {
        userInp.value = 'Slamet Riyadi';
        passInp.value = 'petani123';
        showToast('Akun demo: Petani Anggota (Slamet Riyadi) dipilih', 'info');
    }

    // Visual indicator on clicked button
    document.querySelectorAll('.btn-demo-role').forEach(btn => btn.classList.remove('active'));
    const clickedBtn = document.getElementById(`demo-role-${role}`);
    if (clickedBtn) clickedBtn.classList.add('active');
}

// --- COOL EFFECT 2: PASSWORD SHOW / HIDE ---
function togglePasswordVisibility() {
    const passInp = document.getElementById('login-password');
    const eyeIcon = document.getElementById('password-eye-icon');
    if (!passInp || !eyeIcon) return;

    if (passInp.type === 'password') {
        passInp.type = 'text';
        eyeIcon.className = 'fa-regular fa-eye-slash';
    } else {
        passInp.type = 'password';
        eyeIcon.className = 'fa-regular fa-eye';
    }
}

// --- COOL EFFECT 3: ANIMATED NUMBER COUNTERS ---
function animateCounter(elementId, targetValue, duration = 1000, suffix = '') {
    const el = document.getElementById(elementId);
    if (!el) return;

    const start = 0;
    const startTime = performance.now();

    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easeOutQuad = progress * (2 - progress);
        const currentVal = Math.floor(start + (targetValue - start) * easeOutQuad);

        el.textContent = currentVal.toLocaleString('id-ID') + suffix;

        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        } else {
            el.textContent = targetValue.toLocaleString('id-ID') + suffix;
        }
    }
    requestAnimationFrame(updateCounter);
}

// --- AUTHENTICATION & LOGIN SUBMIT WITH SPINNER ---
async function handleLogin(e) {
    e.preventDefault();
    const username = document.getElementById('login-username').value.trim();
    if (!username) return;

    const btnSubmit = document.getElementById('btn-login-submit');
    if (btnSubmit) btnSubmit.classList.add('loading');

    // Smooth simulate check
    await new Promise(r => setTimeout(r, 700));

    const matchedMember = usersData.find(u => 
        u.nik === username || u.name.toLowerCase() === username.toLowerCase()
    );

    if (matchedMember) {
        const initials = matchedMember.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        currentUser = { name: matchedMember.name, role: matchedMember.role, initials };
    } else if (username.toLowerCase().includes('bendahara') || username.toLowerCase().includes('siti')) {
        currentUser = { name: 'Siti Aminah', role: 'Bendahara Kelompok', initials: 'SA' };
    } else if (username.toLowerCase().includes('admin') || username.toLowerCase().includes('budi')) {
        currentUser = { name: 'Budi Santoso', role: 'Ketua Poktan (Admin)', initials: 'BS' };
    } else {
        const displayName = username.split('@')[0];
        currentUser = {
            name: displayName.charAt(0).toUpperCase() + displayName.slice(1),
            role: 'Pengurus Poktan',
            initials: displayName.substring(0, 2).toUpperCase()
        };
    }

    if (btnSubmit) btnSubmit.classList.remove('loading');

    document.getElementById('current-user-name').textContent = currentUser.name;
    document.getElementById('current-user-role').textContent = currentUser.role;
    document.getElementById('current-user-avatar').textContent = currentUser.initials;

    const loginView = document.getElementById('login-view');
    const appView = document.getElementById('app-view');

    loginView.style.opacity = '0';
    loginView.style.transition = 'opacity 0.3s ease';

    setTimeout(() => {
        loginView.classList.add('hidden');
        loginView.style.opacity = '1';
        appView.classList.remove('hidden');
        appView.classList.add('fade-in');

        refreshAllViews();
        showToast(`Selamat datang kembali, ${currentUser.name}!`, 'success');
    }, 300);
}

function handleLogout() {
    const appView = document.getElementById('app-view');
    const loginView = document.getElementById('login-view');

    appView.style.opacity = '0';
    appView.style.transition = 'opacity 0.25s ease';

    setTimeout(() => {
        appView.classList.add('hidden');
        appView.style.opacity = '1';
        loginView.classList.remove('hidden');
        loginView.classList.add('fade-in');
        showToast('Anda telah keluar dari sesi dashboard.', 'info');
    }, 250);
}

// --- NAVIGATION & TAB SWITCHING ---
function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const isHidden = sidebar.classList.contains('-translate-x-full');
    if (isHidden) {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
    } else {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    }
}

function switchTab(tabId) {
    const titles = {
        dashboard: ['Ringkasan Dashboard', 'Pantau perkembangan kelompok tani, ketersediaan pupuk, dan transaksi.'],
        users: ['Kelola User & Anggota Poktan', 'Manajemen data anggota petani, pengurus, dan luas lahan garapan.'],
        fertilizers: ['Kelola Inventaris Pupuk', 'Pantau stok pupuk bersubsidi, non-subsidi, organik, dan harga tebus (HET).'],
        crops: ['Kelola Komoditas Tanaman', 'Monitoring jadwal tanam, varietas bibit, dan estimasi waktu panen anggota.'],
        transactions: ['Kelola Transaksi & Kas', 'Pencatatan distribusi pupuk, hasil penjualan panen, dan arus kas kelompok.'],
        ai: ['Penyuluh Pertanian AI Pintar', 'Asisten cerdas berbasis Gemini API untuk analisis pupuk, budidaya tanaman, dan kas kelompok.']
    };

    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    const targetTab = document.getElementById(`tab-${tabId}`);
    if (targetTab) {
        targetTab.classList.remove('hidden');
        targetTab.classList.add('fade-in');
    }

    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.className = 'nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium text-agro-100 hover:bg-white/10 transition';
    });
    const activeBtn = document.getElementById(`nav-${tabId}`);
    if (activeBtn) {
        activeBtn.className = 'nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition bg-agro-700 text-white shadow-xs';
    }

    if (titles[tabId]) {
        document.getElementById('page-title').textContent = titles[tabId][0];
        document.getElementById('page-subtitle').textContent = titles[tabId][1];
    }

    // Close mobile sidebar if open
    const sidebar = document.getElementById('sidebar');
    if (sidebar && !sidebar.classList.contains('-translate-x-full') && window.innerWidth < 1024) {
        toggleMobileSidebar();
    }

    refreshAllViews();
}

// --- RENDER FUNCTIONS ---
function refreshAllViews() {
    document.getElementById('badge-users').textContent = usersData.length;
    document.getElementById('badge-fertilizers').textContent = fertilizersData.length;
    document.getElementById('badge-crops').textContent = cropsData.length;
    document.getElementById('badge-transactions').textContent = transactionsData.length;

    renderDashboard();
    renderUsers();
    renderFertilizers();
    renderCrops();
    renderTransactions();
}

function renderDashboard() {
    // 1. KPI Stats with Animation
    const totalLand = usersData.reduce((acc, u) => acc + Number(u.land || 0), 0);
    document.getElementById('stat-total-users').textContent = `${usersData.length} Orang`;
    document.getElementById('stat-total-land').textContent = `${totalLand.toFixed(2)} Ha`;

    const totalFertStock = fertilizersData.reduce((acc, f) => acc + Number(f.stock || 0), 0);
    const lowStockCount = fertilizersData.filter(f => f.stock < 300).length;
    document.getElementById('stat-total-fertilizer').textContent = `${totalFertStock.toLocaleString('id-ID')} Kg`;

    const lowStockEl = document.getElementById('stat-low-stock-alert');
    if (lowStockCount > 0) {
        lowStockEl.textContent = `${lowStockCount} Jenis Menipis`;
        lowStockEl.className = 'font-semibold text-amber-600';
    } else {
        lowStockEl.textContent = 'Stok Gudang Aman';
        lowStockEl.className = 'font-semibold text-emerald-600';
    }

    const readyHarvestCount = cropsData.filter(c => c.phase === 'Siap Panen').length;
    document.getElementById('stat-total-crops').textContent = `${cropsData.length} Lahan`;
    document.getElementById('stat-ready-harvest').textContent = `${readyHarvestCount} Siap Panen`;

    const totalIn = transactionsData.filter(t => t.flow === 'IN').reduce((a, b) => a + Number(b.amount), 0);
    const totalOut = transactionsData.filter(t => t.flow === 'OUT').reduce((a, b) => a + Number(b.amount), 0);
    const netBalance = totalIn - totalOut;
    document.getElementById('stat-net-balance').textContent = formatRupiah(netBalance);
    document.getElementById('stat-tx-count').textContent = `${transactionsData.length} Transaksi`;

    // 2. Fertilizer Progress Bars
    const barsContainer = document.getElementById('dashboard-fertilizer-bars');
    barsContainer.innerHTML = fertilizersData.map(f => {
        const pct = Math.min(100, Math.round((f.stock / f.quota) * 100));
        const barColor = pct < 30 ? 'bg-amber-500' : (f.category === 'Subsidi' ? 'bg-agro-600' : 'bg-blue-600');
        return `
            <div>
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-slate-800">${f.name}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium ${f.category === 'Subsidi' ? 'bg-agro-100 text-agro-800' : 'bg-slate-100 text-slate-600'}">${f.category}</span>
                    </div>
                    <span class="font-semibold text-slate-700">${f.stock.toLocaleString('id-ID')} / ${f.quota.toLocaleString('id-ID')} Kg (${pct}%)</span>
                </div>
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="${barColor} h-full rounded-full transition-all duration-500" style="width: ${pct}%"></div>
                </div>
            </div>
        `;
    }).join('');

    // 3. Crops Phase List on Dashboard
    const cropsListEl = document.getElementById('dashboard-crops-list');
    cropsListEl.innerHTML = cropsData.slice(0, 4).map(c => {
        const badgeClass = c.phase === 'Siap Panen'
            ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
            : 'bg-amber-50 text-amber-800 border-amber-200';
        return `
            <div class="p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition flex items-center justify-between gap-2">
                <div>
                    <div class="font-semibold text-xs text-slate-800">${c.name} (${c.variety})</div>
                    <div class="text-[11px] text-slate-500">${c.farmer} &bull; ${c.area} Ha</div>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-[11px] font-medium border ${badgeClass} shrink-0">${c.phase}</span>
            </div>
        `;
    }).join('');

    // 4. Recent Transactions on Dashboard
    const recentTxEl = document.getElementById('dashboard-recent-tx');
    recentTxEl.innerHTML = transactionsData.slice(0, 5).map(t => `
        <tr class="hover:bg-slate-50/80 transition">
            <td class="py-3.5 px-6 whitespace-nowrap">
                <div class="font-semibold text-slate-800 text-xs">${t.id}</div>
                <div class="text-xs text-slate-400">${formatDateIndo(t.date)}</div>
            </td>
            <td class="py-3.5 px-6 font-medium text-slate-800">${t.member}</td>
            <td class="py-3.5 px-6">
                <span class="text-xs font-semibold text-slate-700 block">${t.category}</span>
                <span class="text-xs text-slate-500">${t.desc}</span>
            </td>
            <td class="py-3.5 px-6">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold ${t.flow === 'IN' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                    <i class="fa-solid ${t.flow === 'IN' ? 'fa-arrow-down-left' : 'fa-arrow-up-right'} text-[10px]"></i>
                    ${t.flow === 'IN' ? 'Kas Masuk' : 'Kas Keluar'}
                </span>
            </td>
            <td class="py-3.5 px-6 text-right font-bold ${t.flow === 'IN' ? 'text-emerald-700' : 'text-rose-700'}">
                ${t.flow === 'IN' ? '+' : '-'}${formatRupiah(t.amount)}
            </td>
        </tr>
    `).join('');
}

function renderUsers() {
    const search = (document.getElementById('search-user')?.value || '').toLowerCase();
    const roleFilter = document.getElementById('filter-user-role')?.value || 'ALL';

    const filtered = usersData.filter(u => {
        const matchSearch = u.name.toLowerCase().includes(search) || u.nik.includes(search) || u.block.toLowerCase().includes(search);
        const matchRole = roleFilter === 'ALL' || u.role === roleFilter;
        return matchSearch && matchRole;
    });

    const tbody = document.getElementById('users-table-body');
    if (!tbody) return;

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">Tidak ada data anggota yang sesuai pencarian.</td></tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(u => {
        const roleBadge = u.role === 'Ketua Poktan' ? 'bg-purple-100 text-purple-800'
            : u.role === 'Bendahara' ? 'bg-blue-100 text-blue-800'
            : u.role === 'Penyuluh' ? 'bg-amber-100 text-amber-800'
            : 'bg-slate-100 text-slate-700';

        return `
            <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3.5 px-6">
                    <div class="font-semibold text-slate-900">${u.name}</div>
                    <div class="text-xs text-slate-400">NIK: ${u.nik}</div>
                </td>
                <td class="py-3.5 px-6">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold ${roleBadge}">${u.role}</span>
                </td>
                <td class="py-3.5 px-6 text-slate-600 text-xs">
                    <i class="fa-brands fa-whatsapp text-agro-600 mr-1"></i> ${u.phone}
                </td>
                <td class="py-3.5 px-6">
                    <div class="font-semibold text-slate-800">${u.land} Ha</div>
                    <div class="text-xs text-slate-500">${u.block}</div>
                </td>
                <td class="py-3.5 px-6">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold ${u.status === 'Aktif' ? 'bg-agro-100 text-agro-800' : 'bg-slate-200 text-slate-600'}">
                        ${u.status}
                    </span>
                </td>
                <td class="py-3.5 px-6 text-right space-x-1">
                    <button onclick="openUserModal('${u.id}')" class="p-2 rounded-lg text-slate-500 hover:text-agro-600 hover:bg-agro-50 transition" title="Edit Anggota">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </button>
                    <button onclick="confirmDelete('user', '${u.id}', '${u.name}')" class="p-2 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition" title="Hapus Anggota">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderFertilizers() {
    const search = (document.getElementById('search-fertilizer')?.value || '').toLowerCase();
    const catFilter = document.getElementById('filter-fertilizer-cat')?.value || 'ALL';

    const filtered = fertilizersData.filter(f => {
        const matchSearch = f.name.toLowerCase().includes(search) || f.code.toLowerCase().includes(search);
        const matchCat = catFilter === 'ALL' || f.category === catFilter;
        return matchSearch && matchCat;
    });

    const grid = document.getElementById('fertilizers-grid');
    if (!grid) return;

    if (filtered.length === 0) {
        grid.innerHTML = `<div class="col-span-full bg-white p-8 rounded-2xl text-center text-slate-400 border border-slate-200">Tidak ada data pupuk yang ditemukan.</div>`;
        return;
    }

    grid.innerHTML = filtered.map(f => {
        const pct = Math.min(100, Math.round((f.stock / f.quota) * 100));
        const badgeClass = f.category === 'Subsidi'
            ? 'bg-agro-100 text-agro-800 border-agro-200'
            : f.category === 'Organik'
            ? 'bg-amber-100 text-amber-800 border-amber-200'
            : 'bg-blue-100 text-blue-800 border-blue-200';

        return `
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div>
                            <span class="text-xs font-mono text-slate-400">${f.code}</span>
                            <h4 class="font-bold text-slate-900 text-base">${f.name}</h4>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold border ${badgeClass}">${f.category}</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">${f.desc || 'Pupuk pertanian kelompok tani'}</p>

                    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100 mb-4">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="text-slate-500">Stok Tersedia:</span>
                            <span class="font-bold text-sm text-slate-900">${f.stock.toLocaleString('id-ID')} Kg <span class="text-xs font-normal text-slate-400">/ ${f.quota} Kg</span></span>
                        </div>
                        <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                            <div class="${pct < 25 ? 'bg-red-500' : 'bg-agro-600'} h-full rounded-full" style="width: ${pct}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs mt-2.5 pt-2 border-t border-slate-200/60">
                            <span class="text-slate-500">Harga Tebus / HET:</span>
                            <span class="font-semibold text-agro-700">${formatRupiah(f.price)} / Kg</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                    <button onclick="quickRestockFertilizer('${f.id}')" class="px-3 py-1.5 rounded-xl bg-agro-50 hover:bg-agro-100 text-agro-700 text-xs font-semibold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus"></i> Tambah +100 Kg
                    </button>
                    <div class="flex items-center gap-1">
                        <button onclick="openFertilizerModal('${f.id}')" class="p-2 rounded-lg text-slate-500 hover:text-agro-600 hover:bg-agro-50 transition" title="Edit Pupuk">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        <button onclick="confirmDelete('fertilizer', '${f.id}', '${f.name}')" class="p-2 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition" title="Hapus Pupuk">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function renderCrops() {
    const search = (document.getElementById('search-crop')?.value || '').toLowerCase();
    const statusFilter = document.getElementById('filter-crop-status')?.value || 'ALL';

    const filtered = cropsData.filter(c => {
        const matchSearch = c.name.toLowerCase().includes(search) || c.variety.toLowerCase().includes(search) || c.farmer.toLowerCase().includes(search);
        const matchStatus = statusFilter === 'ALL' || c.phase === statusFilter;
        return matchSearch && matchStatus;
    });

    const tbody = document.getElementById('crops-table-body');
    if (!tbody) return;

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">Tidak ada data tanaman yang sesuai filter.</td></tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(c => {
        const phaseColor = c.phase === 'Siap Panen' ? 'bg-emerald-100 text-emerald-800'
            : c.phase.includes('Generatif') ? 'bg-blue-100 text-blue-800'
            : c.phase.includes('Vegetatif') ? 'bg-agro-100 text-agro-800'
            : 'bg-amber-100 text-amber-800';

        return `
            <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3.5 px-6">
                    <div class="font-semibold text-slate-900">${c.name}</div>
                    <div class="text-xs text-slate-500">Varietas: ${c.variety}</div>
                </td>
                <td class="py-3.5 px-6 font-medium text-slate-800">${c.farmer}</td>
                <td class="py-3.5 px-6">
                    <span class="font-semibold text-slate-800">${c.area} Ha</span>
                </td>
                <td class="py-3.5 px-6 text-xs">
                    <div class="text-slate-600"><span class="text-slate-400">Tanam:</span> ${formatDateIndo(c.plantDate)}</div>
                    <div class="font-semibold text-agro-700 mt-0.5"><span class="text-slate-400 font-normal">Panen:</span> ${formatDateIndo(c.harvestDate)}</div>
                </td>
                <td class="py-3.5 px-6">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold ${phaseColor}">${c.phase}</span>
                </td>
                <td class="py-3.5 px-6 text-right whitespace-nowrap space-x-1">
                    <button onclick="recommendCropCareAI('${c.id}')" class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-semibold transition inline-flex items-center gap-1" title="Rekomendasi Dosis Pupuk & Perawatan AI">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-600"></i> Saran AI
                    </button>
                    <button onclick="openCropModal('${c.id}')" class="p-2 rounded-lg text-slate-500 hover:text-agro-600 hover:bg-agro-50 transition" title="Edit Tanaman">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </button>
                    <button onclick="confirmDelete('crop', '${c.id}', '${c.name} - ${c.farmer}')" class="p-2 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition" title="Hapus Tanaman">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderTransactions() {
    const totalIn = transactionsData.filter(t => t.flow === 'IN').reduce((a, b) => a + Number(b.amount), 0);
    const totalOut = transactionsData.filter(t => t.flow === 'OUT').reduce((a, b) => a + Number(b.amount), 0);
    
    const sumIn = document.getElementById('tx-summary-in');
    const sumOut = document.getElementById('tx-summary-out');
    const sumNet = document.getElementById('tx-summary-net');
    if (sumIn) sumIn.textContent = formatRupiah(totalIn);
    if (sumOut) sumOut.textContent = formatRupiah(totalOut);
    if (sumNet) sumNet.textContent = formatRupiah(totalIn - totalOut);

    const search = (document.getElementById('search-tx')?.value || '').toLowerCase();
    const typeFilter = document.getElementById('filter-tx-type')?.value || 'ALL';

    const filtered = transactionsData.filter(t => {
        const matchSearch = t.id.toLowerCase().includes(search) || t.member.toLowerCase().includes(search) || t.desc.toLowerCase().includes(search);
        const matchType = typeFilter === 'ALL' || t.category === typeFilter;
        return matchSearch && matchType;
    });

    const tbody = document.getElementById('tx-table-body');
    if (!tbody) return;

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-slate-400 text-sm">Belum ada transaksi yang sesuai filter.</td></tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(t => `
        <tr class="hover:bg-slate-50/80 transition">
            <td class="py-3.5 px-6 whitespace-nowrap">
                <div class="font-semibold text-slate-900 text-xs">${t.id}</div>
                <div class="text-xs text-slate-400">${formatDateIndo(t.date)}</div>
            </td>
            <td class="py-3.5 px-6 font-medium text-slate-800">${t.member}</td>
            <td class="py-3.5 px-6">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold">${t.category}</span>
            </td>
            <td class="py-3.5 px-6 text-slate-600 text-xs max-w-xs">${t.desc}</td>
            <td class="py-3.5 px-6">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold ${t.flow === 'IN' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                    ${t.flow === 'IN' ? 'Masuk (+)' : 'Keluar (-)'}
                </span>
            </td>
            <td class="py-3.5 px-6 text-right font-bold ${t.flow === 'IN' ? 'text-emerald-700' : 'text-rose-700'}">
                ${formatRupiah(t.amount)}
            </td>
            <td class="py-3.5 px-6 text-right whitespace-nowrap space-x-1">
                <button onclick="viewReceipt('${t.id}')" class="p-2 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Lihat Bukti Struk">
                    <i class="fa-solid fa-receipt"></i>
                </button>
                <button onclick="confirmDelete('tx', '${t.id}', '${t.id}')" class="p-2 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition" title="Hapus Transaksi">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// --- CRUD MODAL HANDLERS ---
function closeModal(modalId) {
    const m = document.getElementById(modalId);
    if (m) m.classList.add('hidden');
}

// 1. User CRUD
function openUserModal(editId = null) {
    const form = document.getElementById('user-form');
    form.reset();
    document.getElementById('user-id').value = '';
    document.getElementById('user-modal-title').textContent = 'Tambah Anggota / User Baru';

    if (editId) {
        const u = usersData.find(x => x.id === editId);
        if (u) {
            document.getElementById('user-modal-title').textContent = 'Edit Data Anggota';
            document.getElementById('user-id').value = u.id;
            document.getElementById('user-name').value = u.name;
            document.getElementById('user-nik').value = u.nik;
            document.getElementById('user-role').value = u.role;
            document.getElementById('user-phone').value = u.phone;
            document.getElementById('user-land').value = u.land;
            document.getElementById('user-block').value = u.block;
            document.getElementById('user-status').value = u.status;
        }
    }
    document.getElementById('user-modal').classList.remove('hidden');
}

function saveUser(e) {
    e.preventDefault();
    const id = document.getElementById('user-id').value;
    const payload = {
        id: id || 'USR-' + String(Date.now()).slice(-4),
        name: document.getElementById('user-name').value.trim(),
        nik: document.getElementById('user-nik').value.trim(),
        role: document.getElementById('user-role').value,
        phone: document.getElementById('user-phone').value.trim(),
        land: parseFloat(document.getElementById('user-land').value) || 0,
        block: document.getElementById('user-block').value.trim(),
        status: document.getElementById('user-status').value
    };

    if (id) {
        const idx = usersData.findIndex(x => x.id === id);
        if (idx > -1) usersData[idx] = payload;
        showToast(`Data anggota ${payload.name} berhasil diperbarui.`);
    } else {
        usersData.unshift(payload);
        showToast(`Anggota baru ${payload.name} berhasil ditambahkan.`);
    }
    closeModal('user-modal');
    refreshAllViews();
}

// 2. Fertilizer CRUD
function openFertilizerModal(editId = null) {
    const form = document.getElementById('fertilizer-form');
    form.reset();
    document.getElementById('fert-id').value = '';
    document.getElementById('fertilizer-modal-title').textContent = 'Tambah Data Pupuk Baru';

    if (editId) {
        const f = fertilizersData.find(x => x.id === editId);
        if (f) {
            document.getElementById('fertilizer-modal-title').textContent = 'Edit Inventaris Pupuk';
            document.getElementById('fert-id').value = f.id;
            document.getElementById('fert-code').value = f.code;
            document.getElementById('fert-category').value = f.category;
            document.getElementById('fert-name').value = f.name;
            document.getElementById('fert-stock').value = f.stock;
            document.getElementById('fert-quota').value = f.quota;
            document.getElementById('fert-price').value = f.price;
            document.getElementById('fert-desc').value = f.desc;
        }
    }
    document.getElementById('fertilizer-modal').classList.remove('hidden');
}

function saveFertilizer(e) {
    e.preventDefault();
    const id = document.getElementById('fert-id').value;
    const payload = {
        id: id || 'PPK-' + String(Date.now()).slice(-4),
        code: document.getElementById('fert-code').value.trim(),
        category: document.getElementById('fert-category').value,
        name: document.getElementById('fert-name').value.trim(),
        stock: parseInt(document.getElementById('fert-stock').value) || 0,
        quota: parseInt(document.getElementById('fert-quota').value) || 1000,
        price: parseInt(document.getElementById('fert-price').value) || 0,
        desc: document.getElementById('fert-desc').value.trim()
    };

    if (id) {
        const idx = fertilizersData.findIndex(x => x.id === id);
        if (idx > -1) fertilizersData[idx] = payload;
        showToast(`Data ${payload.name} berhasil diperbarui.`);
    } else {
        fertilizersData.push(payload);
        showToast(`Pupuk ${payload.name} berhasil ditambahkan ke gudang.`);
    }
    closeModal('fertilizer-modal');
    refreshAllViews();
}

function quickRestockFertilizer(id) {
    const f = fertilizersData.find(x => x.id === id);
    if (!f) return;
    f.stock += 100;
    if (f.stock > f.quota) f.quota = f.stock;
    refreshAllViews();
    showToast(`Stok ${f.name} bertambah +100 Kg (Total: ${f.stock} Kg).`);
}

// 3. Crop CRUD
function openCropModal(editId = null) {
    const form = document.getElementById('crop-form');
    form.reset();
    document.getElementById('crop-id').value = '';
    document.getElementById('crop-modal-title').textContent = 'Tambah Komoditas Tanaman';

    const farmerSelect = document.getElementById('crop-farmer');
    farmerSelect.innerHTML = usersData.map(u => `<option value="${u.name}">${u.name} (${u.block})</option>`).join('');

    if (editId) {
        const c = cropsData.find(x => x.id === editId);
        if (c) {
            document.getElementById('crop-modal-title').textContent = 'Edit Data Tanaman';
            document.getElementById('crop-id').value = c.id;
            document.getElementById('crop-name').value = c.name;
            document.getElementById('crop-variety').value = c.variety;
            document.getElementById('crop-farmer').value = c.farmer;
            document.getElementById('crop-area').value = c.area;
            document.getElementById('crop-plant-date').value = c.plantDate;
            document.getElementById('crop-harvest-date').value = c.harvestDate;
            document.getElementById('crop-phase').value = c.phase;
        }
    } else {
        document.getElementById('crop-plant-date').value = '2026-10-17';
        document.getElementById('crop-harvest-date').value = '2027-01-20';
    }
    document.getElementById('crop-modal').classList.remove('hidden');
}

function saveCrop(e) {
    e.preventDefault();
    const id = document.getElementById('crop-id').value;
    const payload = {
        id: id || 'TNM-' + String(Date.now()).slice(-4),
        name: document.getElementById('crop-name').value.trim(),
        variety: document.getElementById('crop-variety').value.trim(),
        farmer: document.getElementById('crop-farmer').value,
        area: parseFloat(document.getElementById('crop-area').value) || 0.5,
        plantDate: document.getElementById('crop-plant-date').value,
        harvestDate: document.getElementById('crop-harvest-date').value,
        phase: document.getElementById('crop-phase').value
    };

    if (id) {
        const idx = cropsData.findIndex(x => x.id === id);
        if (idx > -1) cropsData[idx] = payload;
        showToast(`Data tanaman ${payload.name} berhasil diperbarui.`);
    } else {
        cropsData.unshift(payload);
        showToast(`Komoditas ${payload.name} (${payload.farmer}) berhasil didaftarkan.`);
    }
    closeModal('crop-modal');
    refreshAllViews();
}

// 4. Transaction CRUD + Fertilizer Stock Sync
function openTransactionModal() {
    const form = document.getElementById('tx-form');
    form.reset();
    document.getElementById('tx-date').value = '2026-10-17';

    const memberSelect = document.getElementById('tx-member');
    memberSelect.innerHTML = usersData.map(u => `<option value="${u.name}">${u.name} - ${u.role}</option>`).join('') +
        `<option value="KUD Tani Makmur (Pemasok)">KUD Tani Makmur (Mitra/Pemasok)</option>`;

    const fertSelect = document.getElementById('tx-fert-id');
    fertSelect.innerHTML = fertilizersData.map(f => `<option value="${f.id}">${f.name} (Stok: ${f.stock} Kg - Rp ${f.price}/Kg)</option>`).join('');

    handleTxCategoryChange();
    document.getElementById('tx-modal').classList.remove('hidden');
}

function handleTxCategoryChange() {
    const cat = document.getElementById('tx-category').value;
    const fertBox = document.getElementById('tx-fertilizer-box');
    const flowSelect = document.getElementById('tx-flow');

    if (cat === 'Tebus Pupuk') {
        fertBox.classList.remove('hidden');
        flowSelect.value = 'IN';
        autoCalculateFertilizerPrice();
    } else if (cat === 'Belanja Gudang') {
        fertBox.classList.remove('hidden');
        flowSelect.value = 'OUT';
        autoCalculateFertilizerPrice();
    } else if (cat === 'Penjualan Panen' || cat === 'Iuran Poktan') {
        fertBox.classList.add('hidden');
        flowSelect.value = 'IN';
        document.getElementById('tx-desc').value = cat === 'Penjualan Panen' ? 'Setoran Hasil Panen Komoditas Anggota' : 'Setoran Iuran Kas Wajib Anggota';
        document.getElementById('tx-amount').value = cat === 'Penjualan Panen' ? 1500000 : 100000;
    }
}

function autoCalculateFertilizerPrice() {
    const cat = document.getElementById('tx-category').value;
    const fertId = document.getElementById('tx-fert-id').value;
    const qty = parseInt(document.getElementById('tx-fert-qty').value) || 0;
    const fert = fertilizersData.find(x => x.id === fertId);
    if (!fert) return;

    const total = qty * fert.price;
    document.getElementById('tx-amount').value = total;
    if (cat === 'Tebus Pupuk') {
        document.getElementById('tx-desc').value = `Tebus ${fert.name} (${qty} Kg)`;
    } else if (cat === 'Belanja Gudang') {
        document.getElementById('tx-desc').value = `Belanja Stok Gudang ${fert.name} (${qty} Kg)`;
    }
}

function saveTransaction(e) {
    e.preventDefault();
    const category = document.getElementById('tx-category').value;
    const fertId = document.getElementById('tx-fert-id').value;
    const qty = parseInt(document.getElementById('tx-fert-qty').value) || 0;

    if (category === 'Tebus Pupuk' || category === 'Belanja Gudang') {
        const fert = fertilizersData.find(x => x.id === fertId);
        if (fert) {
            if (category === 'Tebus Pupuk') {
                if (fert.stock < qty) {
                    showToast(`Stok ${fert.name} tidak mencukupi (Tersisa ${fert.stock} Kg)!`, 'error');
                    return;
                }
                fert.stock -= qty;
            } else if (category === 'Belanja Gudang') {
                fert.stock += qty;
                if (fert.stock > fert.quota) fert.quota = fert.stock;
            }
        }
    }

    const newTx = {
        id: 'TRX-' + String(Date.now()).slice(-5),
        date: document.getElementById('tx-date').value,
        member: document.getElementById('tx-member').value,
        category: category,
        desc: document.getElementById('tx-desc').value.trim(),
        flow: document.getElementById('tx-flow').value,
        amount: parseInt(document.getElementById('tx-amount').value) || 0
    };

    transactionsData.unshift(newTx);
    closeModal('tx-modal');
    refreshAllViews();
    showToast(`Transaksi ${newTx.id} berhasil disimpan!`, 'success');
}

// --- RECEIPT PREVIEW & DELETE CONFIRMATION ---
function viewReceipt(txId) {
    const t = transactionsData.find(x => x.id === txId);
    if (!t) return;

    document.getElementById('receipt-body').innerHTML = `
        <div class="flex justify-between py-1.5 border-b border-slate-100">
            <span class="text-slate-500">No. Transaksi</span>
            <span class="font-mono font-bold text-slate-900">${t.id}</span>
        </div>
        <div class="flex justify-between py-1.5 border-b border-slate-100">
            <span class="text-slate-500">Tanggal</span>
            <span class="font-medium text-slate-800">${formatDateIndo(t.date)}</span>
        </div>
        <div class="flex justify-between py-1.5 border-b border-slate-100">
            <span class="text-slate-500">Nama Anggota / Mitra</span>
            <span class="font-semibold text-slate-900">${t.member}</span>
        </div>
        <div class="flex justify-between py-1.5 border-b border-slate-100">
            <span class="text-slate-500">Kategori</span>
            <span class="font-medium text-slate-800">${t.category}</span>
        </div>
        <div class="py-2 border-b border-slate-100">
            <span class="text-slate-500 block mb-1">Rincian Transaksi:</span>
            <p class="font-medium text-slate-800 bg-slate-50 p-2.5 rounded-xl border border-slate-200/60">${t.desc}</p>
        </div>
        <div class="flex justify-between items-center pt-2">
            <span class="font-semibold text-slate-700">Total Nominal</span>
            <span class="text-lg font-bold text-agro-700">${formatRupiah(t.amount)}</span>
        </div>
        <div class="pt-4 text-center text-[11px] text-slate-400">
            Diverifikasi oleh: ${currentUser.name} (${currentUser.role})
        </div>
    `;
    document.getElementById('receipt-modal').classList.remove('hidden');
}

function confirmDelete(type, id, label) {
    document.getElementById('confirm-modal-text').textContent = `Apakah Anda yakin ingin menghapus "${label}" dari sistem?`;
    const btn = document.getElementById('confirm-delete-btn');
    btn.onclick = function () {
        if (type === 'user') usersData = usersData.filter(x => x.id !== id);
        if (type === 'fertilizer') fertilizersData = fertilizersData.filter(x => x.id !== id);
        if (type === 'crop') cropsData = cropsData.filter(x => x.id !== id);
        if (type === 'tx') transactionsData = transactionsData.filter(x => x.id !== id);

        closeModal('confirm-modal');
        refreshAllViews();
        showToast(`Data "${label}" berhasil dihapus.`, 'info');
    };
    document.getElementById('confirm-modal').classList.remove('hidden');
}

// ============================================================================
// GEMINI API INTEGRATION (gemini-3-flash-preview)
// ============================================================================
const apiKey = "";
const GEMINI_API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key=${apiKey}`;

function getPoktanContextSummary() {
    const totalLand = usersData.reduce((acc, u) => acc + Number(u.land || 0), 0);
    const totalIn = transactionsData.filter(t => t.flow === 'IN').reduce((a, b) => a + Number(b.amount), 0);
    const totalOut = transactionsData.filter(t => t.flow === 'OUT').reduce((a, b) => a + Number(b.amount), 0);

    return JSON.stringify({
        namaKelompokTani: "Poktan Harapan Makmur",
        totalAnggota: usersData.length,
        totalLuasLahanHa: totalLand,
        anggota: usersData.map(u => ({ nama: u.name, peran: u.role, luasHa: u.land, blok: u.block, status: u.status })),
        stokPupukGudang: fertilizersData.map(f => ({ kode: f.code, nama: f.name, kategori: f.category, stokKg: f.stock, kuotaKg: f.quota, hargaPerKg: f.price })),
        tanamanAktif: cropsData.map(c => ({ komoditas: c.name, varietas: c.variety, petani: c.farmer, luasHa: c.area, tglTanam: c.plantDate, estPanen: c.harvestDate, fase: c.phase })),
        ringkasanKas: { totalMasukRp: totalIn, totalKeluarRp: totalOut, saldoBersihRp: totalIn - totalOut },
        transaksiTerbaru: transactionsData.slice(0, 8)
    });
}

async function callGeminiAPI({ userPrompt, systemPrompt, useSearch = false, responseSchema = null }) {
    const payload = {
        contents: [{ parts: [{ text: userPrompt }] }],
        systemInstruction: {
            parts: [{ text: systemPrompt }]
        }
    };

    if (useSearch) {
        payload.tools = [{ "google_search": {} }];
    }

    if (responseSchema) {
        payload.generationConfig = {
            responseMimeType: "application/json",
            responseSchema: responseSchema
        };
    }

    const delays = [1000, 2000, 4000, 8000, 16000];
    for (let attempt = 0; attempt <= delays.length; attempt++) {
        try {
            const response = await fetch(GEMINI_API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error(`HTTP status ${response.status}`);
            }

            const result = await response.json();
            const candidate = result.candidates?.[0];
            const text = candidate?.content?.parts?.[0]?.text;

            if (!text) {
                throw new Error("Respons AI kosong.");
            }

            let sources = [];
            const groundingMetadata = candidate?.groundingMetadata;
            if (groundingMetadata && groundingMetadata.groundingAttributions) {
                sources = groundingMetadata.groundingAttributions
                    .map(attr => ({
                        uri: attr.web?.uri,
                        title: attr.web?.title
                    }))
                    .filter(s => s.uri && s.title);
            }

            return { text, sources };
        } catch (err) {
            if (attempt === delays.length) {
                throw err;
            }
            await new Promise(res => setTimeout(res, delays[attempt]));
        }
    }
}

function formatMarkdownToHTML(mdText) {
    if (!mdText) return '';
    let safe = mdText
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    safe = safe.replace(/^### (.*$)/gim, '<h4 class="font-bold text-slate-900 text-sm mt-3 mb-1">$1</h4>');
    safe = safe.replace(/^## (.*$)/gim, '<h3 class="font-bold text-slate-900 text-base mt-3 mb-1">$1</h3>');
    safe = safe.replace(/^- (.*$)/gim, '<li class="ml-4 list-disc">$1</li>');
    safe = safe.replace(/\n/g, '<br>');
    return safe;
}

// 1. Dashboard Executive Summary AI
async function generateDashboardExecutiveBrief() {
    const box = document.getElementById('dashboard-ai-brief-box');
    const content = document.getElementById('dashboard-ai-brief-content');
    const btn = document.getElementById('btn-ai-brief');

    box.classList.remove('hidden');
    btn.disabled = true;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <span>Menganalisis Data...</span>`;
    content.innerHTML = `
        <div class="flex items-center gap-3 py-2 text-amber-200">
            <i class="fa-solid fa-circle-notch fa-spin text-lg"></i>
            <span>Gemini AI sedang mengevaluasi stok gudang pupuk, fase panen tanaman, dan saldo kas kelompok...</span>
        </div>
    `;

    const systemPrompt = "Anda adalah Konsultan Manajemen Kelompok Tani (Poktan) senior di Indonesia. Berikan ringkasan eksekutif yang padat, jelas, dan langsung dapat ditindaklanjuti oleh Ketua Poktan dalam 3 poin utama: (1) Kesiapan Stok Pupuk Gudang, (2) Prioritas Panen & Pemeliharaan Tanaman, dan (3) Kesehatan Kas Kelompok.";
    const userPrompt = `Analisis data real-time Kelompok Tani berikut dan berikan laporan ringkas:\n${getPoktanContextSummary()}`;

    try {
        const { text } = await callGeminiAPI({ userPrompt, systemPrompt });
        content.innerHTML = `
            <div class="flex items-center justify-between border-b border-white/15 pb-2 mb-2">
                <span class="font-bold text-amber-300 text-xs uppercase tracking-wider"><i class="fa-solid fa-check-circle mr-1"></i> Hasil Analisis Eksekutif Gemini AI</span>
                <button onclick="document.getElementById('dashboard-ai-brief-box').classList.add('hidden')" class="text-xs text-agro-200 hover:text-white">Tutup</button>
            </div>
            <div class="leading-relaxed">${formatMarkdownToHTML(text)}</div>
        `;
    } catch (error) {
        content.innerHTML = `<div class="text-red-200">Gagal memuat analisis AI. Silakan coba kembali.</div>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-bolt"></i> <span>Buat Analisis Poktan AI</span>`;
    }
}

// 2. Structured Crop Care & Fertilizer Dosage Recommendation (JSON Schema)
async function recommendCropCareAI(cropId) {
    const crop = cropsData.find(c => c.id === cropId);
    if (!crop) return;

    const modal = document.getElementById('ai-result-modal');
    const titleEl = document.getElementById('ai-modal-title');
    const subEl = document.getElementById('ai-modal-subtitle');
    const bodyEl = document.getElementById('ai-modal-body');

    titleEl.textContent = `Rekomendasi Budidaya & Dosis Pupuk: ${crop.name}`;
    subEl.textContent = `Lahan ${crop.farmer} (${crop.area} Ha) • Varietas ${crop.variety} • Fase: ${crop.phase}`;
    bodyEl.innerHTML = `
        <div class="py-12 text-center space-y-3">
            <i class="fa-solid fa-wand-magic-sparkles fa-bounce text-3xl text-agro-600"></i>
            <p class="font-semibold text-slate-700">Gemini AI sedang menghitung dosis pupuk untuk lahan ${crop.area} Ha...</p>
            <p class="text-xs text-slate-400">Menyesuaikan rekomendasi dengan ketersediaan pupuk di gudang Poktan.</p>
        </div>
    `;
    modal.classList.remove('hidden');

    const schema = {
        type: "OBJECT",
        properties: {
            ringkasanFase: { type: "STRING" },
            rekomendasiPupuk: {
                type: "ARRAY",
                items: {
                    type: "OBJECT",
                    properties: {
                        namaPupuk: { type: "STRING" },
                        totalKebutuhanKg: { type: "NUMBER" },
                        waktuAplikasi: { type: "STRING" },
                        ketersediaanGudang: { type: "STRING" }
                    },
                    propertyOrdering: ["namaPupuk", "totalKebutuhanKg", "waktuAplikasi", "ketersediaanGudang"]
                }
            },
            pencegahanHama: {
                type: "ARRAY",
                items: { type: "STRING" }
            },
            estimasiHasilPanenTon: { type: "STRING" }
        },
        propertyOrdering: ["ringkasanFase", "rekomendasiPupuk", "pencegahanHama", "estimasiHasilPanenTon"]
    };

    const systemPrompt = "Anda adalah Penyuluh Pertanian Lapangan (PPL) ahli agronomi Indonesia. Berikan rekomendasi dosis pupuk yang akurat sesuai luas lahan petani dan stok pupuk yang tersedia di gudang kelompok tani.";
    const userPrompt = `Berikan rekomendasi perawatan terstruktur untuk tanaman berikut:
Komoditas: ${crop.name} (Varietas: ${crop.variety})
Petani: ${crop.farmer}
Luas Lahan: ${crop.area} Hektar
Tanggal Tanam: ${crop.plantDate}, Estimasi Panen: ${crop.harvestDate}
Fase Saat Ini: ${crop.phase}
Stok Pupuk Gudang Poktan Saat Ini: ${JSON.stringify(fertilizersData)}`;

    try {
        const { text } = await callGeminiAPI({ userPrompt, systemPrompt, responseSchema: schema });
        const data = JSON.parse(text);

        bodyEl.innerHTML = `
            <div class="bg-agro-50 border border-agro-200 rounded-xl p-4">
                <div class="text-xs font-bold uppercase text-agro-800 mb-1">Evaluasi Fase Pertumbuhan (${crop.phase})</div>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">${data.ringkasanFase}</p>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-2.5">Rekomendasi Takaran Pupuk (Untuk ${crop.area} Ha)</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    ${(data.rekomendasiPupuk || []).map(p => `
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-slate-900 text-sm">${p.namaPupuk}</span>
                                <span class="px-2 py-0.5 rounded-lg bg-agro-600 text-white font-bold text-xs">${p.totalKebutuhanKg} Kg</span>
                            </div>
                            <div class="text-xs text-slate-600 mb-1"><i class="fa-regular fa-clock mr-1"></i> ${p.waktuAplikasi}</div>
                            <div class="text-[11px] font-medium text-emerald-700">${p.ketersediaanGudang}</div>
                        </div>
                    `).join('')}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2 p-4 rounded-xl border border-amber-200 bg-amber-50/50">
                    <div class="text-xs font-bold uppercase text-amber-900 mb-2"><i class="fa-solid fa-shield-virus mr-1"></i> Mitigasi Hama & Penyakit</div>
                    <ul class="space-y-1 text-xs text-slate-700 list-disc ml-4">
                        ${(data.pencegahanHama || []).map(h => `<li>${h}</li>`).join('')}
                    </ul>
                </div>
                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/50 flex flex-col justify-center text-center">
                    <span class="text-xs font-semibold text-blue-800">Proyeksi Hasil Panen</span>
                    <span class="text-lg font-bold text-blue-900 mt-1">${data.estimasiHasilPanenTon}</span>
                    <span class="text-[11px] text-blue-600 mt-0.5">Luas ${crop.area} Ha</span>
                </div>
            </div>
        `;
    } catch (err) {
        bodyEl.innerHTML = `<div class="p-4 rounded-xl bg-red-50 text-red-700 text-xs">Gagal menghasilkan rekomendasi terstruktur. Silakan coba lagi.</div>`;
    }
}

// 3. Fertilizer e-RDKK Allocation Calculator AI
async function generateFertilizerAllocationAI() {
    const modal = document.getElementById('ai-result-modal');
    document.getElementById('ai-modal-title').textContent = 'Perhitungan Alokasi e-RDKK & Kecukupan Gudang AI';
    document.getElementById('ai-modal-subtitle').textContent = 'Evaluasi stok pupuk gudang terhadap seluruh lahan tanaman anggota aktif';
    const bodyEl = document.getElementById('ai-modal-body');

    bodyEl.innerHTML = `
        <div class="py-12 text-center space-y-3">
            <i class="fa-solid fa-flask-vial fa-bounce text-3xl text-agro-600"></i>
            <p class="font-semibold text-slate-700">Menganalisis kebutuhan pupuk seluruh blok lahan anggota...</p>
        </div>
    `;
    modal.classList.remove('hidden');

    const systemPrompt = "Anda adalah Analis e-RDKK Pupuk Bersubsidi Kementerian Pertanian. Hitung kecukupan stok pupuk di gudang Poktan dibandingkan dengan daftar komoditas tanaman aktif anggota. Berikan saran restock dan prioritas distribusi.";
    const userPrompt = `Berikut adalah data stok pupuk gudang dan daftar lahan tanaman aktif di Poktan kami:\n${getPoktanContextSummary()}\n\nBuat rincian evaluasi kecukupan stok, potensi kekurangan (defisit), dan rekomendasi kuota tebus per anggota.`;

    try {
        const { text } = await callGeminiAPI({ userPrompt, systemPrompt });
        bodyEl.innerHTML = `<div class="leading-relaxed space-y-2">${formatMarkdownToHTML(text)}</div>`;
    } catch (err) {
        bodyEl.innerHTML = `<div class="p-4 rounded-xl bg-red-50 text-red-700 text-xs">Gagal memuat perhitungan e-RDKK AI.</div>`;
    }
}

// 4. Financial & Cashflow Audit AI
async function generateFinancialAuditAI() {
    const modal = document.getElementById('ai-result-modal');
    document.getElementById('ai-modal-title').textContent = 'Audit Arus Kas & Proyeksi Keuangan Poktan AI';
    document.getElementById('ai-modal-subtitle').textContent = 'Analisis pemasukan tebus pupuk, kontribusi panen, dan pengeluaran belanja gudang';
    const bodyEl = document.getElementById('ai-modal-body');

    bodyEl.innerHTML = `
        <div class="py-12 text-center space-y-3">
            <i class="fa-solid fa-scale-balanced fa-bounce text-3xl text-emerald-600"></i>
            <p class="font-semibold text-slate-700">Gemini AI sedang mengaudit riwayat transaksi & rasio kas kelompok...</p>
        </div>
    `;
    modal.classList.remove('hidden');

    const systemPrompt = "Anda adalah Auditor Keuangan Koperasi & Kelompok Tani (Poktan). Analisis arus kas masuk dan keluar kelompok tani, evaluasi kesehatan saldo kas, dan berikan 3 saran pengelolaan modal untuk musim tanam berikutnya.";
    const userPrompt = `Analisis data transaksi dan saldo kas Kelompok Tani berikut:\n${getPoktanContextSummary()}`;

    try {
        const { text } = await callGeminiAPI({ userPrompt, systemPrompt });
        bodyEl.innerHTML = `<div class="leading-relaxed space-y-2">${formatMarkdownToHTML(text)}</div>`;
    } catch (err) {
        bodyEl.innerHTML = `<div class="p-4 rounded-xl bg-red-50 text-red-700 text-xs">Gagal memuat audit keuangan AI.</div>`;
    }
}

// 5. Interactive Penyuluh AI Chat
function sendQuickPrompt(promptText) {
    document.getElementById('ai-use-search').checked = false;
    document.getElementById('ai-chat-input').value = promptText;
    document.getElementById('ai-chat-submit-btn').click();
}

function sendQuickPromptWithSearch(promptText) {
    document.getElementById('ai-use-search').checked = true;
    document.getElementById('ai-chat-input').value = promptText;
    document.getElementById('ai-chat-submit-btn').click();
}

function clearAIChat() {
    const container = document.getElementById('ai-chat-messages');
    container.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-agro-600 text-white flex items-center justify-center shrink-0 text-xs shadow-xs">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl rounded-tl-none p-4 text-xs sm:text-sm text-slate-700 shadow-2xs max-w-2xl">
                Riwayat percakapan telah dibersihkan. Silakan ajukan pertanyaan baru seputar pengelolaan Poktan Harapan Makmur.
            </div>
        </div>
    `;
}

async function handleAIChatSubmit(e) {
    e.preventDefault();
    const inputEl = document.getElementById('ai-chat-input');
    const submitBtn = document.getElementById('ai-chat-submit-btn');
    const useSearch = document.getElementById('ai-use-search').checked;
    const container = document.getElementById('ai-chat-messages');

    const query = inputEl.value.trim();
    if (!query) return;

    // Append User Bubble
    const userBubble = document.createElement('div');
    userBubble.className = 'flex items-start justify-end gap-3 fade-in';
    userBubble.innerHTML = `
        <div class="bg-agro-600 text-white rounded-2xl rounded-tr-none px-4 py-3 text-xs sm:text-sm shadow-xs max-w-xl leading-relaxed">
            ${query.replace(/</g, '&lt;')}
        </div>
    `;
    container.appendChild(userBubble);
    inputEl.value = '';

    // Append Loading Bubble
    const loadingBubble = document.createElement('div');
    loadingBubble.className = 'flex items-start gap-3 fade-in';
    loadingBubble.innerHTML = `
        <div class="w-8 h-8 rounded-xl bg-agro-600 text-white flex items-center justify-center shrink-0 text-xs">
            <i class="fa-solid fa-spinner fa-spin"></i>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl rounded-tl-none px-4 py-3 text-xs text-slate-500">
            Penyuluh AI sedang menyusun jawaban${useSearch ? ' & mencari referensi web terkini' : ''}...
        </div>
    `;
    container.appendChild(loadingBubble);
    container.scrollTop = container.scrollHeight;

    submitBtn.disabled = true;

    const systemPrompt = `Anda adalah Penyuluh Pertanian Lapangan (PPL) AI dan Analis Administrasi untuk Kelompok Tani "Poktan Harapan Makmur".
Gunakan Bahasa Indonesia yang ramah, terstruktur, dan praktis bagi petani maupun pengurus.
Berikut adalah data real-time Kelompok Tani saat ini yang harus Anda jadikan acuan utama:
${getPoktanContextSummary()}`;

    try {
        const { text, sources } = await callGeminiAPI({
            userPrompt: query,
            systemPrompt,
            useSearch
        });

        let sourcesHTML = '';
        if (sources && sources.length > 0) {
            sourcesHTML = `
                <div class="mt-3 pt-2.5 border-t border-slate-100">
                    <span class="text-[11px] font-semibold text-slate-500 block mb-1.5"><i class="fa-brands fa-google text-blue-600 mr-1"></i> Sumber Referensi Web:</span>
                    <div class="flex flex-wrap gap-1.5">
                        ${sources.slice(0, 4).map(s => `
                            <a href="${s.uri}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-[11px] font-medium transition">
                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                <span class="truncate max-w-[180px]">${s.title}</span>
                            </a>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        loadingBubble.innerHTML = `
            <div class="w-8 h-8 rounded-xl bg-agro-600 text-white flex items-center justify-center shrink-0 text-xs shadow-xs">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl rounded-tl-none p-4 text-xs sm:text-sm text-slate-700 shadow-2xs max-w-2xl leading-relaxed">
                <div>${formatMarkdownToHTML(text)}</div>
                ${sourcesHTML}
            </div>
        `;
    } catch (err) {
        loadingBubble.innerHTML = `
            <div class="w-8 h-8 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0 text-xs">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-2xl rounded-tl-none p-3.5 text-xs text-red-700">
                Maaf, koneksi ke Penyuluh AI sedang sibuk. Silakan coba kirim ulang pertanyaan Anda.
            </div>
        `;
    } finally {
        submitBtn.disabled = false;
        container.scrollTop = container.scrollHeight;
    }
}

function copyAIModalText() {
    const bodyText = document.getElementById('ai-modal-body').innerText;
    const tempInput = document.createElement('textarea');
    tempInput.value = bodyText;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    showToast('Hasil analisis AI berhasil disalin ke clipboard!', 'success');
}

// --- COOL EFFECT 4: PARTICLE CANVAS BACKGROUND ON HERO ---
function initHeroParticles() {
    const canvas = document.getElementById('hero-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let W, H, particles = [];

    function resize() {
        if (!canvas.parentElement) return;
        W = canvas.width = canvas.parentElement.offsetWidth;
        H = canvas.height = canvas.parentElement.offsetHeight;
    }

    const COLORS = [
        'rgba(134, 239, 172, ', // agro-300
        'rgba(74, 222, 128, ',  // agro-400
        'rgba(34, 197, 94, ',   // agro-500
        'rgba(250, 204, 21, '   // gold amber
    ];

    function createParticle() {
        const col = COLORS[Math.floor(Math.random() * COLORS.length)];
        return {
            x: Math.random() * W,
            y: Math.random() * H,
            r: Math.random() * 2 + 0.5,
            vx: (Math.random() - 0.5) * 0.35,
            vy: -Math.random() * 0.45 - 0.1, // gently float upward like spores
            base: col,
            alpha: Math.random() * 0.5 + 0.15
        };
    }

    function init() {
        resize();
        particles = Array.from({ length: 45 }, createParticle);
    }

    function draw() {
        ctx.clearRect(0, 0, W, H);
        particles.forEach(p => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = p.base + p.alpha + ')';
            ctx.fill();

            p.x += p.vx;
            p.y += p.vy;

            if (p.y < -10) p.y = H + 10;
            if (p.x < -10) p.x = W + 10;
            if (p.x > W + 10) p.x = -10;
        });
        requestAnimationFrame(draw);
    }

    window.addEventListener('resize', resize);
    init();
    draw();
}

// --- COOL EFFECT 5: 3D CARD TILT PARALLAX ---
function init3DCardTilt() {
    const card = document.querySelector('.auth-card');
    if (!card || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const cx = rect.width / 2;
        const cy = rect.height / 2;
        const dx = (x - cx) / cx;
        const dy = (y - cy) / cy;

        card.style.transition = 'transform 0.08s ease-out, box-shadow 0.2s ease';
        card.style.transform = `perspective(1000px) rotateY(${dx * 4}deg) rotateX(${-dy * 4}deg) translateY(-2px)`;
        card.style.boxShadow = `0 28px 60px -10px rgba(22, 163, 74, 0.2), 0 10px 25px -5px rgba(0, 0, 0, 0.06)`;
    });

    card.addEventListener('mouseleave', () => {
        card.style.transition = 'transform 0.6s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.6s ease';
        card.style.transform = 'perspective(1000px) rotateY(0deg) rotateX(0deg) translateY(0)';
        card.style.boxShadow = 'var(--shadow-card)';
    });
}

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', () => {
    initHeroParticles();
    init3DCardTilt();
    refreshAllViews();
});
