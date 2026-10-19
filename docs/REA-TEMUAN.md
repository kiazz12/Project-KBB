# REA — Register Temuan
## Project KBB (Reverse Engineering Analysis)

| | |
|---|---|
| **Objek** | `https://github.com/kiazz12/Project-KBB.git` (commit `f6d491b`) |
| **Jumlah temuan** | 30 — Keamanan (S) 9 · Fungsional (B) 9 · Arsitektur (D) 12 |
| **Status** | Perbaikan prioritas tinggi dilakukan pada sesi REA ini (19–20 Okt 2026) — rincian di [REA-LOG-PERBAIKAN.md](REA-LOG-PERBAIKAN.md) |
| **Dokumen terkait** | [REA-LAPORAN-ANALISIS.md](REA-LAPORAN-ANALISIS.md) · [REA-DIAGRAM.md](REA-DIAGRAM.md) |

**Legenda status:** ✅ Diperbaiki · 🟡 Sebagian (diperbaiki sebagian, sisa jadi rekomendasi) · ⏳ Belum (didokumentasikan, jadi rekomendasi) · 🔴 Kritis / 🟠 Tinggi / 🟡 Sedang / 🔵 Rendah (severity)

**Catatan verifikasi:** PHP & Composer tidak tersedia di mesin analisis, sehingga seluruh perbaikan diverifikasi lewat **analisis statis** (pencocokan tipe, alur, dan relasi terhadap kode sumber + migrasi). Pengujian otomatis (`php artisan test`) belum dijalankan dan wajib dijalankan di lingkungan ber-PHP sebelum rilis.

---

## A. Keamanan (S-01 … S-09)

| ID | Severity | Temuan | Lokasi | Status |
|---|---|---|---|---|
| **S-01** | 🔴 Kritis | **IDOR detail submission** — `GET` detail submission hanya mengesakan izin atas *form*, tidak memeriksa bahwa submission milik form tersebut; submission form lain bisa dibaca dengan menebak ID | `SubmissionApiController::show`, `PageController::submissionsShow` | ✅ |
| **S-02** | 🔴 Kritis | **IDOR hapus submission** — pola sama pada penghapusan; ditambah `destroy` API memakai `authorize('update')` bukan `delete` | `SubmissionApiController::destroy` | ✅ |
| **S-03** | 🟠 Tinggi | **`require_auth` lolos di jalur web** — hanya REST API yang menegakkan; halaman publik `/form/{slug}` (jalur utama) menerima submit tamu | `Livewire\PublicForm` | ✅ |
| **S-04** | 🟠 Tinggi | **`limit_one_response` lolos di jalur web** — cek "satu jawaban per user" hanya ada di API | `Livewire\PublicForm` | ✅ |
| **S-05** | 🟠 Tinggi | **`starts_at` tidak pernah dicek** — form terjadwal sudah bisa diisi sebelum jadwal buka (baik mount maupun submit) | `PublicForm::mount/submitForm`, API `store/showPublic` | ✅ |
| **S-06** | 🟠 Tinggi | **Izin hapus terlalu longgar** — hapus submission (tunggal & massal) hanya mengecek `view`, tidak sesuai matriks otorisasi | `PageController::deleteSubmission`, `bulkDeleteSubmissions` | ✅ |
| **S-07** | 🟠 Tinggi | **Login web tanpa rate limit** — `POST /login` (web & panel admin) tanpa throttle; brute-force hanya dibatasi di API | `routes/web.php`, `routes/admin.php` | ✅ |
| **S-08** | 🟡 Sedang | **`opd_id` tak tervalidasi** — field FK diterima apa adanya pada buat/update user (nilai tak ada di `opds` → error DB / data korup) | `API\UserController::store/update` | ✅ |
| **S-09** | 🟡 Sedang | **CSV/formula injection** — nilai peserta ditulis mentah ke CSV; sel diawali `=`, `+`, `-`, `@` dieksekusi lembar kerja (OWASP CSV Injection) | 4 titik ekspor CSV (web, API, `FormService`, `ReportingService`) | ✅ |

## B. Cacat Fungsional (B-01 … B-09)

