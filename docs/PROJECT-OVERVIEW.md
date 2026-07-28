# Project KBB — Dokumen Umum Proyek

---

## 1. Judul Proyek

**Project KBB** — Sistem Formulir Digital Pemerintah Kabupaten Bandung Barat

---

## 2. Pengertian Singkat

Project KBB adalah sebuah aplikasi web untuk membuat dan mengelola formulir daring (online). Aplikasi ini dibangun khusus untuk Pemerintah Kabupaten Bandung Barat guna mendukung program transformasi digital pemerintah daerah.

Aplikasi ini bekerja mirip dengan Google Forms, namun memiliki beberapa perbedaan penting:

- Disimpan di server pemerintah sendiri (bukan di server Google)
- Memiliki sistem keamanan berdasarkan OPD (Organisasi Perangkat Daerah)
- Setiap aktivitas dicatat untuk keperluan audit
- Mendukung berbagai jenis input seperti pilihan ganda, angka, tanggal, unggahan file, dan lainnya

---

## 3. Latar Belakang

Pemerintah Kabupaten Bandung Barat memiliki 47 OPD (Organisasi Perangkat Daerah) yang masing-masing membutuhkan cara untuk mengumpulkan data dari masyarakat maupun dari internal pemerintah. Sebelumnya, pengumpulan data dilakukan secara manual atau menggunakan formulir dari pihak ketiga yang tidak terintegrasi.

Project KBB dibangun untuk menyelesaikan masalah berikut:

- Kebutuhan formulir digital yang mudah digunakan oleh operator di setiap OPD
- Pengumpulan data publik dan internal yang terstruktur dan aman
- Pengamanan data berdasarkan tingkat kerahasiaan (publik, internal, sensitif)
- Pencatatan setiap aktivitas penting untuk keperluan audit
- Penyediaan dashboard analitik bagi pengelola

---

## 4. Tujuan Proyek

Tujuan utama Project KBB adalah:

1. Menyediakan platform pembuatan formulir yang mudah digunakan oleh operator OPD
2. Mengumpulkan data dari publik maupun internal secara terstruktur
3. Menjamin keamanan data berdasarkan klasifikasi (publik, internal, sensitif)
4. Mendukung pencatatan audit untuk setiap aksi penting
5. Memberikan dashboard analitik bagi pengelola

---

## 5. Teknologi yang Digunakan

### 5.1 Teknologi Backend (Server)

| Komponen | Teknologi | Keterangan |
|----------|-----------|------------|
| Framework | Laravel versi 13 | Kerangka kerja PHP untuk aplikasi web |
| Bahasa Pemrograman | PHP versi 8.3 atau lebih baru | Bahasa utama yang digunakan |
| ORM | Eloquent | Sistem database bawaan Laravel |
| Autentikasi API | Laravel Sanctum versi 4 | Sistem keamanan untuk API |
| Antrean Pekerjaan | Laravel Queue | Untuk proses yang berjalan di latar belakang |
| Ekspor PDF | DOMPDF versi 3 | Untuk membuat file PDF |
| Ekspor Excel | Maatwebsite Excel | Untuk membuat file Excel/CSV |
| Pembuatan PDF | FPDF | Library tambahan untuk PDF |

### 5.2 Teknologi Frontend (Tampilan)

| Komponen | Teknologi | Keterangan |
|----------|-----------|------------|
| Templat | Blade | Sistem templat bawaan Laravel |
| Interaktivitas | Livewire versi 4 | Membuat halaman interaktif tanpa menulis JavaScript |
| Gaya CSS | Tailwind CSS versi 3 | Kerangka gaya (dimuat dari CDN) |
| Grafik | Chart.js versi 4.4.7 | Untuk membuat grafik (dimuat dari CDN) |
| JavaScript | Vanilla JS | JavaScript murni tanpa library |
| Font | Inter | Jenis huruf (dimuat dari CDN) |

### 5.3 Infrastruktur

| Komponen | Teknologi | Keterangan |
|----------|-----------|------------|
| Basis Data | MySQL (produksi) / SQLite (pengujian) | Tempat penyimpanan data |
| Penyimpanan Sesi | Database | Sesi pengguna disimpan di database |
| Cache | Redis (opsional) | Penyimpanan sementara untuk data yang sering diakses |
| Pengujian | PHPUnit versi 12 | Kerangka kerja untuk pengujian kode |
| Gaya Kode | Laravel Pint | Alat untuk mengecek format kode |
| Alat Build | Vite | Untuk menggabungkan file CSS dan JavaScript |

