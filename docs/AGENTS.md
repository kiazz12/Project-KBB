# Project-KBB

SPBE forms app for Pemerintah Kabupaten Bandung Barat (Laravel 13 + Blade + Livewire).

## Architecture

- **Monolith**: Laravel 13 (`backend/`) serves both API and Blade frontend. No separate frontend app.
- **Frontend**: Blade + Livewire v4 + Tailwind v3 (CDN). No JS framework, no Inertia, no Alpine. Vanilla JS for sidebar toggle and dark mode. CSS design system in `resources/css/app.css` (800+ lines of `.kbb-*` utility classes).
- **Stale `resources/views/app.blade.php`**: Old Inertia shell with `@inertia` + `@viteReactRefresh` + `.tsx` reference — unused. Real layout is `resources/views/layouts/app.blade.php`. Admin has separate layout at `resources/views/admin/layouts/app.blade.php` (no Livewire styles).
- **Auth**: Session-based for web routes, Sanctum API tokens for API routes.
- **Roles**: 2 roles from `App\Enums\UserRole`: `super_admin` and `admin`. Gated by `RoleMiddleware` in routes.
- **Blade + API dual access**: Most features (forms CRUD, submissions, export) accessible via both Blade web routes AND API endpoints.
- **Admin panel**: Separate route group in `routes/admin.php` for super_admin only (user management, oversight dashboard). Uses its own layout, separate login.
- **Domain services** (`app/Domains/*/Services/`) and `app/Services/FormService`/`SubmissionService` exist but controllers use direct model queries and static `App\Services\AuditService` instead. Do NOT assume services are wired in.
- **Notifications**: `UserObserver` creates `Notification` records on user create/update/delete for all super_admins. Password changes notify super_admins + logged to `audit_logs`. Super_admins see a bell icon (Livewire `NotificationBell` component) in the top bar + "Notifikasi" page at `/notifications`.
- **Session limits**: `SessionLimitService` enforces 1 session for super_admin, 3 for admin. Also limits Sanctum tokens. Applied in `WebAuthController` and `API\AuthController`.
- **No real tests** — `tests/Unit/` and `tests/Feature/` contain boilerplate only (just `ExampleTest.php`). Tests use SQLite `:memory:`.

## Commands

```bash
composer dev          # Run server + queue + logs + Vite concurrently (requires Node.js for npx concurrently)
composer setup        # Full project setup (from scratch): composer install, .env, key:generate, migrate, npm install, npm run build
composer test         # Run tests (SQLite :memory:)
composer pint         # Lint (Laravel Pint)
php artisan db:seed   # Seed all accounts + dummy data
php artisan db:seed --class=DinasUserSeeder    # Accounts only (48 users)
php artisan db:seed --class=DummyFormSeeder     # 5 forms + 87 submissions
php artisan route:list --path=api               # List API routes
php artisan route:list --except-path=api        # List web routes
```

`composer dev` runs 4 concurrent processes via `npx concurrently`. On Windows, `start.bat` in repo root is stale — it references a `frontend/` directory that does not exist. Use `composer dev` from `backend/` instead.

## API

- Base: `http://localhost:8000/api/v1`
- Response format: `{"success": bool, "data": ..., "message": string}`
- Auth: Sanctum — session cookies for same-origin, Bearer tokens (`kbb_` prefix, 24h expiry) for external clients
- Throttling: login 5/min, public form submission 10/min
- All API routes require `auth:sanctum` except `POST /auth/login`, `GET|POST /forms/public/{slug}`
- XHR from Blade views must include `X-CSRF-TOKEN` header (from `<meta name="csrf-token">`)
- `ForceJsonResponse` middleware prepended to all API routes

## Database

Default driver is SQLite (`config/database.php`), current `.env` uses MySQL (`project-kbb` database). Session, cache, and queue all use `database` driver. Soft deletes on `Form` and `OPD` models.

## Seeded accounts