| ID | Severity | Temuan | Lokasi | Status |
|---|---|---|---|---|
| **B-01** | 🔴 Kritis | **`AuditService::log` TypeError** — argumen ke-2 historis berupa array; `get_class(array)` memicu `TypeError` (HTTP 500) → aksi (mis. hapus submission) gagal tercatat/gagal bersih | `App\Services\AuditService` | ✅ |
| **B-02** | 🟡 Sedang | **Auto-save pengaturan tanpa guardrail** — `wire:poll.30s` bisa menyimpan judul kosong, atau `ends_at ≤ starts_at` (form langsung terkunci/rusak) | `FormEditor::autoSaveSettings` | ✅ |
| **B-03** | 🟡 Sedang | **Tipe `computed` ditolak API + `conditions` tak di-cast** — daftar `in:` pada `FieldController` tidak memuat `computed`; `conditions` (JSON) tidak di-cast `array` pada `FormField` → logika bersyarat tidak konsisten | `API\FieldController`, `Models\FormField` | ✅ |
| **B-04** | 🟠 Tinggi | **Duplikasi form merusak relasi section** — field ikut disalin menunjuk `section_id` milik **form lama** (duplikat salah tampil / kebocoran silang antar form) | 4 lokasi: `PageController`, `FormCrudController`, `FormService`, `FormManagementService` | ✅ |
| **B-05** | 🟡 Sedang | **Kode mati rusak: `App\Services\SubmissionService`** — `with('data.field')` (relasi `field` tidak ada; aslinya `formField`) & perbandingan `$field->type === 'checkbox'` (enum vs string, selalu salah) | `app/Services/SubmissionService.php` | ✅ |
| **B-06** | 🟡 Sedang | **Kode mati rusak: `ReportingService`** — `$user->role === 'super_admin'` (enum vs string, selalu salah), `published_at` tidak ada kolomnya, tanpa scope `published`, `diffInDays` bisa negatif | `Domains/Reporting/Services/ReportingService.php` | ✅ |
| **B-07** | 🟡 Sedang | **Kode mati rusak: `FormManagementService`** — menulis kolom `config` yang tidak ada di tabel `form_fields` (seharusnya `options`) | `Domains/InternalForms/.../FormManagementService.php` | ✅ |
| **B-08** | 🔵 Rendah | **Kode mati rusak: `PublicFormService`** — `submitForm` tidak menyimpan `user_id`/`ip_address`/`user_agent` (provenance hilang bila diaktifkan); `validateSubmission` memakai `empty()` sehingga nilai `"0"` dianggap kosong | `Domains/PublicForms/Services/PublicFormService.php` | ⏳ |
| **B-09** | 🟠 Tinggi | **Field admin-only bocor di API publik** — `showPublic` mengembalikan seluruh definisi field termasuk `is_admin_only`; `store` menerima input untuk field admin-only | `API\SubmissionApiController` | ✅ |

> **B-08** sengaja tidak diperbaiki: kelas ini **tidak direferensikan controller mana pun** (kode mati). Perbaikannya hanya relevan jika kelas diaktifkan kembali — lihat rekomendasi D-05.

## C. Arsitektur / Hutang Teknis (D-01 … D-12)

