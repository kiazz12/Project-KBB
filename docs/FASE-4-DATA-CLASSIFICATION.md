# Fase 4: Klasifikasi Data dan Perlakuan Data Sensitif

## Tujuan

Menyesuaikan aplikasi dengan karakter data campuran dan menerapkan perlakuan berbeda berdasarkan sensitivitas data.

## Klasifikasi Data

### 1. PUBLIC (Data Publik)
**Karakteristik:**
- Data administratif umum
- Dapat dipublikasikan luas
- Tidak ada pembatasan akses khusus

**Perlakuan Sistem:**
- Akses: Tidak ada pembatasan khusus
- Export: Diizinkan tanpa approval
- Logging: Logging standar
- Retensi: Indefinite
- Masking: Tidak perlu

**Contoh:**
- Formulir permintaan informasi publik
- Data statistik umum
- Informasi layanan publik

---

### 2. INTERNAL (Data Internal)
**Karakteristik:**
- Data internal organisasi
- Akses dibatasi untuk kalangan internal
- Memerlukan approval untuk export
- Logging lebih detail

**Perlakuan Sistem:**
- Akses: Hanya authorized internal users
- Export: Diizinkan dengan approval dan logging detail
- Logging: Detailed dengan masking untuk field sensitif
- Retensi: 1 tahun
- Masking: Di dashboard dan logs

**Contoh:**
- Data pemberi layanan internal
- Data proses administrasi internal
- Data pengguna internal

---

### 3. SENSITIVE (Data Sensitif)
**Karakteristik:**
- Data identitas personal
- Data finansial/kesehatan
- Data yang memerlukan pembatasan maksimal

**Perlakuan Sistem:**
- Akses: Sangat terbatas, hanya untuk role tertentu
- Export: Tidak diizinkan
- Logging: Enhanced dengan full audit trail
- Retensi: 90 hari (sesuai regulasi)
- Masking: Di semua dashboard dan logs
- Enkripsi: Dalam transit dan at-rest (jika aplikasi berkembang)

**Contoh:**
- Nomor identitas (NIK)
- Nomor rekening
- Informasi kesehatan
- Data identitas pribadi lainnya

---

## Database Schema - Fase 4

### Migration: Add Data Classification
Sudah dibuat di Fase 3:
```sql
ALTER TABLE forms ADD COLUMN data_classification ENUM('public', 'internal', 'sensitive') 
DEFAULT 'public' AFTER status;
```

## Enum: DataClassification
**File:** `app/Enums/DataClassification.php`

Menyediakan:
- Classification levels
- Access restrictions
- Export permissions
- Masking rules
- Retention policies

## Services - Fase 4

### DataClassificationService
Akan handle logic perlakuan data berdasarkan klasifikasi:

```php
class DataClassificationService
{
    /**
     * Check if export is allowed for form
     */
    public function canExport(Form $form): bool;

    /**
     * Get submission data with masking applied
     */
    public function getMaskedSubmissionData(FormSubmission $sub): array;

    /**
     * Get dashboard data with appropriate masking
     */
    public function getDashboardStats(Form $form, User $user): array;

    /**
     * Check if user can view sensitive field
     */
    public function canViewSensitiveData(User $user, Form $form): bool;

    /**
     * Get retention policy
     */
    public function getRetentionDays(Form $form): ?int;
}
```

### AuditLogService Enhancement
Enhanced untuk log lebih detail untuk sensitive data:

```php
class AuditLogService
{
    /**
     * Log dengan masking untuk sensitive fields
     */
    public function logWithMasking(
        User $user,
        string $action,
        Form $form,
        array $changes
    ): AuditLog;

    /**
     * Get masked audit trail
     */
    public function getMaskedAuditTrail(Form $form): Collection;
}
```

## Implementation Guidelines - Fase 4

### Field-Level Classification (Future Enhancement)

Untuk fleksibilitas maksimal, di masa depan bisa ada field-level classification:

```php
Schema::table('form_fields', function (Blueprint $table) {
    $table->enum('data_sensitivity', ['public', 'internal', 'sensitive'])->default('public');
});
```

Ini memungkinkan form PUBLIC punya beberapa field SENSITIVE.

### Export Restrictions

1. **Form PUBLIC/INTERNAL:**
   - Export CSV: Allowed (dengan logging)
   - Export PDF: Allowed (dengan logging)

2. **Form SENSITIVE:**
   - Export CSV: NOT Allowed
   - Export PDF: NOT Allowed
   - Recommended: Use dedicated reporting interface dengan masking

### Dashboard Display

```php
// Dashboard harus menampilkan dengan masking untuk INTERNAL/SENSITIVE
$stats = [
    'public' => 123,      // Exact
    'internal' => '100+', // Masked
    'sensitive' => '***', // Hidden
];
```

