# Fase 2: Rapikan Struktur Modular Monolith

## Tujuan

Membuat batas domain jelas tanpa mengubah perilaku bisnis. Aplikasi tetap satu unit deploy, tetapi dengan struktur internal yang terorganisir per domain bisnis.

## Domain Structure

### 1. Auth Domain
**Lokasi:** `app/Domains/Auth/`

**Tanggung Jawab:**
- Login dan logout
- Manajemen sesi dan token
- Change password
- Persiapan untuk integrasi SSO

**Services:**
- `AuthService` - Core authentication logic

**Routes:**
- `POST /v1/auth/login`
- `POST /v1/auth/logout`
- `GET /v1/auth/me`
- `POST /v1/auth/change-password`

---

### 2. PublicForms Domain
**Lokasi:** `app/Domains/PublicForms/`

**Tanggung Jawab:**
- Menampilkan form publik
- Validasi input pengguna eksternal
- Submit data publik
- Pengamanan request publik

**Services:**
- `PublicFormService` - Public form operations

**Routes:**
- `GET /v1/forms/public/{slug}`
- `POST /v1/forms/public/{slug}` (throttled)
- `GET /form/{slug}` (render page)

**Catatan Desain:**
- Jalur publik tidak boleh memuat komponen admin
- Harus tetap responsif walaupun beban internal tinggi
- Throttling dan validasi ketat diterapkan

---

### 3. InternalForms Domain
**Lokasi:** `app/Domains/InternalForms/`

**Tanggung Jawab:**
- CRUD form
- Manajemen field
- Publish, close, duplicate form
- Pembatasan akses form berdasarkan pemilik

**Services:**
- `FormManagementService` - Form CRUD dan management

**Routes:**
- `GET /v1/forms`
- `POST /v1/forms`
- `GET /v1/forms/{form}`
- `PUT /v1/forms/{form}`
- `DELETE /v1/forms/{form}`
- `POST /v1/forms/{form}/publish`
- `POST /v1/forms/{form}/close`
- `POST /v1/forms/{form}/duplicate`
- `POST /v1/forms/{form}/fields`
- `PUT /v1/forms/{form}/fields/{field}`
- `DELETE /v1/forms/{form}/fields/{field}`
- `POST /v1/forms/{form}/fields/reorder`

**Catatan Desain:**
- Operator hanya kelola form miliknya
- Admin pusat dapat kelola semua form
- Setiap perubahan meninggalkan audit trail

---

### 4. Submissions Domain
**Lokasi:** `app/Domains/Submissions/`

**Tanggung Jawab:**
- Penyimpanan jawaban
- Pengelolaan lampiran
- Pembacaan dan penghapusan submission
- Paginasi submission

**Services:**
- `SubmissionService` - Submission management

**Routes:**
- `GET /v1/forms/{form}/submissions`
- `GET /v1/forms/{form}/submissions/{submission}`
- `DELETE /v1/forms/{form}/submissions/{submission}`

**Catatan Desain:**
- Akses harus dibatasi per form owner
- Submission harus dipaginasi
- Tidak boleh load semua data sekaligus

---

### 5. Reporting Domain
**Lokasi:** `app/Domains/Reporting/`

**Tanggung Jawab:**
- Dashboard statistik
- Form analytics
- Export CSV/PDF

**Services:**
- `ReportingService` - Reporting logic

**Routes:**
- `GET /v1/dashboard/stats`
- `GET /v1/dashboard/recent-forms`
- `GET /v1/forms/{form}/analytics`
- `GET /v1/forms/{form}/export/csv`
- `GET /v1/forms/{form}/export/pdf`

**Catatan Desain:**
- Dashboard harus cached
- Export berat dipindahkan ke queue
- Statistics queries harus terindeks

---

### 6. Audit Domain
**Lokasi:** `app/Domains/Audit/`

**Tanggung Jawab:**
- Audit log aktivitas penting
- Pencatatan error
- Jejak perubahan konfigurasi

**Services:**
- `AuditDomainService` - Audit logging

**Catatan Desain:**
- Audit tidak boleh memblok request utama
- Hanya catat event bisnis penting
- Log harus dapat menjawab: siapa, apa, kapan, pada data apa

---

## Directory Structure

