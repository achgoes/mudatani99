<!-- ========================================================
     MODAL 1: TAMBAH USER BARU
======================================================== -->
<div id="modalTambahUser" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-user-plus text-agro-600"></i>
                Tambah Pengguna Baru
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalTambahUser')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('master/user/') ?>" method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="add-nik">NIK Pengguna (4 Digit Angka) <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="add-nik" 
                        name="nik" 
                        required 
                        maxlength="4" 
                        pattern="[0-9]{4}" 
                        inputmode="numeric" 
                        class="modal-input font-mono" 
                        placeholder="Contoh: <?= $nextSuggestedNik ?>"
                        value="<?= $nextSuggestedNik ?>"
                    >
                    <p class="modal-helper-text">Format 4 digit angka, misal: 0001, 0002, dst.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-username">Username <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="add-username" 
                        name="username" 
                        required 
                        class="modal-input" 
                        placeholder="Contoh: ahmad_makmur"
                        autocomplete="off"
                    >
                    <p class="modal-helper-text">Digunakan untuk login ke sistem, huruf kecil & tanpa spasi.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-nama">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="add-nama" 
                        name="nama" 
                        required 
                        class="modal-input" 
                        placeholder="Contoh: Ahmad Dahlan"
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="add-password">Kata Sandi (Password) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="add-password" 
                            name="password" 
                            required 
                            class="modal-input pr-10" 
                            placeholder="Minimal 6 karakter"
                        >
                        <button type="button" onclick="togglePassField('add-password', 'add-eye')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="add-eye" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahUser')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan User
                </button>
            </div>
        </form>
    </div>
</div>
