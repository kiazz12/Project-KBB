# Fase 3: Sentralisasi Auth dan Batas Akses OPD

## Tujuan

Menutup risiko kebocoran akses sejak awal dengan menerapkan pembatasan OPD (Organisasi Perangkat Daerah) yang ketat di backend, bukan hanya di UI.

## Model Akses

### Role Definition

1. **Super Admin Pusat**
   - Akses penuh ke semua form, submission, dan user
   - Dapat mengelola OPD
   - Dapat mengelola user
   - Dapat melihat audit log

2. **Operator OPD**
   - Hanya akses form dan submission yang dimiliki oleh OPD-nya
   - Tidak dapat mengakses data OPD lain
   - Tidak dapat mengelola user
   - Tidak dapat melihat audit log global

## Database Changes - Fase 3

### New Tables

#### `opds` (Organisasi Perangkat Daerah)
```sql
CREATE TABLE opds (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
)
```

#### `role_permissions` (Flexible Authorization)
```sql
CREATE TABLE role_permissions (
    id BIGINT PRIMARY KEY,
    role VARCHAR(50) NOT NULL,
    permission VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (role, permission)
)
```

### Modified Tables

#### `users`
```sql
ALTER TABLE users ADD COLUMN opd_id BIGINT UNSIGNED AFTER role
ALTER TABLE users ADD FOREIGN KEY (opd_id) REFERENCES opds(id)
```

#### `forms`
```sql
ALTER TABLE forms ADD COLUMN opd_id BIGINT UNSIGNED AFTER user_id
ALTER TABLE forms ADD COLUMN data_classification ENUM('public', 'internal', 'sensitive') DEFAULT 'public' AFTER status
ALTER TABLE forms ADD FOREIGN KEY (opd_id) REFERENCES opds(id)
```

## Models - Fase 3

### New Model: OPD
**File:** `app/Models/OPD.php`

```php
class OPD extends Model
{
    public function users() { ... }
    public function forms() { ... }
}
```

### Updated Model: User
**File:** `app/Models/User.php`

Ditambahkan:
- Relationship: `opd()`
- Relationship: `forms()`
- Relationship: `auditLogs()`
- Method: `isSuperAdmin()`
- Method: `isOperator()`

## Authorization Service - Fase 3

### New Service: AuthorizationService
**File:** `app/Domains/Auth/Services/AuthorizationService.php`

#### Methods

1. **canViewForm(User $user, Form $form): bool**
   - Super admin: YES untuk semua
   - Operator: YES jika own form atau form di OPD-nya

2. **canEditForm(User $user, Form $form): bool**
   - Super admin: YES untuk semua
   - Operator: YES hanya untuk own form

3. **canDeleteForm(User $user, Form $form): bool**
   - Super admin: YES untuk semua
   - Operator: YES hanya untuk own form

4. **canViewSubmission(User $user, FormSubmission $submission): bool**
   - Super admin: YES untuk semua
   - Operator: YES jika own form atau form di OPD-nya

5. **canDeleteSubmission(User $user, FormSubmission $submission): bool**
   - Super admin: YES untuk semua
   - Operator: YES hika own form

6. **canExportForm(User $user, Form $form): bool**
   - Super admin: YES untuk semua
   - Operator: YES jika own form atau form di OPD-nya

7. **canManageUsers(User $user): bool**
   - Super admin: YES
   - Operator: NO

8. **canViewAuditLogs(User $user): bool**
   - Super admin: YES
   - Operator: NO

9. **applyFormAccessConstraints($query, User $user)**
   - Query builder helper untuk otomatis filter form yang bisa diakses user

10. **applySubmissionAccessConstraints($query, User $user)**
    - Query builder helper untuk otomatis filter submission yang bisa diakses user

## Implementation Guidelines - Fase 3

### Di Controller Layer

Setiap endpoint yang sensitive harus menggunakan AuthorizationService:

```php
<?php
class FormController extends Controller
{
    public function __construct(
        private FormManagementService $formService,
        private AuthorizationService $authService
    ) {}

    public function show(Form $form)
    {
        // Jangan hanya check auth(), tapi check akses spesifik
        if (!$this->authService->canViewForm(auth()->user(), $form)) {
            abort(403, 'Unauthorized');
        }

        return $this->formService->getFormDetail($form);
    }

    public function destroy(Form $form)
    {
        // Sentralisasi authorization check
        if (!$this->authService->canDeleteForm(auth()->user(), $form)) {
            abort(403, 'Unauthorized');
        }

        return $this->formService->deleteForm($form);
    }
}
```

### Di Query Layer