```
backend/
├── app/
│   ├── Domains/
│   │   ├── Auth/
│   │   │   ├── Services/
│   │   │   │   └── AuthService.php
│   │   │   ├── Models/
│   │   │   ├── Actions/
│   │   │   └── Requests/
│   │   ├── PublicForms/
│   │   │   ├── Services/
│   │   │   │   └── PublicFormService.php
│   │   │   ├── Models/
│   │   │   ├── Actions/
│   │   │   └── Requests/
│   │   ├── InternalForms/
│   │   │   ├── Services/
│   │   │   │   └── FormManagementService.php
│   │   │   ├── Models/
│   │   │   ├── Actions/
│   │   │   └── Requests/
│   │   ├── Submissions/
│   │   │   ├── Services/
│   │   │   │   └── SubmissionService.php
│   │   │   ├── Models/
│   │   │   ├── Actions/
│   │   │   └── Requests/
│   │   ├── Reporting/
│   │   │   ├── Services/
│   │   │   │   └── ReportingService.php
│   │   │   ├── Models/
│   │   │   ├── Actions/
│   │   │   └── Requests/
│   │   └── Audit/
│   │       ├── Services/
│   │       │   └── AuditDomainService.php
│   │       ├── Models/
│   │       ├── Actions/
│   │       └── Requests/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── API/
│   │   │   └── Web/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   └── Models/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── domains/  (NEW)
│       ├── auth.php
│       ├── public-forms.php
│       ├── internal-forms.php
│       ├── submissions.php
│       ├── reporting.php
│       └── audit.php
└── database/
    ├── migrations/
    └── seeders/
```

## Routes Organization

Routes akan diorganisir berdasarkan domain:

- `routes/domains/auth.php` - Auth routes
- `routes/domains/public-forms.php` - Public form routes
- `routes/domains/internal-forms.php` - Internal form management
- `routes/domains/submissions.php` - Submission management
- `routes/domains/reporting.php` - Dashboard dan analytics
- `routes/domains/audit.php` - Audit log routes

**Main Routes File** (`routes/api.php`):
```php
<?php
use Illuminate\Support\Facades\Route;

// Public routes (no auth required)
include base_path('routes/domains/public-forms.php');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    include base_path('routes/domains/auth.php');
    include base_path('routes/domains/internal-forms.php');
    include base_path('routes/domains/submissions.php');
    include base_path('routes/domains/reporting.php');
});

// Admin only routes
Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
    include base_path('routes/domains/audit.php');
});
```

## Service Injection Pattern

Setiap service domain akan di-inject ke controller melalui dependency injection:

```php
<?php
namespace App\Http\Controllers\API;

use App\Domains\InternalForms\Services\FormManagementService;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function __construct(private FormManagementService $formService)
    {
    }

    public function store(Request $request)
    {
        $form = $this->formService->createForm(
            auth()->user(),
            $request->validated()
        );
        return response()->json($form);
    }
}
```

## Model Structure

**Shared Models** (di `app/Models/`):
- User
- Form
- FormField
- FormSubmission
- SubmissionData
- AuditLog

Tidak ada model domain-spesifik di tahap ini. Setiap domain hanya memiliki service yang mengoperasikan shared models.

## Testing Strategy

Setiap domain akan memiliki test suite:

```
tests/
├── Feature/
│   ├── Auth/
│   ├── PublicForms/
│   ├── InternalForms/
│   ├── Submissions/
│   ├── Reporting/
│   └── Audit/
└── Unit/
    ├── Services/
    │   ├── AuthServiceTest.php
    │   ├── PublicFormServiceTest.php
    │   ├── FormManagementServiceTest.php
    │   ├── SubmissionServiceTest.php
    │   ├── ReportingServiceTest.php
    │   └── AuditDomainServiceTest.php
```

## Deliverables Fase 2

- [x] Domain directory structure dibuat
- [x] Service layer untuk setiap domain dibuat
- [x] Auth service diimplementasikan
- [x] PublicForms service diimplementasikan
- [x] InternalForms service diimplementasikan
- [x] Submissions service diimplementasikan
- [x] Reporting service diimplementasikan
- [x] Audit service diimplementasikan
- [ ] Routes reorganisasi ke domain-based structure
- [ ] Controllers refactor untuk menggunakan domain services
- [ ] Frontend dipisahkan antara publik dan internal

## Acceptance Criteria

- [x] Seluruh use case utama punya domain dan service yang jelas
- [ ] Jalur publik dan internal terpisah secara struktural di routes
- [ ] Controller adalah thin layer yang hanya orchestrate
- [ ] Service layer menangani semua business logic
- [ ] Tests dapat memvalidasi domain logic tanpa controller

---

**Dokumentasi dibuat:** 2026-06-26
**Fase Status:** Domain structure dan services selesai, siap untuk refactor routes dan controllers
