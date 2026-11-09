<!-- ========================================================
     MODAL 2: EDIT USER
======================================================== -->
<div id="modalEditUser" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h4>
                <i class="fa-solid fa-pen-to-square text-agro-600"></i>
                Edit Data Pengguna
            </h4>
            <button type="button" class="btn-modal-close" onclick="closeModal('modalEditUser')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= appUrl('master/user/') ?>" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-id" name="id" value="">
            
            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-label" for="edit-nik">NIK Pengguna (4 Digit) <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="edit-nik" 
                        name="nik" 
                        required 
                        maxlength="4" 
                        pattern="[0-9]{4}" 
                        inputmode="numeric" 
                        class="modal-input font-mono" 
                    >
                    <p class="modal-helper-text">Format 4 digit angka dimulai dari 0001.</p>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-username">Username <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="edit-username" 
                        name="username" 
                        required 
                        class="modal-input" 
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-nama">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="edit-nama" 
                        name="nama" 
                        required 
                        class="modal-input" 
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-label" for="edit-password">Kata Sandi Baru (Opsional)</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="edit-password" 
                            name="password" 
                            class="modal-input pr-10" 
                            placeholder="Kosongkan jika tidak ingin mengubah kata sandi"
                        >
                        <button type="button" onclick="togglePassField('edit-password', 'edit-eye')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="edit-eye" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <p class="modal-helper-text text-amber-600">
                        <i class="fa-solid fa-circle-info text-xs"></i> Hanya isi field ini jika ingin mengganti kata sandi.
                    </p>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditUser')">Batal</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-check mr-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