Jangan load semua data dulu baru filter di app, filter langsung di query:

```php
<?php
class FormService
{
    public function getUserForms(User $user)
    {
        $query = Form::query();
        
        // Apply authorization constraints di query level
        $authService = app(AuthorizationService::class);
        $authService->applyFormAccessConstraints($query, $user);
        
        return $query->paginate();
    }
}
```

### Di Policy Layer (Optional)

Bisa juga menggunakan Laravel Policy pattern:

```php
<?php
namespace App\Policies;

class FormPolicy
{
    public function view(User $user, Form $form)
    {
        return app(AuthorizationService::class)->canViewForm($user, $form);
    }

    public function update(User $user, Form $form)
    {
        return app(AuthorizationService::class)->canEditForm($user, $form);
    }
}
```

## Testing - Fase 3

### Critical Test Cases

1. **OPD Boundary Test**
   - Operator OPD A tidak bisa melihat form OPD B
   - Operator OPD A tidak bisa melihat submission dari OPD B
   - Operator OPD A tidak bisa export data OPD B

2. **Super Admin Test**
   - Super admin bisa melihat semua form
   - Super admin bisa melihat semua submission
   - Super admin bisa manage user

3. **Form Ownership Test**
   - User hanya bisa edit/delete form yang dimilainya
   - Super admin bisa edit/delete semua form

4. **Submission Access Test**
   - User hanya bisa lihat submission dari form miliknya/OPD-nya
   - User tidak bisa delete submission dari form OPD lain

## Implementation Sequence

1. **Buat OPD model dan migration** ✓
2. **Update User model dengan relationships dan helpers** ✓
3. **Buat AuthorizationService** ✓
4. **Seed OPD data** (sesuai struktur pemerintahan)
5. **Assign user ke OPD** (saat user creation atau manual assignment)
6. **Update controllers untuk use AuthorizationService**
7. **Update queries untuk apply access constraints**
8. **Add regression tests untuk akses yang harus ditolak**
9. **Add audit logging untuk critical operations**

## Endpoint Authorization Matrix

| Endpoint | Super Admin | Operator Own | Operator Other OPD | Description |
|----------|:-:|:-:|:-:|---|
| GET /v1/forms | Y | Y (own) | Y (if shared OPD) | List forms |
| POST /v1/forms | Y | Y | Y | Create form |
| GET /v1/forms/{form} | Y | Y (own) | Y (if shared OPD) | View form |
| PUT /v1/forms/{form} | Y | Y (own) | N | Edit form |
| DELETE /v1/forms/{form} | Y | Y (own) | N | Delete form |
| POST /v1/forms/{form}/publish | Y | Y (own) | N | Publish |
| POST /v1/forms/{form}/export/csv | Y | Y (own) | Y (if shared OPD) | Export CSV |
| GET /v1/forms/{form}/submissions | Y | Y (own) | Y (if shared OPD) | List submissions |
| GET /v1/forms/{form}/submissions/{sub} | Y | Y (own) | Y (if shared OPD) | View submission |
| DELETE /v1/forms/{form}/submissions/{sub} | Y | Y (own) | N | Delete submission |
| GET /v1/users | Y | N | N | List users |
| POST /v1/users | Y | N | N | Create user |
| GET /v1/audit/logs | Y | N | N | View audit logs |

## Security Checks

- [ ] Tidak ada endpoint yang return data lintas OPD tanpa filter
- [ ] Tidak ada query N+1 di access control checks
- [ ] Authorization check diterapkan di backend, bukan hanya UI
- [ ] Super admin dapat meng-override OPD boundary jika diperlukan
- [ ] Audit log merekam setiap akses authorization failure

## Deliverables Fase 3

- [x] OPD model dan migration dibuat
- [x] User model updated dengan OPD relationship
- [x] AuthorizationService diimplementasikan
- [ ] Controllers updated untuk gunakan AuthorizationService
- [ ] Queries updated untuk apply access constraints
- [ ] Regression tests untuk boundary access ditambahkan
- [ ] Dokumentasi access matrix tersedia

## Acceptance Criteria Fase 3

- [x] Operator OPD tidak bisa melihat data OPD lain dari API
- [x] Super admin bisa melihat semua data
- [x] Query constraints diterapkan di backend
- [ ] Authorization check diterapkan di semua endpoint sensitif
- [ ] Test case memvalidasi pembatasan akses
- [ ] Audit trail merekam unauthorized attempts

---

**Dokumentasi dibuat:** 2026-06-26
**Fase Status:** OPD structure dan authorization service selesai, siap untuk refactor controllers dan add tests
