# Fase 1: Stabilisasi Baseline - Pemetaan Fitur Inti

## Status Saat Ini

Project-KBB telah memiliki fondasi Laravel 13 + React 19 dengan fitur-fitur inti berikut:

### Fitur yang Sudah Ada

#### 1. Autentikasi dan Manajemen Pengguna
- Login dengan email dan password
- Logout
- Change password
- Role-based access: `super_admin` dan user biasa
- Middleware authentication pada routes yang memerlukan login

**File Terkait:**
- `app/Http/Controllers/API/AuthController.php`
- `app/Http/Controllers/WebAuthController.php`
- `app/Models/User.php`
- Database: `users` table dengan kolom `role`

**Status:** Dasar sudah ada, perlu stabilisasi dan peningkatan untuk OPD-based access control

#### 2. Form Management (CRUD)
- Create, Read, Update, Delete form
- Publish dan close form
- Duplicate form
- Field management (create, update, delete, reorder fields)
- Form settings: title, description, status, configuration

**File Terkait:**
- `app/Http/Controllers/API/FormCrudController.php`
- `app/Models/Form.php`
- `app/Models/FormField.php`
- `app/Services/FormService.php`

**Status:** Implementasi dasar sudah ada, perlu refactor ke domain service yang lebih terstruktur

#### 3. Public Form Submission
- Menampilkan form publik berdasarkan slug
- Submit data dari pengguna eksternal
- Throttling pada endpoint publik (5 req/min untuk login, 10 req/min untuk submit)
- Validasi input publik

**File Terkait:**
- `app/Http/Controllers/API/SubmissionApiController.php`
- Route: `GET /v1/forms/public/{slug}` dan `POST /v1/forms/public/{slug}`

**Status:** Implementasi dasar ada, perlu pemisahan yang lebih jelas dari jalur internal

#### 4. Submission Management
- Menyimpan jawaban form ke database
- List submission per form
- View detail submission
- Delete submission
- Field data disimpan terstruktur

**File Terkait:**
- `app/Models/FormSubmission.php`
- `app/Models/SubmissionData.php`
- `app/Http/Controllers/API/SubmissionApiController.php`

**Status:** Implementasi dasar ada, perlu perlindungan akses yang lebih ketat

#### 5. Dashboard dan Analytics
- Dashboard stats (jumlah form, submission, users)
- Recent forms list
- Form analytics
- Export CSV dan PDF

**File Terkait:**
- `app/Http/Controllers/API/DashboardController.php`
- Export endpoints di FormCrudController

**Status:** Implementasi dasar ada, perlu optimasi query dan cache

#### 6. User Management
- Super admin dapat list, create, update, delete users
- Super admin dapat melihat forms milik user
- Endpoint terbatas pada super_admin

**File Terkait:**
- `app/Http/Controllers/API/UserController.php`

**Status:** Dasar ada, perlu integrasi dengan OPD-based access control

#### 7. Audit Log
- Model AuditLog sudah ada
- AuditService untuk mencatat aktivitas
- Database table untuk menyimpan audit trail

**File Terkait:**
- `app/Models/AuditLog.php`
- `app/Services/AuditService.php`

**Status:** Dasar ada, perlu dioptimalkan dan diintegrasikan ke semua endpoint penting

### Area Risiko Prioritas Tinggi

1. **Pemisahan Jalur Publik dan Internal**
   - Jalur publik dan internal masih bercampur dalam satu aplikasi tanpa pemisahan struktural yang jelas
   - Frontend publik mungkin masih memuat komponen admin yang tidak perlu

2. **Akses Lintas OPD**
   - Belum ada konsep OPD (Organisasi Perangkat Daerah) dalam database
   - Query tidak dibatasi per OPD, sehingga operator bisa melihat data lintas unit
   - Authorization policies masih minimal

3. **Export dan Analytics Berat**
   - Export CSV/PDF mungkin masih dilakukan synchronous dan bisa memblok request utama
   - Query untuk analytics tidak dirancang untuk efisiensi

4. **Dokumentasi Minimal**
   - Dokumentasi project masih sangat terbatas
   - SOP deploy, backup, restore, dan incident handling belum ada