### 5.4 Identitas Visual

| Elemen | Nilai | Keterangan |
|--------|-------|------------|
| Warna Utama | #003778 (Biru KBB) | Warna utama aplikasi |
| Warna Aksen | #C8A45C (Emas) | Warna pendukung |
| Jenis Huruf | Inter | Digunakan di seluruh aplikasi |

---

## 6. Arsitektur Aplikasi

### 6.1 Pendekatan: Modular Monolith

Project KBB dibangun sebagai satu aplikasi Laravel utama dengan kode terorganisir berdasarkan bidang bisnis (domain), bukan berdasarkan lapisan teknis.

**Mengapa menggunakan pendekatan ini:**

- Realistis untuk kondisi proyek yang sudah berjalan
- Lebih cepat stabil dibanding pendekatan microservices
- Mudah dioperasikan karena hanya ada satu aplikasi yang di-deploy
- Dapat berkembang ke microservices di kemudian hari jika diperlukan
- Seluruh kode berada dalam satu repositori, sehingga mudah diawasi

### 6.2 Enam Domain Utama

Aplikasi ini terbagi menjadi enam domain (bidang) utama:

| Domain | Fungsi |
|--------|--------|
| Autentikasi (Auth) | Pengelolaan login, logout, dan izin akses |
| Formulir Publik (PublicForms) | Penyajian dan pengumpulan formulir dari publik |
| Formulir Internal (InternalForms) | Pembuatan dan pengelolaan formulir oleh operator |
| Pengumpulan Data (Submissions) | Pengelolaan jawaban yang masuk dari formulir |
| Pelaporan (Reporting) | Dashboard, statistik, dan ekspor data |
| Audit | Pencatatan seluruh aktivitas penting |

### 6.3 Struktur Folder Utama

```
project-kbb/
  backend/                    (seluruh kode aplikasi Laravel)
    app/
      Domains/                (6 layanan domain)
      Http/Controllers/       (pengontrol Web, API, dan Admin)
      Livewire/               (7 komponen Livewire)
      Models/                 (11 model basis data)
      Services/               (5 layanan aplikasi)
      Enums/                  (4 enumerasi)
      Policies/               (1 kebijakan keamanan)
      Exports/                (1 kelas ekspor)
    resources/views/          (templat Blade)
    routes/                   (web.php, api.php, admin.php)
    database/migrations/      (26 migrasi basis data)
    tests/                    (pengujian PHPUnit)
  docs/                       (dokumentasi arsitektur)
  start.bat                   (usang, abaikan)
```

---

## 7. Struktur Basis Data

### 7.1 Ikhtisar

Terdapat 26 migrasi (perubahan struktur basis data) yang menghasilkan 11 tabel utama dan beberapa tabel pendukung.

### 7.2 Tabel-Tabel Utama

#### Tabel users (Pengguna)

Tabel ini menyimpan data seluruh pengguna aplikasi.

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| id | bilangan bulat (primary key) | Identifikasi unik pengguna |
| name | teks | Nama lengkap pengguna |
| email | teks (unik) | Alamat email pengguna |
| password | teks | Kata sandi (tersimpan dalam bentuk terenkripsi) |
| role | pilihan (super_admin atau admin) | Peran pengguna dalam sistem |
| nip | teks (opsional) | Nomor Induk Pegawai |
| opd | teks (opsional) | Nama OPD (warisan lama) |
| opd_id | bilangan bulat (foreign key) | Penghubung ke tabel opds |
| display_name | teks (opsional) | Nama tampilan yang dapat diubah pengguna |

#### Tabel opds (Organisasi Perangkat Daerah)

Tabel ini menyimpan daftar seluruh OPD di Kabupaten Bandung Barat.

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| id | bilangan bulat (primary key) | Identifikasi unik OPD |
| name | teks (unik) | Nama OPD |
| description | teks panjang (opsional) | Deskripsi OPD |
| deleted_at | tanggal waktu | Waktu penghapusan data (soft delete) |

#### Tabel forms (Formulir)

