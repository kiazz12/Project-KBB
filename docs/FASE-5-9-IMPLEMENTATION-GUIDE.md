# Fase 5-9: Implementation Guideline Lengkap

## Fase 5: Optimasi Performa dan Pemisahan Beban

### Tujuan
Menjaga aplikasi tetap cepat dengan prinsip: **ringan di request utama, berat di background**

### Implementation Checklist

- [ ] **Query Optimization**
  - [ ] Tambah index pada kolom filter: `form_id`, `status`, `created_at`, `user_id`, `opd_id`
  - [ ] Eliminate N+1 queries menggunakan eager loading
  - [ ] Pagination pada daftar submission (default 20 per page)
  - [ ] Cache query result untuk stats yang jarang berubah

- [ ] **Queue Integration**
  - [ ] Setup Laravel Queue (use Redis atau database)
  - [ ] Move CSV/PDF export ke background job
  - [ ] Move email notifications ke background job
  - [ ] Add retry logic dengan exponential backoff

- [ ] **Caching Strategy**
  - [ ] Cache dashboard stats (invalidate every 1 hour)
  - [ ] Cache user role/permission checks
  - [ ] Cache form list per user (invalidate on form change)
  - [ ] Implement cache warming untuk frequently accessed data

- [ ] **Asset Optimization**
  - [ ] Separate public form CSS/JS dari admin assets
  - [ ] Lazy load admin components
  - [ ] Minify dan gzip assets
  - [ ] Use CDN untuk static assets jika tersedia

### Key Metrics

- [ ] Public form load time < 2 seconds
- [ ] Dashboard load time < 3 seconds
- [ ] Submission list pagination: < 500ms
- [ ] Export job: < 30 seconds di background

### Example: Export Job

```php
<?php
namespace App\Domains\Reporting\Jobs;

use App\Models\Form;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExportFormDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Form $form,
        private User $user
    ) {}

    public function handle()
    {
        $csv = app(ReportingService::class)->exportToCsv($this->form);
        
        // Store atau send via email
        Storage::disk('exports')->put(
            "form-{$this->form->id}-{$this->user->id}.csv",
            $csv
        );
    }
}
```

---

## Fase 6: Hardening Keamanan

### Tujuan
Membuat keamanan bagian inti implementasi, bukan tambahan belakangan

### Implementation Checklist

- [ ] **Input Validation**
  - [ ] Validate semua input dari public forms
  - [ ] Use Laravel Requests untuk Form Request Validation
  - [ ] File upload: validate tipe, ukuran, scan malware jika perlu
  - [ ] Sanitize HTML input dari textarea

- [ ] **Output Escaping**
  - [ ] Escape all user data di response
  - [ ] Use prepared statements untuk database queries
  - [ ] Configure Content Security Policy headers

- [ ] **Rate Limiting**
  - [ ] Login endpoint: 5 attempts per 1 minute
  - [ ] Public submit: 10 submissions per 1 minute per IP
  - [ ] API endpoints: 60 requests per 1 minute

- [ ] **Session Management**
  - [ ] Session timeout: 30 minutes inactivity
  - [ ] CSRF protection on state-changing requests
  - [ ] Secure cookie flags (HttpOnly, Secure, SameSite)

- [ ] **Audit Logging**
  - [ ] Log: Login attempts (success dan failure)
  - [ ] Log: Form publish, close, delete
  - [ ] Log: Submission delete
  - [ ] Log: User create, update, delete
  - [ ] Log: Export operations
  - [ ] Log: Failed authorization attempts

### Middleware untuk Hardening

```php
<?php
// Middleware untuk log sensitive operations
Route::post('/v1/forms/{form}/publish', [FormController::class, 'publish'])
    ->middleware('log.critical');

Route::delete('/v1/forms/{form}', [FormController::class, 'destroy'])
    ->middleware('log.critical');
```

### Security Headers

```php
<?php
// Add ke middleware atau config
Header::make('X-Content-Type-Options', 'nosniff'),
Header::make('X-Frame-Options', 'DENY'),
Header::make('X-XSS-Protection', '1; mode=block'),
Header::make('Referrer-Policy', 'strict-origin-when-cross-origin'),
```

---

## Fase 7: Testing Berlapis

### Tujuan
Memberikan jaring pengaman untuk refactor dan validasi critical path

### Test Pyramid

```
        ▲ Integration Tests (20%)
       / \
      /   \
     /     \
    / Unit  \
   /_________\
  / Feature  \
 /___________\ (80%)
```

### Testing Checklist

- [ ] **Feature Tests (Primary)**
  - [ ] Auth: Login, logout, change password
  - [ ] Public form: View, submit, validation
  - [ ] Form CRUD: Create, read, update, delete, publish, close
  - [ ] Submission: List, view, delete
  - [ ] Export: CSV, PDF
  - [ ] Authorization: OPD boundary, role restrictions

