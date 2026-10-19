# REA — Reverse Engineering Analysis
## Project KBB (Sistem Formulir Digital Kabupaten Bandung Barat)

| | |
|---|---|
| **Objek Analisis** | `https://github.com/kiazz12/Project-KBB.git` (commit `f6d491b`, branch `master`) |
| **Metode** | Pemulihan desain (design recovery) dari kode sumber, migrasi, rute, dan dokumen lama |
| **Ruang Lingkup** | Seluruh `backend/` (Laravel 13 + Livewire 4), 26 migrasi, 11 model, 10 controller, 8 service, 7 komponen Livewire, 4 berkas rute |
| **Tanggal** | 19 Oktober 2026 |
| **Dokumen terkait** | [REA-DIAGRAM.md](REA-DIAGRAM.md) · [REA-TEMUAN.md](REA-TEMUAN.md) · [REA-LOG-PERBAIKAN.md](REA-LOG-PERBAIKAN.md) |

---

## 1. Ringkasan Eksekutif

Project KBB adalah **modular monolith** Laravel yang berfungsi sebagai pembuat formulir daring setara Google Forms untuk 47 OPD Pemerintah Kabupaten Bandung Barat. Dari pemulihan arsitektur, sistem ini terbukti memiliki fondasi fitur yang lengkap (form builder multi-bidang, ekspor CSV/XLSX/PDF, audit trail, pembatasan sesi, klasifikasi data), tetapi **implementasinya tidak konsisten antar-jalur akses** (web Livewire vs REST API vs panel admin) dan mengandung beberapa cacat keamanan yang dapat dieksploitasi.

### Ringkasan temuan

| Kategori | Jumlah | Risiko Tertinggi |
|---|---:|---|
| Keamanan (S) | 9 | 🔴 Kritis — IDOR pada detail & penghapusan submission |
| Cacat fungsional (B) | 9 | 🟠 Tinggi — fitur pengaman form tidak dijalankan di jalur web |
| Arsitektur / hutang teknis (D) | 12 | 🟡 Sedang — duplikasi logika, kode mati rusak, kebocoran status sesi |
| **Total** | **30** | |

### Temuan paling berdampak

1. **🔴 IDOR submission (S-01, S-02)** — endpoint detail/hapus submission memverifikasi hak akses terhadap *form*, tetapi **tidak memverifikasi bahwa submission memang milik form tersebut**. Admin pemilik form lain dapat melihat/menghapus submission mana pun hanya dengan mengubah ID di URL.
2. **🔴 Bug fatal audit trail (B-01)** — `AuditService::log()` memanggil `get_class()` di atas array pada aksi *hapus submission* → memicu `TypeError` (HTTP 500) sehingga aksi penghapusan gagal tercatat (dan gagal dieksekusi bersih).
3. **🟠 `require_auth` & `limit_one_response` tidak dijalankan di jalur web (S-03, S-04)** — hanya REST API yang menegakkan kedua pengaturan ini. Melalui halaman publik `/form/{slug}` (jalur utama!), form "wajib login" dan "satu jawaban per orang" bisa dilewati.
4. **🟠 Jendela waktu form (`starts_at`) tidak pernah dicek (S-05)** — form yang dijadwalkan buka di masa depan bisa langsung diisi.
5. **🟠 Idle-timeout 5 menit tidak pernah aktif (D-01)** — `wire:poll` setiap 30 detik pada komponen `SessionTimeout` men-*refresh* `last_activity_at` melalui middleware, sehingga "tidak aktif" tidak pernah terdeteksi selama tab terbuka.
6. **🟠 Penggandaan form merusak relasi section (B-04)** — field yang menunjuk section ikut disalin menunjuk ke section **form lama** (kebocoran silang antar form).
7. **🟠 Pembatasan hapus tidak sesuai matriks otorisasi (S-06)** — penghapusan submission hanya memeriksa `view`, sehingga admin satu OPD (yang seharusnya ❌ sesuai matriks dokumen) dapat menghapus submission.

### Kesimpulan