### Logging Rules

**PUBLIC:**
- Log: Action only
- Contoh: "User X downloaded CSV for form Y"

**INTERNAL:**
- Log: Action + timestamp + user + form
- Mask: Sensitive field names
- Contoh: "User X viewed submission for form Y, field [MASKED] value count: N"

**SENSITIVE:**
- Log: Full audit trail
- Mask: All sensitive data
- Contoh: "User X viewed sensitive form Y, [MASKED] field accessed"
- Include: IP address, user agent, session info

### Retention & Cleanup

Automated job untuk delete data sesuai retention policy:

```php
class CleanupExpiredData extends Command
{
    public function handle()
    {
        // Delete INTERNAL data older than 1 year
        FormSubmission::whereHas('form', fn($q) => 
            $q->where('data_classification', 'internal')
        )
        ->where('created_at', '<', now()->subYear())
        ->delete();

        // Delete SENSITIVE data older than 90 days
        FormSubmission::whereHas('form', fn($q) => 
            $q->where('data_classification', 'sensitive')
        )
        ->where('created_at', '<', now()->subDays(90))
        ->delete();
    }
}
```

## UI/UX Implications - Fase 4

### Form Creation/Edit
- Add dropdown untuk select classification level
- Show warning untuk SENSITIVE: "Export tidak diizinkan"
- Show info: "Data akan dihapus setelah [X hari]"

### Submission List/Export
- SENSITIVE form: Hide export button
- INTERNAL/PUBLIC: Show export button dengan logging

### Dashboard
- Public stats: Exact numbers
- Internal stats: "100+" atau summary only
- Sensitive forms: Not shown atau "**Access Restricted**"

### Audit Log Viewer
- Show: Action, user, timestamp, form
- Mask: Sensitive field details
- Access: Super admin only

## Testing - Fase 4

### Test Cases

1. **Classification Boundary Test**
   ```php
   test('public form allows export', ...)
   test('internal form allows export', ...)
   test('sensitive form denies export', ...)
   ```

2. **Masking Test**
   ```php
   test('dashboard masks internal data', ...)
   test('dashboard hides sensitive data', ...)
   ```

3. **Logging Test**
   ```php
   test('sensitive access creates audit log', ...)
   test('audit log masks sensitive fields', ...)
   ```

4. **Retention Test**
   ```php
   test('sensitive data deleted after 90 days', ...)
   test('internal data deleted after 1 year', ...)
   ```

## Matrix: Perlakuan Data Per Classification

| Aspek | Public | Internal | Sensitive |
|-------|:------:|:--------:|:---------:|
| **Akses** |
| Operator lihat | Y | Y | N |
| Admin lihat | Y | Y | Y |
| Export CSV | Y | Y | N |
| Export PDF | Y | Y | N |
| **Logging** |
| Detail level | Minimal | Medium | Maximum |
| Field masking | N | Y | Y |
| IP logging | N | N | Y |
| **Dashboard** |
| Show count | Exact | 100+ | *** |
| Show stats | Yes | Summary | Hidden |
| **Retensi** |
| Simpan hingga | Forever | 1 tahun | 90 hari |
| Auto-delete | No | Yes (1yr) | Yes (90d) |

## Implementation Sequence

1. **DataClassification enum dibuat** ✓
2. **Migration untuk data_classification diterapkan**
3. **DataClassificationService diimplementasikan**
4. **AuditLogService di-enhance untuk masking**
5. **Controllers updated untuk enforce restrictions**
6. **Export endpoints check classification sebelum export**
7. **Dashboard apply masking berdasarkan classification**
8. **Scheduler job dibuat untuk retention/cleanup**
9. **Tests ditambahkan untuk semua boundary cases**
10. **UI updated untuk show classification dan restrictions**

## Deliverables Fase 4

- [x] DataClassification enum dibuat
- [x] Database schema ready (dari Fase 3)
- [ ] DataClassificationService diimplementasikan
- [ ] AuditLogService di-enhance
- [ ] Controllers updated
- [ ] Export restrictions diterapkan
- [ ] Dashboard masking diterapkan
- [ ] Retention job dibuat
- [ ] Tests ditambahkan
- [ ] Documentation lengkap

## Acceptance Criteria Fase 4

- [ ] Setiap form memiliki data_classification
- [ ] Export tidak allowed untuk SENSITIVE forms
- [ ] Dashboard masks INTERNAL/SENSITIVE data
- [ ] Audit log merekam accesses dengan detail sesuai classification
- [ ] Data otomatis dihapus sesuai retention policy
- [ ] Tests memvalidasi semua classification boundaries

---

**Dokumentasi dibuat:** 2026-06-26
**Fase Status:** Enum structure dan documentation selesai, siap untuk implement services dan policy enforcement
