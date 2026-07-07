# Project-KBB

SPBE forms app for Pemerintah Kabupaten Bandung Barat (Laravel 13 + Blade + Livewire).

## Architecture

- **Monolith**: Laravel 13 (`backend/`) serves both API and Blade frontend. No separate `frontend/` directory. All views are Blade templates at `resources/views/`.
- **Frontend**: Pure Blade + Livewire v4 (no JS framework) + Tailwind v3 (CDN). No Inertia.js, no React, no Alpine.js, no Vite frontend build. Interactive views are Livewire components (`app/Livewire/`). Vanilla JS is used only for trivial UI (sidebar toggle, password toggle).
- **Auth**: Session-based for web routes (POST /login), Sanctum API tokens for API routes.
- **API auth**: Sanctum accepts both Bearer tokens AND session cookies (stateful domains). Internal XHR from Blade views uses `X-CSRF-TOKEN` header + session cookie.
- **Domain services** (`app/Domains/*/Services/`) exist but controllers do NOT use them yet — controllers use direct model queries and the static `App\Services\AuditService`. Do NOT assume services are wired in.
- **Roles**: Only 2 from `App\Enums\UserRole`: `super_admin` and `admin`. The seeder creates `admin` accounts, not `operator`/`viewer`.
- **OPD model** exists (`app/Models/OPD`) with migration but seeder does NOT populate it — only `User.opd_id` exists.
- **No real tests** — `tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php` are boilerplate only.

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

# Seed accounts
php artisan db:seed --class=DinasUserSeeder

# List API routes
php artisan route:list --path=api

# List web routes
php artisan route:list --except-path=api
```

## API

- Base: `http://localhost:8000/api/v1`
- Response format: `{success: bool, data: ..., message: string}`
- Auth: Sanctum — session cookies for same-origin XHR, Bearer tokens for external clients (`kbb_` prefix, 24h expiry)
- Throttling: login 5/min, public form submission 10/min
- All API routes require `auth:sanctum` except `POST /auth/login`, `GET/POST /forms/public/{slug}`
- Internal XHR from Blade views must include `X-CSRF-TOKEN` header (from `<meta name="csrf-token">`)

## Database

| Driver | Env |
|--------|-----|
| SQLite | dev (default) |
| MySQL  | production |

Session, cache, and queue all use `database` driver.

## Key files

| Path | Purpose |
|------|---------|
| `routes/api.php` | 31 REST endpoints |
| `routes/web.php` | 14 Blade routes (named) |
| `app/Http/Controllers/API/` | 6 API controllers |
| `app/Http/Controllers/WebAuthController.php` | Session-based login/logout/change-password |
| `app/Http/Controllers/PageController.php` | Renders all Blade pages with data |
| `app/Models/` | Form, FormField, FormSubmission, SubmissionData, User, OPD, AuditLog |
| `app/Domains/*/Services/` | 7 domain services (not wired to controllers) |
| `app/Services/AuditService.php` | Static audit logger (actually used by controllers) |
| `app/Enums/` | UserRole (2), FormStatus (3), FieldType (13), DataClassification (3) |
| `app/Policies/FormPolicy.php` | Authorization (super_admin || owner) |
| `resources/views/layouts/` | `app.blade.php` (sidebar nav) + `auth.blade.php` (login layout) |
| `resources/views/` | 10 Blade templates (login, dashboard, forms CRUD, submissions, users, change-password, public-form) |
| `app/Livewire/` | 3 Livewire components (FormEditor, CreateForm, PublicForm) |
| `resources/views/livewire/` | 3 Livewire views |
| `database/seeders/DinasUserSeeder.php` | 48 accounts (super_admin + 47 admin) |

## Accounts

Email: `admin@{slug}.com` — Password: `admin12345`
Super admin: `admin@dinas.com`

Register/forgot-password are disabled. Only superadmin creates accounts.

## Gotchas

- **Tailwind v3 via CDN** (in `layouts/app.blade.php` + `layouts/auth.blade.php`) — NOT v4 via npm.
- **Alpine.js via CDN** — no npm build step for JS. Vite only bundles minimal CSS.
- **Livewire v4** — no npm build step for JS. `@livewireStyles`/`@livewireScripts` in layouts. Components auto-discovered from `app/Livewire/`.
- Controllers use `$this->authorize()` with FormPolicy, NOT domain AuthorizationService.
- FormPolicy checks: `super_admin` has full access, `admin` can only access own forms (`user_id` match).
- Login throttled at 5/min (API). Web login route has no throttling.
- `composer setup` runs `npm install --ignore-scripts` then `npm run build`.
- `composer dev` runs 4 concurrent processes. On Windows use `start.bat` instead.
- `resources/views/app.blade.php` (root-level, old Inertia shell) is unused.
- The `app/Http/Middleware/ForceJsonResponse` middleware is prepended to all API routes.
- PDF export uses `barryvdh/laravel-dompdf`.
- Soft deletes on `Form` and `OPD` models.
- CSRF token validation is **excluded** for POST `/login`.
- `resources/js/` contains only a stub `app.js` importing CSS — no React/Inertia files.
