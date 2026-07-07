# Project-KBB: Modular Monolith Architecture

## Overview

Project-KBB adalah aplikasi SPBE pemerintah daerah untuk **Pemerintah Kabupaten Bandung Barat** yang dibangun dengan arsitektur **Modular Monolith**. Ini berarti:

- **Satu aplikasi utama** - Single Laravel application (Blade + Alpine.js)
- **Modular structure** - Code organized by business domain, not by layer
- **Clear boundaries** - Each module has defined responsibilities
- **Easy to test** - Domain logic can be tested independently
- **Simple to deploy** - Single deployment unit vs multiple services

### Brand Identity

| Element | Value |
|---------|-------|
| Primary | `#003778` (KBB Blue) |
| Accent  | `#C8A45C` (Gold) |
| Font    | Inter (CDN) |

### Why Modular Monolith?

Kami memilih modular monolith karena:

1. **Realistis** - Cocok dengan kondisi project yang sudah berjalan
2. **Cepat stabilkan** - Lebih cepat dari microservices refactoring
3. **Mudah dioperasikan** - Single deployment, monitoring, logging
4. **Flexible growth** - Bisa evolve ke microservices later jika perlu
5. **Easy to audit** - Semua kode dalam satu repo, mudah di-review

---

## Domain-Driven Design

### Domains

Project-KBB diorganisir dalam 6 domain utama:

#### 1. **Auth Domain**
**Responsibility:** User authentication, authorization, session management

```
app/Domains/Auth/
├── Services/
│   ├── AuthService.php           # Login, logout, password change
│   └── AuthorizationService.php  # Permission checks, access control
├── Models/
├── Actions/
└── Requests/
```

**Key Services:**
- `AuthService::login()` - Authenticate user
- `AuthService::logout()` - Revoke session
- `AuthService::changePassword()` - Update password
- `AuthorizationService::canViewForm()` - Check form access
- `AuthorizationService::applyFormAccessConstraints()` - Query filtering

**Routes:**
- `POST /v1/auth/login` - Login
- `POST /v1/auth/logout` - Logout
- `GET /v1/auth/me` - Get current user
- `POST /v1/auth/change-password` - Change password

---

#### 2. **PublicForms Domain**
**Responsibility:** Public form display and submission

```
app/Domains/PublicForms/
├── Services/
│   └── PublicFormService.php  # Public form operations
├── Models/
├── Actions/
└── Requests/
```

**Key Services:**
- `PublicFormService::getFormBySlug()` - Fetch published form
- `PublicFormService::validateSubmission()` - Validate user input
- `PublicFormService::submitForm()` - Save submission data

**Routes:**
- `GET /v1/forms/public/{slug}` - Get form for display
- `POST /v1/forms/public/{slug}` - Submit form (throttled)
- `GET /form/{slug}` - Render form page

**Characteristics:**
- Minimal asset footprint
- Throttling untuk abuse protection
- Input validation ketat
- Tidak perlu authentication
- Fast response times

---

#### 3. **InternalForms Domain**
**Responsibility:** Form management for internal users

```
app/Domains/InternalForms/
├── Services/
│   └── FormManagementService.php  # Form CRUD, field management
├── Models/
├── Actions/
└── Requests/
```

**Key Services:**
- `FormManagementService::createForm()` - Create new form
- `FormManagementService::updateForm()` - Edit form
- `FormManagementService::publishForm()` - Make form public
- `FormManagementService::closeForm()` - Stop accepting responses
- `FormManagementService::duplicateForm()` - Clone form
- `FormManagementService::addField()` - Add field to form
- `FormManagementService::updateField()` - Edit field
- `FormManagementService::deleteField()` - Remove field
- `FormManagementService::reorderFields()` - Change field order