Tabel ini menyimpan seluruh data formulir yang dibuat.

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| id | bilangan bulat (primary key) | Identifikasi unik formulir |
| uuid | UUID (unik) | Identifikasi universal unik |
| user_id | bilangan bulat (foreign key) | Pengguna yang membuat formulir |
| opd_id | bilangan bulat (foreign key) | OPD pemilik formulir |
| title | teks | Judul formulir |
| description | teks panjang (opsional) | Deskripsi formulir |
| slug | teks (unik) | Alamat pendek untuk URL formulir |
| status | pilihan (draft, published, closed) | Status formulir |
| data_classification | pilihan (public, internal, sensitive) | Klasifikasi keamanan data |
| settings | JSON | Pengaturan formulir |
| starts_at | tanggal waktu (opsional) | Waktu mulai formulir dibuka |
| ends_at | tanggal waktu (opsional) | Waktu akhir formulir ditutup |
| max_submissions | bilangan bulat (opsional) | Batas jumlah pengumpulan data |
| require_auth | boolean | Apakah wajib login untuk mengisi |
| collect_ip | boolean | Apakah menyimpan alamat IP pengisi |
| show_kbb_logo | boolean | Apakah menampilkan logo KBB |
| redirect_url | teks (opsional) | Alamat tujuan setelah mengisi formulir |
| header_image | teks (opsional) | Gambar header formulir |
| theme_color | teks (opsional) | Warna tema formulir |
| confirmation_type | pilihan (message atau redirect) | Jenis konfirmasi setelah mengisi |
| deleted_at | tanggal waktu | Waktu penghapusan data (soft delete) |

#### Tabel form_fields (Bidang Formulir)

Tabel ini menyimpan seluruh bidang (field) yang ada di dalam formulir.

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| id | bilangan bulat (primary key) | Identifikasi unik bidang |
| form_id | bilangan bulat (foreign key) | Formulir pemilik bidang |
| section_id | bilangan bulat (foreign key, opsional) | Penghubung ke tabel form_sections |
| type | pilihan (text, email, number, select, radio, checkbox, textarea, date, file, heading, paragraph, rating, matrix) | Jenis bidang |
| label | teks | Teks label yang ditampilkan |
| placeholder | teks (opsional) | Teks petunjuk di dalam bidang |
| help_text | teks (opsional) | Teks bantuan tambahan |
| required | boolean | Apakah bidang wajib diisi |
| options | JSON (opsional) | Daftar pilihan untuk select, radio, atau checkbox |
| order | bilangan bulat | Urutan tampilan bidang |
| min_length | bilangan bulat (opsional) | Panjang minimum karakter |
| max_length | bilangan bulat (opsional) | Panjang maksimum karakter |
| default_value | teks (opsional) | Nilai awal bidang |
| conditions | JSON (opsional) | Aturan tampil/sembunyi berdasarkan jawaban lain |
| formula | teks (opsional) | Rumus untuk bidang yang dihitung otomatis |
| allow_other | boolean | Apakah ada opsi "Lainnya" |
| is_admin_only | boolean | Apakah bidang hanya terlihat oleh admin |

#### Tabel form_sections (Bagian Formulir)

Tabel ini menyimpan pengelompokan bidang-bidang dalam formulir.

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| id | bilangan bulat (primary key) | Identifikasi unik bagian |
| form_id | bilangan bulat (foreign key) | Formulir pemilik bagian |
| title | teks | Judul bagian |
| description | teks panjang (opsional) | Deskripsi bagian |
| order | bilangan bulat | Urutan tampilan bagian |

#### Tabel form_submissions (Pengumpulan Data)

Tabel ini menyimpan setiap kiriman jawaban dari pengisi formulir.

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| id | bilangan bulat (primary key) | Identifikasi unik kiriman |
| uuid | UUID (unik) | Identifikasi universal unik |
| form_id | bilangan bulat (foreign key) | Formulir yang diisi |
| user_id | bilangan bulat (foreign key, opsional) | Pengguna yang mengisi (jika login) |
| ip_address | teks (opsional) | Alamat IP pengirim |
| user_agent | teks (opsional) | Informasi perangkat pengirim |
| submitted_at | tanggal waktu | Waktu kiriman |

#### Tabel submission_data (Data Jawaban)

Tabel ini menyimpan nilai jawaban untuk setiap bidang dalam satu kiriman.

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| id | bilangan bulat (primary key) | Identifikasi unik data |
| submission_id | bilangan bulat (foreign key) | Kiriman pemilik data |
| form_field_id | bilangan bulat (foreign key) | Bidang formulir yang dijawab |
| value | teks panjang | Nilai jawaban |

