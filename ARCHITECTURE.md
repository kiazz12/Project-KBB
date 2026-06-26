# Project-KBB: Modular Monolith Architecture

## Overview

Project-KBB adalah aplikasi SPBE pemerintah daerah yang dibangun dengan arsitektur **Modular Monolith**. Ini berarti:

- **Satu aplikasi utama** - Single Laravel + React application
- **Modular structure** - Code organized by business domain, not by layer
- **Clear boundaries** - Each module has defined responsibilities
- **Easy to test** - Domain logic can be tested independently
- **Simple to deploy** - Single deployment unit vs multiple services

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
    │   └─→ PublicFormService::getFormBySlug()
    │       └─→ Form marked as 'published'
    │           └─→ Return form with fields
    │
    └─→ POST /v1/forms/public/{slug}
        ├─→ Throttling check (10 req/min per IP)
        ├─→ PublicFormService::validateSubmission()
        │   └─→ Required fields validation
        │       └─→ File type/size validation
        │
        ├─→ PublicFormService::submitForm()
        │   ├─→ Create FormSubmission
        │   └─→ Create SubmissionData for each field
        │
        └─→ Return success or validation errors
```

### Internal Form Management Flow

```
Operator
    │
    ├─→ GET /v1/forms
    │   ├─→ AuthorizationService::applyFormAccessConstraints()
    │   │   └─→ Filter by user.opd_id
    │   └─→ Return paginated list
    │
    ├─→ POST /v1/forms
    │   ├─→ FormManagementService::createForm()
    │   │   ├─→ Create Form record
    │   │   ├─→ Assign user_id
    │   │   └─→ Assign opd_id
    │   │
    │   ├─→ AuditDomainService::logFormAction()
    │   │   └─→ Log: "Form created by User X"
    │   │
    │   └─→ Return created form
    │
    ├─→ PUT /v1/forms/{form}
    │   ├─→ AuthorizationService::canEditForm() check
    │   │   └─→ Only creator or super admin
    │   │
    │   ├─→ FormManagementService::updateForm()
    │   │   └─→ Update form attributes
    │   │
    │   ├─→ AuditDomainService::logFormAction()
    │   │   └─→ Log changes made
    │   │
    │   └─→ Return updated form
    │
    └─→ POST /v1/forms/{form}/publish
        ├─→ AuthorizationService::canEditForm() check
        ├─→ FormManagementService::publishForm()
        │   └─→ Set status = 'published', published_at = now
        │
        ├─→ AuditDomainService::logFormAction()
        │   └─→ Log: "Form published by User X"
        │
        └─→ Return published form
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
├── password (hashed)
├── role (super_admin, operator)
├── opd_id (FK to opds)
└── timestamps

opds
├── id (PK)
├── name (UNIQUE)
├── code (UNIQUE)
├── description
└── timestamps

forms
├── id (PK)
├── user_id (FK to users)
├── opd_id (FK to opds)
├── title
├── slug (UNIQUE)
├── description
├── status (draft, published, closed)
├── data_classification (public, internal, sensitive)
├── published_at
├── closed_at
└── timestamps

form_fields
├── id (PK)
├── form_id (FK to forms)
├── label
├── type (text, email, number, select, checkbox, etc)
├── required (boolean)
├── order
├── config (JSON - options for select, etc)
└── timestamps

form_submissions
├── id (PK)
├── form_id (FK to forms)
├── status (submitted, etc)
├── submitted_at
└── timestamps

submission_data
├── id (PK)
├── submission_id (FK to form_submissions)
├── form_field_id (FK to form_fields)
├── value (TEXT - store as string or JSON)
└── timestamps

audit_logs
├── id (PK)
├── user_id (FK to users)
├── action
├── subject
├── subject_id
├── changes (JSON)
├── ip_address
├── user_agent
├── timestamp
└── created_at
```

---

## Service Injection Pattern

### In Controllers

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
        // Create form using domain service
        $form = $this->formService->createForm(
            auth()->user(),
            $request->validated()
        );

        return response()->json($form, 201);
    }

    public function show(Form $form)
    {
        // Check authorization using auth service
        if (!$this->authService->canViewForm(auth()->user(), $form)) {
            abort(403, 'Unauthorized');
        }

        return response()->json($form);
    }
}
```

---

## OPD-Based Access Control

### Implementation

```
Every query must respect OPD boundaries:

Super Admin: Can access ALL data
Operator: Can access ONLY their OPD's data

Query Example:

// Without access control (WRONG!)
Form::where('status', 'published')->get();  // Returns ALL forms!

// With access control (CORRECT!)
$query = Form::query();
$authService->applyFormAccessConstraints($query, auth()->user());
$forms = $query->get();  // Returns only accessible forms
```

### Authorization Matrix

| Action | Super Admin | Operator (Own) | Operator (Same OPD) | Operator (Other OPD) |
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
enum DataClassification {
    case PUBLIC;     // No restrictions
    case INTERNAL;   // Limited access, logging detail, 1 year retention
    case SENSITIVE;  // Strict access, no export, 90 days retention
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
├── Web Server (Laravel + React)
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

**Architecture Version:** 1.0.0  
**Last Updated:** 2026-06-26  
**Status:** Active Development
