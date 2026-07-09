# System Analysis — Project KBB

> **Dibuat:** Juli 2026
> **Tujuan:** Dokumentasi flow of operation, auth flow, risk map, dan change guide untuk pengembangan & integrasi.

---

## Daftar Isi

1. [Flow of Operation](#1-flow-of-operation)
2. [Auth Flow](#2-auth-flow)
3. [Risk Map](#3-risk-map)
4. [Change Guide](#4-change-guide)

---

## 1. Flow of Operation

### 1.1 Alur Data: Public Submit Form (Livewire)

```
User buka /form/{slug}
  ↓
[1] PublicForm::mount() → Form::with('fields.section', 'sections')
       → where('slug', $slug) → where('status', 'published') → first()
  ↓                                  ↓ error → $this->error = 'Form tidak ditemukan'
[2] Cek isExpired() / isFull()
  ↓                                  ↓ error = 'Form sudah tidak menerima jawaban'
[3] User navigasi step → nextStep() → validateStep($currentStep)
  ↓                                  ↓ error → Livewire validation (responses.{fieldId})
[4] submitForm() → validate semua field
  ↓                                  ↓ error → Livewire validation messages
[5] Simpan:
     form_submissions (uuid, form_id, user_id/null, ip_address, user_agent, submitted_at)
     submission_data  (submission_id, form_field_id, value)  — 1 baris per field
  ↓
[6] $this->submitted = true → tampilkan konfirmasi
```

**File:** `app/Livewire/PublicForm.php`

---

### 1.2 Alur Data: API Public Submission

```
POST /api/v1/forms/public/{slug}
  ↓
[1] SubmissionApiController@store → Form::published()->where('slug', $slug)->firstOrFail()
  ↓                                                     ↓ error → 404
[2] isExpired() / isFull()
  ↓                                ↓ error → 410 "Gone"
[3] require_auth / limit_one_response
  ↓                                ↓ error → 401 / 409
[4] Convert input: fields.{id} → field_{id}, lalu validasi per field
  ↓                                ↓ error → 422 ValidationException
[5] Simpan form_submissions + submission_data
  ↓
[6] Response 201 { uuid, message }
```

**File:** `app/Http/Controllers/API/SubmissionApiController.php`  
**Throttle:** `10:1` (max 10 request per menit)

---

### 1.3 Alur Data: Create & Edit Form

```
[Blade] /forms/create → CreateForm Livewire → isi title + description → submit
  ↓
[API] POST /api/v1/forms → FormCrudController@store
  ↓ validasi title, dates, settings...
  ↓ generate slug = Str::slug(title) + '-' + Str::random(6)
  ↓ create forms (uuid, user_id, title, slug, status=draft, settings JSON)
  ↓                               ↓ error 422
[201] → redirect ke /forms/{id}/edit
  ↓
FormEditor Livewire:
  ├── Tambah field → FormField::create (type, label, options JSON, order, section_id...)
  ├── Tambah section → FormSection::create
  ├── Atur settings → form->update(...)
  └── Publish → status = 'published'
```

**File:** `app/Livewire/FormEditor.php`, `app/Http/Controllers/API/FormCrudController.php`

---

### 1.4 Tabel Database

| Tabel | Fungsi | Key Fields |
|---|---|---|
| `users` | Akun admin/operator | name, email, password, role (super_admin/admin), opd_id |
| `opds` | Daftar OPD | name, code, softDeletes |
| `forms` | Form utama | uuid, user_id, opd_id, title, slug, status, data_classification, settings (JSON), starts_at, ends_at, max_submissions, softDeletes |
| `form_fields` | Field dalam form | form_id, type (enum 13), label, options (JSON), required, order, section_id, validasi |
| `form_sections` | Grup field multi-step | form_id, title, description, order |
| `form_submissions` | Satu kali pengisian | uuid, form_id, user_id, ip_address, user_agent, submitted_at |
| `submission_data` | Value per field per submission | submission_id, form_field_id, value |
| `audit_logs` | Log aktivitas | user_id, action, auditable_type/id, old/new values (JSON), ip_address, user_agent |
| `notifications` | Notifikasi in-app | user_id, type, message, data (JSON), read_at |
| `personal_access_tokens` | Token Sanctum | tokenable_id, name, token, abilities, expires_at |

---

### 1.5 Titik Error Muncul

| Titik | Lokasi | Bentuk Error |
|---|---|---|
| **Request validation** | Setiap controller: `$request->validate(...)` | 422 ValidationException / redirect back |
| **Auth gagal** | `auth:sanctum` / `auth` middleware | 401 Unauthenticated |
| **Forbidden** | `FormPolicy` + `$this->authorize()` | 403 Forbidden |
| **Role salah** | `RoleMiddleware` | 403 JSON / abort(403) |
| **Model not found** | `findOrFail()` / `firstOrFail()` | 404 Not Found |
| **Form expired/penuh** | `isExpired()` / `isFull()` | 410 Gone / error message |
| **Livewire validation** | `$this->validate()` / `validateOnly()` | Error per field di Blade |
| **Manual error** | `$this->error = '...'` di PublicForm | Tampil di view |

---

## 2. Auth Flow

### 2.1 Dua Jalur Autentikasi

#### A. Web Login (Blade Session)

```
GET  /login   → PageController@login   → tampilkan view auth.login
POST /login   → WebAuthController@login → cari user by email
               ↓ Hash::check(password)  → session login → redirect /dashboard
               ↓ gagal                  → redirect back with error
POST /logout  → WebAuthController@logout → session logout
```

**File:** `app/Http/Controllers/WebAuthController.php`  
**Route:** `routes/web.php:8-9`

#### B. API Login (Sanctum Token)

```
POST /api/v1/auth/login → AuthController@login
  ↓ validasi email + password (throttle:5,1)
  ↓ User::where('email', ...)->first()
  ↓ Hash::check(password)
  ↓ success → createToken('api-token') → return token + user
  ↓          → SessionLimitService::limitTokens($user)
  ↓ fail    → ValidationException
```

**File:** `app/Http/Controllers/API/AuthController.php`  
**Route:** `routes/api.php:11`  
**Token:** Sanctum plainTextToken (prefix `kbb_*`, 24h expiry)

---

### 2.2 User Role

| Role | Enum Value | Hak Akses |
|---|---|---|
| **super_admin** | `super_admin` | Full akses semua form, user, OPD. Session limit: 1, Token limit: 1 |
| **admin** | `admin` | Hanya form milik sendiri (`user_id`). Session limit: 3, Token limit: 3 |

**File:** `app/Enums/UserRole.php`

Akun seed (DinasUserSeeder): 1 super_admin + 47 admin per OPD.  
Semua password default: `admin12345`.

---

### 2.3 Halaman yang Butuh Login

| Middleware | Routes |
|---|---|
| `auth` (web) | `/dashboard`, `/forms*`, `/users*` (super_admin only), `/change-password`, `/notifications*` |
| `auth:sanctum` (API) | Semua kecuali login & public form submission |
| `auth` + `role:super_admin` | `/admin/*` (admin panel) |

**Halaman publik (tanpa login):**
- `GET /login`
- `GET /form/{slug}` — lihat & isi form publik

---

### 2.4 Permission Check Layers

| Layer | Mekanisme | File |
|---|---|---|
| **1. Route Middleware** | `auth:sanctum` / `auth` — pastikan sudah login | `routes/*.php` |
| **2. Role Middleware** | `role:super_admin` → cek `$user->role->value` | `app/Http/Middleware/RoleMiddleware.php` |
| **3. FormPolicy** | `$this->authorize('view|update|delete', $form)` → super_admin atau creator | `app/Policies/FormPolicy.php` |
| **4. Query Filter Manual** | Non-super: `->where('user_id', auth()->id())` | `PageController`, `FormCrudController`, `DashboardController` |

---

### 2.5 Isolasi Data User

Saat ini data dipisah berdasarkan **`user_id`** (bukan `opd_id`).

Pola yang digunakan:
```php
// FormCrudController@index
if ($request->user()->role->value === 'super_admin') {
    $query->with('user:id,name');
} else {
    $query->where('user_id', $request->user()->id);
}
```

Pola ini diulang di:
- `PageController.php:27-30, 33-37, 44, 49, 58, 69-71, 80, 99-100`
- `FormCrudController.php:18-23`
- `SubmissionApiController.php` (via FormPolicy)

> **Catatan:** Kolom `opd_id` sudah ada di tabel `users` dan `forms` (migrasi `add_opd_support`), tetapi **belum digunakan**. Semua isolasi masih via `user_id`.

---

## 3. Risk Map

### 🔴 Gampang Error / Rawan Bug

| Area | Risiko | Lokasi | Severity |
|---|---|---|---|
| **Duplicate form tidak bawa sections** | `replicate()` fields di-loop, tapi section tidak ikut | `FormCrudController.php:174-178` | Tinggi |
| **Session limit web tidak cegah** | `canLogin()` ada tapi tidak dipanggil. Session cuma dihapus setelah login | `SessionLimitService.php`, WebAuthController | Tinggi |
| **File upload public storage** | File bisa diakses langsung via URL tanpa auth | `PublicForm.php:208` | Tinggi |
| **Search submission O(n)** | `whereIn` ambil semua ID submission dulu | `PageController.php:185-189` | Sedang |
| **CSV export unbuffered** | `php://output` tanpa memory buffer | `FormCrudController.php:266-293` | Sedang |
| **N+1 query dashboard** | 10+ query terpisah untuk hitung statistik | `PageController.php:22-92` | Rendah |

### 🔴 Rawan Data Bocor

| Area | Risiko | Lokasi | Severity |
|---|---|---|---|
| **IP selalu terekam di Livewire** | `ip_address` & `user_agent` dikirim tanpa cek `collect_ip` | `PublicForm.php:199-200` | **Kritis** |
| **Klasifikasi data tidak ditegakkan** | Enum sudah ada, tapi tidak ada controller yang membatasi akses | Semua controller | **Kritis** |
| **Export SENSITIVE tanpa cek** | CSV/PDF export bisa untuk form klasifikasi SENSITIVE | `FormCrudController.php:265-309` | **Kritis** |
| **Cascade delete** | Hapus user → cascade hapus form + submission + audit | Migration `forms`: `cascadeOnDelete` | Tinggi |
| **Token tidak expired** | Sanctum token tanpa `expires_at` | `AuthController.php:32` | Sedang |

### 🟡 Sulit Diubah

| Area | Alasan |
|---|---|
| **Migrasi user_id → opd_id** | 15+ titik query manual filter by `user_id` tersebar di seluruh controller |
| **Tambah tipe field baru** | Harus update 6+ file (Enum, 2x validasi API, 2x validasi Livewire, 2x Blade render) |
| **Domain services tidak terpakai** | `app/Domains/` berisi logika bisnis tapi tidak di-inject ke controller manapun |
| **Test coverage 0%** | `tests/` hanya boilerplate → perubahan berisiko regresi tinggi |

### 🟡 Belum Ada Validasi

| Fitur | Detail | Lokasi |
|---|---|---|
| **XSS di submission value** | Value ditampilkan tanpa sanitasi di view | `submission_data.value` |
| **Signature field** | Tidak ada validasi format/tanda tangan | `PublicForm.php:107-109` |
| **Allowed domains** | Field ada di model casts tapi tidak pernah dicek | `Form.php:50` |
| **Header image** | Ada di fillable tapi tidak ada upload logic | `Form.php:34` |
| **Theme color** | Ada di fillable tapi tidak dipakai | `Form.php:35` |
| **Redirect URL** | Bisa diisi, tapi PublicForm tidak redirect setelah submit | `PublicForm.php` |
| **Field conditions** | Migration sudah ada, logika conditional skip belum implementasi | Migration `2026_06_25_000001` |

### 🟡 Belum Ada Loading / Error State

| Area | Masalah |
|---|---|
| **PublicForm** | Tidak ada loading state saat submit (user bisa double-click). Tidak redirect walau `confirmation_type=redirect` |
| **FormEditor** | `saveField()` tanpa feedback loading. `deleteField()` langsung hapus tanpa konfirmasi |
| **API controllers** | Error handling pakai `ValidationException` bawaan → response JSON tidak konsisten |
| **Dashboard** | Tidak ada empty state untuk form/submission kosong |
| **Halaman publik** | Jika form tidak ada, cuma text error tanpa tombol kembali |

---

## 4. Change Guide

### 4.1 File yang Perlu Diubah (per jenis perubahan)

#### A. Menambah Tipe Field Baru

| No | File | Perubahan |
|---|---|---|
| 1 | `app/Enums/FieldType.php` | Tambah enum case baru |
| 2 | `app/Http/Controllers/API/FieldController.php:19` | Tambah ke `in:` validation rule |
| 3 | `app/Livewire/FormEditor.php:55` | Tambah ke `in:` validation rule |
| 4 | `app/Livewire/PublicForm.php:120-124, 172-176` | Tambah match case untuk validasi |
| 5 | `app/Http/Controllers/API/SubmissionApiController.php:144-150` | Tambah match case untuk validasi |
| 6 | `resources/views/livewire/form-editor.blade.php` | Tambah render logic |
| 7 | `resources/views/livewire/public-form.blade.php` | Tambah render logic |

#### B. Integrasi dengan Sistem Eksternal (API)

| No | File | Perubahan |
|---|---|---|
| 1 | `routes/api.php` | Tambah endpoint baru |
| 2 | `app/Http/Controllers/API/` | Buat controller / tambah method |
| 3 | `app/Http/Middleware/ForceJsonResponse.php` | Pastikan response format JSON |
| 4 | `app/Exceptions/Handler.php` | Custom error format jika perlu |
| 5 | `.env` | Tambah konfigurasi API key / URL |

#### C. Implementasi OPD-based Access

| No | File | Perubahan |
|---|---|---|
| 1 | `app/Http/Controllers/PageController.php` | Ubah semua `where('user_id')` → `whereHas('user', fn => where('opd_id', ...))` |
| 2 | `app/Http/Controllers/API/FormCrudController.php:18-23` | Sama |
| 3 | `app/Http/Controllers/API/DashboardController.php` | Sama |
| 4 | `app/Policies/FormPolicy.php` | Ubah `$user->id === $form->user_id` → `$user->opd_id === $form->opd_id` |
| 5 | `app/Livewire/FormEditor.php` | Cek authorize |
| 6 | `database/seeders/DinasUserSeeder.php` | Tambah opd_id |
| 7 | `database/seeders/OPDSeeder.php` (baru) | Seeder tabel opds |

#### D. Implementasi Data Classification

| No | File | Perubahan |
|---|---|---|
| 1 | `app/Http/Controllers/API/FormCrudController.php` | Cek `canExport()` sebelum export |
| 2 | `app/Http/Controllers/API/SubmissionApiController.php` | Masking data INTERNAL/SENSITIVE |
| 3 | `app/Http/Controllers/PageController.php` | Cek classification di export & display |
| 4 | `app/Models/Form.php` | Tambah scope/helper untuk classification |
| 5 | `resources/views/` | Masking display value jika classification > PUBLIC |

#### E. Menambahkan Testing

| No | File | Test Scope |
|---|---|---|
| 1 | `tests/Feature/AuthTest.php` | Login web, login API, logout, role middleware, session limit |
| 2 | `tests/Feature/FormCrudTest.php` | CRUD form, publish, close, duplicate, field management |
| 3 | `tests/Feature/SubmissionTest.php` | Submit public (API + Livewire), validation, limit, export |
| 4 | `tests/Feature/AccessControlTest.php` | Isolasi data antar OPD, super_admin vs admin |
| 5 | `tests/Feature/AuditTest.php` | Audit log tercatat untuk aksi penting |
| 6 | `tests/Unit/FormModelTest.php` | isExpired, isFull, scopes |

---

### 4.2 Flow yang Terdampak (perubahan)

| Perubahan | Flow Terdampak |
|---|---|
| **Tambah field type** | Create form → Edit form → Public submit → API submit → Validasi → Render |
| **Integrasi API eksternal** | Auth → Endpoint baru → Response format → Error handling |
| **OPD-based access** | Semua query form + submission + dashboard + export |
| **Data classification** | Public form display → Submission storage → Export → Analytics → Audit |
| **Session/token limit** | Login API → Login Web → AuthController → SessionLimitService |

---

### 4.3 Bagian yang Jangan Disentuh

| Bagian | Alasan |
|---|---|
| **`app/Domains/`** | Sudah ditulis untuk fase 5-9. Jangan hapus, tapi jangan ubah sampai nanti di-wire ke controller |
| **`resources/views/layouts/`** | Layout utama (app, auth) di-extends banyak view |
| **`app/Http/Middleware/ForceJsonResponse.php`** | Critical untuk format API response |
| **`app/Providers/`** | Service provider bawaan Laravel |
| **`config/sanctum.php`** | Token prefix, expiry, middleware |
| **Database migration yg sudah di-`up()`** | Jangan edit migration lama. Buat migration baru. |
| **`.env`** | Jangan commit (ada di `.gitignore`). Gunakan `.env.example`. |

---

### 4.4 Rollback Strategy

| Skenario | Cara Rollback |
|---|---|
| **Migrasi gagal** | `php artisan migrate:rollback` |
| **Kode error production** | `git revert <commit-hash>` atau `git checkout HEAD~1 -- <file>` |
| **Integrasi API bermasalah** | Matikan feature flag di `.env`, rollback deployment |
| **Perubahan frontend jelek** | Git reset ke commit sebelumnya, redeploy |
| **Classification salah** | Migrasi balik, set semua form ke `public` default |

#### Pre-rollback Checklist:
1. Backup database: `mysqldump -u <user> -p <db> > backup_$(date +%Y%m%d).sql`
2. Catat commit hash: `git rev-parse HEAD`
3. Screenshot halaman sebelum perubahan
4. Pastikan migration punya method `down()` yang benar

---

### 4.5 Best Practice untuk Perubahan

1. **Buat migration baru** — jangan edit migration yang sudah running
2. **Gunakan Feature Flag** — `config('app.feature_x_enabled')` untuk toggle fitur tanpa kode
3. **Commit kecil & sering** — satu logical change per commit
4. **Test di DB copy** — jangan langsung di production
5. **Update user manual** — dokumentasi perubahan untuk admin
6. **Pertimbangkan Domain Services** — kalau nambah fitur baru, langsung pakai service pattern dari `app/Domains/` agar tidak perlu refactor 2x nanti

---

## Referensi File Penting

| File Path | Deskripsi |
|---|---|
| `routes/api.php` | 31 endpoint REST API v1 |
| `routes/web.php` | ~20 Blade routes |
| `routes/admin.php` | 8 route admin panel |
| `app/Http/Controllers/API/AuthController.php` | Login/logout/me/change-password |
| `app/Http/Controllers/API/FormCrudController.php` | CRUD form + publish/close/duplicate/analytics/export |
| `app/Http/Controllers/API/SubmissionApiController.php` | Submission public + admin |
| `app/Http/Controllers/API/FieldController.php` | Field CRUD + reorder |
| `app/Http/Controllers/PageController.php` | Semua render halaman Blade |
| `app/Livewire/FormEditor.php` | Form builder (field, section, settings, publish) |
| `app/Livewire/PublicForm.php` | Public form display + submission |
| `app/Livewire/CreateForm.php` | Create form wizard |
| `app/Policies/FormPolicy.php` | Authorization rules |
| `app/Http/Middleware/RoleMiddleware.php` | Role-based gate |
| `app/Services/AuditService.php` | Audit logger |
| `app/Services/SessionLimitService.php` | Session & token limiter |
| `app/Models/Form.php` | Form model (+ isExpired, isFull, scopes) |
| `app/Models/FormSubmission.php` | Submission model |
| `app/Enums/UserRole.php` | super_admin, admin |
| `app/Enums/DataClassification.php` | PUBLIC, INTERNAL, SENSITIVE |
| `app/Enums/FieldType.php` | 13 tipe field |
| `database/seeders/DinasUserSeeder.php` | 48 akun seed |
| `docs/ARCHITECTURE.md` | Dokumentasi arsitektur lengkap |
| `docs/IMPLEMENTATION_SUMMARY.md` | Ringkasan fase 1-4 |