#### Tabel audit_logs (Catatan Audit)

Tabel ini menyimpan seluruh catatan aktivitas penting dalam aplikasi.

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| id | bilangan bulat (primary key) | Identifikasi unik catatan |
| user_id | bilangan bulat (foreign key, opsional) | Pengguna yang melakukan aksi |
| action | teks | Jenis aksi (contoh: login, logout, create form) |
| auditable_type | teks | Jenis data yang terpengaruh |
| auditable_id | bilangan bulat | Identifikasi data yang terpengaruh |
| description | teks panjang | Deskripsi aksi |
| old_values | JSON | Nilai sebelum perubahan |
| new_values | JSON | Nilai sesudah perubahan |
| ip_address | teks (opsional) | Alamat IP pelaku aksi |
| user_agent | teks (opsional) | Informasi perangkat pelaku aksi |

### 7.3 Tabel-Tabel Pendukung

| Tabel | Fungsi |
|-------|--------|
| notifications | Menyimpan notifikasi untuk pengguna |
| participants | Menyimpan data peserta (untuk fitur pencarian otomatis) |
| user_sessions | Melacak sesi aktif untuk setiap pengguna |
| sessions | Penyimpanan sesi pengguna (menggunakan basis data) |
| personal_access_tokens | Token keamanan untuk API |
| cache | Penyimpanan sementara data yang sering diakses |
| jobs | Antrean pekerjaan yang berjalan di latar belakang |

---

## 8. Sistem Autentikasi dan Otorisasi

### 8.1 Tiga Sistem Login

Aplikasi ini memiliki tiga sistem login yang berjalan secara paralel:

| Sistem | Berkas Rute | Driver | Pengontrol |
|--------|-------------|--------|------------|
| Web | routes/web.php | Sesi (session) | WebAuthController |
| API | routes/api.php | Token Sanctum | API/AuthController |
| Admin | routes/admin.php | Sesi (khusus super_admin) | Admin/AuthController |

### 8.2 Peran Pengguna

| Peran | Keterangan |
|-------|------------|
| super_admin | Dapat mengakses seluruh data, mengelola pengguna, melihat formulir dari semua OPD |
| admin | Dapat mengakses formulir sendiri dan formulir dari OPD yang sama |

### 8.3 Batasan Sesi

| Pengaturan | Nilai |
|------------|-------|
| Sesi aktif maksimal untuk super_admin | 1 sesi |
| Sesi aktif maksimal untuk admin | 3 sesi |
| Batas waktu tidak aktif (idle timeout) | 5 menit |
| Peringatan sebelum timeout | 2 menit sebelum logout otomatis |

### 8.4 Pengendalian Akses Berdasarkan OPD

Pengendalian akses didasarkan pada keanggotaan OPD:

- **Super Admin:** Dapat melihat seluruh data dari semua OPD
- **Admin:** Hanya dapat melihat data dari OPD sendiri dan formulir yang dibuat sendiri

**Matriks Otorisasi:**

| Aksi | Super Admin | Admin (Sendiri) | Admin (OPD Sama) | Admin (OPD Berbeda) |
|------|:-----------:|:---------------:|:-----------------:|:-------------------:|
| Melihat formulir | Ya | Ya | Ya | Tidak |
| Mengubah formulir | Ya | Ya | Tidak | Tidak |
| Menghapus formulir | Ya | Ya | Tidak | Tidak |
| Melihat pengumpulan data | Ya | Ya | Ya | Tidak |
| Menghapus pengumpulan data | Ya | Ya | Tidak | Tidak |
| Mengekspor formulir | Ya | Ya | Ya | Tidak |
| Mengelola pengguna | Ya | Tidak | Tidak | Tidak |

---

## 9. Klasifikasi Data

Terdapat tiga tingkat klasifikasi keamanan data yang mengontrol ekspor, pelindungan data, pencatatan, dan retensi:

| Klasifikasi | Dapat Diekspor | Pelindungan Data | Pencatatan | Retensi |
|-------------|:--------------:|:----------------:|:----------:|:-------:|
| public (publik) | Ya | Tidak | Minimal | Selamanya |
| internal (internal) | Ya | Di log dan dashboard | Detail | 1 tahun |
| sensitive (sensitif) | Tidak | Di log dan dashboard | Maksimal | 90 hari |