Sistem layak dilanjutkan pengembangannya, tetapi **wajib melewati perbaikan temuan kritis/tinggi di `REA-TEMUAN.md` sebelum dipakai produksi**. Seluruh temuan prioritas tinggi telah diperbaiki dalam sesi REA ini (lihat [REA-LOG-PERBAIKAN.md](REA-LOG-PERBAIKAN.md)).

---

## 2. Metodologi REA

Analisis rekayasa mundur dilakukan dalam 5 tahap:

1. **Inventarisasi** — pemetaan seluruh berkas sumber, migrasi, rute, konfigurasi, dan dokumen lama.
2. **Pemulihan struktur** — rekonstruksi arsitektur bercabang (arsitektur kontainer, komponen, alur data).
3. **Pemulihan skema data** — penyusun ulang ERD penuh dari 26 migrasi (14 tabel aplikasi).
4. **Pemulihan perilaku** — telaah alur autentikasi, pengiriman formulir, ekspor, otorisasi, dan manajemen sesi baris per baris.
5. **Validasi silang & temuan** — pencocokan klaim dokumen lama (README/ARCHITECTURE/INDEX) terhadap kode aktual, lalu pengelompokan selisih sebagai temuan berseverity.

Diagram lengkap setiap tahap ada di [REA-DIAGRAM.md](REA-DIAGRAM.md) (konteks, arsitektur, ERD, sequence, state machine, decision tree).

---

## 3. Identifikasi Sistem

### 3.1 Profil teknologi (dari kode aktual)

| Lapisan | Teknologi | Bukti |
|---|---|---|
| Framework | Laravel `^13.8`, PHP `^8.3` | `composer.json` |
| Antarmuka | Livewire `^4.3` + Blade + Tailwind (CDN) | `app/Livewire/`, `resources/views/` |
| ORM | Eloquent | seluruh `app/Models/` |
| Auth API | Sanctum `^4.0` | `routes/api.php`, `config/sanctum.php` |
| Ekspor | Maatwebsite Excel, DOMPDF, FPDF | `app/Exports/`, `PageController` |
| Basis data | MySQL 8 (produksi) / SQLite in-memory (uji) | `config/database.php`, `phpunit.xml` |
| Antrean/backup | Database queue, `mysqldump` via `backup:run` | `routes/console.php`, `app/Console/Commands/BackupRun.php` |

> **Catatan REA:** dokumen lama (`docs/INDEX.md`) menyebut React 19 + Inertia SPA. Tidak ada satu pun berkas React/Inertia di repositori — frontend sesungguhnya Blade + Livewire. Berkas sisa (`pnpm-lock.yaml`, `tsconfig.json`, `start.bat` lama) adalah artefak scaffold React yang tidak pernah dipakai.

### 3.2 Tiga jalur akses paralel

| Jalur | Rute | Otorisasi | Kontroler |
|---|---|---|---|
| **Web (utama)** | `routes/web.php` — `/dashboard`, `/forms/*`, `/form/{slug}` | Session + `FormPolicy` | `PageController`, `WebAuthController`, Livewire |
| **API** | `routes/api.php` — `/v1/*` | Bearer token Sanctum + `FormPolicy` | `App\Http\Controllers\API\*` |
| **Panel Admin** | `routes/admin.php` — `/admin/*` | Session khusus `super_admin` | `App\Http\Controllers\Admin\*` |

### 3.3 Enam domain (`app/Domains/`) — status: kode mati

`Auth`, `PublicForms`, `InternalForms`, `Submissions`, `Reporting`, `Audit` berisi 7 service class yang **tidak direferensikan controller mana pun** (dikonfirmasi dengan pencarian referensi). Logika bisnis sesungguhnya terduplikasi di controller + `App\Services\*`. Beberapa service domain ini juga mengandung cacat (lihat B-05…B-08 di `REA-TEMUAN.md`).

### 3.4 Model otorisasi (pemulihan aktual vs dokumen)

