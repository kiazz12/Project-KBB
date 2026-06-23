# Project-KBB

A Google Forms-like application for Pemerintah Kabupaten Bandung Barat (KBB), compliant with **Permenkomdigi No. 6 Tahun 2025** (Standar Teknis dan Prosedur Pembangunan dan Pengembangan Aplikasi Sistem Pemerintahan Berbasis Elektronik / SPBE).

## Tech Stack

- **Backend:** Laravel 13 (PHP 8.3) — pure REST API
- **Frontend:** React 19 + Vite + Tailwind CSS v4 — SPA
- **Database:** SQLite (dev) / MySQL (production)
- **Auth:** Laravel Sanctum (token-based for SPA)
- **Design:** Glassmorphism, 3D depth, bentogrid, Gen UI
- **Colors:** Deep blue #003778 (KBB), Gold #C8A45C

## Project Structure

```
project-kbb/
├── backend/            # Laravel API
│   ├── app/Http/Controllers/API/  # REST API controllers
│   ├── app/Models/                 # Eloquent models
│   ├── app/Services/               # Business logic (AuditService)
│   ├── app/Enums/                  # UserRole, FormStatus, FieldType
│   ├── routes/api.php              # 29 API routes
│   └── database/seeders/           # User seeds (48 accounts)
└── frontend/           # React SPA
    └── src/
        ├── services/   # OOP service layer (BaseService + 4 services)
        ├── components/ # Reusable components
        ├── context/    # AuthContext
        ├── layouts/    # AppLayout, AuthLayout
        ├── pages/      # 12 page components
        └── types/      # TypeScript interfaces
```

## Commands

```bash
# Terminal 1: Laravel API (http://localhost:8000)
cd project-kbb && php artisan serve --port=8000

# Terminal 2: React frontend (http://localhost:5173)
cd project-kbb/frontend && npm run dev

# Start both simultaneously (Windows)
start.bat

# Build React for production
cd project-kbb/frontend && npm run build

# Seed all accounts
php artisan db:seed --class=DinasUserSeeder

# List routes
php artisan route:list --path=api
```

## API Base URL

`http://localhost:8000/api/v1`

Frontend Vite dev server proxies `/api` → `http://localhost:8000`.

## Dummy Accounts (48 total)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@dinas.com | admin12345 |
| Admin (47 OPD/Kecamatan) | admin@{slug}.dinas.com | admin12345 |

Email format: `admin@{slug}.dinas.com` for all non-superadmin accounts.

Email format: `admin@{slug}.dinas.com` with abbreviation-based slugs:

| Kategori | Slug | Contoh Email |
|----------|------|-------------|
| Kecamatan (16) | `kec-{nama}` | `admin@kec-lembang.dinas.com` |
| Dinas Pendidikan | `diknas` | `admin@diknas.dinas.com` |
| Dinas Kesehatan | `dinkes` | `admin@dinkes.dinas.com` |
| Dinas Sosial | `dinsos` | `admin@dinsos.dinas.com` |
| Disnaker | `disnaker` | `admin@disnaker.dinas.com` |
| Dinas LH | `dlh` | `admin@dlh.dinas.com` |
| Disdukcapil | `disdukcapil` | `admin@disdukcapil.dinas.com` |
| Diskominfo | `diskominfo` | `admin@diskominfo.dinas.com` |
| DPM PTSP | `dpmptsp` | `admin@dpmptsp.dinas.com` |
| Bappeda | `bappeda` | `admin@bappeda.dinas.com` |
| Bapenda | `bapenda` | `admin@bapenda.dinas.com` |
| BKPSDM | `bkpsdm` | `admin@bkpsdm.dinas.com` |
| Kesbangpol | `kesbangpol` | `admin@kesbangpol.dinas.com` |
| BPBD | `bpbd` | `admin@bpbd.dinas.com` |
| Sekda | `sekda` | `admin@sekda.dinas.com` |
| Setwan | `setwan` | `admin@setwan.dinas.com` |
| Inspektorat | `inspektorat` | `admin@inspektorat.dinas.com` |
| Satpol PP | `satpolpp` | `admin@satpolpp.dinas.com` |

Register & forgot-password are disabled. Only superadmin creates accounts.

## API Routes (29 total)

### Public (no auth)
```
POST /v1/auth/login                # Login → returns token
GET  /v1/forms/public/{slug}       # Get published form by slug
POST /v1/forms/public/{slug}       # Submit to form
```