---

## 10. Fitur Utama

### 10.1 Pembuatan Formulir

- Membuat formulir dengan judul, deskripsi, dan alamat pendek (slug) yang dibuat otomatis
- 13 jenis bidang: teks, email, angka, pilihan ganda (select), radio, checkbox, area teks (textarea), tanggal, unggahan file, judul (heading), paragraf, penilaian (rating), dan matriks
- Pembuat bidang dengan cara menyeret dan melepaskan (drag-and-drop) melalui Livewire
- Pengelompokan bidang menggunakan bagian (section)
- Logika bersyarat (conditional logic): menampilkan atau menyembunyikan bidang berdasarkan jawaban sebelumnya
- Mesin formula: bidang yang dihitung otomatis berdasarkan rumus
- Pengaturan: batas jumlah pengumpulan, waktu buka tutup formulir, wajib login, dan klasifikasi data

### 10.2 Formulir Publik

- Dapat diakses tanpa login melalui alamat /form/{slug}
- Validasi nyata waktu (real-time) untuk setiap bidang melalui Livewire
- Pencarian peserta secara otomatis dari tabel peserta
- Status pemuatan (loading state) untuk mencegah pengiriman ganda (double-submit)
- Pengalihan URL atau halaman terima kasih setelah mengirim

### 10.3 Pengelolaan Pengumpulan Data

- Daftar pengumpulan data dengan paginasi (pembagian halaman)
- Detail pengumpulan data per bidang
- Hapus pengumpulan data
- Penyaringan berdasarkan tanggal atau status

### 10.4 Ekspor Data

- CSV melalui Maatwebsite Excel
- PDF melalui DOMPDF
- Uang Saku (format khusus pemerintah)
- Presensi (format khusus pemerintah)
- Pemblokiran ekspor untuk formulir dengan klasifikasi sensitif

### 10.5 Dashboard dan Analitik

- Statistik: total formulir, yang diterbitkan, konsep, dan ditutup
- Statistik pengumpulan data: total, hari ini, minggu ini
- Grafik aktivitas pengumpulan data (Chart.js) dengan penyaringan rentang waktu: 7 hari, 1 bulan, 3 bulan, 6 bulan, 1 tahun
- Tabel formulir terbaru
- Dashboard admin terpisah

### 10.6 Pengelolaan Pengguna (Super Admin)

- CRUD pengguna (membaca, membuat, mengubah, menghapus)
- Menetapkan peran (super_admin atau admin)
- Menetapkan OPD
- Pelacakan sesi dan pembatasan jumlah sesi

### 10.7 Keamanan

- Waktu tunggu sesi tidak aktif 5 menit (logout otomatis)
- Pembatasan jumlah sesi aktif secara bersamaan
- Pencatatan audit untuk seluruh aksi penting
- Pembatasan laju login API (maksimal 5 kali per menit)
- Perlindungan CSRF (Cross-Site Request Forgery)
- Pengamanan kata sandi dengan hash

---

## 11. Komponen Livewire

Livewire adalah komponen yang membuat halaman web menjadi interaktif tanpa menulis JavaScript secara langsung.

| Komponen | Fungsi |
|----------|--------|
| CreateForm | Formulir untuk membuat formulir baru |
| FormEditor | Pembuat bidang: CRUD, pengurutan, terbitkan/tutup, pengaturan |
| PublicForm | Menampilkan formulir publik, validasi, dan pengiriman |
| ActivityChart | Grafik batang aktivitas pengumpulan data |
| SessionTimeout | Pelacakan sesi dan logout otomatis saat tidak aktif |
| DisplayNamePrompt | Pengingat nama tampilan untuk pengguna baru |
| NotificationBell | Lonceng notifikasi dengan daftar dropdown |

---

## 12. Layanan (Services)

### 12.1 Layanan Level Aplikasi (app/Services/)

| Layanan | Fungsi |
|---------|--------|
| AuditService | Mencatat aktivitas penting ke tabel audit_logs |
| FormService | Operasi formulir (membuat, mengubah, menghapus, menerbitkan, menutup) |
| SubmissionService | Pengelolaan pengumpulan data |
| NotificationService | Mengirim notifikasi kepada pengguna |
| SessionLimitService | Mengecek batas jumlah sesi aktif |

### 12.2 Layanan Domain (app/Domains/)

