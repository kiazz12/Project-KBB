# Project KBB

**Sistem Formulir Digital untuk Pemerintah Kabupaten Bandung Barat**

Aplikasi web untuk membuat, mengelola, dan mengumpulkan data melalui formulir daring — setara dengan Google Forms, tetapi berjalan di server pemerintah sendiri dengan keamanan berbasis OPD, pencatatan audit, dan klasifikasi data.

---

## Daftar Isi

- [Tentang Proyek](#tentang-proyek)
- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Arsitektur](#arsitektur)
- [Struktur Repositori](#struktur-repositori)
- [Basis Data](#basis-data)
- [Persyaratan](#persyaratan)
- [Instalasi & Setup](#instalasi--setup)
- [Perintah Pengembangan](#perintah-pengembangan)
- [Pengujian](#pengujian)
- [Dokumentasi](#dokumentasi)
- [Lisensi](#lisensi)

---

## Tentang Proyek

Pemerintah Kabupaten Bandung Barat memiliki **47 OPD** (Organisasi Perangkat Daerah) yang masing-masing membutuhkan cara mengumpulkan data dari masyarakat maupun internal. Sebelumnya pengumpulan data dilakukan secara manual atau melalui layanan pihak ketiga yang tidak terintegrasi.

Project KBB dibangun untuk menyediakan:

- Formulir digital yang mudah digunakan oleh operator di setiap OPD
- Pengumpulan data publik dan internal secara terstruktur dan aman
- Pengamanan data berdasarkan tingkat kerahasiaan (publik, internal, sensitif)
- Pencatatan seluruh aktivitas penting untuk keperluan audit
- Dashboard analitik bagi pengelola

---

## Fitur Utama

### Pembuatan Formulir
- 14 jenis bidang: teks, textarea, email, angka, tanggal, waktu, select, radio, checkbox, unggahan file, judul, paragraf, tanda tangan, dan bidang terhitung (`computed`)
- Editor drag-and-drop berbasis Livewire (`FormEditor`)
- Pengelompokan bidang dengan **section**
- Logika bersyarat (menampilkan/menyembunyikan bidang berdasarkan jawaban lain)
- Mesin formula untuk bidang terhitung otomatis (`computed`)
- Pengaturan: batas pengumpulan, waktu buka/tutup, wajib login, klasifikasi data

### Formulir Publik
- Dapat diakses tanpa login melalui `/form/{slug}`
- Validasi real-time via Livewire
- Proteksi double-submit, halaman/URL konfirmasi setelah mengirim
- Perekaman IP & User-Agent hanya jika diaktifkan (`collect_ip`)

### Pengelolaan Data & Ekspor
- Daftar pengumpulan data dengan paginasi, detail per bidang, dan penghapusan
- Ekspor **CSV**, **XLSX/Excel** (Maatwebsite Excel), dan **PDF** (DOMPDF)
- Ekspor **diblokir** untuk formulir berklasifikasi **sensitive**

### Dashboard & Analitik
- Statistik formulir (total, published, draft, closed)
- Statistik pengumpulan data (total, hari ini, minggu ini)
- Grafik aktivitas (Chart.js) dengan rentang waktu 7 hari s.d. 1 tahun
- Dashboard admin terpisah

### Keamanan
- **3 sistem autentikasi** paralel (Web sesi, API token Sanctum, Admin super_admin)
- Pembatasan sesi aktif: super_admin = 1, admin = 3
- Idle timeout 5 menit dengan peringatan 2 menit sebelum logout otomatis
- Rate limit login API (5/menit) dan pengiriman publik (10/menit)
- Pencatatan audit untuk seluruh aksi penting
- CSRF protection dan hashing kata sandi

---

## Teknologi

| Lapisan | Teknologi |
|---------|-----------|
| Framework | Laravel 13 |
| Bahasa | PHP 8.3+ |
| ORM | Eloquent |
| Interaktivitas | Livewire 4 (Blade) |
| Templat | Blade |
| Gaya | Tailwind CSS (via CDN, tanpa build step) |
| Grafik | Chart.js 4 (via CDN) |
| Auth API | Laravel Sanctum 4 |
| Ekspor | Maatwebsite Excel, DOMPDF |
| Basis Data | MySQL (produksi) / SQLite in-memory (testing) |
| Pengujian | PHPUnit 12 |
| Gaya Kode | Laravel Pint |
| Build | Vite (untuk `resources/js` & `resources/css`) |

---

## Arsitektur

### Modular Monolith
Satu aplikasi Laravel yang kodenya diorganisasi berdasarkan **domain bisnis**, bukan lapisan teknis. Realistis untuk proyek berjalan, mudah di-deploy, dan bisa berkembang ke microservices di kemudian hari.

### Enam Domain Utama (`app/Domains/`)

| Domain | Layanan | Fungsi |
|--------|---------|--------|
| Auth | `AuthService`, `AuthorizationService` | Login/logout, izin akses, penyaringan kueri |
| PublicForms | `PublicFormService` | Penyajian & pengiriman formulir publik |
| InternalForms | `FormManagementService` | CRUD formulir & bidang |
| Submissions | `SubmissionService` | Pengelolaan jawaban |
| Reporting | `ReportingService` | Dashboard, statistik, ekspor |
| Audit | `AuditDomainService` | Pencatatan aktivitas |

> **Catatan (hutang teknis):** layanan domain sudah ada tetapi **belum dipakai controller**. Controller masih memakai Eloquent langsung + `App\Services\AuditService`.

### Tiga Sistem Autentikasi Paralel

| Sistem | Rute | Driver | Kontroler |
|--------|------|--------|-----------|
| Web | `routes/web.php` | Session | `WebAuthController` |
| API | `routes/api.php` | Token Sanctum | `API/AuthController` |
| Admin | `routes/admin.php` | Session (khusus super_admin) | `Admin/AuthController` |

### Pengendalian Akses Berbasis OPD

- **super_admin**: akses semua data, kelola pengguna
- **admin**: akses formulir milik sendiri + formulir OPD yang sama

`FormPolicy` (terdaftar via Gate) membatasi *view/edit* ke formulir milik sendiri; logika cakupan OPD ada di `AuthorizationService::applyFormAccessConstraints()` (belum terhubung ke controller).

### Klasifikasi Data

| Klasifikasi | Ekspor | Retensi |
|-------------|:------:|:-------:|
| public | ✅ | Selamanya |
| internal | ✅ (dengan log) | 1 tahun |
| sensitive | ❌ (diblokir) | 90 hari |

---

## Struktur Repositori

```
project-kbb/
├── backend/                    ← SELURUH kode aplikasi (Laravel)
│   ├── app/
│   │   ├── Domains/            ← 6 layanan domain
│   │   ├── Http/Controllers/   ← Web, API, Admin
│   │   ├── Livewire/           ← 7 komponen Livewire
│   │   ├── Models/             ← 10 model Eloquent
│   │   ├── Services/           ← 5 layanan aplikasi (Audit, Notification, SessionLimit, Form, Submission)
│   │   ├── Enums/              ← UserRole, FormStatus, FieldType, DataClassification
│   │   ├── Policies/           ← FormPolicy
│   │   └── Exports/            ← Ekspor Excel
│   ├── resources/views/        ← Templat Blade + Livewire
│   ├── routes/                 ← web.php, api.php, admin.php, console.php
│   ├── database/migrations/    ← 27 migrasi
│   ├── tests/                  ← PHPUnit (Feature + Unit)
│   └── config/
└── README.md
```

**Frontend adalah Blade + Livewire** — bukan React. Dokumen lama dan `backend/pnpm-lock.yaml` menyebut React 19 / Inertia SPA yang tidak pernah dibangun; abaikan.

---

## Basis Data

27 migrasi → 10 model utama:

| Tabel | Fungsi |
|-------|--------|
| `users` | Pengguna (role, nip, `opd_id`, display_name) |
| `opds` | 47 Organisasi Perangkat Daerah |
| `forms` | Formulir (slug, status, klasifikasi data, pengaturan) |
| `form_sections` | Bagian/kelompok bidang |
| `form_fields` | Bidang formulir (jenis, validasi, formula, kondisi) |
| `form_submissions` | Kiriman jawaban |
| `submission_data` | Nilai jawaban per bidang |
| `audit_logs` | Catatan aktivitas (old/new values) |
| `notifications` | Notifikasi pengguna |
| `user_sessions` | Pelacakan sesi aktif |

---

## Persyaratan

- PHP 8.3+
- Composer
- Node.js + npm (untuk Vite & `composer dev`)
- MySQL 8+ (produksi) atau SQLite (pengembangan cepat)
- Redis (opsional, untuk cache/queue)

---

## Instalasi & Setup

Semua perintah dijalankan dari direktori `backend/`.

> **Penting:** salin `.env.example` bawaan lalu isi `APP_KEY`:

```bash
cd backend

# 1. Buat .env dari template bawaan
cp .env.example .env
php artisan key:generate

# 2. Atur koneksi database di .env
#    DB_CONNECTION=mysql  (atau sqlite untuk pengembangan cepat)
#    DB_DATABASE=project-kbb

# 3. Install dependensi
composer install
npm install --ignore-scripts

# 4. Migrasi + seed
php artisan migrate --seed

# 5. Build aset
npm run build
```

Alternatif: jika `.env.example` tersedia, jalankan:

```bash
composer setup
```

---

## Perintah Pengembangan

```bash
# Server aplikasi + antrean + log + Vite sekaligus (butuh Node.js)
composer dev

# Terpisah
php artisan serve            # aplikasi di http://localhost:8000
npm run dev                  # Vite di http://localhost:5173
php artisan queue:listen --tries=1 --timeout=0
php artisan pail             # viewer log

# Basis data
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed    # reset total

# Pengujian
php artisan test                     # semua test
php artisan test --filter=LoginSessionTest
php artisan test --filter="nama_metode_test"

# Gaya kode
php vendor/bin/pint
```

---

## Pengujian

- PHPUnit 12 dengan **SQLite in-memory** (lihat `phpunit.xml`) — tidak butuh layanan eksternal
- Isolasi per test dengan `RefreshDatabase`, autentikasi via `actingAs()`
- 13 berkas test (Feature + Unit) — cakupan masih rendah

---

## Dokumentasi

Dokumentasi teknis tidak lagi disertakan di dalam repositori. Dokumentasi arsitektur, laporan REA, dan daftar tugas dipindahkan ke `C:\Users\Acer\Downloads\docs`.

---

## Lisensi

Perangkat lunak ini adalah **properti Hizkia Sirait** dan dikembangkan khusus untuk **Pemerintah Kabupaten Bandung Barat**. Hak penggunaan terbatas hanya untuk kedua pihak tersebut (lihat [LICENSE](LICENSE)). Komponen pihak ketiga (Laravel dll.) tetap tunduk pada lisensi masing-masing.