**Routes:**
- `GET /v1/forms` - List user's forms
- `POST /v1/forms` - Create form
- `GET /v1/forms/{form}` - Get form details
- `PUT /v1/forms/{form}` - Update form
- `DELETE /v1/forms/{form}` - Delete form
- `POST /v1/forms/{form}/publish` - Publish form
- `POST /v1/forms/{form}/close` - Close form
- `POST /v1/forms/{form}/duplicate` - Duplicate form
- `POST /v1/forms/{form}/fields` - Add field
- `PUT /v1/forms/{form}/fields/{field}` - Edit field
- `DELETE /v1/forms/{form}/fields/{field}` - Delete field
- `POST /v1/forms/{form}/fields/reorder` - Reorder fields

**Characteristics:**
- Requires authentication
- Authorization checks (OPD boundary)
- Audit logging untuk critical operations
- Structured data model

---

#### 4. **Submissions Domain**
**Responsibility:** Submission management and retrieval

```
app/Domains/Submissions/
├── Services/
│   └── SubmissionService.php  # Submission listing, retrieval
├── Models/
├── Actions/
└── Requests/
```

**Key Services:**
- `SubmissionService::getFormSubmissions()` - Paginated list
- `SubmissionService::getSubmissionDetail()` - Fetch with related data
- `SubmissionService::getSubmissionAsArray()` - Format for display
- `SubmissionService::deleteSubmission()` - Remove submission
- `SubmissionService::getFormStatistics()` - Submission stats

**Routes:**
- `GET /v1/forms/{form}/submissions` - List submissions (paginated)
- `GET /v1/forms/{form}/submissions/{submission}` - View submission detail
- `DELETE /v1/forms/{form}/submissions/{submission}` - Delete submission

**Characteristics:**
- Pagination enforced (no bulk load)
- Access control checked
- Supports filtering by status/date
- OPD boundary applied

---

#### 5. **Reporting Domain**
**Responsibility:** Analytics, statistics, and exports

```
app/Domains/Reporting/
├── Services/
│   └── ReportingService.php  # Dashboard, analytics, export
├── Models/
├── Actions/
└── Requests/
```

**Key Services:**
- `ReportingService::getDashboardStats()` - Dashboard numbers
- `ReportingService::getRecentForms()` - Recently modified forms
- `ReportingService::getFormAnalytics()` - Form statistics
- `ReportingService::exportToCsv()` - Export to CSV format

**Routes:**
- `GET /v1/dashboard/stats` - Dashboard statistics
- `GET /v1/dashboard/recent-forms` - Recent forms
- `GET /v1/forms/{form}/analytics` - Form analytics
- `GET /v1/forms/{form}/export/csv` - Export CSV
- `GET /v1/forms/{form}/export/pdf` - Export PDF

**Characteristics:**
- Result caching untuk performance
- Data masking berdasarkan classification
- Export dapat di-queue untuk large datasets
- OPD-aware statistics

---

#### 6. **Audit Domain**
**Responsibility:** Activity logging and audit trail

```
app/Domains/Audit/
├── Services/
│   └── AuditDomainService.php  # Audit logging
├── Models/
├── Actions/
└── Requests/
```

**Key Services:**
- `AuditDomainService::logAction()` - Log generic action
- `AuditDomainService::logFormAction()` - Log form operation
- `AuditDomainService::logSubmissionAction()` - Log submission operation
- `AuditDomainService::logUserAction()` - Log user management
- `AuditDomainService::logAuthAction()` - Log authentication
- `AuditDomainService::getUserActivityLog()` - Get user's action history

**Audit Tracking:**
- Login/logout
- Form publish/close/delete
- Submission delete
- User management
- Export operations
- Failed authorization attempts

**Characteristics:**
- Non-blocking (async preferred)
- Data masking untuk sensitive information
- IP address dan user agent captured
- Compliance-focused

---

## Data Flow

### Public Form Submission Flow