| Domain | Layanan | Fungsi |
|--------|---------|--------|
| Autentikasi | AuthService | Login, logout, mengubah kata sandi |
| Autentikasi | AuthorizationService | Pengecekan izin, penyaringan kueri |
| Formulir Publik | PublicFormService | Mengambil formulir berdasarkan slug, validasi, mengirim |
| Formulir Internal | FormManagementService | CRUD formulir, pengelolaan bidang |
| Pengumpulan Data | SubmissionService | Penyaringan, pengambilan, penghapusan |
| Pelaporan | ReportingService | Statistik dashboard, ekspor |
| Audit | AuditDomainService | Pencatatan audit |

> **Catatan:** Layanan domain sudah diimplementasikan tetapi belum digunakan oleh pengontrol (controllers). Pengontrol masih menggunakan Eloquent langsung dan AuditService. Ini merupakan hutang teknis (technical debt) yang perlu ditangani di masa mendatang.

---

## 13. Rute (Routes)

### 13.1 Rute Web (routes/web.php)

| Alamat | Fungsi |
|--------|--------|
| /login | Halaman login |
| /logout | Logout |
| /dashboard | Dashboard utama |
| /forms | Daftar formulir |
| /forms/create | Membuat formulir baru |
| /forms/{form}/edit | Mengubah formulir |
| /forms/{form} | Detail formulir |
| /forms/{form}/submissions | Daftar pengumpulan data |
| /forms/{form}/submissions/{submission} | Detail pengumpulan data |
| /forms/{form}/analytics | Analitik formulir |
| /forms/{form}/export/csv | Ekspor CSV |
| /forms/{form}/export/pdf | Ekspor PDF |
| /users | Pengelolaan pengguna (khusus super_admin) |
| /change-password | Mengubah kata sandi |
| /form/{slug} | Formulir publik |

### 13.2 Rute API (routes/api.php)

| Alamat | Metode | Fungsi |
|--------|--------|--------|
| /api/v1/auth/login | POST | Login (dibatasi maksimal 5 kali per menit) |
| /api/v1/auth/logout | POST | Logout |
| /api/v1/auth/me | GET | Profil pengguna |
| /api/v1/auth/change-password | POST | Mengubah kata sandi |
| /api/v1/forms | GET/POST | Daftar dan buat formulir |
| /api/v1/forms/{form} | GET/PUT/DELETE | Lihat, ubah, hapus formulir |
| /api/v1/forms/{form}/fields | POST | Tambah bidang |
| /api/v1/forms/{form}/fields/{field} | PUT/DELETE | Ubah atau hapus bidang |
| /api/v1/forms/{form}/submissions | GET | Daftar pengumpulan data |
| /api/v1/forms/{form}/submissions/{submission} | GET/DELETE | Lihat atau hapus pengumpulan data |

### 13.3 Rute Admin (routes/admin.php)

| Alamat | Fungsi |
|--------|--------|
| /admin/login | Login panel admin |
| /admin/dashboard | Dashboard admin |
| /admin/forms | Pengelolaan formulir |
| /admin/users | Pengelolaan pengguna |

---

## 14. Pengujian (Testing)

### 14.1 Konfigurasi

- Menggunakan PHPUnit versi 12 dengan basis data SQLite di memori
- Menggunakan trait RefreshDatabase untuk isolasi setiap pengujian
- Menggunakan metode actingAs() untuk autentikasi dalam pengujian

### 14.2 Cakupan Saat Ini

| Lokasi | Berkas | Skenario |
|--------|--------|----------|
| Feature | DisplayNamePromptTest.php | Pengingat nama tampilan |
| Feature | LoginSessionTest.php | Login dan pembatasan sesi |
| Feature | ExampleTest.php | Contoh pengujian |
| Unit | NotificationServiceTest.php | Layanan notifikasi |
| Unit | UserDisplayNameTest.php | Nama tampilan pengguna |
| Unit | UserSessionTest.php | Sesi pengguna |
| Unit | ExampleTest.php | Contoh pengujian |

**Total:** 25 pengujian, 46 asersi — seluruhnya berhasil

---

## 15. Gaya Kode

- Menggunakan Laravel Pint dengan pengaturan bawaan (tanpa berkas pint.json)
- Indentasi 4 spasi, UTF-8, akhir baris LF
- Penamaan: PascalCase untuk kelas, camelCase untuk metode
- Tanpa pengaturan PHP-CS-Fixer khusus

