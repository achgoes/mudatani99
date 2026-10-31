---
name: focused-coding
description: Fokus pada coding, perbaikan, dan debugging sesuai perintah spesifik pengguna tanpa pengecekan berlebih pada environment (WSL, versi PHP, OS, dll) atau file di luar cakupan yang diminta.
---

# Focused Coding & Scope Guard

Skill ini memastikan agen bekerja secara presisi, fokus pada kode yang ditargetkan, dan tidak melakukan inspeksi berlebih pada sistem atau lingkungan kerja.

## Prinsip Kerja

1. **Fokus pada Kode Target (Strict Scope)**
   - Periksa, baca, dan ubah hanya file, fungsi, atau baris kode yang secara spesifik dipertanyakan atau diminta oleh pengguna.
   - Jangan menyusuri atau memeriksa file-file lain di dalam repositori jika tidak berhubungan langsung dengan perintah pengguna.

2. **Tanpa Pengecekan Lingkungan/Sistem Berlebih (No Over-Checking)**
   - Jangan melakukan pengecekan status WSL, versi PHP, konfigurasi web server (Apache/Nginx), ekstensi runtime, atau OS kecuali jika pengguna secara eksplisit memintanya.
   - Jangan menjalankan perintah shell/terminal diagnostik lingkungan yang tidak relevan dengan logika kode yang sedang dikerjakan.

3. **Perbaikan Presisi Sesuai Perintah (Direct Execution)**
   - Lakukan perbaikan atau implementasi kode tepat sesuai instruksi pengguna.
   - Hindari over-engineering, refactoring massal yang tidak diminta, atau menambah dependensi/abstraksi baru tanpa persetujuan.
   - Pertahankan format, indentasi, konvensi, dan komentar yang sudah ada pada kode target.

4. **Pemeriksaan & Validasi Efisien**
   - Jika diperlukan validasi kode, batasi peninjauan hanya pada alur logika kode yang diperbarui atau ditanyakan.
   - Berikan jawaban yang ringkas, lugas, dan langsung pada pokok perbaikan/kode yang dimaksud.