```
Public User
    │
    ├─→ GET /form/{slug}
    │   └─→ PageController@publicForm()
    │       └─→ Blade view: public-form.blade.php (Alpine.js SPA-like)
    │           └─→ fetch GET /api/v1/forms/public/{slug}
    │               └─→ Form marked as 'published'
    │                   └─→ Render form fields via Alpine.js
    │
    └─→ POST /api/v1/forms/public/{slug}
        ├─→ Throttling check (10 req/min per IP)
        ├─→ Validate submission
        │   └─→ Required fields validation
        │
        ├─→ Create FormSubmission
        │   └─→ Create SubmissionData for each field
        │
        └─→ Return success or validation errors
```

### Internal Form Management Flow

```
Admin (authenticated via session)
    │
    ├─→ GET  /forms
    │   └─→ Blade view: forms/index.blade.php
    │       └─→ Data from PageController@formsIndex() (direct model query)
    │
    ├─→ GET  /forms/create
    │   └─→ Blade view: forms/create.blade.php
    │       └─→ POST /api/v1/forms via fetch (X-CSRF-TOKEN + session auth)
    │
    ├─→ GET  /forms/{form}/edit
    │   └─→ Blade view: forms/edit.blade.php (Alpine.js field builder)
    │       ├─→ fetch API calls (CRUD fields, publish, close, save settings)
    │       ├─→ POST /api/v1/forms/{form}/fields        (add field)
    │       ├─→ PUT  /api/v1/forms/{form}/fields/{field} (edit field)
    │       ├─→ DELETE /api/v1/forms/{form}/fields/{field}
    │       ├─→ POST /api/v1/forms/{form}/fields/reorder
    │       ├─→ POST /api/v1/forms/{form}/publish
    │       ├─→ POST /api/v1/forms/{form}/close
    │       └─→ PUT  /api/v1/forms/{form}               (update settings)
    │
    ├─→ GET  /forms/{form}
    │   └─→ Blade view: forms/show.blade.js
    │
    ├─→ GET  /forms/{form}/submissions
    │   └─→ Blade view: forms/submissions/index.blade.js
    │
    └─→ GET  /forms/{form}/analytics
        └─→ Blade view: forms/analytics.blade.js
```

### Submission Viewing Flow

```
Operator
    │
    ├─→ GET /v1/forms/{form}/submissions
    │   ├─→ AuthorizationService::canViewForm() check
    │   ├─→ AuthorizationService::applySubmissionAccessConstraints()
    │   │   └─→ Filter by form.user_id or form.opd_id
    │   │
    │   ├─→ SubmissionService::getFormSubmissions()
    │   │   ├─→ Paginate (default 20 per page)
    │   │   ├─→ Order by created_at DESC
    │   │   └─→ Return paginated results
    │   │
    │   └─→ Apply data masking if needed
    │       └─→ Based on form.data_classification
    │
    └─→ GET /v1/forms/{form}/submissions/{submission}
        ├─→ AuthorizationService::canViewSubmission() check
        │   └─→ User owns form OR same OPD
        │
        ├─→ SubmissionService::getSubmissionDetail()
        │   ├─→ Load submission
        │   ├─→ Load all SubmissionData
        │   └─→ Load Form fields
        │
        ├─→ Apply data masking if needed
        │   └─→ Based on form.data_classification
        │
        └─→ Return submission detail
```

### Export Flow

```
Operator
    │
    └─→ GET /v1/forms/{form}/export/csv
        ├─→ AuthorizationService::canExportForm() check
        ├─→ DataClassification::canExport() check
        │   └─→ SENSITIVE = NO, others = YES
        │
        ├─→ If large dataset:
        │   ├─→ Queue ExportFormDataJob
        │   ├─→ Return "Export queued" + download link when ready
        │   └─→ Send email dengan download link
        │
        ├─→ If small dataset:
        │   ├─→ ReportingService::exportToCsv()
        │   │   └─→ Generate CSV from submissions
        │   │
        │   ├─→ AuditDomainService::logFormAction()
        │   │   └─→ Log: "Form exported by User X"
        │   │
        │   └─→ Return CSV file
        │
        └─→ Update export stats untuk dashboard
```

---

## Database Schema

