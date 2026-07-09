# Project-KBB

SPBE forms app for Pemerintah Kabupaten Bandung Barat (Laravel 13 + Blade + Livewire).

## Architecture

- **Monolith**: Laravel 13 (`backend/`) serves both API and Blade frontend.
- **Frontend**: Blade + Livewire v4 + Tailwind v3 (CDN). No JS framework, no Inertia, no Alpine. Vanilla JS for sidebar toggle and dark mode.
- **Stale `resources/views/app.blade.php`**: Old Inertia shell with `@inertia` + `@viteReactRefresh` — unused. Real layout is `resources/views/layouts/app.blade.php`.
- **Auth**: Session-based for web routes, Sanctum API tokens for API routes.
- **Roles**: 2 roles from `App\Enums\UserRole`: `super_admin` and `admin`. Gated by `RoleMiddleware` in routes.
- **Blade + API dual access**: Most features (forms CRUD, submissions, export) accessible via both Blade web routes AND API endpoints.
- **Admin panel**: Separate route group in `routes/admin.php` for super_admin only (user management, oversight dashboard).
- **Domain services** (`app/Domains/*/Services/`) and `app/Services/FormService`/`SubmissionService` exist but controllers use direct model queries and static `App\Services\AuditService` instead. Do NOT assume services are wired in.
- **Notifications**: `UserObserver` creates `Notification` records on user create/update/delete for all super_admins. Password changes notify super_admins + logged to `audit_logs`. Super_admins see a bell icon (Livewire `NotificationBell` component) in the top bar + "Notifikasi" page at `/notifications`.
- **Session limits**: `SessionLimitService` enforces 1 session for super_admin, 3 for admin. Also limits Sanctum tokens. Applied in `WebAuthController` and `API\AuthController`.
- **No real tests** — `tests/Unit/` and `tests/Feature/` contain boilerplate only. Tests use SQLite `:memory:`.

## Commands

```bash
composer dev          # Run server + queue + logs + Vite concurrently
composer setup        # Full project setup (from scratch)
composer test         # Run tests (SQLite :memory:)
composer pint         # Lint (Laravel Pint)
php artisan db:seed   # Seed all accounts + dummy data
php artisan db:seed --class=DinasUserSeeder    # Accounts only (48 users)
php artisan db:seed --class=DummyFormSeeder     # 5 forms + 87 submissions
php artisan route:list --path=api               # List API routes
php artisan route:list --except-path=api        # List web routes
```

`composer dev` runs 4 concurrent processes. On Windows use `start.bat` instead (starts Laravel serve + expects `frontend/` at port 5173 which does not exist yet).

## API

- Base: `http://localhost:8000/api/v1`
- Response format: `{"success": bool, "data": ..., "message": string}`
- Auth: Sanctum — session cookies for same-origin, Bearer tokens (`kbb_` prefix, 24h expiry) for external clients
- Throttling: login 5/min, public form submission 10/min
- All API routes require `auth:sanctum` except `POST /auth/login`, `GET|POST /forms/public/{slug}`
- XHR from Blade views must include `X-CSRF-TOKEN` header (from `<meta name="csrf-token">`)
- `ForceJsonResponse` middleware prepended to all API routes

## Database

Default driver is SQLite (`config/database.php`), current `.env` uses MySQL. Session, cache, and queue all use `database` driver. Soft deletes on `Form` and `OPD` models.

## Seeded accounts

Email: `admin@{slug}.com` — Password: `admin12345`
Super admin: `admin@dinas.com` (role: `super_admin`)
47 admin accounts for KBB kecamatan/dinas (see `daftar_kbb.md` for the full list of 47 OPDs).

Register and forgot-password are disabled. Only superadmin creates accounts.

## Key files

| Path | Purpose |
|------|---------|
| `routes/api.php` | ~31 REST endpoints across 6 API controllers |
| `routes/web.php` | ~20 Blade routes via PageController + notification routes + uploads |
| `routes/admin.php` | ~8 super_admin panel routes |
| `app/Http/Controllers/API/` | 6 API controllers |
| `app/Http/Controllers/Admin/` | 4 admin panel controllers (Auth, Dashboard, Form, User) |
| `app/Http/Controllers/WebAuthController.php` | Session-based login/logout/change-password |
| `app/Http/Controllers/PageController.php` | Renders all Blade pages |
| `app/Http/Controllers/NotificationController.php` | Notification list, unread count, mark read |
| `app/Models/` | Form, FormField, FormSection, FormSubmission, SubmissionData, User, OPD, AuditLog, Notification |
| `app/Livewire/` | 4 components: FormEditor, CreateForm, PublicForm, NotificationBell |
| `app/Services/` | AuditService (used by controllers), SessionLimitService (used by auth), FormService/SubmissionService (unused by controllers) |
| `app/Observers/UserObserver.php` | Creates notifications on user CRUD |
| `app/Enums/` | UserRole (2), FormStatus (3), FieldType (13), DataClassification (3) |
| `app/Policies/FormPolicy.php` | Authorization (super_admin full access, admin only own forms) |
| `app/Http/Middleware/RoleMiddleware.php` | Role-based route gating |
| `app/Http/Middleware/ForceJsonResponse.php` | Forces JSON on API routes |
| `resources/views/layouts/` | `app.blade.php` (sidebar nav + dark mode), `auth.blade.php` (login layout) |
| `database/seeders/DinasUserSeeder.php` | 48 accounts (super_admin + 47 admin) |
| `database/seeders/DummyFormSeeder.php` | 5 published forms with 87 submissions total |
| `daftar_kbb.md` | Reference list of 47 KBB OPDs/kecamatan |

## Gotchas

- **Tailwind v3 via CDN** — NOT v4 via npm. Config is inline `<script>` tags in layouts.
- **No Alpine.js or React** — Livewire handles all interactivity.
- **Livewire v4** — components auto-discovered from `app/Livewire/`. `@livewireStyles`/`@livewireScripts` in both layouts.
- **Controllers use `$this->authorize()` with FormPolicy**, NOT domain AuthorizationService.
- **CSRF token validation is excluded** for POST `/login` (both web and admin).
- **PDF export** uses `barryvdh/laravel-dompdf`.
- **`composer setup`** runs `npm install --ignore-scripts` then `npm run build` (Vite bundles only CSS).
- **DB_CONNECTION** defaults to SQLite in config; `.env` currently uses MySQL. Tests always use SQLite `:memory:`.