- [ ] **Regression Tests (Critical)**
  - [ ] Operator OPD A cannot view form OPD B
  - [ ] Operator cannot edit form of other user
  - [ ] Operator cannot export sensitive data
  - [ ] Super admin can access everything
  - [ ] Public form accessible without auth

- [ ] **Unit Tests (Core Logic)**
  - [ ] AuthService login/logout
  - [ ] FormManagementService CRUD
  - [ ] SubmissionService pagination
  - [ ] DataClassificationService masking
  - [ ] AuthorizationService permissions

- [ ] **API Tests**
  - [ ] HTTP status codes correct
  - [ ] Response format consistent
  - [ ] Error messages helpful
  - [ ] Pagination working

### Example Test

```php
<?php
test('operator cannot access other opd form', function () {
    $opd1 = OPD::create(['name' => 'OPD 1', 'code' => 'OPD1']);
    $opd2 = OPD::create(['name' => 'OPD 2', 'code' => 'OPD2']);
    
    $user1 = User::create([...attributes..., 'opd_id' => $opd1->id]);
    $user2 = User::create([...attributes..., 'opd_id' => $opd2->id]);
    
    $form = Form::create(['user_id' => $user1->id, 'opd_id' => $opd1->id]);
    
    // User2 should not access form
    $this->actingAs($user2)
        ->getJson("/api/v1/forms/{$form->id}")
        ->assertForbidden();
});
```

### Coverage Targets

- [ ] Line coverage: > 80%
- [ ] Branch coverage: > 70%
- [ ] Critical path coverage: 100%

---

## Fase 8: Deploy dan Operasional PDN-Ready

### Tujuan
Mengubah aplikasi dari development menjadi production-ready untuk pusat data nasional

### Infrastructure Checklist

- [ ] **Web Application**
  - [ ] PHP 8.3+
  - [ ] Laravel 13
  - [ ] Database: PostgreSQL 15+ atau MySQL 8.0+
  - [ ] Redis untuk cache dan queue (optional tapi recommended)

- [ ] **Queue Worker**
  - [ ] Setup supervisor/systemd untuk background jobs
  - [ ] Configure queue retry dengan backoff
  - [ ] Monitor queue depth

- [ ] **Scheduler**
  - [ ] Setup cron atau equivalent
  - [ ] Schedule cleanup job (retention)
  - [ ] Schedule cache warming
  - [ ] Schedule backup

- [ ] **Storage**
  - [ ] File uploads di secure location
  - [ ] Backup storage separated
  - [ ] Cleanup old files per retention policy

- [ ] **Logging**
  - [ ] Centralized logging (ELK, Loki, atau Papertrail)
  - [ ] Structured logging dengan context
  - [ ] Log levels: debug, info, warning, error, critical
  - [ ] Separate audit logs dari application logs

- [ ] **Monitoring**
  - [ ] Application health check endpoint
  - [ ] Database connection pool monitoring
  - [ ] Queue depth monitoring
  - [ ] Disk space monitoring
  - [ ] Memory/CPU usage monitoring

- [ ] **Backup & Recovery**
  - [ ] Database backup: daily
  - [ ] Incremental backup: hourly
  - [ ] Test restore procedure monthly
  - [ ] Backup stored off-site

### Example: Health Check Endpoint

```php
<?php
Route::get('/health', function () {
    $health = [
        'status' => 'up',
        'database' => 'ok',
        'queue' => 'ok',
        'cache' => 'ok',
    ];

    try {
        DB::connection()->getPdo();
    } catch (Exception $e) {
        $health['database'] = 'error';
        $health['status'] = 'degraded';
    }

    try {
        Cache::put('health-check', time(), 60);
    } catch (Exception $e) {
        $health['cache'] = 'error';
        $health['status'] = 'degraded';
    }

    return response()->json($health, $health['status'] === 'up' ? 200 : 503);
});
```

### Environment Configuration