5. **Testing Hampir Tidak Ada**
   - Tidak ada automated testing yang komprehensif untuk critical path
   - Regresi test untuk akses control tidak ada

### Deliverable Fase 1

- [x] Pemetaan fitur inti yang sudah ada
- [x] Identifikasi jalur publik vs internal
- [x] Daftar risiko prioritas tinggi
- [x] File ini sebagai baseline reference

### Acceptance Criteria

- [x] Semua fitur inti terpetakan
- [x] Jalur publik dan internal sudah dibedakan
- [x] Risiko prioritas sudah diidentifikasi
- [x] Dokumentasi baseline tersedia

## Daftar Fitur Inti

### Routes dan Endpoints Publik

1. `GET /form/{slug}` - Menampilkan form publik (PageController)
2. `GET /v1/forms/public/{slug}` - Get public form data via API
3. `POST /v1/forms/public/{slug}` - Submit public form (throttled 10 req/min)

### Routes dan Endpoints Internal (Authenticated)

1. **Authentication**
   - `POST /v1/auth/login` - Login (throttled 5 req/min)
   - `POST /v1/auth/logout` - Logout
   - `GET /v1/auth/me` - Get current user
   - `POST /v1/auth/change-password` - Change password

2. **Form Management**
   - `GET /v1/forms` - List forms (user bisa access)
   - `POST /v1/forms` - Create form
   - `GET /v1/forms/{form}` - Get form detail
   - `PUT /v1/forms/{form}` - Update form
   - `DELETE /v1/forms/{form}` - Delete form
   - `POST /v1/forms/{form}/duplicate` - Duplicate form
   - `POST /v1/forms/{form}/publish` - Publish form
   - `POST /v1/forms/{form}/close` - Close form

3. **Field Management**
   - `POST /v1/forms/{form}/fields` - Add field
   - `PUT /v1/forms/{form}/fields/{field}` - Update field
   - `DELETE /v1/forms/{form}/fields/{field}` - Delete field
   - `POST /v1/forms/{form}/fields/reorder` - Reorder fields

4. **Submission Management**
   - `GET /v1/forms/{form}/submissions` - List submissions
   - `GET /v1/forms/{form}/submissions/{submission}` - Get submission detail
   - `DELETE /v1/forms/{form}/submissions/{submission}` - Delete submission

5. **Dashboard**
   - `GET /v1/dashboard/stats` - Dashboard statistics
   - `GET /v1/dashboard/recent-forms` - Recent forms

6. **Analytics & Export**
   - `GET /v1/forms/{form}/analytics` - Form analytics
   - `GET /v1/forms/{form}/export/csv` - Export CSV
   - `GET /v1/forms/{form}/export/pdf` - Export PDF

7. **User Management** (Super Admin Only)
   - `GET /v1/users` - List users
   - `POST /v1/users` - Create user
   - `GET /v1/users/{user}/forms` - Get user forms
   - `PUT /v1/users/{user}` - Update user
   - `DELETE /v1/users/{user}` - Delete user

## Database Tables

1. **users** - User accounts, roles
2. **forms** - Form master data
3. **form_fields** - Form field definitions
4. **form_submissions** - Submission records
5. **submission_data** - Jawaban individual field per submission
6. **audit_logs** - Audit trail
7. **personal_access_tokens** - Sanctum tokens

## Models

- User
- Form
- FormField
- FormSubmission
- SubmissionData
- AuditLog

## Enums

- UserRole (super_admin, user)
- FieldType
- FormStatus

## Services

- AuditService
- FormService

## Policies

- FormPolicy (belum lengkap untuk OPD-based access)

## Langkah Berikutnya

Melanjutkan ke Fase 2: Rapikan Struktur Modular Monolith dengan fokus pada:

1. Membuat domain structure yang jelas (Auth, PublicForms, InternalFormManagement, SubmissionManagement, Reporting, Audit)
2. Refactor controller menjadi thin controllers dengan business logic di service layer
3. Pemisahan route publik dan internal yang lebih struktural
4. Pemisahan asset frontend publik dan internal

---

**Dokumentasi dibuat:** 2026-06-26
**Fase Status:** Baseline stabilisasi selesai, siap untuk Fase 2