### Core Tables

```
users
├── id (PK)
├── name
├── email (UNIQUE)
├── email_verified_at
├── password (hashed)
├── remember_token
├── role (super_admin, admin)
├── nip
├── opd
├── timestamps

opds
├── id (PK)
├── name
├── description
├── deleted_at (soft deletes)
├── timestamps

forms
├── id (PK)
├── uuid (UNIQUE)
├── user_id (FK to users)
├── title
├── description
├── slug (UNIQUE)
├── status (draft, published, closed)
├── settings (JSON)
├── starts_at
├── ends_at
├── max_submissions
├── require_auth
├── collect_ip
├── show_kbb_logo
├── deleted_at (soft deletes)
├── timestamps

form_fields
├── id (PK)
├── form_id (FK to forms)
├── type (text, email, number, select, radio, checkbox, textarea, date, file, heading, paragraph, rating, matrix)
├── label
├── placeholder
├── help_text
├── required (boolean)
├── options (JSON)
├── order
├── min_length
├── max_length
├── default_value
├── timestamps

form_submissions
├── id (PK)
├── uuid (UNIQUE)
├── form_id (FK to forms)
├── user_id (FK to users, nullable)
├── ip_address
├── user_agent
├── submitted_at
├── timestamps

submission_data
├── id (PK)
├── submission_id (FK to form_submissions)
├── form_field_id (FK to form_fields)
├── value (TEXT)
├── timestamps

audit_logs
├── id (PK)
├── user_id (FK to users, nullable)
├── action
├── auditable_type
├── auditable_id
├── description
├── old_values (JSON)
├── new_values (JSON)
├── ip_address
├── user_agent
├── timestamps
```

---

## Frontend Architecture

### Blade + Alpine.js + Tailwind CSS

Frontend menggunakan **server-side rendering** dengan Blade templates dan Alpine.js untuk interaktivitas:

**Stack:**
- **Blade** - Server-side templating engine Laravel
- **Alpine.js v3** (CDN) - Interaktivitas client-side (field builder, form submission, live search)
- **Tailwind CSS v3** (CDN) - Utility-first styling
- **DOMPDF** - PDF export generation

**Layout Structure:**
```
resources/views/
├── layouts/
│   ├── app.blade.php       # Main layout (sidebar nav, flash messages)
│   └── auth.blade.php      # Auth layout (login page)
├── auth/
│   └── login.blade.php     # Login form
├── dashboard/
│   └── index.blade.php     # Dashboard with stats
├── forms/
│   ├── index.blade.php     # Form list
│   ├── create.blade.php    # Create form
│   ├── edit.blade.php      # Form editor (field builder)
│   ├── show.blade.php      # Form detail
│   └── submissions/
│       ├── index.blade.php # Submission list
│       └── show.blade.php  # Submission detail
├── users/
│   ├── index.blade.php     # User management
│   └── show.blade.php      # User detail
├── change-password.blade.php
├── public-form.blade.php   # Public form submission
```

**Interactivity (Alpine.js):**
- Form field builder: drag-free reorder, add/edit/delete fields
- Real-time form validation
- Flash message auto-dismiss
- Form submission tracking

**Brand Colors:**
```css
--kbb-700: #003778  /* Primary blue */
--gold-400: #C8A45C /* Accent gold */
```

**Note:** Tailwind v3 dan Alpine.js dimuat via CDN — tidak ada build step untuk JavaScript.

---

## Service Injection Pattern

### In Controllers

Domain services (`app/Domains/*/Services/`) sudah diimplementasikan sebagai business logic layer, namun **controllers belum menggunakannya**. Saat ini, controllers menggunakan:

1. **Direct model queries** - Query Eloquent langsung di controller
2. **App\Services\AuditService** - Static audit logger (benar-benar digunakan)