```
.env production:
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stack
LOG_LEVEL=info
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### SOP Documents

- [ ] **SOP Deploy** - Step-by-step deployment procedure
- [ ] **SOP Backup & Restore** - Backup strategy dan restore testing
- [ ] **SOP Incident Handling** - Response procedure untuk berbagai incident type
- [ ] **SOP Monitoring** - Daily monitoring checklist
- [ ] **SOP Maintenance** - Routine maintenance tasks

---

## Fase 9: Dokumentasi Hidup dan Review Audit

### Tujuan
Menutup gap dokumentasi dan memastikan kesiapan audit

### Documentation Checklist

- [ ] **Technical Documentation**
  - [ ] Architecture diagram (system, data flow, deployment)
  - [ ] Database schema dan relationships
  - [ ] API documentation (OpenAPI/Swagger)
  - [ ] Domain responsibilities dan service layers
  - [ ] Development setup guide

- [ ] **Operational Documentation**
  - [ ] SOP deploy, backup, restore, incident
  - [ ] Monitoring dan alerting setup
  - [ ] Configuration management
  - [ ] Known issues dan workarounds

- [ ] **Compliance Documentation**
  - [ ] Data classification dan handling rules
  - [ ] Access control matrix (role x resource)
  - [ ] Audit logging configuration
  - [ ] Security incident procedure

- [ ] **User Documentation**
  - [ ] Admin guide (dashboard, user management, form management)
  - [ ] OPD operator guide (form creation, submission viewing, export)
  - [ ] FAQ dan troubleshooting

- [ ] **Code Documentation**
  - [ ] README per domain dengan responsibilities
  - [ ] API endpoint documentation
  - [ ] Configuration options documentation
  - [ ] Database migration changelog

### Audit Readiness Checklist

- [ ] Authentication: Documented dan tested
- [ ] Authorization: Tested untuk boundary violations
- [ ] Data Classification: Implemented dan enforced
- [ ] Audit Trail: Complete untuk critical operations
- [ ] Backup & Recovery: Tested dan documented
- [ ] Incident Response: SOP documented
- [ ] Access Control: Matrix documented dan enforced
- [ ] Testing: Evidence of automated test coverage
- [ ] Change Management: Git history shows tracking

### Documentation Living Strategy

- Link documentation di code comments
- Include documentation updates dalam git commit messages
- Automated doc generation dari code (API docs dari swagger/openapi)
- Version documentation per release
- Maintain changelog file (CHANGELOG.md)

### Example: Architecture Documentation

```
Architecture
├── System Diagram
│   └── Shows web, queue, scheduler, database, storage, logging
├── Data Flow
│   ├── Public submission flow
│   ├── Internal form management flow
│   └── Reporting/export flow
├── Domain Breakdown
│   ├── Auth domain
│   ├── PublicForms domain
│   ├── InternalForms domain
│   ├── Submissions domain
│   ├── Reporting domain
│   └── Audit domain
└── Deployment Topology
    ├── Development environment
    ├── Staging environment
    └── Production environment (PDN)
```

### Release Checklist

Sebelum setiap release:

- [ ] All tests passing (unit, feature, integration)
- [ ] Code review completed
- [ ] Documentation updated
- [ ] CHANGELOG updated dengan release notes
- [ ] Database migration tested di staging
- [ ] Performance benchmarks acceptable
- [ ] Security audit passed
- [ ] Deployment SOP reviewed

---

## Overall Implementation Timeline

| Fase | Durasi | Priority | Status |
|------|:------:|:--------:|:------:|
| 1: Baseline | 2 hari | HIGH | ✓ Complete |
| 2: Structure | 3 hari | HIGH | In Progress |
| 3: Auth/OPD | 4 hari | HIGH | TODO |
| 4: Classification | 3 hari | HIGH | TODO |
| 5: Performa | 5 hari | MEDIUM | TODO |
| 6: Security | 4 hari | MEDIUM | TODO |
| 7: Testing | 5 hari | MEDIUM | TODO |
| 8: Deploy | 3 hari | MEDIUM | TODO |
| 9: Docs | 3 hari | MEDIUM | TODO |
| **Total** | **~32 hari** | | |

---

## Risk Mitigation

### Common Risks & Mitigation

1. **Data Migration Issues**
   - Risk: Data corruption saat add OPD/classification
   - Mitigation: Test migration di staging, backup sebelum production

2. **Performance Regression**
   - Risk: Queries jadi lambat setelah refactor
   - Mitigation: Load testing, query profiling sebelum deploy

3. **Authorization Bypass**
   - Risk: Bug di authorization logic terlewatkan
   - Mitigation: Comprehensive regression testing, code review

4. **Queue Failure**
   - Risk: Jobs stuck atau fail
   - Mitigation: Proper error handling, DLQ (Dead Letter Queue), monitoring

5. **Documentation Drift**
   - Risk: Dokumentasi tidak sync dengan implementasi
   - Mitigation: Living documentation, update dalam setiap commit

---

## Success Criteria

Project dianggap **BERHASIL** bila:

✓ Aplikasi tetap satu unit deploy utama
✓ Domain struktur jelas dan terpisah
✓ Jalur publik ringan dan responsif
✓ Operator OPD tidak bisa lihat data lintas OPD
✓ Data klasifikasi diterapkan dengan perlakuan berbeda
✓ Export dan proses berat di background
✓ Security hardening done dengan audit logging lengkap
✓ Test coverage > 80% untuk critical path
✓ Deployment SOP dan monitoring siap
✓ Dokumentasi lengkap dan audit-ready

---

**Dokumentasi dibuat:** 2026-06-26
**Revisi Terakhir:** 2026-06-26
