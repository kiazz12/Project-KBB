# AGENTS.md — Project KBB

Custom Google Forms clone for Pemerintah Kabupaten Bandung Barat (KBB). Laravel 13 + Livewire 4 + Blade. Tailwind CSS loaded via CDN in Blade templates (no utility-class build step). Vite processes `resources/js/app.js` and `resources/css/app.css` but is not used for Tailwind.

## Repository Layout

```
project-kbb/
├── backend/          ← ALL code lives here (Laravel app)
│   ├── app/
│   │   ├── Domains/          ← Domain services (6 domains)
│   │   ├── Http/Controllers/ ← Web, API, Admin controllers
│   │   ├── Livewire/         ← CreateForm, FormEditor, PublicForm + 4 helpers
│   │   ├── Models/           ← Eloquent models
│   │   ├── Services/         ← App-level services (Audit, Notification, SessionLimit, Form, Submission)
│   │   ├── Enums/            ← UserRole, FormStatus, FieldType, DataClassification
│   │   ├── Policies/         ← FormPolicy (OPD-based)
│   │   └── Exports/          ← Maatwebsite Excel export
│   ├── resources/views/      ← Blade templates + Livewire views
│   ├── routes/               ← web.php, api.php, admin.php, console.php
│   ├── database/migrations/  ← 26 migrations
│   ├── tests/                ← PHPUnit (Feature + Unit)
│   └── config/
├── docs/             ← Architecture docs, phase plans, master task list
└── start.bat         ← Stale: tries to start nonexistent frontend/ directory
```

## Commands

All commands run from `backend/` directory:

```bash
# Initial setup (install deps, generate key, migrate, build assets)
composer setup

# Dev server (artisan + vite)
php artisan serve          # API at :8000
npm run dev                # Vite at :5173

# Full dev via composer script (all-in-one)
composer dev               # Runs artisan serve + queue + pail + vite concurrently

# Tests
php artisan test           # All tests
php artisan test --filter=LoginSessionTest   # Single test class
php artisan test --filter="test_method_name" # Single test method

# Code style
php vendor/bin/pint        # Laravel Pint (default config, no pint.json)

# Database
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed   # Full reset

# Queue worker
php artisan queue:listen --tries=1 --timeout=0

# Log viewer
php artisan pail           # Log viewer
```

## Key Architecture Facts

### Three Parallel Auth Systems
1. **Web** (`routes/web.php`) — Session-based, `WebAuthController`
2. **API** (`routes/api.php`) — Sanctum tokens, `API\AuthController`, throttle: 5/min on login
3. **Admin** (`routes/admin.php`) — Session-based, super_admin only, separate login

### Domain Services vs Controllers — Mismatch
`app/Domains/` has 6 service classes with business logic, but **controllers do NOT use them yet**. Controllers use direct Eloquent queries + `App\Services\AuditService`. This is a known technical debt (Task 5.2 in MASTER-TASK-LIST.md).

### OPD-Based Access Control
- Users belong to an OPD (Organisasi Perangkat Daerah) — 47 OPDs
- `FormPolicy` enforces: super_admin sees all, admin sees own + same-OPD forms
- `AuthorizationService` has query-scoping methods but they're not wired into controllers yet

### Data Classification
Three levels: `public`, `internal`, `sensitive`. Controls export permissions (`DataClassification::canExport()`), masking, and retention. **Export block for SENSITIVE forms is not yet enforced** (Task 1.2).

### Session Limits
`SessionLimitService`: super_admin = 1 concurrent session, admin = 3. Limit check on login is not yet wired (Task 2.2).

## Testing

- PHPUnit with **SQLite in-memory** for tests (see `phpunit.xml` env overrides)
- Tests use `actingAs()` for auth, no external services needed
- Current coverage: 7 test files (low). See `docs/MASTER-TASK-LIST.md` Priority 4 for test gaps.

## Code Style

- **Laravel Pint** with default config (no `pint.json`)
- 4-space indent, UTF-8, LF line endings (`.editorconfig`)
- No custom PHP-CS-Fixer config

## Gotchas

- `start.bat` references `frontend/` directory — this doesn't exist. The frontend is Blade+Livewire inside `backend/resources/views/`. The bat file is stale.
- `PageController` is the heaviest file (~443 lines) — handles all Blade views, exports (CSV/XLSX/PDF/Uang Saku/Presensi), and form duplication.
- Public form submission via Livewire (`PublicForm.php`) has a **formula engine** for computed fields and **participant search** autocomplete from `participants` table.
- `.env` uses `DB_DATABASE=project-kbb` (MySQL). Tests override to SQLite in-memory.
- `composer dev` uses `npx concurrently` — requires Node.js alongside PHP.
- No `pint.json` or `.php-cs-fixer.php` — Pint uses Laravel defaults.

## Reference Docs

- `docs/ARCHITECTURE.md` — Full architecture, data flow, schema
- `docs/MASTER-TASK-LIST.md` — 20 tasks across 5 priorities (security → tech debt)
- `docs/INDEX.md` — Documentation index and phase roadmap