```php
<?php
// Current pattern (direct queries + AuditService)
namespace App\Http\Controllers\API;

use App\Models\Form;
use App\Services\AuditService;
use Illuminate\Http\Request;

class FormCrudController extends Controller
{
    public function index(Request $request)
    {
        $forms = Form::where('user_id', auth()->id())
            ->withCount('submissions')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $forms
        ]);
    }
}
```

**Target pattern (future — services injected via DI):**

```php
<?php
namespace App\Http\Controllers\API;

use App\Domains\InternalForms\Services\FormManagementService;
use App\Domains\Auth\Services\AuthorizationService;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function __construct(
        private FormManagementService $formService,
        private AuthorizationService $authService
    ) {
        // Services injected automatically by Laravel container
    }

    public function store(Request $request)
    {
        $form = $this->formService->createForm(
            auth()->user(),
            $request->validated()
        );

        return response()->json($form, 201);
    }
}
```

---

## OPD-Based Access Control

### Implementation

**Database model:**
- `opds` table: id, name (UNIQUE), code (UNIQUE), description, soft deletes
- `users.opd_id` — FK dari user ke OPD (added via migration 2026_06_26_000001)
- `forms.opd_id` — FK dari form ke OPD
- `forms.data_classification` — enum (public, internal, sensitive)

**Access rules:**

```
Super Admin: Can access ALL data
Admin: Can access ONLY their OPD's data
```

**Current implementation:**
Saat ini OPD access control dilakukan via FormPolicy (`app/Policies/FormPolicy.php`) dengan `$this->authorize()` di controllers. Query filtering langsung di controller atau model, belum menggunakan AuthorizationService.

**Target pattern (future):**

```
// With access control (CORRECT!)
$query = Form::query();
$authService->applyFormAccessConstraints($query, auth()->user());
$forms = $query->get();  // Returns only accessible forms
```

### Authorization Matrix

| Action | Super Admin | Admin (Own) | Admin (Same OPD) | Admin (Other OPD) |
|--------|:-:|:-:|:-:|:-:|
| View form | ✅ | ✅ | ✅ | ❌ |
| Edit form | ✅ | ✅ | ❌ | ❌ |
| Delete form | ✅ | ✅ | ❌ | ❌ |
| View submission | ✅ | ✅ | ✅ | ❌ |
| Delete submission | ✅ | ✅ | ❌ | ❌ |
| Export form | ✅ | ✅ | ✅ | ❌ |
| Manage users | ✅ | ❌ | ❌ | ❌ |

---

## Data Classification

### Implementation

```php
// app/Enums/DataClassification.php
enum DataClassification: string
{
    case PUBLIC = 'public';      // No restrictions
    case INTERNAL = 'internal';  // Limited access, logging detail, 1 year retention
    case SENSITIVE = 'sensitive'; // Strict access, no export, 90 days retention
}
```

### Treatment Rules

| Classification | Export | Masking | Logging | Retention |
|---|:-:|:-:|---|---|
| PUBLIC | Yes | No | Minimal | Forever |
| INTERNAL | Yes | In logs/dashboard | Detail | 1 year |
| SENSITIVE | No | In logs/dashboard | Maximum | 90 days |

---

## Testing Strategy

### Test Organization

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── AuthenticationTest.php
│   │   └── AuthorizationTest.php
│   ├── PublicForms/
│   │   ├── SubmissionTest.php
│   │   └── ValidationTest.php
│   ├── InternalForms/
│   │   ├── FormCrudTest.php
│   │   ├── FieldManagementTest.php
│   │   └── OpdBoundaryTest.php
│   ├── Submissions/
│   │   ├── ListingTest.php
│   │   └── AccessControlTest.php
│   ├── Reporting/
│   │   ├── DashboardTest.php
│   │   └── ExportTest.php
│   └── Audit/
│       └── LoggingTest.php
└── Unit/
    ├── Services/
    │   ├── AuthServiceTest.php
    │   ├── AuthorizationServiceTest.php
    │   ├── FormManagementServiceTest.php
    │   └── ...
    └── Models/
        ├── UserTest.php
        ├── FormTest.php
        └── ...