| ID | Severity | Temuan | Status | Keterangan |
|---|---|---|---|---|
| **D-01** | 🟠 Tinggi | **Idle-timeout 5 menit tidak pernah aktif** — `wire:poll.30s` `SessionTimeout` memicu request yang me-refresh `last_activity_at` lewat middleware → "tidak aktif" tak pernah terdeteksi selama tab terbuka | ✅ | Middleware kini hanya refresh pada GET non-Livewire; ada `recordActivity()` + event JS throttled (mousemove/keydown/scroll/touchstart, ≥20 dtk) |
| **D-02** | 🟡 Sedang | **Duplikasi logika ekspor CSV di 4 tempat** | 🟡 | Sanitasi (F-07) disinkronkan ke 4 titik; konsolidasi ke satu service tetap rekomendasi |
| **D-03** | 🟡 Sedang | **Duplikasi logika duplikasi form di 4 tempat** | 🟡 | Semua titik kini remap section dengan benar; konsolidasi ke satu service tetap rekomendasi |
| **D-04** | 🟡 Sedang | **Duplikasi logika analitik form** (`FormCrudController`, `FormService`, `ReportingService`) | ⏳ | Perbaikan gabung ke rekomendasi "satu service layer" |
| **D-05** | 🟡 Sedang | **7 service `app/Domains/**` adalah kode mati** — tidak direferensikan controller mana pun; logika bisnis terduplikasi di controller | 🟡 | Bug di dalamnya diperbaiki (B-06, B-07, sebagian B-04) agar aman bila diaktifkan; keputusan integrasi/pembuangan = rekomendasi |
| **D-06** | 🟡 Sedang | **`App\Services\FormService` & `SubmissionService` juga tak terpakai** oleh controller | 🟡 | Bug diperbaiki (B-05, bagian B-04); integrasi = rekomendasi |
| **D-07** | 🟡 Sedang | **Dokumentasi tidak akurat** — README/INDEX menyebut React 19, bidang "rating/matriks" (tak ada), jumlah test & migrasi keliru, `start.bat` merujuk `frontend/` yang tak ada, `.env.example` hilang | ✅ | README dikoreksi (14 jenis bidang, 13 test, 27 migrasi, tabel dokumentasi REA); `start.bat` menjadi launcher backend yang valid |
| **D-08** | 🟡 Sedang | **`.env.example` tidak ada** — `composer setup` gagal di langkah salin `.env`; onboarding rusak | ✅ | `backend/.env.example` dibuat (SQLite default + MySQL terkomentari, sesuai `config/*`) |
| **D-09** | 🟠 Tinggi | **Matriks otorisasi OPD tidak terimplementasi** — `opd_id` tersimpan di DB tetapi tak pernah dipakai menyaring akses; `FormPolicy` hanya `super_admin` vs pemilik; `AuthorizationService::applyFormAccessConstraints()` belum terhubung | ⏳ | Butuh perencanaan + uji regresi bertahap (rekomendasi #1) |
| **D-10** | 🟡 Sedang | **Pengujian rendah** — 7 berkas uji; nol uji untuk otorisasi form, guardrail publik, duplikasi, audit, ekspor | 🟡 | 5 berkas uji baru ditambahkan (13 total): ownership/IDOR, guardrail publik, duplikasi, AuditService, Csv::sanitize; cakupan ekspor & policy masih perlu diperluas |
| **D-11** | 🟡 Sedang | **Kontrol keamanan pasif belum aktif** — `SESSION_ENCRYPT` default `false`, `shouldMaskInLogs()` tak dipakai, retensi data `DataClassification::retentionDays()` tanpa scheduler pembersih | ⏳ | Rekomendasi #4 & #5 |
| **D-12** | 🟡 Sedang | **Indeks performa DB belum ada** — query volume besar tanpa indeks pendukung: `form_submissions(form_id, submitted_at)`, `(user_id)`, `submission_data(submission_id)/(form_field_id)`, `audit_logs(user_id, created_at)/(action)/(auditable)`, `user_sessions(user_id, logged_out_at)`, `participants(nama)` | ✅ | Migrasi `2026_10_19_000001_add_rea_performance_indexes` (idempoten via `hasIndex`) |

---

## Ringkasan status

| Status | Jumlah | Temuan |
|---|---:|---|
| ✅ Diperbaiki | **22** | S-01…S-09, B-01…B-04, B-05…B-07, B-09, D-01, D-07, D-08, D-12 |
| 🟡 Sebagian | **5** | D-02, D-03, D-05, D-06, D-10 |
| ⏳ Belum (rekomendasi) | **3** | B-08, D-09, D-11 |

### Prioritas tindak lanjut

1. **D-09** — implementasikan matriks OPD (fitur inti dokumen yang belum jalan).
2. **Verifikasi runtime** — jalankan `php artisan test` (13 berkas) + `vendor/bin/pint` di lingkungan ber-PHP.
3. **D-05/D-06** — putuskan: aktifkan service domain sebagai satu-satunya lokasi logika bisnis, atau buang; jangan biarkan dua sumber kebenaran.
4. **D-10** — tambah uji untuk `FormPolicy`, ekspor, dan rate limit.
5. **D-11** — aktifkan `SESSION_ENCRYPT=true`, masking log, dan scheduler retensi data.

---

*Register ini adalah dokumen hidup — perbarui kolom Status setiap kali temuan ditangani. Log perbaikan: [REA-LOG-PERBAIKAN.md](REA-LOG-PERBAIKAN.md).*
