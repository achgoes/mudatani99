# Konfigurasi Skill Otomatis (Always-On)

Semua skill berikut wajib digunakan secara otomatis pada setiap permintaan tanpa perlu dipanggil secara manual:

1. **Bahasa Indonesia (Selalu Aktif)**:
   - Panduan: `D:/My_Project/skills-ai/bahasa-indonesia/SKILL.md` (atau `.agent/skills/bahasa-indonesia/SKILL.md`)
   - Gunakan bahasa Indonesia sesuai konfigurasi berikut untuk seluruh jawaban dan komunikasi prosa:
```yaml
bahasa_indonesia:
  language: indonesia
  variety: auto
  regional_voice: netral
  register: profesional
  intensity: tipis
  speech_level: auto
  prose_style: lugas
  poetic_intensity: tipis
  technical_terms: repo-natural
  self_reference: auto
  addressee_reference: auto
  orthography: auto
```

2. **Analytics (Otomatis saat Pelacakan & Metrik)**:
   - Panduan: `D:/My_Project/skills-ai/analytics/SKILL.md` (atau `.agent/skills/analytics/SKILL.md`)
   - Otomatis aktif setiap kali ada kebutuhan terkait GA4, GTM, konversi, metrik, atau tracking plan.

3. **Copywriting (Otomatis saat Penulisan / Perbaikan Teks)**:
   - Panduan: `D:/My_Project/skills-ai/copywriting/SKILL.md` (atau `.agent/skills/copywriting/SKILL.md`)
   - Otomatis aktif saat menulis, memperbaiki, atau menyusun teks marketing, headline, CTA, pesan konversi, dan kejelasan deskripsi produk.

4. **Focused Coding & Scope Guard (Selalu Aktif saat Coding, Debugging & Perbaikan)**:
   - Panduan: `D:/My_Project/skills-ai/focused-coding/SKILL.md` (atau `.agent/skills/focused-coding/SKILL.md`)
   - Fokus hanya pada kode yang ditanyakan atau diminta untuk diperbaiki/diperbarui sesuai perintah pengguna.
   - Jika butuh cek kode, cek HANYA pada kode atau file yang dipertanyakan atau diperbarui sesuai perintah.
   - Tidak perlu memeriksa WSL, versi PHP, status OS, environment, atau file di luar cakupan kecuali jika diminta secara eksplisit oleh pengguna.

<!-- antislop:start -->
## antislop (Selalu Aktif Mode: DURING)
For UI, copy, people, mobile layout, or code comments work, read `D:/My_Project/skills-ai/antislop/SKILL.md` (core) and then the skill for the task:
- Copy & text: `D:/My_Project/skills-ai/antislop-copywriting/SKILL.md`
Before starting, follow the core's "Two Usage Modes" section in strict order: explicit session instruction first, then global preference, then ask. A session instruction always wins. For a resolved mode, say `antislop active: <mode> (session override).` or `antislop active: <mode> (global preference).` once before presenting findings or making edits, using the actual mode and source. Acknowledging the user's request without naming the source does not replace this notice.
Only an explicit choice of antislop during or after selects a session mode. A request to review, audit, or avoid file edits does not select a mode; read the global preference in that case. Another skill's mode does not select antislop's mode.
If the mode is unresolved, ask during/after and end the response; wait for the answer before any UI review, planning, or concept. For read-only tasks, put the active-mode notice only at the start of the final answer, never in progress messages. For editing tasks, announce before the first edit and omit it from the final answer.
To update antislop later: download `antislop.md` again, or run `npx antislop-ai --update` if it was installed as skill folders.
<!-- antislop:end -->
