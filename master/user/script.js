// Modal Controller Functions
function openModalTambah() {
    const modal = document.getElementById('modalTambahUser');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => {
            const nikInput = document.getElementById('add-nik');
            if (nikInput) nikInput.focus();
        }, 150);
    }
}

function openModalEdit(userData) {
    if (!userData) return;
    document.getElementById('edit-id').value       = userData.id;
    document.getElementById('edit-nik').value      = userData.nik;
    document.getElementById('edit-username').value = userData.username;
    document.getElementById('edit-nama').value     = userData.nama;
    document.getElementById('edit-password').value = '';

    const modal = document.getElementById('modalEditUser');
    if (modal) modal.classList.add('show');
}

function openModalDelete(id, nama, nik) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name-display').innerText = nama;
    document.getElementById('delete-nik-display').innerText = nik;

    const modal = document.getElementById('modalDeleteUser');
    if (modal) modal.classList.add('show');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('show');
}

// Close modals when clicking backdrop
document.querySelectorAll('.custom-modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) {
            backdrop.classList.remove('show');
        }
    });
});

// ESC key closes any open modal
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.custom-modal-backdrop.show').forEach(m => m.classList.remove('show'));
    }
});

// Toggle Password Field Eye
function togglePassField(fieldId, eyeId) {
    const field = document.getElementById(fieldId);
    const eye   = document.getElementById(eyeId);
    if (!field || !eye) return;

    if (field.type === 'password') {
        field.type = 'text';
        eye.className = 'fa-regular fa-eye-slash';
    } else {
        field.type = 'password';
        eye.className = 'fa-regular fa-eye';
    }
}

// Live Search Filter for Users Table
function filterUserTable() {
    const query = document.getElementById('user-search-input').value.toLowerCase();
    const rows  = document.querySelectorAll('.user-row-item');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (text.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Otomatis ubah input Nama menjadi Huruf Depan Kapital dan sisanya kecil (Title Case)
function toTitleCaseLive(str) {
    return str.toLowerCase().replace(/(?:^|\s)\S/g, (match) => match.toUpperCase());
}

document.querySelectorAll('#add-nama, #edit-nama').forEach(inp => {
    inp.addEventListener('input', () => {
        const start = inp.selectionStart;
        const end   = inp.selectionEnd;
        inp.value   = toTitleCaseLive(inp.value);
        if (start !== null && end !== null) {
            inp.setSelectionRange(start, end);
        }
    });
});

/**
 * Global Top-Right Toast Notification Function
 * @param {string} title - Judul notifikasi
 * @param {string} message - Isi pesan notifikasi
 * @param {string} type - 'success' | 'danger'
 * @param {number} durationMs - Durasi tampil dalam ms (default: 1000ms / 1 detik)
 */
function showToast(title, message, type = 'success', durationMs = 1000) {
    let portal = document.getElementById('toast-portal');
    if (!portal) {
        portal = document.createElement('div');
        portal.id = 'toast-portal';
        portal.className = 'toast-portal-topright';
        document.body.appendChild(portal);
    }

    const toast = document.createElement('div');
    toast.className = `toast-item-card toast-${type}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('title', 'Klik notifikasi untuk menutup seketika');
    
    const iconClass = (type === 'success') ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';

    toast.innerHTML = `
        <div class="toast-card-inner">
            <div class="toast-icon-wrapper">
                <i class="${iconClass}"></i>
            </div>
            <div class="toast-content-wrapper">
                <div class="toast-title-text">${title}</div>
                <div class="toast-body-text">${message}</div>
            </div>
            <button type="button" class="toast-close-btn" aria-label="Tutup notifikasi">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="toast-progress-container">
            <div class="toast-progress-bar" style="animation-duration: ${durationMs}ms;"></div>
        </div>
    `;

    portal.appendChild(toast);

    let isDismissed = false;
    function dismiss() {
        if (isDismissed) return;
        isDismissed = true;
        toast.classList.add('toast-dismissing');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 320);
    }

    // Hilang otomatis setelah durasi 1 detik (1000ms)
    const autoTimer = setTimeout(dismiss, durationMs);

    // Notifikasi jika ditekan / diklik maka langsung hilang seketika
    toast.addEventListener('click', () => {
        clearTimeout(autoTimer);
        dismiss();
    });
}

// Panggil toast notifikasi saat halaman dimuat (konfigurasi dari views/toast-config.php)
if (window.USER_TOAST) {
    document.addEventListener('DOMContentLoaded', () => {
        showToast(
            window.USER_TOAST.title,
            window.USER_TOAST.message,
            window.USER_TOAST.type,
            1000
        );

        // Bersihkan parameter query status dari URL browser tanpa reload
        if (window.history.replaceState) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        }
    });
}
