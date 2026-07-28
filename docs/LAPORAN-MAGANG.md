# Laporan Magang — Project KBB

## Sistem Formulir Digital Pemerintah Kabupaten Bandung Barat

**Nama:** Hizkia
**Institusi:** (Isi nama institusi)
**Periode Magang:** 23 Juni 2026 - 28 Juli 2026
**Total Jam Kerja:** 320 Work Hours (WH)
**Pembimbing:** (Isi nama pembimbing)

---

## DAFTAR ISI

1. [BAB 1: PENDAHULUAN](#bab-1-pendahuluan)
2. [BAB 2: TINJAUAN PUSTAKA](#bab-2-tinjauan-pustaka)
3. [BAB 3: ANALISIS KEBUTUHAN](#bab-3-analisis-kebutuhan)
4. [BAB 4: PERANCANGAN SISTEM](#bab-4-perancangan-sistem)
5. [BAB 5: IMPLEMENTASI](#bab-5-implementasi)
6. [BAB 6: HASIL DAN PEMBAHASAN](#bab-6-hasil-dan-pembahasan)
7. [BAB 7: KESIMPULAN DAN SARAN](#bab-7-kesimpulan-dan-saran)
8. [LAMPIRAN](#lampiran)

---

## BAB 1: PENDAHULUAN

### 1.1 Latar Belakang

Pemerintah Kabupaten Bandung Barat memiliki 47 Organisasi Perangkat Daerah (OPD) yang masing-masing membutuhkan cara untuk mengumpulkan data dari masyarakat maupun dari internal pemerintah. Sebelumnya, pengumpulan data dilakukan secara manual atau menggunakan formulir dari pihak ketiga yang tidak terintegrasi dengan sistem pemerintah daerah.

Beberapa permasalahan utama yang dihadapi antara lain:

1. **Ketergantungan pada layanan pihak ketiga** — Google Forms dan layanan sejenis menyimpan data di server luar negeri, yang berpotensi melanggar regulasi perlindungan data pemerintah.
2. **Tidak adanya pengendalian akses berbasis OPD** — Tidak ada mekanisme untuk memastikan hanya admin OPD tertentu yang dapat mengakses formulir milik OPD-nya.
3. **Ketiadaan audit trail** — Tidak ada pencatatan aktivitas yang memadai untuk keperluan audit internal pemerintah.
4. **Format ekspor tidak sesuai kebutuhan** — Pegawai pemerintah membutuhkan format ekspor khusus seperti Uang Saku dan Presensi yang tidak tersedia di Google Forms.
5. **Batasan fitur formulir** — Tidak ada logika bersyarat (conditional logic), mesin formula untuk bidang yang dihitung otomatis, atau pengelompokan bidang menggunakan section.

Berdasarkan permasalahan tersebut, dibutuhkan sebuah aplikasi web formulir digital yang dapat digunakan oleh seluruh OPD di Kabupaten Bandung Barat dengan host mandiri di server pemerintah, memiliki sistem keamanan berdasarkan OPD, dan mendukung pencatatan audit untuk setiap aktivitas penting.

### 1.2 Tujuan Magang

Tujuan dari kegiatan magang ini adalah:

1. Mengembangkan aplikasi formulir digital untuk Pemerintah Kabupaten Bandung Barat
2. Menerapkan konsep pengembangan perangkat lunak berbasis web menggunakan framework Laravel
3. Memahami arsitektur modular monolith dalam pengembangan aplikasi berskala menengah
4. Menerapkan sistem autentikasi dan otorisasi berbasis peran dan OPD
5. Membangun fitur keamanan data berdasarkan klasifikasi (publik, internal, sensitif)
6. Mencatat audit trail untuk setiap aktivitas penting dalam aplikasi
7. Mengimplementasikan pola Domain-Driven Design (DDD) dalam struktur kode
8. Membangun antarmuka interaktif menggunakan Livewire tanpa menulis JavaScript secara langsung

### 1.3 Ruang Lingkup

Project ini mencakup pengembangan aplikasi web formulir digital dengan fitur utama sebagai berikut:

- Pembuatan dan pengelolaan formulir dengan 14 jenis bidang input (termasuk bidang computed)
- Formulir publik yang dapat diakses tanpa login
- Dashboard analitik dengan grafik aktivitas
- Pengelolaan pengguna berdasarkan peran (super_admin dan admin)
- Pengendalian akses berdasarkan OPD
- Ekspor data dalam format CSV, PDF, Uang Saku, dan Presensi
- Pencatatan audit untuk seluruh aktivitas penting
- Sesi pengguna dengan batasan jumlah sesi aktif
- Notifikasi real-time untuk aktivitas penting
- Pencarian peserta dari database untuk formulir kehadiran

### 1.4 Metodologi Pengembangan

Pengembangan dilakukan menggunakan metode **iteratif incremental** dengan pembagian fase:

1. **Fase 1 (Setup)** — Fondasi aplikasi dan autentikasi
2. **Fase 2 (Fitur Utama)** — CRUD formulir, pengisian publik, ekspor
3. **Fase 3 (Antarmuka)** — Dashboard, tampilan, panel admin
4. **Fase 4 (Keamanan)** — Autentikasi lanjutan, audit, klasifikasi data
5. **Fase 5 (Optimasi)** — Pengujian dan optimasi performa
6. **Fase 6 (Penyelesaian)** — Dokumentasi dan fitur akhir

Setiap fase menghasilkan Increment yang dapat diuji dan diverifikasi.

### 1.5 Manfaat yang Diharapkan

**Manfaat bagi Pemerintah Kabupaten Bandung Barat:**
- Pengumpulan data yang lebih efisien dan terpusat
- Pengendalian akses berdasarkan OPD
- Pencatatan audit untuk kepatuhan regulasi
- Format ekspor sesuai kebutuhan pemerintah daerah

**Manfaat bagi Penulis:**
- Pengalaman pengembangan aplikasi web berskala production
- Pemahaman praktis arsitektur modular monolith dan DDD
- Keterampilan keamanan aplikasi web (autentikasi, otorisasi, audit)
- Pengalaman bekerja dengan teknologi modern (Laravel 13, Livewire 4)

---

## BAB 2: TINJAUAN PUSTAKA

### 2.1 Laravel Framework

Laravel adalah framework PHP untuk pengembangan aplikasi web yang menggunakan pola arsitektur Model-View-Controller (MVC). Versi yang digunakan dalam project ini adalah Laravel 13.16.1 dengan PHP 8.3.

Fitur-fitur Laravel yang dimanfaatkan dalam project ini:

| Fitur | Penerapan dalam Project |
|-------|------------------------|
| **Eloquent ORM** | Model-model seperti `Form`, `User`, `FormSubmission` dengan relasi relasional |
| **Blade Template** | Templat untuk seluruh halaman web (dashboard, form editor, formulir publik) |
| **Routing** | 3 grup rute terpisah (web, API, admin) |
| **Middleware** | Otorisasi berbasis peran (`role:super_admin`), autentikasi (`auth`, `auth:sanctum`) |
| **Validation** | Validasi data pada controller dan Livewire component |
| **Queue System** | Pekerjaan latar belakang untuk notifikasi |
| **Artisan CLI** | migrasi, seeder, dan command kustom |
| **Service Container** | Dependency injection untuk service classes |
| **Events & Listeners** | Event-driven architecture untuk audit logging |
| **Soft Deletes** | Penghapusan logis pada model `Form` dan `OPD` |
| **Pagination** | Penanganan data dalam jumlah besar pada daftar |

### 2.2 Livewire

Livewire adalah kerangka kerja untuk Laravel yang memungkinkan pembuatan antarmuka interaktif tanpa menulis JavaScript secara langsung. Livewire bekerja dengan mengirim HTML yang diperbarui dari server ke browser, sehingga pengembang dapat fokus pada logika bisnis di sisi server.

Dalam project ini, Livewire 4.3.3 digunakan untuk 7 komponen:

| Komponen | Fungsi | Kompleksitas |
|----------|--------|-------------|
| `PublicForm` | Rendering dan pengiriman formulir publik | Tinggi (multi-step, formula engine, pencarian peserta) |
| `FormEditor` | Editor formulir drag-and-drop | Tinggi (25+ properti, CRUD fields/sections) |
| `CreateForm` | Pembuatan formulir baru | Sedang |
| `ActivityChart` | Grafik aktivitas dashboard | Sedang |
| `SessionTimeout` | Timeout sesi 5 menit | Sedang |
| `DisplayNamePrompt` | Nama tampilan per sesi | Rendah |
| `NotificationBell` | Lonceng notifikasi real-time | Rendah |

**Arsitektur Livewire yang Digunakan:**

```
Browser (Livewire JS)
    ↕ AJAX (JSON + HTML partial)
Server (Livewire PHP Component)
    ↕ Eloquent / Service
Database (MySQL)
```

Setiap komponen Livewire memiliki:
- **Properti publik** — data yang di-bind ke tampilan
- **Method** — aksi yang dipanggil dari tampilan
- **Mount()** — inisialisasi saat komponen dimuat
- **Render()** — mengembalikan templat Blade yang di-render

### 2.3 Blade Template Engine

Blade adalah mesin templat bawaan Laravel yang menyediakan cara cepat dan ringkas untuk membuat templat dengan menggunakan ekspresi PHP yang bersih. Blade mendukung inheritance (pewarisan) dan komponen yang dapat digunakan kembali.

Fitur Blade yang digunakan dalam project ini:

```blade
{{-- 1. Layout Inheritance --}}
@extends('layouts.app')
@section('content') ... @endsection

{{-- 2. Component Tags --}}
<x-sidebar />
<x-alert type="success" />

{{-- 3. Conditional Directives --}}
@if($form->status === 'published')
    <span class="badge-success">Published</span>
@endif

{{-- 4. Loop Directives --}}
@foreach($form->fields as $field)
    <div wire:key="field-{{ $field->id }}">
        @include('livewire.fields.' . $field->type)
    </div>
@endforeach

{{-- 5. Livewire Integration --}}
@livewire('public-form', ['slug' => $slug])
```

### 2.4 Tailwind CSS

Tailwind CSS adalah kerangka kerja CSS utility-first yang menyediakan kelas-kelas siap pakai untuk membangun antarmuka web. Dalam project ini, Tailwind CSS dimuat melalui CDN (Content Delivery Network) tanpa memerlukan proses build terpisah.

Pendekatan CDN dipilih karena:
- Tidak memerlukan Node.js di environment produksi
- Mengurangi kompleksitas deployment
- Cukup untuk kebutuhan UI yang ada

### 2.5 MySQL

MySQL adalah sistem manajemen basis data relasional yang digunakan sebagai database utama dalam project ini. MySQL menyediakan dukungan untuk JSON, transaksi, dan indeks yang diperlukan untuk aplikasi berskala menengah.

Fitur MySQL yang dimanfaatkan:
- **Tipe data JSON** — untuk menyimpan konfigurasi formulir (`settings`), opsi bidang (`options`), logika bersyarat (`conditions`), dan formula (`formula`)
- **Foreign Key Constraints** — menjaga integritas relasional antar tabel
- **Indeks** — mempercepat query pada kolom yang sering diakses
- **Soft Deletes** — penghapusan logis tanpa menghapus data fisik
- **UUID** — pengidentifikasian unik untuk formulir dan submission

### 2.6 Sanctum

Sanctum adalah paket autentikasi API untuk Laravel yang menyediakan sistem token untuk autentikasi API dan SPA (Single Page Application). Dalam project ini, Sanctum digunakan untuk autentikasi API dengan token yang dapat kedaluwarsa.

Sanctum menyediakan dua metode autentikasi:
1. **Cookie-based** — untuk aplikasi SPA yang berada pada domain yang sama
2. **Token-based** — untuk API yang diakses dari luar (mobile apps, integrasi pihak ketiga)

### 2.7 DomPDF

DomPDF adalah pustaka PHP untuk rendering dokumen PDF dari HTML. Digunakan untuk ekspor formulir pengumpulan data ke format PDF, termasuk format khusus Uang Saku dan Presensi.

### 2.8 Maatwebsite Excel

Maatwebsite Excel adalah paket Laravel untuk import/export file Excel dan CSV. Digunakan untuk streaming ekspor data dalam jumlah besar tanpa kehabisan memori menggunakan chunking.

### 2.9 Chart.js

Chart.js adalah pustaka JavaScript untuk visualisasi data menggunakan grafik. Versi 4.4.7 digunakan untuk menampilkan grafik aktivitas pengumpulan data pada dashboard.

### 2.10 PHPUnit

PHPUnit adalah framework pengujian unit untuk PHP. Versi 12 digunakan dalam project ini dengan konfigurasi SQLite in-memory untuk pengujian yang cepat dan terisolasi.

---

## BAB 3: ANALISIS KEBUTUHAN

### 3.1 Kebutuhan Fungsional

#### 3.1.1 Pembuatan Formulir

| No | Kebutuhan | Prioritas | Keterangan |
|----|-----------|-----------|------------|
| 1 | Membuat formulir dengan judul, deskripsi, dan slug otomatis | Tinggi | Slug di-generate dari judul, unik |
| 2 | Mendukung 14 jenis bidang input | Tinggi | Termasuk computed field |
| 3 | Drag-and-drop field builder | Tinggi | Melalui FormEditor Livewire |
| 4 | Pengelompokan bidang menggunakan section | Sedang | Section membagi form menjadi multi-step |
| 5 | Logika bersyarat (conditional logic) | Sedang | Field tampil berdasarkan nilai field lain |
| 6 | Mesin formula untuk bidang computed | Sedang | Operasi arithmetic antar field |
| 7 | Pengaturan: batas submission, waktu buka/tutup, wajib login | Tinggi | Field JSON `settings` |
| 8 | Klasifikasi data (publik, internal, sensitif) | Tinggi | Mempengaruhi izin ekspor |
| 9 | Duplikasi formulir | Sedang | Menyalin field dan konfigurasi |
| 10 | Logo KBB dan warna tema | Rendah | Kustomisasi tampilan |

#### 3.1.2 Formulir Publik

| No | Kebutuhan | Prioritas | Keterangan |
|----|-----------|-----------|------------|
| 1 | Akses tanpa login melalui /form/{slug} | Tinggi | Publik tanpa autentikasi |
| 2 | Validasi real-time per bidang | Tinggi | Client-side + server-side |
| 3 | Pencarian peserta secara otomatis | Sedang | Autocomplete dari tabel participants |
| 4 | Status pemuatan untuk mencegah double-submit | Sedang | Loading state pada tombol |
| 5 | Pengalihan URL atau halaman terima kasih | Sedang | Konfigurable per formulir |
| 6 | Multi-step wizard | Sedang | Berdasarkan section |
| 7 | Batas waktu dan jumlah submission | Sedang | Cek expired dan max_submissions |
| 8 | Honeypot anti-spam | Rendah | Field tersembunyi company_website |
| 9 | Batas satu respons per pengguna | Sedang | limit_one_response setting |

#### 3.1.3 Pengelolaan Pengumpulan Data

| No | Kebutuhan | Prioritas | Keterangan |
|----|-----------|-----------|------------|
| 1 | Daftar pengumpulan data dengan paginasi | Tinggi | 20 data per halaman |
| 2 | Detail pengumpulan data per bidang | Tinggi | Tampilan key-value |
| 3 | Hapus pengumpulan data | Tinggi | Single dan bulk delete |
| 4 | Penyaringan berdasarkan tanggal/status | Sedang | Date range filter |
| 5 | Pencarian | Sedang | Search pada nama/email |

#### 3.1.4 Ekspor Data

| No | Kebutuhan | Prioritas | Keterangan |
|----|-----------|-----------|------------|
| 1 | Ekspor CSV | Tinggi | Streaming, chunking 100 baris |
| 2 | Ekspor PDF | Tinggi | DomPDF, A4 portrait |
| 3 | Ekspor XLSX | Sedang | Maatwebsite Excel |
| 4 | Ekspor Uang Saku (format khusus) | Sedang | Kwitansi format pemerintah |
| 5 | Ekspor Presensi (format khusus) | Sedang | A4 landscape, daftar hadir |
| 6 | Pemblokiran ekspor untuk formulir sensitif | Tinggi | DataClassification::canExport() |

#### 3.1.5 Dashboard dan Analitik

| No | Kebutuhan | Prioritas | Keterangan |
|----|-----------|-----------|------------|
| 1 | Statistik: total form, published, draft, closed | Tinggi | Card statistik |
| 2 | Statistik pengumpulan data (hari ini, minggu ini) | Tinggi | Angka ringkas |
| 3 | Grafik aktivitas dengan Chart.js | Sedang | Rentang 7hari - 1 tahun |
| 4 | Tabel formulir terbaru | Sedang | 5 terbaru |
| 5 | Analitik per-bidang (distribusi nilai) | Sedang | Pie/bar chart per field |

#### 3.1.6 Keamanan

| No | Kebutuhan | Prioritas | Keterangan |
|----|-----------|-----------|------------|
| 1 | Autentikasi berbasis sesi (web) dan token (API) | Tinggi | Triple auth system |
| 2 | Otorisasi berdasarkan peran dan OPD | Tinggi | FormPolicy + AuthorizationService |
| 3 | Batas waktu sesi tidak aktif 5 menit | Tinggi | SessionTimeout Livewire |
| 4 | Pembatasan jumlah sesi aktif | Tinggi | super_admin: 1, admin: 3 |
| 5 | Pencatatan audit | Tinggi | AuditService, 6 jenis aksi |
| 6 | Pembatasan laju login API | Sedang | throttle: 5,1 |
| 7 | Nama tampilan per sesi | Sedang | DisplayNamePrompt |
| 8 | Notifikasi aktivitas | Sedang | NotificationService |

### 3.2 Kebutuhan Non-Fungsional

| No | Kebutuhan | Target | Metode Pengukuran |
|----|-----------|--------|-------------------|
| 1 | Waktu muat formulir publik | < 2 detik | DevTools Network tab |
| 2 | Waktu muat dashboard | < 3 detik | DevTools Network tab |
| 3 | Waktu muat daftar pengumpulan data | < 500ms | Livewire response time |
| 4 | Waktu pekerjaan ekspor | < 30 detik | Server processing time |
| 5 | Waktu login | < 1 detik | DevTools Network tab |
| 6 | Waktu pengecekan otorisasi | < 10ms | Query execution time |
| 7 | Konsumsi memori ekspor | < 256MB | PHP memory_get_peak_usage() |
| 8 | Ukuran database per 1000 form | < 100MB | Database file size |

### 3.3 Kebutuhan Pengguna

| Peran | Kebutuhan Utama | Level Akses |
|-------|----------------|-------------|
| **Super Admin** | Melihat seluruh form, mengelola pengguna, melihat audit log | Full access ke semua fitur |
| **Admin (OPD)** | Membuat/mengedit form milik OPD, melihat submission, ekspor data | Akses terbatas berdasarkan OPD |
| **Publik** | Mengisi formulir yang dipublikasikan | Akses formulir publik saja |

### 3.4 Analisis Risiko

| Risiko | Probabilitas | Dampak | Mitigasi |
|--------|-------------|--------|----------|
| Kehilangan data | Rendah | Tinggi | Soft deletes, backup database |
| Serangan brute force | Sedang | Tinggi | Rate limiting, session limits |
| Kebocoran data sensitif | Rendah | Sangat Tinggi | Data classification, export blocking |
| Performance degradation | Sedang | Sedang | Query optimization, pagination |
| Session hijacking | Rendah | Tinggi | Timeout 5 menit, session limits |

---

## BAB 4: PERANCANGAN SISTEM

### 4.1 Arsitektur Aplikasi

Project KBB dibangun dengan arsitektur **Modular Monolith** menggunakan pendekatan **Domain-Driven Design (DDD)**. Aplikasi terbagi menjadi enam domain utama yang masing-masing bertanggung jawab atas fitur tertentu:

```
┌─────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                     │
│  ┌──────────┐  ┌──────────────┐  ┌───────────────────┐  │
│  │  Blade    │  │   Livewire   │  │   REST API        │  │
│  │ Templates │  │  Components  │  │   (Sanctum)       │  │
│  └────┬─────┘  └──────┬───────┘  └────────┬──────────┘  │
│       │               │                    │              │
├───────┼───────────────┼────────────────────┼──────────────┤
│       │        CONTROLLER LAYER           │              │
│  ┌────┴─────┐  ┌──────┴───────┐  ┌────────┴──────────┐  │
│  │  Page    │  │   Livewire   │  │  API Controllers  │  │
│  │Controller│  │  Components  │  │  (Form, Sub, User) │  │
│  └────┬─────┘  └──────┬───────┘  └────────┬──────────┘  │
│       │               │                    │              │
├───────┼───────────────┼────────────────────┼──────────────┤
│       │          DOMAIN LAYER             │              │
│  ┌────┴──────────────────────────────────┴──────────┐   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────────────┐  │   │
│  │  │   Auth   │ │  Forms   │ │   Submissions    │  │   │
│  │  │ Domain   │ │  Domain  │ │    Domain        │  │   │
│  │  └──────────┘ └──────────┘ └──────────────────┘  │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────────────┐  │   │
│  │  │ Reporting│ │  Audit   │ │  Public Forms    │  │   │
│  │  │ Domain   │ │  Domain  │ │    Domain        │  │   │
│  │  └──────────┘ └──────────┘ └──────────────────┘  │   │
│  └───────────────────────────┬───────────────────────┘   │
│                              │                           │
├──────────────────────────────┼───────────────────────────┤
│       │               SERVICE LAYER              │       │
│  ┌────┴─────┐  ┌─────────────┐  ┌───────────────┐       │
│  │  Audit   │  │  Session    │  │ Notification  │       │
│  │ Service  │  │  Limit Svc  │  │   Service     │       │
│  └────┬─────┘  └──────┬──────┘  └───────┬───────┘       │
│       │               │                 │                │
├───────┼───────────────┼─────────────────┼────────────────┤
│       │          DATA LAYER               │              │
│  ┌────┴──────────────────────────────────┴──────────┐   │
│  │              Eloquent Models                      │   │
│  │  User, Form, FormField, FormSection,              │   │
│  │  FormSubmission, SubmissionData, OPD,              │   │
│  │  AuditLog, Notification, UserSession, Participant  │   │
│  └──────────────────────┬────────────────────────────┘   │
│                         │                                 │
│  ┌──────────────────────┴────────────────────────────┐   │
│  │              MySQL Database                        │   │
│  │  11 tabel utama + tabel infrastruktur Laravel       │   │
│  └───────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

### 4.2 Struktur Folder

```
backend/
├── app/
│   ├── Domains/                    ← Domain Services (6 domain)
│   │   ├── Audit/
│   │   │   └── AuditDomainService.php
│   │   ├── Auth/
│   │   │   ├── AuthService.php
│   │   │   └── AuthorizationService.php
│   │   ├── InternalForms/
│   │   │   └── FormManagementService.php
│   │   ├── PublicForms/
│   │   │   └── PublicFormService.php
│   │   ├── Reporting/
│   │   │   └── ReportingService.php
│   │   └── Submissions/
│   │       └── SubmissionService.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              ← Admin panel controllers
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── FormController.php
│   │   │   │   └── UserController.php
│   │   │   ├── API/                ← REST API controllers
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── FieldController.php
│   │   │   │   ├── FormCrudController.php
│   │   │   │   ├── SubmissionApiController.php
│   │   │   │   └── UserController.php
│   │   │   ├── WebAuthController.php
│   │   │   ├── NotificationController.php
│   │   │   └── PageController.php  ← 459 baris, terberat
│   │   └── Middleware/
│   ├── Livewire/                   ← 7 komponen Livewire
│   │   ├── PublicForm.php
│   │   ├── FormEditor.php
│   │   ├── CreateForm.php
│   │   ├── ActivityChart.php
│   │   ├── SessionTimeout.php
│   │   ├── DisplayNamePrompt.php
│   │   └── NotificationBell.php
│   ├── Models/                     ← 11 Eloquent models
│   ├── Services/                   ← App-level services
│   │   ├── AuditService.php
│   │   ├── SessionLimitService.php
│   │   ├── NotificationService.php
│   │   ├── FormService.php
│   │   └── SubmissionService.php
│   ├── Enums/                      ← 4 enumerasi
│   │   ├── UserRole.php
│   │   ├── FormStatus.php
│   │   ├── FieldType.php
│   │   └── DataClassification.php
│   ├── Policies/
│   │   └── FormPolicy.php
│   └── Exports/
│       └── SubmissionsExport.php
├── resources/views/                ← 41 template Blade
│   ├── layouts/app.blade.php
│   ├── dashboard/
│   ├── forms/
│   ├── submissions/
│   ├── admin/
│   └── livewire/
├── routes/                         ← 3 file rute
│   ├── web.php
│   ├── api.php
│   └── admin.php
├── database/migrations/            ← 26 migrasi
├── tests/                          ← 7 file pengujian
└── config/
```

### 4.3 Struktur Basis Data (ERD)

Basis data terdiri dari 11 tabel utama yang diatur dalam 26 migrasi:

```
┌──────────┐     ┌──────────┐     ┌──────────────┐
│  users   │────<│  forms   │────<│ form_fields  │
│          │     │          │     │              │
│ id       │←─┐  │ id       │←─┐  │ id           │
│ name     │  │  │ user_id  │──┘  │ form_id      │──┘
│ email    │  │  │ opd_id   │     │ section_id   │──┐
│ role     │  │  │ title    │     │ type         │  │
│ opd_id   │──┘  │ slug     │     │ label        │  │
└────┬─────┘     │ status   │     │ required     │  │
     │           │ data_    │     │ options      │  │
     │           │ classif. │     │ conditions   │  │
     │           │ settings │     │ formula      │  │
     │           └────┬─────┘     └──────────────┘  │
     │                │                              │
     │           ┌────┴──────────┐           ┌──────┴─────────┐
     │           │form_submissions│          │ form_sections  │
     │           │               │          │                │
     │           │ id            │          │ id             │
     │           │ form_id       │──┐       │ form_id        │──┐
     │           │ user_id       │  │       │ title          │  │
     │           │ ip_address    │  │       │ order          │  │
     │           │ submitted_at  │  │       └────────────────┘  │
     │           └───────┬───────┘  │                          │
     │                   │          │                          │
     │              ┌────┴──────────┴──┐                       │
     │              │ submission_data  │                       │
     │              │                  │                       │
     │              │ id               │                       │
     │              │ submission_id    │──┘                    │
     │              │ form_field_id    │───────────────────────┘
     │              │ value            │
     │              └──────────────────┘
     │
     ├──>┌──────────────┐  ┌──────────────┐  ┌──────────────┐
     │   │ user_sessions│  │ audit_logs   │  │notifications │
     │   │              │  │              │  │              │
     │   │ id           │  │ id           │  │ id           │
     │   │ user_id      │──┘ │ user_id      │──┘ │ user_id      │──┘
     │   │ display_name │     │ action       │     │ type         │
     │   │ ip_address   │     │ auditable_*  │     │ message      │
     │   │ logged_in_at │     │ old_values   │     │ read_at      │
     │   │ logged_out_at│     │ new_values   │     └──────────────┘
     │   └──────────────┘     └──────────────┘
     │
     │   ┌──────────────┐
     └──>│    opds      │
         │              │
         │ id           │
         │ name         │
         │ code         │
         └──────────────┘
```

### 4.4 Rincian Schema Basis Data

#### 4.4.1 Tabel `users`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto-increment |
| `name` | string | Nama lengkap pengguna |
| `display_name` | string, nullable | Nama tampilan per sesi |
| `email` | string, unique | Alamat email untuk login |
| `email_verified_at` | timestamp, nullable | Waktu verifikasi email |
| `password` | string | Kata sandi ter-hash (bcrypt) |
| `remember_token` | string | Token "ingat saya" |
| `role` | string(20) | Default `'admin'`, enum: `super_admin`, `admin` |
| `nip` | string, nullable | Nomor Induk Pegawai |
| `opd` | string, nullable | Legacy field OPD teks |
| `opd_id` | FK → `opds.id`, nullable | Relasi ke tabel OPD (nullOnDelete) |
| `created_at` | timestamp | Waktu pembuatan |
| `updated_at` | timestamp | Waktu pembaruan terakhir |

#### 4.4.2 Tabel `opds`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto-increment |
| `name` | string, unique | Nama OPD |
| `code` | string, unique | Kode OPD |
| `description` | text, nullable | Deskripsi OPD |
| `deleted_at` | timestamp, nullable | Soft delete |
| `created_at` | timestamp | Waktu pembuatan |
| `updated_at` | timestamp | Waktu pembaruan terakhir |

#### 4.4.3 Tabel `forms`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto-increment |
| `uuid` | uuid, unique | UUID unik untuk formulir |
| `user_id` | FK → `users.id` | Pembuat formulir (cascadeOnDelete) |
| `opd_id` | FK → `opds.id`, nullable | OPD pemilik (nullOnDelete) |
| `title` | string | Judul formulir |
| `description` | text, nullable | Deskripsi formulir |
| `slug` | string, unique | URL-friendly identifier |
| `status` | string(20) | Default `'draft'`, enum: draft/published/closed |
| `data_classification` | enum | Default `'public'`, publik/internal/sensitif |
| `settings` | json, nullable | Konfigurasi form (JSON) |
| `confirmation_message` | text, nullable | Pesan konfirmasi pasca-submit |
| `confirmation_type` | string(20) | Default `'message'`, enum: message/redirect |
| `redirect_url` | string, nullable | URL redirect pasca-submit |
| `starts_at` | timestamp, nullable | Waktu form mulai aktif |
| `ends_at` | timestamp, nullable | Waktu form berakhir |
| `max_submissions` | unsigned int, nullable | Batas maksimum submission |
| `require_auth` | boolean | Default `false`, wajib login |
| `collect_ip` | boolean | Default `true`, kumpulkan IP |
| `show_kbb_logo` | boolean | Default `true`, tampilkan logo KBB |
| `limit_one_response` | boolean | Default `false`, satu respons per user |
| `header_image` | string, nullable | Gambar header form |
| `theme_color` | string(7), nullable | Hex warna tema (#RRGGBB) |
| `deleted_at` | timestamp, nullable | Soft delete |
| `created_at` | timestamp | Waktu pembuatan |
| `updated_at` | timestamp | Waktu pembaruan terakhir |

#### 4.4.4 Tabel `form_fields`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto-increment |
| `form_id` | FK → `forms.id` | Formulir induk (cascadeOnDelete) |
| `section_id` | FK → `form_sections.id`, nullable | Section induk (nullOnDelete) |
| `type` | string(30) | Tipe bidang (FieldType enum) |
| `label` | string | Label bidang |
| `placeholder` | string, nullable | Teks placeholder |
| `help_text` | text, nullable | Teks bantuan |
| `required` | boolean | Default `false`, wajib diisi |
| `options` | json, nullable | Opsi untuk select/radio/checkbox |
| `allow_other` | boolean | Default `false`, izin opsi "lainnya" |
| `order` | integer | Default `0`, urutan tampilan |
| `conditions` | json, nullable | Logika bersyarat (conditional logic) |
| `formula` | json, nullable | Konfigurasi untuk bidang computed |
| `is_admin_only` | boolean | Default `false`, hanya terlihat admin |
| `min_length` | unsigned int, nullable | Panjang minimum input |
| `max_length` | unsigned int, nullable | Panjang maksimum input |
| `default_value` | string, nullable | Nilai default |
| `created_at` | timestamp | Waktu pembuatan |
| `updated_at` | timestamp | Waktu pembaruan terakhir |

#### 4.4.5 Tabel `form_sections`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto-increment |
| `form_id` | FK → `forms.id` | Formulir induk (cascadeOnDelete) |
| `title` | string | Judul section |
| `description` | text, nullable | Deskripsi section |
| `order` | unsigned int | Default `0`, urutan |
| `created_at` | timestamp | Waktu pembuatan |
| `updated_at` | timestamp | Waktu pembaruan terakhir |

#### 4.4.6 Tabel `form_submissions`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto-increment |
| `uuid` | uuid, unique | UUID unik untuk submission |
| `form_id` | FK → `forms.id` | Formulir terkait (cascadeOnDelete) |
| `user_id` | FK → `users.id`, nullable | Pengguna yang submit (nullOnDelete) |
| `ip_address` | string(45), nullable | IP address (mendukung IPv6) |
| `user_agent` | text, nullable | User agent browser |
| `submitted_at` | timestamp | Waktu pengiriman |
| `created_at` | timestamp | Waktu pembuatan |
| `updated_at` | timestamp | Waktu pembaruan terakhir |

#### 4.4.7 Tabel `submission_data`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto-increment |
| `submission_id` | FK → `form_submissions.id` | Submission induk (cascadeOnDelete) |
| `form_field_id` | FK → `form_fields.id` | Bidang terkait (cascadeOnDelete) |
| `value` | text, nullable | Nilai jawaban |
| `created_at` | timestamp | Waktu pembuatan |
| `updated_at` | timestamp | Waktu pembaruan terakhir |

#### 4.4.8 Tabel `audit_logs`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto-increment |
| `user_id` | FK → `users.id`, nullable | Pengguna yang melakukan aksi (nullOnDelete) |
| `action` | string | Jenis aksi (misal: `auth.login`, `form.created`) |
| `auditable_type` | string, nullable | Tipe model terkait (polymorphic) |
| `auditable_id` | bigint, nullable | ID model terkait |
| `description` | text, nullable | Deskripsi aksi |
| `old_values` | json, nullable | Nilai sebelum perubahan |
| `new_values` | json, nullable | Nilai sesudah perubahan |
| `ip_address` | string(45), nullable | IP address pelaku |
| `user_agent` | text, nullable | User agent pelaku |
| `created_at` | timestamp | Waktu pencatatan |
| `updated_at` | timestamp | Waktu pembaruan terakhir |

#### 4.4.9 Tabel `notifications`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto-increment |
| `user_id` | FK → `users.id` | Penerima notifikasi (cascadeOnDelete) |
| `type` | string | Tipe notifikasi |
| `message` | text | Isi pesan |
| `data` | json, nullable | Data tambahan (actor_id, dll) |
| `read_at` | timestamp, nullable | Waktu dibaca (null = belum dibaca) |
| `created_at` | timestamp | Waktu pembuatan |
| `updated_at` | timestamp | Waktu pembaruan terakhir |
| **Indeks:** | `(user_id, read_at)` | Mempercepat query notifikasi belum dibaca |

#### 4.4.10 Tabel `user_sessions`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto-increment |
| `user_id` | FK → `users.id` | Pengguna pemilik sesi (cascadeOnDelete) |
| `display_name` | string | Default `''`, nama tampilan per sesi |
| `ip_address` | string, nullable | IP address sesi |
| `user_agent` | string, nullable | User agent sesi |
| `logged_in_at` | timestamp | Waktu login |
| `logged_out_at` | timestamp, nullable | Waktu logout (null = aktif) |
| `created_at` | timestamp | Waktu pembuatan |
| `updated_at` | timestamp | Waktu pembaruan terakhir |
| **Indeks:** | `user_id`, `logged_in_at` | Mempercepat query sesi aktif |

#### 4.4.11 Tabel `participants`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto-increment |
| `no` | integer | Nomor urut |
| `nama` | string | Nama peserta |
| `jabatan` | string, nullable | Jabatan peserta |
| `opd_institusi` | string, nullable | OPD/Institusi asal |
| `role` | string, nullable | Peran dalam acara |
| `tanggal_presensi` | date, nullable | Tanggal presensi |
| `created_at` | timestamp | Waktu pembuatan |
| `updated_at` | timestamp | Waktu pembaruan terakhir |

### 4.5 Tipe Field (FieldType Enum)

Project mendukung 14 jenis bidang input:

| No | Tipe | Nilai | Keterangan |
|----|------|-------|------------|
| 1 | Text | `text` | Input teks satu baris |
| 2 | Textarea | `textarea` | Input teks beberapa baris |
| 3 | Email | `email` | Input email dengan validasi format |
| 4 | Number | `number` | Input angka |
| 5 | Date | `date` | Input tanggal (date picker) |
| 6 | Time | `time` | Input waktu (time picker) |
| 7 | Select | `select` | Dropdown pilihan |
| 8 | Radio | `radio` | Pilihan ganda (satu jawaban) |
| 9 | Checkbox | `checkbox` | Pilihan ganda (beberapa jawaban) |
| 10 | File | `file` | Upload file (jpg, pdf, doc) |
| 11 | Heading | `heading` | Judul/heading (non-input) |
| 12 | Paragraph | `paragraph` | Paragraf penjelasan (non-input) |
| 13 | Signature | `signature` | Tanda tangan digital |
| 14 | Computed | `computed` | Bidang yang dihitung otomatis dari bidang lain |

### 4.6 Diagram Alur Autentikasi

#### 4.6.1 Autentikasi Web (Session-based)

```
┌──────────┐    POST /login     ┌──────────────────┐
│  Browser │ ──────────────────>│ WebAuthController │
│          │                    │    ::login()      │
└──────────┘                    └────────┬─────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Validasi Kredensial  │
                              │  Auth::attempt()     │
                              └──────────┬──────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Cek Session Limit    │
                              │  SessionLimitService  │
                              │  ::canLogin()         │
                              └──────────┬──────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Buat/Batas Sesi      │
                              │  SessionLimitService  │
                              │  ::limit()            │
                              └──────────┬──────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Simpan UserSession   │
                              │  + Audit Log          │
                              └──────────┬──────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Redirect Dashboard   │
                              └──────────────────────┘
```

#### 4.6.2 Autentikasi API (Token-based)

```
┌──────────┐  POST /v1/auth/login  ┌──────────────────┐
│  Client  │ ────────────────────>│ API\AuthController │
│ (Mobile) │                      │    ::login()       │
└──────────┘                      └────────┬─────────┘
                                           │
                                ┌──────────▼──────────┐
                                │  Validasi Kredensial  │
                                └──────────┬──────────┘
                                           │
                                ┌──────────▼──────────┐
                                │  Buat Sanctum Token   │
                                │  + Limit Tokens       │
                                └──────────┬──────────┘
                                           │
                                ┌──────────▼──────────┐
                                │  Return Token + User  │
                                └──────────────────────┘
```

#### 4.6.3 Autentikasi Admin (Gate-based)

```
┌──────────┐  POST /admin/login  ┌──────────────────────┐
│  Browser │ ──────────────────>│ Admin\AuthController   │
│          │                    │    ::login()           │
└──────────┘                    └────────┬─────────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Cek Gate: super_admin│
                              │  Gate::before()       │
                              └──────────┬──────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Buat Sesi + Audit    │
                              └──────────┬──────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Redirect Admin Panel │
                              └──────────────────────┘
```

### 4.7 Diagram Alur Pengiriman Formulir Publik

```
┌──────────┐  GET /form/{slug}   ┌──────────────────┐
│  Publik  │ ──────────────────>│   PageController   │
│          │                    │  ::publicForm()    │
└──────────┘                    └────────┬─────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Load Livewire       │
                              │  PublicForm Component│
                              └──────────┬──────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Validasi Form:       │
                              │  - Status = published?│
                              │  - Belum expired?     │
                              │  - Belum full?        │
                              └──────────┬──────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Render Formulir      │
                              │  Multi-step wizard    │
                              │  + Field validation   │
                              └──────────┬──────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Submit (Livewire)    │
                              │  - Validasi semua field│
                              │  - Cek honeypot       │
                              │  - Simpan submission  │
                              │  - Simpan data        │
                              └──────────┬──────────┘
                                         │
                              ┌──────────▼──────────┐
                              │  Tampilkan Konfirmasi │
                              │  atau Redirect        │
                              └──────────────────────┘
```

### 4.8 Diagram Alur Otorisasi OPD

```
┌──────────┐     ┌─────────────────────────────────┐
│  User    │────>│  FormPolicy::update(User, Form)  │
│ (Admin)  │     └────────────────┬────────────────┘
└──────────┘                      │
                    ┌─────────────▼──────────────┐
                    │  $user->role === 'super_admin'?│
                    └─────────────┬──────────────┘
                          ┌───────┴───────┐
                          │ Ya            │ Tidak
                    ┌─────▼─────┐   ┌─────▼──────────────┐
                    │  izinkan  │   │  $user->id ===       │
                    └───────────┘   │  $form->user_id?     │
                                    └──────────┬──────────┘
                                        ┌──────┴──────┐
                                        │ Ya          │ Tidak
                                  ┌─────▼─────┐ ┌─────▼─────┐
                                  │  izinkan  │ │  tolak    │
                                  └───────────┘ └───────────┘
```

### 4.9 Diagram Alur Manajemen Sesi

```
┌──────────┐  Login  ┌──────────────────┐
│  User    │────────>│ SessionLimitService│
│          │         │ ::canLogin()       │
└──────────┘         └────────┬─────────┘
                              │
                    ┌─────────▼──────────┐
                    │  Hitung sesi aktif   │
                    │  WHERE logged_out_at│
                    │  IS NULL             │
                    └─────────┬──────────┘
                              │
                    ┌─────────▼──────────┐
                    │  Role = super_admin? │
                    └─────────┬──────────┘
                       ┌──────┴──────┐
                       │ Ya          │ Tidak (admin)
                 ┌─────▼─────┐ ┌────▼──────────────┐
                 │ Sesi aktif│ │ Sesi aktif < 3?    │
                 │ = 0?      │ └────┬──────────────┘
                 └─────┬─────┘      │
                  ┌────┴────┐ ┌─────┴─────┐
                  │Ya: login│ │Ya: login   │
                  │Tidak:   │ │Tidak: tolak│
                  │tolak    │ └────────────┘
                  └─────────┘
```

### 4.10 Diagram Alur Mesin Formula (Computed Fields)

```
┌────────────────────────────────────────────┐
│  FormField.type === 'computed'              │
│  FormField.formula = {                      │
│    "ref_field_id": 15,                      │
│    "operation": "multiply",                 │
│    "value": 2                               │
│  }                                          │
└──────────────────┬─────────────────────────┘
                   │
         ┌─────────▼──────────┐
         │  Ambil nilai dari    │
         │  ref_field_id       │
         └─────────┬──────────┘
                   │
         ┌─────────▼──────────┐
         │  Operasi arithmetic │
         │  - multiply: a × b  │
         │  - add: a + b       │
         │  - subtract: a - b  │
         │  - divide: a / b    │
         └─────────┬──────────┘
                   │
         ┌─────────▼──────────┐
         │  Hasil ditampilkan  │
         │  di bidang computed  │
         └──────────────────────┘
```

### 4.11 Diagram Alur Pencatatan Audit

```
┌──────────┐     ┌──────────────────┐
│  User    │────>│  Aksi apapun:     │
│          │     │  - Login/Logout   │
└──────────┘     │  - Buat/Edit Form │
                 │  - Submit Form    │
                 │  - Export Data    │
                 │  - Delete Data    │
                 └────────┬─────────┘
                          │
               ┌──────────▼──────────┐
               │  AuditService::log() │
               └──────────┬──────────┘
                          │
               ┌──────────▼──────────┐
               │  Tangkap:            │
               │  - user_id           │
               │  - action            │
               │  - auditable_type/id │
               │  - old_values        │
               │  - new_values        │
               │  - ip_address        │
               │  - user_agent        │
               └──────────┬──────────┘
                          │
               ┌──────────▼──────────┐
               │  Simpan ke            │
               │  audit_logs table     │
               └──────────────────────┘
```

---

## BAB 5: IMPLEMENTASI

### 5.1 Lingkungan Pengembangan

| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Framework | Laravel | 13.16.1 |
| Bahasa | PHP | 8.3 |
| Template Engine | Blade | - |
| Interaktivitas | Livewire | 4.3.3 |
| CSS | Tailwind CSS | 3 (CDN) |
| Database (Produksi) | MySQL | - |
| Database (Pengujian) | SQLite | In-memory |
| Autentikasi API | Laravel Sanctum | 4 |
| Ekspor PDF | DOMPDF | 3 |
| Ekspor Excel | Maatwebsite Excel | - |
| Grafik | Chart.js | 4.4.7 |
| Pengujian | PHPUnit | 12 |
| Code Style | Laravel Pint | Default config |
| Anti-spam | Honeypot field | Custom |

### 5.2 Triple Authentication System

Project ini menggunakan tiga sistem autentikasi paralel yang masing-masing dirancang untuk kebutuhan berbeda:

#### 5.2.1 Web Authentication (Session-based)

**File:** `app/Http/Controllers/WebAuthController.php`

Sistem autentikasi web menggunakan session Laravel dengan cookie yang di-encrypt. Proses login:

1. Validasi input: email + password
2. `Auth::attempt()` — verifikasi kredensial
3. Cek `SessionLimitService::canLogin()` — pastikan belum melebihi batas sesi
4. `SessionLimitService::limit()` — hapus sesi lama jika melebihi batas
5. Buat record `UserSession` — catat IP, user agent, waktu login
6. Simpan `user_session_id` di Laravel session
7. Catat audit log

```php
// WebAuthController::login() — Alur Utama
if (Auth::attempt($credentials)) {
    if (!SessionLimitService::canLogin($user)) {
        return back()->withErrors([...]);
    }
    SessionLimitService::limit($user);
    $userSession = UserSession::create([...]);
    session(['user_session_id' => $userSession->id]);
    AuditService::log('auth.login', null, 'User logged in');
    return redirect()->intended('/dashboard');
}
```

**Logout:**
1. Update `UserSession.logged_out_at` = waktu sekarang
2. `Auth::logout()` — invalidate session
3. Hapus session cookie
4. Catat audit log

#### 5.2.2 API Authentication (Token-based)

**File:** `app/Http/Controllers/API/AuthController.php`

Sistem autentikasi API menggunakan Sanctum Personal Access Tokens:

1. Validasi input: email + password
2. `Auth::attempt()` — verifikasi kredensial
3. `$user->createToken('auth-token')` — buat token baru
4. `SessionLimitService::limitTokens()` — batasi jumlah token aktif
5. Return token + data user

```php
// API\AuthController::login() — Token Creation
$tokenResult = $user->createToken('auth-token');
$token = $tokenResult->plainTextToken;
SessionLimitService::limitTokens($user);
return response()->json([
    'user' => $user,
    'token' => $token,
]);
```

**Rate Limiting:** Login API dibatasi 5 percobaan per menit menggunakan middleware `throttle:5,1`.

#### 5.2.3 Admin Authentication (Gate-based)

**File:** `app/Http/Controllers/Admin/AuthController.php`

Panel admin menggunakan Gate `super_admin` yang memastikan hanya pengguna dengan role `super_admin` yang dapat mengakses:

```php
// Admin\AuthController — Gate Definition
Gate::before(function ($user) {
    if ($user->isSuperAdmin()) {
        return true;
    }
});
```

### 5.3 OPD-Based Authorization

#### 5.3.1 FormPolicy

**File:** `app/Policies/FormPolicy.php`

Kebijakan otorisasi berbasis peran pengguna dan kepemilikan formulir:

| Method | super_admin | admin (owner) | admin (lain) |
|--------|------------|---------------|--------------|
| `viewAny()` | Ya | Ya | Ya |
| `view()` | Ya | Ya | Tidak |
| `create()` | Ya | Ya | Ya |
| `update()` | Ya | Ya | Tidak |
| `delete()` | Ya | Ya | Tidak |

```php
// FormPolicy::update() — Contoh Implementasi
public function update(User $user, Form $form): bool
{
    if ($user->isSuperAdmin()) {
        return true;
    }
    return $user->id === $form->user_id;
}
```

#### 5.3.2 AuthorizationService (Domain Service)

**File:** `app/Domains/Auth/AuthorizationService.php`

Layanan otorisasi yang lebih granular dengan pendekatan OPD-based:

```php
// AuthorizationService — Otorisasi OPD
public function canViewForm(User $user, Form $form): bool
{
    if ($user->isSuperAdmin()) return true;
    return $user->id === $form->user_id
        || $user->opd_id === $form->opd_id;
}

// Query Scope untuk filtering data
public function applyFormAccessConstraints(Builder $query, User $user): void
{
    if ($user->isSuperAdmin()) return;
    $query->where(function ($q) use ($user) {
        $q->where('user_id', $user->id)
          ->orWhere('opd_id', $user->opd_id);
    });
}
```

### 5.4 Sistem Klasifikasi Data

**File:** `app/Enums/DataClassification.php`

Tiga tingkat klasifikasi data yang mempengaruhi perilaku aplikasi:

| Klasifikasi | Level | canExport() | shouldMaskInLogs() | Retention |
|-------------|-------|-------------|---------------------|-----------|
| `public` | 0 | Ya | Tidak | Tanpa batas |
| `internal` | 1 | Ya | Ya | 365 hari |
| `sensitive` | 2 | Tidak | Ya | 90 hari |

```php
// DataClassification — Methods
public function canExport(): bool
{
    return $this !== self::SENSITIVE;
}

public function shouldMaskInLogs(): bool
{
    return $this->accessLevel() > 0;
}

public function retentionDays(): ?int
{
    return match ($this) {
        self::PUBLIC => null,
        self::INTERNAL => 365,
        self::SENSITIVE => 90,
    };
}
```

**Pengecekan Ekspor:**

```php
// PageController — Ekspor Diblokir untuk Form Sensitif
public function exportCsv(Form $form)
{
    if (!$form->dataClassification->canExport()) {
        abort(403, 'Export not allowed for sensitive data.');
    }
    // ... proses ekspor
}
```

### 5.5 Sistem Manajemen Sesi

#### 5.5.1 SessionLimitService

**File:** `app/Services/SessionLimitService.php`

```php
// Limit Sesi — super_admin: 1, admin: 3
public static function limit(User $user): int
{
    $maxSessions = $user->isSuperAdmin() ? 1 : 3;
    $activeSessions = UserSession::where('user_id', $user->id)
        ->active()
        ->orderByDesc('logged_in_at')
        ->get();

    $toTerminate = $activeSessions->slice($maxSessions);
    foreach ($toTerminate as $session) {
        $session->update(['logged_out_at' => now()]);
    }
    return $toTerminate->count();
}

// Cek Batas Login
public static function canLogin(User $user): bool
{
    $activeCount = UserSession::where('user_id', $user->id)
        ->active()
        ->count();

    if ($user->isSuperAdmin()) {
        return $activeCount === 0;
    }
    return $activeCount < 3;
}
```

#### 5.5.2 SessionTimeout Livewire Component

**File:** `app/Livewire/SessionTimeout.php`

Komponen ini mengimplementasikan timeout sesi 5 menit dengan peringatan pada menit ke-2:

```php
// SessionTimeout — Timeout Logic
public function checkSession(): void
{
    $lastActivity = session('last_activity_at');
    $elapsed = now()->timestamp - $lastActivity;
    $this->remainingSeconds = max(0, 300 - $elapsed);

    if ($this->remainingSeconds <= 0) {
        $this->expired = true;
        $this->logout();
    } elseif ($this->remainingSeconds <= 120) {
        $this->showWarning = true;
    }
}

public function logout(): void
{
    $sessionId = session('user_session_id');
    if ($sessionId) {
        UserSession::where('id', $sessionId)
            ->update(['logged_out_at' => now()]);
    }
    AuditService::log('auth.logout', null, 'Session timeout');
    Auth::logout();
    session()->invalidate();
    redirect('/login');
}
```

### 5.6 Sistem Notifikasi

#### 5.6.1 NotificationService

**File:** `app/Services/NotificationService.php`

```php
// Notifikasi ke seluruh super_admin
public static function notifySuperAdmins(
    string $type,
    string $message,
    array $data = []
): void {
    $actor = Auth::user();
    $displayName = $actor?->currentDisplayName() ?? $actor?->name ?? 'System';
    $fullName = $actor?->name ?? 'System';
    $roleLabel = $actor?->isSuperAdmin() ? 'Super Admin' : 'Admin';

    $fullMessage = "{$roleLabel} {$displayName} ({$fullName}) {$message}";

    User::where('role', 'super_admin')
        ->where('id', '!=', $actor?->id)
        ->each(function ($admin) use ($type, $fullMessage, $data, $actor) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => $type,
                'message' => $fullMessage,
                'data' => array_merge($data, [
                    'actor_id' => $actor?->id,
                    'actor_name' => $actor?->name,
                    'actor_email' => $actor?->email,
                    'actor_role' => $actor?->role,
                ]),
            ]);
        });
}
```

### 5.7 Pencatatan Audit

#### 5.7.1 AuditService

**File:** `app/Services/AuditService.php`

```php
// AuditService — Facade Pattern
public static function log(
    string $action,
    $model = null,
    ?string $description = null,
    ?array $oldValues = null,
    ?array $newValues = null
): void {
    AuditLog::create([
        'user_id' => Auth::id(),
        'action' => $action,
        'auditable_type' => $model ? get_class($model) : null,
        'auditable_id' => $model?->id,
        'description' => $description,
        'old_values' => $oldValues,
        'new_values' => $newValues,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
}
```

**Jenis Aksi yang Dicatat:**

| Aksi | Keterangan | Model Terkait |
|------|------------|---------------|
| `auth.login` | Pengguna login | - |
| `auth.logout` | Pengguna logout | - |
| `form.created` | Formulir dibuat | Form |
| `form.updated` | Formulir diperbarui | Form |
| `form.published` | Formulir dipublikasikan | Form |
| `form.closed` | Formulir ditutup | Form |
| `form.deleted` | Formulir dihapus | Form |
| `submission.created` | Data dikirim | FormSubmission |
| `submission.deleted` | Data dihapus | FormSubmission |
| `export.csv` | Ekspor CSV | Form |
| `export.pdf` | Ekspor PDF | Form |
| `user.created` | Pengguna dibuat | User |
| `user.updated` | Pengguna diperbarui | User |
| `user.deleted` | Pengguna dihapus | User |
| `settings.updated` | Pengaturan diubah | Form |

### 5.8 Livewire Components — Detail Implementasi

#### 5.8.1 PublicForm (Komponen Terkompleks)

**File:** `app/Livewire/PublicForm.php`

Komponen ini menangani seluruh proses rendering dan pengiriman formulir publik. Fitur utama:

**Multi-Step Wizard:**
```php
// PublicForm — Mount & Step Calculation
public function mount(string $slug): void
{
    $this->form = Form::where('slug', $slug)
        ->published()
        ->with('fields', fn($q) => $q->orderBy('order'))
        ->with('sections', fn($q) => $q->orderBy('order'))
        ->firstOrFail();

    // Validasi: expired check, max submissions check
    if ($this->form->isExpired()) { abort(410); }
    if ($this->form->isFull()) { abort(410); }

    // Build steps from sections
    $this->steps = $this->form->sections->map(fn($s) => [
        'id' => $s->id,
        'title' => $s->title,
        'fields' => $this->form->fields
            ->where('section_id', $s->id)
            ->where('is_admin_only', false),
    ])->toArray();
    $this->totalSteps = count($this->steps);
}
```

**Formula Engine (Computed Fields):**
```php
// PublicForm — calculateComputedFields()
public function calculateComputedFields(): void
{
    foreach ($this->form->fields->where('type', 'computed') as $field) {
        $formula = $field->formula;
        $refValue = (float) ($this->responses[$formula['ref_field_id']] ?? 0);
        $constValue = (float) ($formula['value'] ?? 0);

        $this->responses[$field->id] = match ($formula['operation']) {
            'multiply' => $refValue * $constValue,
            'add' => $refValue + $constValue,
            'subtract' => $refValue - $constValue,
            'divide' => $constValue != 0 ? $refValue / $constValue : 0,
            default => 0,
        };
    }
}
```

**Pencarian Peserta:**
```php
// PublicForm — Autocomplete Participant Search
public function updatedParticipantSearch(): void
{
    if (strlen($this->participantSearch) < 2) {
        $this->participantResults = [];
        return;
    }
    $this->participantResults = Participant::where('nama', 'like', "%{$this->participantSearch}%")
        ->limit(20)
        ->get();
}

public function selectParticipant(int $id): void
{
    $participant = Participant::findOrFail($id);
    $this->selectedParticipantId = $id;
    // Auto-fill fields based on label matching
    foreach ($this->form->fields as $field) {
        $lower = strtolower($field->label);
        if (str_contains($lower, 'nama')) {
            $this->responses[$field->id] = $participant->nama;
        } elseif (str_contains($lower, 'instansi') || str_contains($lower, 'opd')) {
            $this->responses[$field->id] = $participant->opd_institusi;
        } elseif (str_contains($lower, 'jabatan')) {
            $this->responses[$field->id] = $participant->jabatan;
        }
    }
    $this->calculateComputedFields();
}
```

**Anti-Spam Honeypot:**
```php
// PublicForm — Honeypot Check
public string $company_website = ''; // Hidden field, should be empty

public function submitForm(): void
{
    if (!empty($this->company_website)) {
        return; // Spam detected, silently reject
    }
    // ... normal submission logic
}
```

#### 5.8.2 FormEditor (Komponen Kedua Terkompleks)

**File:** `app/Livewire/FormEditor.php`

**Fitur Utama:**
- 25+ properti publik untuk state management
- CRUD untuk fields dan sections
- Drag-and-drop reordering
- Pengaturan formulir (settings)
- Publikasi dan penutupan formulir

```php
// FormEditor — Field Reordering
public function moveFieldUp(int $fieldId): void
{
    $field = $this->form->fields->firstWhere('id', $fieldId);
    $prevField = $this->form->fields
        ->where('order', '<', $field->order)
        ->sortByDesc('order')
        ->first();

    if ($prevField) {
        $tempOrder = $field->order;
        $field->update(['order' => $prevField->order]);
        $prevField->update(['order' => $tempOrder]);
        $this->form->load('fields', fn($q) => $q->orderBy('order'));
    }
}
```

#### 5.8.3 ActivityChart

**File:** `app/Livewire/ActivityChart.php`

Grafik aktivitas dengan 5 rentang waktu dan level detail yang berbeda:

```php
// ActivityChart — Data Aggregation
public function getChartDataProperty(): array
{
    $query = FormSubmission::query();
    if (!auth()->user()->isSuperAdmin()) {
        $query->whereHas('form', fn($q) =>
            $q->where('user_id', auth()->id()));
    }

    return match ($this->range) {
        '7d' => $query->where('submitted_at', '>=', now()->subDays(7))
            ->groupBy('date')->pluck('count'),
        '1m' => /* daily granularity */,
        '3m' => /* weekly buckets */,
        '6m' => /* monthly buckets */,
        '1y' => /* monthly buckets */,
    };
}
```

### 5.9 Sistem Ekspor Data

#### 5.9.1 Ekspor CSV (Streaming)

```php
// PageController::exportCsv() — Streaming dengan Chunking
public function exportCsv(Form $form)
{
    if (!$form->dataClassification->canExport()) {
        abort(403);
    }

    $fileName = Str::slug($form->title) . '-submissions.csv';
    $headers = ['Content-Type' => 'text/csv'];

    return response()->stream(function () use ($form) {
        $handle = fopen('php://output', 'w');
        // Header row
        fputcsv($handle, ['No', 'Submitted At', 'IP Address', ...$fieldLabels]);
        // Data rows (chunked)
        $form->submissions()->with('data')->chunk(100, function ($submissions) use ($handle) {
            foreach ($submissions as $i => $sub) {
                fputcsv($handle, [$i + 1, $sub->submitted_at, $sub->ip_address, ...]);
            }
        });
        fclose($handle);
    }, 200, $headers);
}
```

#### 5.9.2 Ekspor PDF (DomPDF)

```php
// PageController::exportPdf()
$pdf = Pdf::loadView('exports.pdf.submissions', [
    'form' => $form,
    'submissions' => $form->submissions()->with('data.field')->get(),
    'fields' => $form->fields()->orderBy('order')->get(),
]);
return $pdf->download($fileName);
```

#### 5.9.3 Ekspor Uang Saku

Format khusus kwitansi pemerintah untuk klaim uang saku. PDF dalam format portrait A4 dengan data nominal yang dihitung dari field tertentu.

#### 5.9.4 Ekspor Presensi

Format daftar hadir dengan orientasi landscape A4. Menggunakan `memory_limit(2G)` untuk dataset besar dan chunking data.

### 5.10 Sistem Pencarian Peserta

Fitur autocomplete untuk formulir kehadiran (presensi):

1. Pengguna mengetik minimal 2 karakter di field pencarian
2. Livewire mengirim query ke tabel `participants` (LIKE search)
3. Hasil ditampilkan dalam dropdown (maksimal 20)
4. Saat dipilih, field formulir terkait terisi otomatis:
   - Field dengan label mengandung "nama" → isi nama peserta
   - Field dengan label mengandung "instansi" atau "opd" → isi OPD
   - Field dengan label mengandung "jabatan" → isi jabatan
5. Bidang computed dihitung ulang otomatis

### 5.11 Pengujian

#### 5.11.1 Konfigurasi Pengujian

```xml
<!-- phpunit.xml — SQLite In-Memory -->
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

#### 5.11.2 Cakupan Pengujian

| Test File | Jumlah Test | Asersi | Fungsi |
|-----------|-------------|--------|--------|
| `LoginSessionTest.php` | 4 | 12 | Login, logout, session |
| `DisplayNamePromptTest.php` | 5 | 15 | Nama tampilan sesi |
| `NotificationServiceTest.php` | 5 | 15 | Notifikasi super_admin |
| `UserDisplayNameTest.php` | 5 | 15 | Fallback display name |
| `UserSessionTest.php` | 4 | 12 | Model & scope sesi |
| `ExampleTest.php` | 1 | 1 | HTTP 200 check |
| **Total** | **20** | **70** | |

#### 5.11.3 Contoh Test

```php
// LoginSessionTest — Menguji Pembuatan Sesi
public function test_login_creates_user_session_record(): void
{
    $user = User::factory()->create([
        'role' => 'admin',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('user_sessions', [
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
    ]);
}
```

### 5.12 Struktur Rute

#### 5.12.1 Rute Web (Session-based)

| Metode | Rute | Controller | Keterangan |
|--------|------|------------|------------|
| GET | `/login` | PageController@login | Halaman login |
| POST | `/login` | WebAuthController@login | Proses login |
| POST | `/logout` | WebAuthController@logout | Proses logout |
| GET | `/dashboard` | PageController@dashboard | Dashboard utama |
| GET | `/forms` | PageController@formsIndex | Daftar formulir |
| GET | `/forms/create` | PageController@formsCreate | Formulir baru |
| GET | `/forms/{form}` | PageController@formsShow | Detail formulir |
| GET | `/forms/{form}/edit` | PageController@formsEdit | Edit formulir |
| POST | `/forms/{form}/duplicate` | PageController@duplicateForm | Duplikasi |
| GET | `/forms/{form}/analytics` | PageController@formsAnalytics | Analitik |
| GET | `/forms/{form}/submissions` | PageController@submissionsIndex | Daftar submission |
| GET | `/forms/{form}/submissions/{sub}` | PageController@submissionsShow | Detail submission |
| POST | `/forms/{form}/submissions/{sub}/delete` | Hapus submission | |
| POST | `/forms/{form}/submissions/bulk-delete` | Hapus bulk | |
| GET | `/forms/{form}/export/csv` | exportCsv | |
| GET | `/forms/{form}/export/xlsx` | exportXlsx | |
| GET | `/forms/{form}/export/pdf` | exportPdf | |
| GET | `/forms/{form}/export/uang-saku` | exportUangSakuPdf | |
| GET | `/forms/{form}/export/presensi` | exportPresensiPdf | |
| GET | `/users` | PageController@usersIndex | Daftar pengguna |
| GET | `/change-password` | PageController@changePassword | Ganti password |
| POST | `/change-password` | WebAuthController@changePassword | Proses ganti |
| GET | `/notifications` | NotificationController@index | Notifikasi |
| GET | `/form/{slug}` | PageController@publicForm | Formulir publik |

#### 5.12.2 Rute API (Sanctum Token)

| Metode | Rute | Controller | Autentikasi |
|--------|------|------------|------------|
| POST | `/v1/auth/login` | API\AuthController@login | Publik (throttle:5,1) |
| POST | `/v1/auth/logout` | API\AuthController@logout | Sanctum |
| GET | `/v1/auth/me` | API\AuthController@me | Sanctum |
| POST | `/v1/auth/change-password` | API\AuthController@changePassword | Sanctum |
| GET | `/v1/dashboard/stats` | API\DashboardController@stats | Sanctum |
| GET | `/v1/dashboard/recent-forms` | API\DashboardController@recentForms | Sanctum |
| GET | `/v1/forms` | API\FormCrudController@index | Sanctum |
| POST | `/v1/forms` | API\FormCrudController@store | Sanctum |
| GET | `/v1/forms/{form}` | API\FormCrudController@show | Sanctum |
| PUT | `/v1/forms/{form}` | API\FormCrudController@update | Sanctum |
| DELETE | `/v1/forms/{form}` | API\FormCrudController@destroy | Sanctum |
| POST | `/v1/forms/{form}/duplicate` | API\FormCrudController@duplicate | Sanctum |
| POST | `/v1/forms/{form}/publish` | API\FormCrudController@publish | Sanctum |
| POST | `/v1/forms/{form}/close` | API\FormCrudController@close | Sanctum |
| GET | `/v1/forms/{form}/analytics` | API\FormCrudController@analytics | Sanctum |
| GET | `/v1/forms/{form}/export/csv` | API\FormCrudController@exportCsv | Sanctum |
| POST | `/v1/forms/{form}/fields` | API\FieldController@store | Sanctum |
| PUT | `/v1/forms/{form}/fields/{field}` | API\FieldController@update | Sanctum |
| DELETE | `/v1/forms/{form}/fields/{field}` | API\FieldController@destroy | Sanctum |
| POST | `/v1/forms/{form}/fields/reorder` | API\FieldController@reorder | Sanctum |
| GET | `/v1/forms/{form}/submissions` | API\SubmissionApiController@index | Sanctum |
| GET | `/v1/forms/{form}/submissions/{sub}` | API\SubmissionApiController@show | Sanctum |
| DELETE | `/v1/forms/{form}/submissions/{sub}` | API\SubmissionApiController@destroy | Sanctum |
| GET | `/v1/forms/public/{slug}` | API\SubmissionApiController@showPublic | Publik |
| POST | `/v1/forms/public/{slug}` | API\SubmissionApiController@store | Publik (throttle:10,1) |
| GET | `/v1/users` | API\UserController@index | Sanctum + super_admin |
| POST | `/v1/users` | API\UserController@store | Sanctum + super_admin |
| PUT | `/v1/users/{user}` | API\UserController@update | Sanctum + super_admin |
| DELETE | `/v1/users/{user}` | API\UserController@destroy | Sanctum + super_admin |

#### 5.12.3 Rute Admin

| Metode | Rute | Controller | Autentikasi |
|--------|------|------------|------------|
| GET | `/admin/login` | Admin\AuthController@loginForm | Publik |
| POST | `/admin/login` | Admin\AuthController@login | Publik |
| POST | `/admin/logout` | Admin\AuthController@logout | Auth + super_admin |
| GET | `/admin/` | Admin\DashboardController@index | Auth + super_admin |
| GET | `/admin/dashboard` | Admin\DashboardController@index | Auth + super_admin |
| GET | `/admin/users` | Admin\UserController@index | Auth + super_admin |
| GET | `/admin/users/create` | Admin\UserController@create | Auth + super_admin |
| POST | `/admin/users` | Admin\UserController@store | Auth + super_admin |
| GET | `/admin/users/{user}/edit` | Admin\UserController@edit | Auth + super_admin |
| PUT | `/admin/users/{user}` | Admin\UserController@update | Auth + super_admin |
| DELETE | `/admin/users/{user}` | Admin\UserController@destroy | Auth + super_admin |
| GET | `/admin/forms` | Admin\FormController@index | Auth + super_admin |
| GET | `/admin/forms/{form}` | Admin\FormController@show | Auth + super_admin |

### 5.13 Fase Pengembangan

#### Fase 1: Setup Awal (23 Juni 2026) — 40 WH

Pada fase ini, dilakukan pengaturan awal proyek meliputi:

- Instalasi Laravel 13 dan dependensi yang diperlukan
- Konfigurasi basis data MySQL
- Pembuatan struktur migrasi untuk seluruh tabel
- Pembuatan model Eloquent dan relasi antar tabel
- Konfigurasi autentikasi menggunakan Sanctum
- Pembuatan pengontrol (controller) untuk API autentikasi
- Pembuatan rute (routes) untuk API dan web
- Pengaturan middleware untuk otorisasi

**Hasil:** Aplikasi Laravel dasar dengan autentikasi API yang berfungsi.

#### Fase 2: Pengembangan Fitur Utama (24-30 Juni 2026) — 80 WH

Pada fase ini, dilakukan pengembangan fitur-fitur utama aplikasi:

**Pembuatan Formulir:**
- Pengontrol FormCrudController untuk operasi CRUD formulir
- Pengontrol FieldController untuk pengelolaan bidang
- Model Form dengan 14 jenis bidang (text, textarea, email, number, date, time, select, radio, checkbox, file, heading, paragraph, signature, computed)
- Logika publikasi dan penutupan formulir
- Fitur duplikasi formulir

**Formulir Publik:**
- Komponen Livewire PublicForm untuk tampilan formulir publik
- Validasi real-time per bidang
- Pencarian peserta dari tabel participants
- Mesin formula untuk bidang computed
- Multi-step wizard berdasarkan section
- Honeypot anti-spam

**Pengumpulan Data:**
- Pengontrol SubmissionApiController untuk operasi pengumpulan data
- Daftar pengumpulan data dengan paginasi
- Detail pengumpulan data per bidang
- Hapus pengumpulan data (single dan bulk)

**Ekspor Data:**
- Ekspor CSV menggunakan streaming + chunking
- Ekspor PDF menggunakan DomPDF
- Ekspor XLSX menggunakan Maatwebsite Excel
- Format khusus Uang Saku dan Presensi

**Hasil:** Aplikasi dengan fitur pembuatan formulir, pengisian publik, dan ekspor data yang berfungsi.

#### Fase 3: Pengembangan Antarmuka (1-7 Juli 2026) — 60 WH

Pada fase ini, dilakukan pengembangan antarmuka pengguna:

**Dashboard:**
- Dashboard utama dengan statistik formulir dan pengumpulan data
- Dashboard admin terpisah
- Grafik aktivitas menggunakan Chart.js dengan 5 rentang waktu

**Tampilan Formulir:**
- Daftar formulir dengan penyaringan status
- Detail formulir dengan statistik
- Editor formulir dengan drag-and-drop field builder

**Tampilan Pengumpulan Data:**
- Daftar pengumpulan data dengan paginasi
- Detail pengumpulan data per bidang
- Analitik pengumpulan data (distribusi nilai per bidang)

**Antarmuka Admin:**
- Panel admin terpisah
- Pengelolaan pengguna (CRUD)
- Pengelolaan formulir (read-only)

**Hasil:** Antarmuka web yang lengkap dengan dashboard, tampilan formulir, dan panel admin.

#### Fase 4: Keamanan dan Audit (8-14 Juli 2026) — 50 WH

Pada fase ini, dilakukan penguatan keamanan dan pencatatan audit:

**Autentikasi dan Otorisasi:**
- Tiga sistem login paralel (Web, API, Admin)
- Otorisasi berdasarkan peran (super_admin dan admin)
- Pengendalian akses berdasarkan OPD menggunakan FormPolicy
- AuthorizationService dengan query scoping

**Manajemen Sesi:**
- Batas waktu sesi tidak aktif 5 menit
- Pembatasan jumlah sesi aktif (super_admin: 1, admin: 3)
- Pelacakan sesi aktif per pengguna

**Pencatatan Audit:**
- Service AuditService untuk pencatatan aktivitas
- Pencatatan login, logout, pembuatan formulir, pengiriman data
- Tabel audit_logs dengan detail aktivitas
- 15+ jenis aksi yang dicatat

**Klasifikasi Data:**
- Tiga tingkat klasifikasi: publik, internal, sensitif
- Pemblokiran ekspor untuk formulir sensitif
- Pelindungan data berdasarkan klasifikasi

**Notifikasi:**
- NotificationService untuk notifikasi super_admin
- NotificationBell Livewire component
- Pesan notifikasi dengan display name sesi

**Hasil:** Aplikasi dengan sistem keamanan yang kuat dan pencatatan audit yang lengkap.

#### Fase 5: Optimasi dan Pengujian (15-21 Juli 2026) — 50 WH

Pada fase ini, dilakukan optimasi dan pengujian:

**Optimasi:**
- Optimasi query database untuk dashboard
- Optimasi ekspor CSV dengan chunking (100 records per batch)
- Penambahan indeks pada kolom yang sering diakses
- Streaming response untuk ekspor data besar

**Pengujian:**
- Pembuatan pengujian untuk autentikasi (4 test)
- Pembuatan pengujian untuk sesi pengguna (4 test)
- Pembuatan pengujian untuk layanan notifikasi (5 test)
- Pembuatan pengujian untuk display name (5 test)
- Total 20 pengujian dengan 70 asersi

**Pembersihan Kode:**
- Refactoring kode untuk meningkatkan keterbacaan
- Penerapan gaya kode menggunakan Laravel Pint
- Penghapusan kode yang tidak terpakai

**Hasil:** Aplikasi yang dioptimasi dan memiliki cakupan pengujian yang memadai.

#### Fase 6: Penyelesaian Akhir (22-28 Juli 2026) — 40 WH

Pada fase ini, dilakukan penyelesaian akhir dan dokumentasi:

**Fitur Terakhir:**
- Komponen Livewire ActivityChart untuk grafik aktivitas
- Komponen Livewire SessionTimeout untuk timeout sesi
- Komponen Livewire DisplayNamePrompt untuk nama tampilan
- Komponen Livewire NotificationBell untuk notifikasi

**Pencatatan Aktivitas:**
- Pencatatan aktivitas melihat formulir
- Pencatatan aktivitas mengekspor data
- Pencatatan aktivitas mengubah pengaturan

**Dokumentasi:**
- Dokumentasi arsitektur lengkap (ARCHITECTURE.md)
- Dokumentasi daftar tugas (MASTER-TASK-LIST.md)
- Dokumentasi umum proyek (PROJECT-OVERVIEW.md)
- Laporan magang ini

**Hasil:** Aplikasi yang lengkap dengan dokumentasi yang memadai.

---

## BAB 6: HASIL DAN PEMBAHASAN

### 6.1 Hasil Pengembangan

Aplikasi yang berhasil dikembangkan memiliki karakteristik sebagai berikut:

| Metrik | Jumlah |
|--------|--------|
| Berkas PHP | 220+ |
| Berkas Blade | 41 |
| Total Baris Kode PHP | 20.200+ |
| Tabel Basis Data | 11 utama + infrastruktur |
| Domain Services | 6 |
| Controllers | 14 |
| Livewire Components | 7 |
| Eloquent Models | 11 |
| App Services | 5 |
| Enums | 4 |
| Routes (Web) | 25+ |
| Routes (API) | 30+ |
| Routes (Admin) | 13 |
| Migrasi | 26 |
| File Pengujian | 7 |
| Total Test | 20 |
| Total Asersi | 70 |
| Status Pengujian | Semua Berhasil |

### 6.2 Fitur yang Berhasil Dikembangkan

| No | Fitur | Status | Komponen Utama |
|----|-------|--------|----------------|
| 1 | Pembuatan formulir dengan 14 jenis bidang | Selesai | FormEditor, CreateForm |
| 2 | Formulir publik tanpa login | Selesai | PublicForm |
| 3 | Multi-step wizard | Selesai | PublicForm (section-based) |
| 4 | Mesin formula (computed fields) | Selesai | PublicForm::calculateComputedFields() |
| 5 | Pencarian peserta autocomplete | Selesai | PublicForm::updatedParticipantSearch() |
| 6 | Dashboard analitik dengan grafik | Selesai | ActivityChart, PageController |
| 7 | Pengelolaan pengguna berdasarkan peran | Selesai | Admin\UserController, API\UserController |
| 8 | Pengendalian akses berdasarkan OPD | Selesai | FormPolicy, AuthorizationService |
| 9 | Ekspor CSV (streaming) | Selesai | PageController::exportCsv() |
| 10 | Ekspor PDF | Selesai | PageController::exportPdf() |
| 11 | Ekspor XLSX | Selesai | PageController::exportXlsx() |
| 12 | Ekspor Uang Saku | Selesai | PageController::exportUangSakuPdf() |
| 13 | Ekspor Presensi | Selesai | PageController::exportPresensiPdf() |
| 14 | Pencatatan audit | Selesai | AuditService, 15+ jenis aksi |
| 15 | Manajemen sesi dengan batasan | Selesai | SessionLimitService |
| 16 | Timeout sesi 5 menit | Selesai | SessionTimeout |
| 17 | Klasifikasi data (publik, internal, sensitif) | Selesai | DataClassification enum |
| 18 | Notifikasi real-time | Selesai | NotificationService, NotificationBell |
| 19 | Nama tampilan per sesi | Selesai | DisplayNamePrompt |
| 20 | Triple authentication system | Selesai | Web, API, Admin auth |
| 21 | Honeypot anti-spam | Selesai | PublicForm::$company_website |
| 22 | Duplikasi formulir | Selesai | FormEditor, FormManagementService |

### 6.3 Teknologi yang Dipelajari dan Diterapkan

| No | Teknologi | Penerapan | Tingkat Penguasaan |
|----|-----------|-----------|-------------------|
| 1 | Laravel 13 | Framework utama aplikasi | Lanjut |
| 2 | Livewire 4 | Komponen interaktif tanpa JavaScript | Lanjut |
| 3 | Blade | Templat engine untuk antarmuka | Mahir |
| 4 | Tailwind CSS | Gaya antarmuka utility-first | Mahir |
| 5 | MySQL | Basis data utama | Menengah |
| 6 | Sanctum | Autentikasi API | Menengah |
| 7 | DOMPDF | Ekspor PDF | Menengah |
| 8 | Maatwebsite Excel | Ekspor CSV/Excel | Menengah |
| 9 | Chart.js | Grafik aktivitas | Dasar |
| 10 | PHPUnit | Pengujian kode | Menengah |
| 11 | Domain-Driven Design | Arsitektur aplikasi | Menengah |
| 12 | Modular Monolith | Struktur project | Dasar |

### 6.4 Tantangan dan Solusi

#### Tantangan 1: Komponen Livewire Tidak Merender Ulang

**Masalah:** Komponen Livewire menggunakan morphing, sehingga tag script biasa tidak dijalankan ulang saat data berubah.

**Solusi:** Menggunakan arsitektur `@script` dengan `Livewire.on()` untuk menangani perubahan data dan menjalankan ulang kode JavaScript.

```html
@script
<script>
    Livewire.on('chartUpdated', (data) => {
        // Re-initialize Chart.js dengan data baru
        updateChart(data.labels, data.data);
    });
</script>
@endscript
```

#### Tantangan 2: Otorisasi Berbasis OPD

**Masalah:** Pengendalian akses harus mempertimbangkan peran pengguna dan keanggotaan OPD secara bersamaan.

**Solusi:** Membuat `FormPolicy` yang memeriksa peran `super_admin` terlebih dahulu, kemudian membandingkan `opd_id` pengguna dengan `opd_id` formulir.

```php
// Solusi: Hierarki otorisasi
public function update(User $user, Form $form): bool
{
    return $user->isSuperAdmin() || $user->id === $form->user_id;
}
```

#### Tantangan 3: Ekspor Data Besar

**Masalah:** Ekspor data dalam jumlah besar dapat menyebabkan kehabisan memori.

**Solusi:** Menggunakan streaming response dengan chunking untuk memproses data dalam batch kecil (100 records per chunk) dan flushing buffer secara berkala.

```php
// Solusi: Streaming + Chunking
return response()->stream(function () use ($form) {
    $handle = fopen('php://output', 'w');
    $form->submissions()->chunk(100, function ($subs) use ($handle) {
        foreach ($subs as $sub) {
            fputcsv($handle, [...]);
        }
    });
    fclose($handle);
}, 200, ['Content-Type' => 'text/csv']);
```

#### Tantangan 4: Manajemen Sesi Bersamaan

**Masalah:** Pengguna dapat login dari banyak perangkat secara bersamaan, yang dapat menimbulkan risiko keamanan.

**Solusi:** Membatasi jumlah sesi aktif berdasarkan peran (`super_admin`: 1, `admin`: 3) dan menandai sesi yang tidak aktif.

```php
// Solusi: Pembatasan sesi
$activeSessions = UserSession::where('user_id', $user->id)
    ->active()->orderByDesc('logged_in_at')->get();
$toTerminate = $activeSessions->slice($maxSessions);
foreach ($toTerminate as $session) {
    $session->update(['logged_out_at' => now()]);
}
```

#### Tantangan 5: Konsistensi Display Name

**Masalah:** Nama pengguna di tabel `users` adalah nama akun, tetapi pengguna ingin menampilkan nama yang berbeda per sesi (misal: nama panggilan).

**Solusi:** Menyimpan `display_name` di tabel `user_sessions` (bukan di `users`), dengan urutan prioritas: session display_name → user display_name → user name.

```php
// Solusi: Fallback chain
public function currentDisplayName(): string
{
    $sessionId = session('user_session_id');
    if ($sessionId) {
        $session = UserSession::find($sessionId);
        if ($session && $session->display_name) {
            return $session->display_name;
        }
    }
    return $this->display_name ?? $this->name;
}
```

#### Tantangan 6: Notifikasi yang Tepat Sasaran

**Masalah:** Super admin perlu mengetahui aktivitas admin di bawahnya, tetapi admin tidak perlu menerima notifikasi aktivitas admin lain.

**Solusi:** `NotificationService::notifySuperAdmins()` hanya mengirim notifikasi ke pengguna dengan role `super_admin`, kecuali pelaku aksi itu sendiri.

```php
// Solusi: Notifikasi selektif
User::where('role', 'super_admin')
    ->where('id', '!=', $actor?->id)
    ->each(function ($admin) use ($notification) {
        Notification::create([...]);
    });
```

### 6.5 Analisis Komparatif

#### Komparasi dengan Google Forms

| Aspek | Google Forms | Project KBB |
|-------|-------------|-------------|
| Hosting | Cloud (Google) | Mandiri (server pemerintah) |
| Otorisasi OPD | Tidak ada | Ya, berdasarkan OPD |
| Audit Trail | Terbatas | Lengkap (15+ jenis aksi) |
| Klasifikasi Data | Tidak ada | 3 tingkat (publik, internal, sensitif) |
| Format Ekspor | CSV, Excel | CSV, PDF, XLSX, Uang Saku, Presensi |
| Batas Sesi | Tidak ada | super_admin: 1, admin: 3 |
| Timeout Sesi | 30 hari | 5 menit |
| Custom Domain | Ya | Ya |
| Biaya | Gratis (terbatas) | Gratis (open source) |
| Privasi Data | Data di server Google | Data di server pemerintah |

#### Komparasi Arsitektur

| Aspek | Monolith Tradisional | Project KBB (Modular Monolith) |
|-------|---------------------|-------------------------------|
| Struktur | Flat (semua di satu folder) | Domain-based (6 domain) |
| Ketergantungan | Tinggi (saling ketergantungan) | Rendah (domain terpisah) |
| Pengujian | Sulit | Lebih mudah per domain |
| Skalabilitas | Terbatas | Dapat di-scale ke microservice |
| Pemeliharaan | Sulit untuk codebase besar | Lebih mudah dengan domain boundary |

---

## BAB 7: KESIMPULAN DAN SARAN

### 7.1 Kesimpulan

Berdasarkan kegiatan magang yang telah dilaksanakan, dapat disimpulkan bahwa:

1. **Aplikasi berhasil dikembangkan** — Aplikasi formulir digital untuk Pemerintah Kabupaten Bandung Barat berhasil dikembangkan dengan menggunakan framework Laravel 13, Livewire 4, dan MySQL dengan total 320 Work Hours.

2. **Fitur lengkap** — Aplikasi memiliki 22+ fitur meliputi pembuatan formulir (14 jenis bidang), pengisian publik multi-step, mesin formula, pencarian peserta, dashboard analitik, pengelolaan pengguna, pengendalian akses berdasarkan OPD, 5 format ekspor, pencatatan audit, manajemen sesi, dan notifikasi real-time.

3. **Arsitektur terstruktur** — Arsitektur Modular Monolith dengan Domain-Driven Design terbukti efektif untuk mengorganisir kode aplikasi berskala menengah dengan 6 domain, 14 controllers, 7 Livewire components, dan 5 services.

4. **Keamanan berlapis** — Sistem keamanan yang diterapkan mencakup triple authentication (web, API, admin), otorisasi berbasis peran dan OPD, batas waktu sesi, pembatasan jumlah sesi, klasifikasi data, dan pencatatan audit untuk 15+ jenis aksi.

5. **Pengujian memadai** — 20 pengujian dengan 70 asersi mencakup autentikasi, sesi, notifikasi, dan display name. Semua pengujian berhasil (passing).

6. **Dokumentasi lengkap** — Arsitektur, daftar tugas, dan laporan magang telah didokumentasikan dengan baik.

### 7.2 Saran

Saran untuk pengembangan lebih lanjut:

#### 7.2.1 Prioritas Tinggi (Tech Debt)

1. **Integrasi Domain Service ke Controller:** Saat ini domain services sudah diimplementasikan tetapi belum digunakan oleh controllers. Pengintegrasian ini akan meningkatkan keterpisahan logika bisnis dan membuat kode lebih mudah dipelihara.

2. **Penerapan OPD-Based Query Scoping:** `AuthorizationService` memiliki method `applyFormAccessConstraints()` dan `applySubmissionAccessConstraints()` yang belum diimplementasikan di controller. Ini akan memastikan data OPD hanya diakses oleh user yang berwenang.

3. **Pemblokiran Ekspor untuk Data Sensitif:** Meskipun `DataClassification::canExport()` sudah diimplementasikan, ada beberapa jalur ekspor yang belum mengecek klasifikasi data.

#### 7.2.2 Prioritas Menengah

4. **Peningkatan Cakupan Pengujian:** Membuat pengujian untuk setiap fitur (form CRUD, submission, export) akan meningkatkan keandalan aplikasi. Target: 100+ tests.

5. **Dokumentasi API:** Membuat dokumentasi API yang lengkap (menggunakan Swagger/OpenAPI) akan memudahkan integrasi dengan sistem lain di masa mendatang.

6. **Rate Limiting yang Lebih Ketat:** Menambahkan rate limiting untuk seluruh API endpoint, bukan hanya login, akan meningkatkan keamanan.

#### 7.2.3 Prioritas Rendah

7. **Migrasi Filter OPD:** Mengganti penggunaan `user_id` dengan `opd_id` untuk pengendalian akses akan mendukung skenario di mana satu OPD memiliki lebih dari satu admin.

8. **Background Job untuk Ekspor Besar:** Menggunakan Laravel Queue untuk ekspor data dalam jumlah sangat besar akan meningkatkan pengalaman pengguna.

9. **Real-time Updates:** Menambahkan WebSocket/Laravel Reverb untuk update notifikasi real-time tanpa refresh halaman.

10. **Mobile Responsive:** Optimasi lebih lanjut untuk tampilan mobile, terutama pada formulir publik.

### 7.3 Penutup

Kegiatan magang ini memberikan pengalaman berharga dalam pengembangan aplikasi web berskala production untuk instansi pemerintah. Dari kegiatan ini, penulis mempelajari pentingnya arsitektur yang terstruktur, keamanan berlapis, dan dokumentasi yang memadai dalam pengembangan perangkat lunak.

Aplikasi yang dikembangkan diharapkan dapat membantu Pemerintah Kabupaten Bandung Barat dalam pengumpulan data yang lebih efisien, aman, dan teraudit.

---

## LAMPIRAN

### Lampiran 1: Rincian Jam Kerja

| Fase | Kegiatan | Jam Kerja | Tanggal |
|------|----------|-----------|---------|
| Fase 1 | Setup Awal | 40 WH | 23 Juni 2026 |
| Fase 2 | Pengembangan Fitur Utama | 80 WH | 24-30 Juni 2026 |
| Fase 3 | Pengembangan Antarmuka | 60 WH | 1-7 Juli 2026 |
| Fase 4 | Keamanan dan Audit | 50 WH | 8-14 Juli 2026 |
| Fase 5 | Optimasi dan Pengujian | 50 WH | 15-21 Juli 2026 |
| Fase 6 | Penyelesaian Akhir | 40 WH | 22-28 Juli 2026 |
| **Total** | | **320 WH** | |

### Lampiran 2: Daftar Domain Services

| Domain | Service | Method Utama |
|--------|---------|--------------|
| Audit | AuditDomainService | logAction(), getUserActivityLog(), logFormAction(), logSubmissionAction(), logUserAction(), logAuthAction() |
| Auth | AuthService | login(), logout(), changePassword(), getCurrentUser() |
| Auth | AuthorizationService | canViewForm(), canEditForm(), canExportForm(), applyFormAccessConstraints() |
| InternalForms | FormManagementService | createForm(), updateForm(), publishForm(), closeForm(), duplicateForm(), deleteForm(), addField(), updateField(), deleteField(), reorderFields() |
| PublicForms | PublicFormService | getFormBySlug(), submitForm(), validateSubmission() |
| Reporting | ReportingService | getDashboardStats(), getRecentForms(), getFormAnalytics(), exportToCsv() |
| Submissions | SubmissionService | getFormSubmissions(), getSubmissionDetail(), deleteSubmission(), getFormStatistics() |

### Lampiran 3: Daftar Eloquent Models

| Model | Tabel | Relasi Utama | Scope |
|-------|-------|-------------|-------|
| User | users | hasMany(Form, UserSession, AuditLog); belongsTo(OPD) | - |
| Form | forms | belongsTo(User, OPD); hasMany(FormField, FormSection, FormSubmission) | published(), draft(), closed() |
| FormField | form_fields | belongsTo(Form, FormSection); hasMany(SubmissionData) | - |
| FormSection | form_sections | belongsTo(Form); hasMany(FormField) | - |
| FormSubmission | form_submissions | belongsTo(Form, User); hasMany(SubmissionData) | - |
| SubmissionData | submission_data | belongsTo(FormSubmission, FormField) | - |
| OPD | opds | hasMany(User, Form) | - |
| AuditLog | audit_logs | belongsTo(User) | - |
| Notification | notifications | belongsTo(User) | unread() |
| UserSession | user_sessions | belongsTo(User) | active() |
| Participant | participants | - | - |

### Lampiran 4: Daftar Pengujian

| No | Test Class | Test Method | Asersi |
|----|-----------|-------------|--------|
| 1 | LoginSessionTest | test_login_creates_user_session_record | 3 |
| 2 | LoginSessionTest | test_login_stores_session_id_in_laravel_session | 3 |
| 3 | LoginSessionTest | test_logout_updates_logged_out_at | 3 |
| 4 | LoginSessionTest | test_failed_login_does_not_create_session | 3 |
| 5 | DisplayNamePromptTest | test_display_name_prompt_always_shows_on_mount | 3 |
| 6 | DisplayNamePromptTest | test_display_name_prompt_saves_to_session_record | 3 |
| 7 | DisplayNamePromptTest | test_display_name_prompt_hides_after_save | 3 |
| 8 | DisplayNamePromptTest | test_display_name_prompt_validates_minimum_length | 3 |
| 9 | DisplayNamePromptTest | test_display_name_prompt_does_not_save_to_users_table | 3 |
| 10 | NotificationServiceTest | test_uses_session_display_name_not_account_name | 3 |
| 11 | NotificationServiceTest | test_falls_back_to_user_name_when_no_session | 3 |
| 12 | NotificationServiceTest | test_does_not_notify_actor_themselves | 3 |
| 13 | NotificationServiceTest | test_includes_data_payload_with_actor_id | 3 |
| 14 | NotificationServiceTest | test_does_nothing_without_authenticated_user | 3 |
| 15 | UserDisplayNameTest | test_user_display_name_returns_session_display_name | 3 |
| 16 | UserDisplayNameTest | test_user_display_name_falls_back_to_user_display_name | 3 |
| 17 | UserDisplayNameTest | test_user_display_name_falls_back_to_name | 3 |
| 18 | UserDisplayNameTest | test_user_display_name_falls_back_to_name_when_session_not_found | 3 |
| 19 | UserDisplayNameTest | test_user_display_name_falls_back_to_name_when_no_session_in_laravel_session | 3 |
| 20 | UserSessionTest | test_user_session_belongs_to_user | 3 |
| **Total** | | | **70** |

### Lampiran 5: Daftar File Utama yang Dibuat/Diubah

| No | Berkas | Fungsi | Baris |
|----|--------|--------|-------|
| 1 | app/Http/Controllers/WebAuthController.php | Login/logout web | ~120 |
| 2 | app/Http/Controllers/Admin/AuthController.php | Login/logout admin | ~80 |
| 3 | app/Http/Controllers/API/AuthController.php | Login/logout API | ~90 |
| 4 | app/Http/Controllers/PageController.php | Halaman web utama + ekspor | ~459 |
| 5 | app/Http/Controllers/API/FormCrudController.php | CRUD formulir API | ~250 |
| 6 | app/Http/Controllers/API/SubmissionApiController.php | Pengumpulan data API | ~200 |
| 7 | app/Http/Controllers/API/FieldController.php | CRUD fields API | ~100 |
| 8 | app/Http/Controllers/API/UserController.php | CRUD users API | ~150 |
| 9 | app/Http/Controllers/API/DashboardController.php | Dashboard API | ~60 |
| 10 | app/Http/Controllers/NotificationController.php | Notifikasi | ~80 |
| 11 | app/Livewire/FormEditor.php | Editor formulir | ~350 |
| 12 | app/Livewire/CreateForm.php | Pembuatan formulir | ~60 |
| 13 | app/Livewire/PublicForm.php | Formulir publik | ~400 |
| 14 | app/Livewire/ActivityChart.php | Grafik aktivitas | ~80 |
| 15 | app/Livewire/SessionTimeout.php | Timeout sesi | ~100 |
| 16 | app/Livewire/DisplayNamePrompt.php | Nama tampilan | ~40 |
| 17 | app/Livewire/NotificationBell.php | Lonceng notifikasi | ~60 |
| 18 | app/Models/Form.php | Model formulir | ~80 |
| 19 | app/Models/User.php | Model pengguna | ~60 |
| 20 | app/Models/FormSubmission.php | Model pengumpulan data | ~40 |
| 21 | app/Models/AuditLog.php | Model catatan audit | ~30 |
| 22 | app/Models/UserSession.php | Model sesi pengguna | ~40 |
| 23 | app/Models/Notification.php | Model notifikasi | ~30 |
| 24 | app/Models/OPD.php | Model OPD | ~30 |
| 25 | app/Models/Participant.php | Model peserta | ~20 |
| 26 | app/Models/FormField.php | Model bidang form | ~40 |
| 27 | app/Models/FormSection.php | Model section form | ~30 |
| 28 | app/Models/SubmissionData.php | Model data submission | ~20 |
| 29 | app/Policies/FormPolicy.php | Kebijakan keamanan formulir | ~50 |
| 30 | app/Services/AuditService.php | Layanan pencatatan audit | ~40 |
| 31 | app/Services/SessionLimitService.php | Layanan batas sesi | ~60 |
| 32 | app/Services/NotificationService.php | Layanan notifikasi | ~50 |
| 33 | app/Services/FormService.php | Layanan formulir | ~150 |
| 34 | app/Services/SubmissionService.php | Layanan submission | ~100 |
| 35 | app/Domains/Audit/AuditDomainService.php | Domain audit | ~80 |
| 36 | app/Domains/Auth/AuthService.php | Domain autentikasi | ~60 |
| 37 | app/Domains/Auth/AuthorizationService.php | Domain otorisasi | ~120 |
| 38 | app/Domains/InternalForms/FormManagementService.php | Domain form management | ~200 |
| 39 | app/Domains/PublicForms/PublicFormService.php | Domain form publik | ~80 |
| 40 | app/Domains/Reporting/ReportingService.php | Domain reporting | ~100 |
| 41 | app/Domains/Submissions/SubmissionService.php | Domain submission | ~80 |
| 42 | app/Enums/UserRole.php | Enum peran pengguna | ~20 |
| 43 | app/Enums/FormStatus.php | Enum status form | ~20 |
| 44 | app/Enums/FieldType.php | Enum tipe bidang | ~30 |
| 45 | app/Enums/DataClassification.php | Enum klasifikasi data | ~40 |
| 46 | resources/views/layouts/app.blade.php | Templat utama | ~80 |
| 47 | resources/views/admin/layouts/app.blade.php | Templat admin | ~60 |
| 48 | resources/views/livewire/activity-chart.blade.php | Templat grafik | ~40 |
| 49 | resources/views/livewire/session-timeout.blade.php | Templat timeout | ~30 |
| 50 | resources/views/dashboard/index.blade.php | Dashboard utama | ~100 |
| 51 | resources/views/admin/dashboard/index.blade.php | Dashboard admin | ~60 |
| 52 | tests/Feature/LoginSessionTest.php | Pengujian login/sesi | ~60 |
| 53 | tests/Feature/DisplayNamePromptTest.php | Pengujian nama tampilan | ~70 |
| 54 | tests/Unit/NotificationServiceTest.php | Pengujian notifikasi | ~70 |
| 55 | tests/Unit/UserDisplayNameTest.php | Pengujian display name | ~60 |
| 56 | tests/Unit/UserSessionTest.php | Pengujian sesi pengguna | ~50 |
| 57 | docs/ARCHITECTURE.md | Dokumentasi arsitektur | ~300 |
| 58 | docs/MASTER-TASK-LIST.md | Daftar tugas utama | ~200 |
| 59 | docs/PROJECT-OVERVIEW.md | Dokumentasi umum proyek | ~150 |

### Lampiran 6: Screenshot Aplikasi

(Sisipkan screenshot dashboard, form editor, formulir publik, dan panel admin)

---

**Dokumen ini disusun sebagai laporan kegiatan magang untuk memenuhi persyaratan administrasi.**

**Hizkia**
**28 Juli 2026**
