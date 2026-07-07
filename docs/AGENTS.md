# Project-KBB

SPBE forms app for Pemerintah Kabupaten Bandung Barat (Laravel 13 + Blade + Livewire).

## Architecture

- **Monolith**: Laravel 13 (`backend/`) serves both API and Blade frontend. No separate `frontend/` directory.
- **Frontend**: Pure Blade + Livewire v4 + Tailwind v3 (CDN). No JS framework, no Inertia, no Alpine. Vanilla JS for sidebar toggle and dark mode.
- **Auth**: Session-based for web routes, Sanctum API tokens for API routes. Sanctum accepts both Bearer tokens and session cookies.
- **Roles**: 2 roles from `App\Enums\UserRole`: `super_admin` and `admin`. The `RoleMiddleware` gates routes.
- **Blade + API dual access**: Most features (forms CRUD, submissions, export) are accessible via both Blade web routes AND API endpoints.
- **Admin panel**: Separate route group in `routes/admin.php` for super_admin only (user management, oversight dashboard). Accessible via sidebar for super_admin.
- **Domain services** (`app/Domains/*/Services/`) and `app/Services/FormService`, `app/Services/SubmissionService` exist but controllers use direct model queries and static `App\Services\AuditService` instead. Do NOT assume services are wired in.
- **Notifications**: When any admin changes their password, a notification is sent to all super_admin accounts and logged to `audit_logs` via `AuditService`. Superadmins see a bell icon with unread count in the top bar + a "Notifikasi" page at `/notifications`. Superadmin CRUD operations on users are already logged to `audit_logs`.
- **No real tests** — `tests/Unit/` and `tests/Feature/` contain boilerplate only. Tests use SQLite `:memory:`.

## Commands

```bash
# Run everything (server + queue + logs + Vite concurrently)
composer dev

# Full project setup (from scratch)
composer setup

# Run tests
composer test

# Lint (Laravel Pint)
composer pint

# Seed all accounts + dummy data
php artisan db:seed

# Seed accounts only
php artisan db:seed --class=DinasUserSeeder

# Seed forms + submissions (5 forms, 87 submissions total)
php artisan db:seed --class=DummyFormSeeder

# List API routes
php artisan route:list --path=api

# List web routes
php artisan route:list --except-path=api
```

## API

- Base: `http://localhost:8000/api/v1`
- Response format: `{"success": bool, "data": ..., "message": string}`
- Auth: Sanctum — session cookies for same-origin requests, Bearer tokens (`kbb_` prefix, 24h expiry) for external clients
- Throttling: login 5/min, public form submission 10/min
- All API routes require `auth:sanctum` except `POST /auth/login`, `GET/POST /forms/public/{slug}`
- Internal XHR from Blade views must include `X-CSRF-TOKEN` header (from `<meta name="csrf-token">`)
- `ForceJsonResponse` middleware is prepended to all API routes

## Database

Default driver is SQLite (`config/database.php`), current `.env` uses MySQL. Session, cache, and queue all use `database` driver.

## Seeded accounts

Email: `admin@{slug}.com` — Password: `admin12345`
Super admin: `admin@dinas.com` (role: `super_admin`)
47 admin accounts corresponding to KBB kecamatan/dinas (role: `admin`)

Register and forgot-password are disabled. Only superadmin creates accounts.

## Key files

| Path | Purpose |
|------|---------|
| `routes/api.php` | ~31 REST endpoints across 6 API controllers |
| `routes/web.php` | ~20 Blade routes (named) via PageController |
| `routes/admin.php` | ~8 super_admin panel routes |
| `app/Http/Controllers/API/` | 6 API controllers |
| `app/Http/Controllers/Admin/` | 4 admin panel controllers (Auth, Dashboard, Form, User) |
| `app/Http/Controllers/WebAuthController.php` | Session-based login/logout/change-password |
| `app/Http/Controllers/PageController.php` | Renders all Blade pages |
| `app/Models/` | Form, FormField, FormSubmission, SubmissionData, User, OPD, AuditLog |
| `app/Domains/*/Services/` | 6 domain services (not wired to controllers) |
| `app/Services/` | AuditService (used by controllers), FormService, SubmissionService (unused by controllers) |
| `app/Enums/` | UserRole (2), FormStatus (3), FieldType (13), DataClassification (3) |
| `app/Policies/FormPolicy.php` | Authorization (super_admin has full access, admin only own forms) |
| `app/Http/Middleware/RoleMiddleware.php` | Role-based route gating |
| `app/Http/Middleware/ForceJsonResponse.php` | Forces JSON on API routes |
| `resources/views/layouts/` | `app.blade.php` (sidebar nav + dark mode), `auth.blade.php` (login layout) |
| `app/Livewire/` | 3 components: FormEditor, CreateForm, PublicForm |
| `database/seeders/DinasUserSeeder.php` | 48 accounts (super_admin + 47 admin) |
| `database/seeders/DummyFormSeeder.php` | 5 published forms with 87 submissions total across 5 themes |

## Gotchas

- **Tailwind v3 via CDN** — NOT v4 via npm. Tailwind config is inline in `<script>` tags in layouts.
- **No Alpine.js or React** — Livewire handles all interactivity. `resources/views/app.blade.php` is an old Inertia shell; completely unused.
- **Livewire v4** — components auto-discovered from `app/Livewire/`. `@livewireStyles`/`@livewireScripts` in both layouts.
- **Controllers use `$this->authorize()` with FormPolicy**, NOT domain AuthorizationService.
- **CSRF token validation is excluded** for POST `/login` (both web and admin).
- **Soft deletes** on `Form` and `OPD` models.
- **PDF export** uses `barryvdh/laravel-dompdf`.
- **`composer setup`** runs `npm install --ignore-scripts` then `npm run build` (Vite bundles only CSS).
- **`composer dev`** runs 4 concurrent processes. On Windows use `start.bat` instead (starts API + expects a frontend at `frontend/` that does not exist yet).
- **DB_CONNECTION** defaults to SQLite in config; the `.env` currently uses MySQL. Tests always use SQLite `:memory:`.