```

### Critical Test Cases

1. **OPD Boundary** - Operator cannot access other OPD's data
2. **Authorization** - Permission checks work correctly
3. **Data Classification** - Export restrictions enforced
4. **Pagination** - Never load all data
5. **Public Form** - Accessible without authentication
6. **Audit Trail** - Important actions logged

---

## Deployment Architecture

### Single Application Deployment

```
├── Web Server (Laravel + Blade + Alpine.js)
│   ├── Public routes (forms, submissions)
│   └── Internal routes (admin dashboard)
│
├── Queue Worker
│   ├── Export jobs
│   ├── Email notifications
│   └── Background processing
│
├── Scheduler
│   ├── Daily cleanup (retention)
│   ├── Cache warming
│   └── Backup jobs
│
├── Database (PostgreSQL/MySQL)
│   └── All application data
│
├── Storage (File uploads)
│   ├── Form attachments
│   ├── Exports
│   └── Backups
│
├── Cache (Redis)
│   ├── Session store
│   ├── Query cache
│   └── Rate limiting counters
│
└── Logging
    ├── Application logs
    ├── Audit trail
    └── Error tracking
```

---

## Development Guidelines

### Adding New Feature

1. **Identify Domain** - Which domain does this belong to?
2. **Create Service** - Add logic to domain service
3. **Create/Update Model** - Ensure database relationships
4. **Create Route** - Add endpoint to routes
5. **Create Controller** - Thin controller using service
6. **Write Tests** - Feature + unit tests
7. **Update Documentation** - Reflect changes in docs

### Code Organization

```
✅ DO:
- Put business logic in Services
- Use dependency injection
- Check authorization before operations
- Log important actions
- Test critical paths
- Follow existing patterns

❌ DON'T:
- Put logic in controllers
- Global variables or singletons
- Skip authorization checks
- Load all data then filter in PHP
- Export sensitive data
- Ignore edge cases
```

---

## Performance Targets

| Operation | Target | Implementation |
|-----------|--------|---|
| Public form load | < 2 sec | Minimal assets, throttling |
| Dashboard load | < 3 sec | Caching, pagination |
| Submission list | < 500 ms | Pagination, indexing |
| Export job | < 30 sec | Background queue |
| Login | < 1 sec | Efficient query |
| Authorization check | < 10 ms | Caching |

---

## Security Measures

### In Code

- ✅ Input validation on all endpoints
- ✅ Output escaping in responses
- ✅ Rate limiting on sensitive operations
- ✅ Authorization checks before every operation
- ✅ Audit logging for critical actions
- ✅ CSRF protection
- ✅ Secure password hashing

### In Database

- ✅ Data masking for sensitive fields
- ✅ Retention policies enforced
- ✅ Access control at query level
- ✅ Indexes on frequently filtered columns

### In Infrastructure

- ✅ HTTPS/TLS for all communications
- ✅ Secure session cookies
- ✅ Separate file storage from web root
- ✅ Regular backups
- ✅ Monitoring and alerting

---

## References

- [INDEX.md](./docs/INDEX.md) - Complete documentation index
- [FASE-1-BASELINE.md](./docs/FASE-1-BASELINE.md) - Current state analysis
- [FASE-2-MODULAR-STRUCTURE.md](./docs/FASE-2-MODULAR-STRUCTURE.md) - Domain structure
- [FASE-3-AUTH-OPD-ACCESS.md](./docs/FASE-3-AUTH-OPD-ACCESS.md) - Access control
- [FASE-4-DATA-CLASSIFICATION.md](./docs/FASE-4-DATA-CLASSIFICATION.md) - Data handling
- [FASE-5-9-IMPLEMENTATION-GUIDE.md](./docs/FASE-5-9-IMPLEMENTATION-GUIDE.md) - Remaining phases

---

**Architecture Version:** 1.1.0  
**Last Updated:** 2026-07-07  
**Status:** Active Development (Blade + Alpine.js)