Email: `admin@{slug}.com` — Password: `admin12345`
Super admin: `admin@dinas.com` (role: `super_admin`)
47 admin accounts for KBB kecamatan/dinas (see `backend/daftar_kbb.md` for the full list of 47 OPDs).

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
| `app/Models/` | Form, FormField, FormSection, FormSubmission, SubmissionData, User, OPD, AuditLog, Notification, Participant |
| `app/Livewire/` | 4 components: FormEditor, CreateForm, PublicForm, NotificationBell |
| `app/Services/` | AuditService (used by controllers), SessionLimitService (used by auth), FormService/SubmissionService (unused by controllers) |
| `app/Observers/UserObserver.php` | Creates notifications on user CRUD |
| `app/Enums/` | UserRole (2), FormStatus (3), FieldType (14), DataClassification (3) |
| `app/Policies/FormPolicy.php` | Authorization (super_admin full access, admin only own forms) |
| `app/Http/Middleware/RoleMiddleware.php` | Role-based route gating |
| `app/Http/Middleware/ForceJsonResponse.php` | Forces JSON on API routes |
| `resources/views/layouts/` | `app.blade.php` (sidebar nav + dark mode + Livewire), `auth.blade.php` (login layout) |
| `resources/views/admin/layouts/app.blade.php` | Admin panel layout (no Livewire) |
| `resources/css/app.css` | Full design system: `.kbb-*` classes, dark mode, animations |
| `database/seeders/DinasUserSeeder.php` | 48 accounts (super_admin + 47 admin) |
| `database/seeders/DummyFormSeeder.php` | 5 published forms with 87 submissions total |
| `backend/daftar_kbb.md` | Reference list of 47 KBB OPDs/kecamatan |

## Gotchas

- **Tailwind v3 via CDN** — NOT v4 via npm. Config is inline `<script>` tags in layouts.
- **No Alpine.js or React** — Livewire handles all interactivity.
- **Livewire v4** — components auto-discovered from `app/Livewire/`. `@livewireStyles`/`@livewireScripts` in main layout only (not admin layout).
- **Controllers use `$this->authorize()` with FormPolicy**, NOT domain AuthorizationService.
- **CSRF token validation is excluded** for POST `/login` (both web and admin).
- **PDF export** uses `barryvdh/laravel-dompdf`.
- **`composer setup`** runs `npm install --ignore-scripts` then `npm run build` (Vite bundles only CSS). `.npmrc` sets `ignore-scripts=true` globally.
- **DB_CONNECTION** defaults to SQLite in config; `.env` currently uses MySQL. Tests always use SQLite `:memory:`.
- **Mixed package manager artifacts** — both `package-lock.json` and `pnpm-lock.yaml` exist in `backend/`. `composer setup` uses npm.
- **`resources/views/app.blade.php`** is dead code (Inertia shell). Do not edit or reference it.
- **PHP 8.3+ required** (`composer.json` constraint `^8.3`).
- **CSS design system** — `resources/css/app.css` defines `.kbb-btn-*`, `.kbb-card`, `.kbb-input`, `.kbb-badge-*`, `.kbb-table`, dark mode overrides, and animation utilities. Prefer these over raw Tailwind for consistency.

## Features added (verify before relying on)

- **Excel export** — `maatwebsite/excel` (`SubmissionsExport` in `app/Exports/`). Route `forms.export.xlsx`; honors `DataClassification::canExport()`. Has no API counterpart (CSV/PDF do).
- **Form duplication in UI** — `PageController::duplicateForm` + POST `forms.duplicate`. Available from forms list and form show page. Replicates form + fields as a `draft`.
- **Bulk delete submissions** — `PageController::bulkDeleteSubmissions` + POST `forms.submissions.bulk-delete` (expects `ids[]`). Submissions list also has date filters `from`/`to` and `search`.
- **FormEditor autosave** — settings auto-saved every 30s via `wire:poll` (`autoSaveSettings()`) and on tab switch; shows a transient "Tersimpan otomatis" indicator. Only settings are autosaved, not individual fields.
- **NotificationBell** — already polls every 10s (`wire:poll.10s="refresh"`) and refreshes on window focus; no WebSocket/Echo setup. `.env` uses `BROADCAST_CONNECTION=log`, so real-time Echo is NOT wired up.
- **Public form bot protection** — honeypot field `company_website` (hidden, must stay empty) checked at top of `PublicForm::submitForm()`. Throttled 10/min. No Turnstile/Captcha.