### Sanctum-protected
```
POST /v1/auth/logout               # Revoke token
GET  /v1/auth/me                   # Current user
POST /v1/auth/change-password      # Change password

# Dashboard
GET  /v1/dashboard/stats           # Stats overview
GET  /v1/dashboard/recent-forms    # Recent 5 forms

# Forms CRUD
GET    /v1/forms                   # List owned forms
POST   /v1/forms                   # Create form
GET    /v1/forms/{id}              # Show form with fields
PUT    /v1/forms/{id}              # Update form
DELETE /v1/forms/{id}              # Soft delete
POST   /v1/forms/{id}/duplicate    # Duplicate with fields
POST   /v1/forms/{id}/publish      # Set published
POST   /v1/forms/{id}/close        # Set closed
GET    /v1/forms/{id}/analytics    # Stats per date + field
GET    /v1/forms/{id}/export/csv   # CSV download

# Fields
POST   /v1/forms/{id}/fields       # Add field
PUT    /v1/forms/{id}/fields/{fid} # Update field
DELETE /v1/forms/{id}/fields/{fid} # Delete field
POST   /v1/forms/{id}/fields/reorder # Reorder fields

# Submissions
GET    /v1/forms/{id}/submissions              # List submissions
GET    /v1/forms/{id}/submissions/{sid}        # Show submission
DELETE /v1/forms/{id}/submissions/{sid}        # Delete submission

# User Management (superadmin only)
GET    /v1/users                    # List users
POST   /v1/users                    # Create user
PUT    /v1/users/{id}               # Update user
DELETE /v1/users/{id}               # Delete user
```

## Frontend Pages

| Route | Component | Description |
|-------|-----------|-------------|
| `/login` | Login | Glassmorphism login form |
| `/dashboard` | Dashboard | Stats + recent forms |
| `/forms` | FormsIndex | Form listing with search/tabs |
| `/forms/create` | FormCreate | New form form |
| `/forms/:id/edit` | FormEdit | Form builder with field editor + settings |
| `/forms/:id` | FormShow | Form detail + actions |
| `/forms/:id/analytics` | FormAnalytics | Charts + field stats |
| `/forms/:id/submissions` | SubmissionsIndex | Submissions table |
| `/forms/:formId/submissions/:id` | SubmissionShow | Submission detail |
| `/users` | UsersIndex | User CRUD (superadmin only) |
| `/change-password` | ChangePassword | Change current user password |
| `/form/:slug` | PublicForm | Public form submission |

## Permenkomdigi No. 6/2025 Compliance

### Pasal 3 — Standar Teknis
| Ayat | Persyaratan | Implementasi |
|------|-------------|--------------|
| (2) a | Persyaratan umum | REST API, JSON responses, HTTPS-ready |
| (2) b | Infrastruktur SPBE | Laravel Sanctum token auth, CORS configured |
| (2) c | Siklus pengembangan | Analisis kebutuhan → Perencanaan → Rancang bangun → Implementasi → Uji kelaikan → Pemeliharaan → Evaluasi |
| (2) d | Data dan informasi | SQLite/MySQL, migrations, validated inputs |
| (2) e | Interoperabilitas data | RESTful `/api/v1/forms/*` endpoints, JSON format, standardized response `{success, data, message}` |
| (2) f | Keberlangsungan layanan | Soft deletes, form status workflow (draft → published → closed), `isExpired()`/`isFull()` checks |
| (2) g | Manajemen SPBE | Audit trail (`audit_logs` + `AuditService`), Role-based access (`FormPolicy` + `RoleMiddleware`) |
| (2) h | Dokumentasi | `AGENTS.md`, route list (`php artisan route:list`), OpenAPI-ready response formats |

### Pasal 6 — Siklus Pembangunan dan Pengembangan
| Tahap | Implementasi |
|-------|-------------|
| Analisis kebutuhan | Form builder with 13 field types, submission management, analytics |
| Perencanaan | RBAC (super_admin/admin), BCDR-ready soft deletes |
| Rancang bangun | Glassmorphism UI, mobile-responsive, Laravel 13 + React 19 |
| Implementasi | Feature-complete: form CRUD, fields, submissions, export, analytics |
| Uji kelaikan | TypeScript strict mode, Laravel validation, 0 build errors |
| Pemeliharaan | Audit logging for all mutations, pagination, error handling |
| Evaluasi | Analytics dashboard, submission statistics per field |

### Pasal 10 — Implementasi
- REST API controllers with proper authorization
- Service layer (`AuditService`) for business logic separation
- OOP frontend service layer (`BaseService` → `AuthService`, `FormService`, etc.)

### Fitur Keamanan & Tata Kelola
- **Audit trail** — `audit_logs` table, `AuditService` logging on every CUD operation
- **Interoperabilitas** — REST API at `/api/v1/forms/*` with JSON responses
- **RBAC** — `super_admin` / `admin` / `operator` / `viewer` roles with `FormPolicy` authorization
- **Data protection** — Sanctum tokens, input validation, authorization policies
- **TTE readiness** — Digital signature field type (`signature`) in form builder
- **Service continuity** — Soft deletes, form status management (draft/published/closed), expiry/limit checks

## Key Files

| Path | Description |
|------|-------------|
| `app/Models/` | Form, FormField, FormSubmission, SubmissionData, AuditLog, User |
| `app/Http/Controllers/API/` | AuthController, FormCrudController, FieldController, SubmissionApiController, UserController, FormApiController |
| `app/Services/AuditService.php` | Audit logging service |
| `routes/api.php` | All 29 API routes |
| `frontend/src/services/` | OOP service layer (BaseService + 4 services) |
| `frontend/src/pages/` | 12 page components |
| `frontend/src/types/index.ts` | TypeScript interfaces |
| `database/seeders/DinasUserSeeder.php` | 48 user accounts seeder |

## Remote

`https://github.com/kiazz12/Project-KBB.git`