Dokumen (ARCHITECTURE.md) menjanjikan matriks: admin hanya boleh *view* form/selesai OPD sendiri, dan *edit/hapus* hanya milik sendiri. **Implementasi aktual**: `FormPolicy` hanya membandingkan `super_admin` atau `user_id === form.user_id` — **konsep OPD tidak terimplementasi di jalur mana pun** (`opd_id` tersimpan di database tetapi tidak pernah dipakai untuk menyaring akses). Temuan D-09.

---

## 4. Arsitektur Pemulihan (ringkas)

> Diagram visual lengkap: [REA-DIAGRAM.md](REA-DIAGRAM.md)

- **Bentuk**: modular monolith satu unit deploy, logika bisnis tersebar di controller (bukan service).
- **Komponen utama**:
  - `PageController` (363 baris) — otak jalur web: dashboard, CRUD form, submission, 5 jenis ekspor.
  - Livewire: `FormEditor` (editor form multi-tab), `PublicForm` (render+submit publik multi-step), `SessionTimeout`, `NotificationBell`, `ActivityChart`, `CreateForm`, `DisplayNamePrompt`.
  - `App\Services\AuditService` — pencatat audit tunggal yang benar-benar dipakai (static).
  - `SessionLimitService` — membatasi sesi aktif (super_admin=1, admin=3) langsung ke tabel `sessions`.
- **Data**: 14 tabel aplikasi; form → sections → fields → submissions → data (skema "EAV" per jawaban).
- **Alur data publik**: `GET /form/{slug}` → `Livewire\PublicForm` → validasi per langkah (step/section) → `form_submissions` + `submission_data` → (untuk form presensi) pencocokan otomatis `participants`.

---

## 5. Analisis Keamanan (temuan utama)

### 5.1 Permukaan serangan

| Permukaan | Proteksi | Kondisi |
|---|---|---|
| Login web `/login` | `guest`, rate-limit API 5/mnt | ⚠️ login web **tanpa** rate limit (S-07) |
| Login API `/v1/auth/login` | `throttle:5,1` | ✅ |
| Submit publik API | `throttle:10,1` | ✅ (web Livewire mengandung honeypot) |
| Detail/hapus submission | `FormPolicy::view` | 🔴 IDOR lintas-form (S-01/S-02), izin hapus terlalu longgar (S-06) |
| File unggahan `/uploads/{path}` | wajib login + pemilik form/super_admin | ✅ (disaring via tabel `submission_data`) |
| Ekspor data | blokir klasifikasi `sensitive` | ✅ jalur web/API, ⚠️ tanpa mitigasi CSV-injection (S-09) |
| Data publik API form | — | ⚠️ field `is_admin_only` ikut terbocor (B-09) |

### 5.2 Kontrol keamanan yang berjalan dengan baik

- Hashing bcrypt, session regenerate saat login, CSRF (`preventRequestForgery`), cookie dibersihkan saat logout.
- Pembatasan jumlah sesi & token aktif per role (`SessionLimitService`).
- Kebocoran kredensial lewat pesan error generik; API memakai `ForceJsonResponse`.
- Enkripsi tidak wajib untuk sesi DB (nilai default Laravel) — rekomendasi mengaktifkan `SESSION_ENCRYPT=true` tercatat di rekomendasi.

### 5.3 Kebocoran status sesi (D-01, unik & penting)

Idle-timeout 5 menit dijual sebagai fitur keamanan, tetapi mekanismenya rusak diam-diam:

```
wire:poll.30s="checkSession"  →  POST /livewire/update
        ↓
TrackSessionActivity (web middleware)  →  Session::put('last_activity_at', now())
        ↓
checkSession membaca last_activity_at  →  elapsed ≈ 0 selamanya
```

Timeout hanya akan terjadi jika pengguna menutup tab (tidak ada request = tidak ada refresh, dan tidak ada pemeriksaan). Perbaikan: pencatatan aktivitas dipindah ke **peristiwa pengguna nyata** (mousemove/keydown/scroll, di-throttle 30 detik) dan middleware hanya me-refresh pada request GET (navigasi halaman). Lihat log perbaikan F-19.