---

## 16. Perintah Pengembangan

### 16.1 Setup Awal

```bash
composer setup
```

Perintah ini akan: menginstal dependensi, menghasilkan kunci aplikasi, menjalankan migrasi database, dan membangun aset frontend.

### 16.2 Pengembangan

```bash
composer dev
```

Perintah ini akan menjalankan empat layanan secara bersamaan: server Laravel, antrean pekerjaan, pemantauan log, dan Vite.

```bash
php artisan serve
```

Menjalankan server Laravel di port 8000.

```bash
npm run dev
```

Menjalankan Vite di port 5173 untuk pengembangan frontend.

### 16.3 Pengujian

```bash
php artisan test
```

Menjalankan seluruh pengujian.

```bash
php vendor/bin/pint
```

Memeriksa format kode sesuai standar.

### 16.4 Basis Data

```bash
php artisan migrate:fresh --seed
```

Mereset seluruh database dan menjalankan ulang migrasi beserta data contoh.

---

## 17. Hutang Teknis (Technical Debt)

Berikut adalah daftar isu teknis yang perlu ditangani:

| No | Isu | Prioritas | Estimasi Waktu |
|----|-----|-----------|----------------|
| 1 | Alamat IP dan User-Agent bocor di Livewire (tidak mengecek collect_ip) | Kritis | 15 menit |
| 2 | Ekspor formulir sensitif tidak diblokir | Kritis | 1 jam |
| 3 | Unggahan file tersimpan di disk publik | Kritis | 2-3 jam |
| 4 | Pencegahan XSS belum diterapkan di beberapa tampilan | Tinggi | 30 menit |
| 5 | Token API tidak pernah kedaluwarsa | Tinggi | 15 menit |
| 6 | Batas sesi tidak dicek sebelum login | Sedang | 1 jam |
| 7 | Hapus pengguna menghapus semua formulir (cascade delete) | Sedang | 30 menit |
| 8 | Duplikasi formulir tidak menduplikasi bagian (section) | Tinggi | 30 menit |
| 9 | Status pemuatan pada pengiriman formulir publik | Sedang | 30 menit |
| 10 | URL pengalihan belum berfungsi | Sedang | 15 menit |
| 11 | Layanan domain belum digunakan oleh pengontrol | Sedang | 2-3 hari |
| 12 | Penyaringan kueri masih menggunakan user_id bukan opd_id | Tinggi | 3-4 hari |
| 13 | Cakupan pengujian rendah (hanya 7 berkas) | Sedang | 3-4 hari |

Lihat berkas docs/MASTER-TASK-LIST.md untuk daftar lengkap dan rencana implementasi.

---

## 18. Arsitektur Deployment

Aplikasi ini dijalankan dengan komponen-komponen berikut:

1. **Server Web (Nginx atau Apache):** Menjalankan aplikasi Laravel dengan Blade dan Livewire
2. **Antrean Pekerjaan (Queue Worker):** Menjalankan tugas-tugas di latar belakang seperti ekspor data dan pengiriman email
3. **Penjadwal (Scheduler):** Menjalankan tugas terjadwal seperti pembersihan data lama
4. **Basis Data (MySQL):** Menyimpan seluruh data aplikasi
5. **Penyimpanan (Storage):** Menyimpan file unggahan dan hasil ekspor
6. **Cache (Redis):** Penyimpanan sementara data yang sering diakses (opsional)

### Target Performa

| Operasi | Target Waktu |
|---------|-------------|
| Memuat formulir publik | Kurang dari 2 detik |
| Memuat dashboard | Kurang dari 3 detik |
| Daftar pengumpulan data | Kurang dari 500 milidetik |
| Pekerjaan ekspor | Kurang dari 30 detik |
| Login | Kurang dari 1 detik |
| Pengecekan otorisasi | Kurang dari 10 milidetik |

---

## 19. Referensi

| Dokumen | Lokasi Berkas |
|---------|---------------|
| Arsitektur lengkap | docs/ARCHITECTURE.md |
| Daftar tugas utama | docs/MASTER-TASK-LIST.md |
| Indeks dokumentasi | docs/INDEX.md |

---

**Versi Dokumen:** 1.1  
**Terakhir Diperbarui:** 28 Juli 2026  
**Status:** Aktif (Dalam Pengembangan)