---

## 6. Analisis Kualitas Kode

| Aspek | Penilaian | Keterangan |
|---|---|---|
| Konsistensi jalur | 🔴 Buruk | Web vs API berbeda perilaku pada fitur identik (validasi, otorisasi, guardrail) |
| Pemisahan tanggung jawab | 🟠 Sedang | Controller gemuk (PageController 363 baris, FormEditor 385 baris), service layer ada tetapi tidak dipakai |
| Kode mati | 🟠 Sedang | 7 service domain + `App\Services\FormService`/`SubmissionService` tidak direferensikan — dan sebagian rusak |
| Duplikasi | 🟠 Sedang | Logika ekspor CSV, duplikasi form, dan analytics diduplikasi 2–3× |
| Pengujian | 🔴 Rendah | 7 berkas uji; tidak ada uji untuk otorisasi form, ekspor, atau submission |
| Dokumentasi | 🟡 Sedang | Dokumen lama janjikan React/fitur (rating, matriks) yang tidak ada di kode |
| Skema DB | 🟡 Sedang | Indeks kunci untuk pola query besar belum ada (form_submissions, submission_data, audit_logs) |

---

## 7. Kecocokan Dokumentasi vs Kode

| Klaim dokumen | Kenyataan | Status |
|---|---|---|
| Frontend React 19 + Inertia | Blade + Livewire 4 | ❌ tidak ada |
| 13 jenis bidang termasuk "rating" & "matriks" | 14 jenis: …, `signature`, `computed` — **tanpa** rating/matriks | ❌ |
| "Proteksi double-submit" pada form publik | Hanya via API (`limit_one_response`), jalur web tanpa cek | ❌ sebagian (kini diperbaiki) |
| Batas sesi super_admin = 1, admin = 3 | Sesuai `SessionLimitService` | ✅ |
| Idle timeout 5 menit + peringatan 2 menit | Kode ada, tetapi mekanisme rusak (D-01) | ❌ (kini diperbaiki) |
| Ekspor diblokir untuk `sensitive` | Sesuai di 5 titik ekspor web/API | ✅ |
| 26 migrasi → 11 model | 26 migrasi, 11 model + `OPD`, `Notification`, `Participant`, `UserSession` (14 model) | ⚠️ kurang akurat |
| `start.bat` "USANG — merujuk frontend/ yang tidak ada" | Benar, lalu diperbaiki menjadi launcher backend yang valid | ✅ kini diperbaiki |

---

## 8. Rekomendasi (setelah perbaikan sesi REA ini)

1. **Segera aktifkan matriks OPD** — kolom `opd_id` sudah ada di `users` & `forms`, `AuthorizationService` sudah menyiapkan `applyFormAccessConstraints()` tetapi belum dipakai. Rencanakan penerapan bertahap + uji regresi (D-09).
2. **Jadikan satu service layer** — pertimbangkan memindahkan logika `PageController`/`FormCrudController` ke `FormManagementService`/`SubmissionService` (domain) agar jalur web & API seragam; buang duplikasi.
3. **Perluas pengujian** — uji otorisasi, guardrail form publik, dan ekspor (test baru ditambahkan pada sesi ini sebagai fondasi).
4. **Aktifkan retensi data** — `DataClassification::retentionDays()` sudah ada tetapi belum ada scheduler pembersihan otomatis (Fase 8 roadmap lama).
5. **Masking & enkripsi** — `shouldMaskInLogs()` belum dipakai; aktifkan `SESSION_ENCRYPT=true` di produksi.
6. **Monitoring** — tidak ada APM/metrics; untuk layanan publik 47 OPD, tambahkan minimal health-check terjadwal (`/up` sudah tersedia) + alerting.

---

*Dokumen ini dihasilkan dari Reverse Engineering Analysis pada 19 Oktober 2026. Perbaikan atas temuan terdokumentasi di* [REA-LOG-PERBAIKAN.md](REA-LOG-PERBAIKAN.md).
