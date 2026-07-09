# Master Task List — Project KBB

> **Total:** 20 task | **Estimasi:** ~15-20 hari kerja
> **Dibuat:** Juli 2026

---

## Daftar Isi

- [Prioritas 1: Security Critical](#-prioritas-1-security-critical-2-3-hari)
- [Prioritas 2: Security Hardening](#-prioritas-2-security-hardening-1-2-hari)
- [Prioritas 3: Stabilkan Core Flow](#-prioritas-3-stabilkan-core-flow-1-2-hari)
- [Prioritas 4: Testing Foundation](#-prioritas-4-testing-foundation-3-4-hari)
- [Prioritas 5: Technical Debt](#-prioritas-5-technical-debt-5-8-hari)
- [Ringkasan File yang Diubah](#-ringkasan-semua-file-yang-akan-diubah)

---

# 🚨 PRIORITAS 1: Security Critical (2-3 hari)

Harus dikerjakan **sekarang juga** sebelum aplikasi digunakan oleh publik.

---

## Task 1.1 — Fix IP & User-Agent Bocor di Livewire

| Item | Detail |
|---|---|
| **Severity** | 🔴 **Critical** |
| **Estimasi** | 15 menit |
| **File** | `app/Livewire/PublicForm.php` |
| **Baris** | 195-202 — method `submitForm()` |
| **Masalah** | IP dan User-Agent selalu direkam tanpa mengecek `collect_ip`. Di API (`SubmissionApiController.php:185-186`) sudah benar ada pengecekan, tapi Livewire tidak. |

### Kode Sebelum
```php
$submission = FormSubmission::create([
    'uuid' => Str::uuid(),
    'form_id' => $this->form->id,
    'user_id' => auth()->check() ? auth()->id() : null,
    'ip_address' => request()->ip(),           // ← selalu terisi
    'user_agent' => request()->userAgent(),    // ← selalu terisi
    'submitted_at' => now(),
]);
```

### Kode Sesudah
```php
$submission = FormSubmission::create([
    'uuid' => Str::uuid(),
    'form_id' => $this->form->id,
    'user_id' => auth()->check() ? auth()->id() : null,
    'ip_address' => $this->form->collect_ip ? request()->ip() : null,
    'user_agent' => $this->form->collect_ip ? request()->userAgent() : null,
    'submitted_at' => now(),
]);
```

### Verifikasi
1. Buat form baru dengan `collect_ip = false`
2. Submit via Livewire (halaman `/form/{slug}`)
3. Cek DB: `SELECT ip_address, user_agent FROM form_submissions ORDER BY id DESC LIMIT 1;`
4. Hasil harus: `NULL, NULL`

---

## Task 1.2 — Cegah Export Form SENSITIVE

| Item | Detail |
|---|---|
| **Severity** | 🔴 **Critical** |
| **Estimasi** | 1 jam |
| **Masalah** | Enum `DataClassification` sudah punya `canExport()` — SENSITIVE return `false` — tapi tidak pernah dipanggil |

### File 1: `app/Http/Controllers/API/FormCrudController.php`

**Lokasi A:** Baris 267 — method `exportCsv()`, setelah `$this->authorize('view', $form);`

**Lokasi B:** Baris 297 — method `exportPdf()`, setelah `$this->authorize('view', $form);`

**Kode yang ditambahkan di kedua lokasi:**
```php
if (!$form->data_classification?->canExport()) {
    return response()->json([
        'success' => false,
        'data' => null,
        'message' => 'Form dengan klasifikasi ini tidak dapat diexport.',
    ], 403);
}
```

### File 2: `app/Http/Controllers/PageController.php`

**Lokasi A:** Baris 247 — method `exportCsv()`, setelah `$this->authorize('view', $form);`

**Lokasi B:** Baris 278 — method `exportPdf()`, setelah `$this->authorize('view', $form);`

**Kode yang ditambahkan di kedua lokasi:**
```php
if (!$form->data_classification?->canExport()) {
    return redirect()->back()->with('error', 'Form dengan klasifikasi ini tidak dapat diexport.');
}
```

### Verifikasi
1. Set form ke `sensitive`: `DB::table('forms')->where('slug', '...')->update(['data_classification' => 'sensitive'])`
2. Export CSV via API → harus 403
3. Export PDF via Web → harus redirect back with error
4. Set `data_classification` ke `public`, ulangi → export berhasil

---

## Task 1.3 — Pindah File Upload ke Private Disk

| Item | Detail |
|---|---|
| **Severity** | 🔴 **Critical** |
| **Estimasi** | 2-3 jam |
| **Masalah** | File submission disimpan di `storage/app/public/uploads/` (disk `public`) → bisa diakses siapa pun via URL `/storage/uploads/{file}` tanpa login |

### Langkah Implementasi

| Step | File | Perubahan |
|---|---|---|
| 1 | `app/Livewire/PublicForm.php:208` | Ubah `'public'` jadi `'local'` (private disk) |
| 2 | `routes/web.php` (tambah) | Route baru: `/uploads/{path}` dengan auth + authorization check |

### Kode di `routes/web.php` (tambah di akhir)
```php
Route::get('/uploads/{path}', function (string $path) {
    abort_unless(auth()->check(), 401);

    $fullPath = storage_path('app/private/uploads/' . $path);
    abort_if(!file_exists($fullPath), 404);

    $submissionData = \App\Models\SubmissionData::where('value', 'uploads/' . $path)->first();
    abort_if(!$submissionData, 404);

    $form = $submissionData->submission->form;
    abort_if(auth()->id() !== $form->user_id && !auth()->user()->isSuperAdmin(), 403);

    return response()->file($fullPath);
})->where('path', '.*')->name('uploads.show');
```

### Verifikasi
1. Submit form dengan upload file (jpg/png)
2. Catat path dari `submission_data.value` (contoh: `uploads/abc123.jpg`)
3. Coba akses langsung: `http://localhost:8000/storage/uploads/abc123.jpg` → harus 404
4. Login, akses via route: `http://localhost:8000/uploads/abc123.jpg` → harus muncul file
5. Logout, akses via route yang sama → harus 401
6. Login sebagai admin lain → harus 403

---

## Task 1.4 — XSS Prevention di Blade Views

| Item | Detail |
|---|---|
| **Severity** | 🟡 **High** |
| **Estimasi** | 30 menit |
| **Masalah** | Value submission ditampilkan tanpa `strip_tags()` di beberapa view. Blade sudah auto-escape `{{ }}`, tapi defense in depth tetap perlu |

### File 1: `resources/views/forms/submissions/show.blade.php:21`
```blade
{{-- Sebelum --}}
<p class="...">{{ $data->value ?: '(empty)' }}</p>

{{-- Sesudah --}}
<p class="...">{{ strip_tags($data->value) ?: '(empty)' }}</p>
```

### File 2: `resources/views/exports/submissions-pdf.blade.php:55`
```blade
{{-- Sebelum --}}
<td>{{ $submission->data->firstWhere('form_field_id', $field->id)?->value ?? '—' }}</td>

{{-- Sesudah --}}
<td>{{ strip_tags($submission->data->firstWhere('form_field_id', $field->id)?->value ?? '—') }}</td>
```

### File 3: `resources/views/forms/analytics.blade.php:54`
```blade
{{-- Sebelum --}}
<span class="text-sm text-gray-700 w-48 truncate">{{ $value ?: '(empty)' }}</span>

{{-- Sesudah --}}
<span class="text-sm text-gray-700 w-48 truncate">{{ strip_tags($value) ?: '(empty)' }}</span>
```

### Catatan
File `resources/views/forms/submissions/index.blade.php:45` **sudah aman** karena sudah pakai `strip_tags()`:
```blade
{{ Str::limit(strip_tags($data->value), 80) }}
```

### Verifikasi
1. Submit form dengan value: `<script>alert('xss')</script>` atau `<h1>Judul</h1>`
2. Atau insert langsung via tinker:
```php
$s = App\Models\FormSubmission::first();
$sf = App\Models\FormField::first();
App\Models\SubmissionData::create([
    'submission_id' => $s->id,
    'form_field_id' => $sf->id,
    'value' => '<script>alert("xss")</script>',
]);
```
3. Buka detail submission → harus tampil text biasa, **bukan** popup alert
4. Download PDF → lihat di PDF, harus text biasa
5. Buka analytics → value harus text biasa

---

# 🔒 PRIORITAS 2: Security Hardening (1-2 hari)

Kerjakan setelah Prioritas 1 selesai.

---

## Task 2.1 — Set Token Expiry Sanctum

| Item | Detail |
|---|---|
| **Severity** | 🟡 **High** |
| **Estimasi** | 15 menit |
| **File** | `app/Http/Controllers/API/AuthController.php:32` |
| **Masalah** | Token API tidak pernah expired |

### Kode Sebelum
```php
$token = $user->createToken('api-token')->plainTextToken;
```

### Kode Sesudah
```php
$token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken;
```

### Verifikasi
1. Login API → dapat token
2. Cek DB: `SELECT expires_at FROM personal_access_tokens ORDER BY id DESC LIMIT 1;` → harus terisi (24 jam)
3. Setelah expired, akses endpoint dengan token tersebut → harus 401

---

## Task 2.2 — Session Limit Web Cegah Login

| Item | Detail |
|---|---|
| **Severity** | 🟡 **Medium** |
| **Estimasi** | 1 jam |
| **File** | `app/Http/Controllers/WebAuthController.php` |
| **File pendukung** | `app/Services/SessionLimitService.php` — method `canLogin()` sudah siap |
| **Masalah** | `SessionLimitService::canLogin()` sudah ada tapi tidak dipanggil. Session hanya dihapus setelah login (forced logout), bukan dicegah sebelum login |

### Kode yang Ditambahkan
Di method `login()` WebAuthController, setelah user ditemukan & password cocok:
```php
if (!SessionLimitService::canLogin($user)) {
    return redirect()->back()->with('error', 'Sesi anda sudah mencapai batas maksimal.');
}
```

### Verifikasi
1. Login super_admin di Browser A → sukses
2. Login super_admin di Browser B → harus ditolak
3. Login admin di Browser A, B, C → sukses
4. Login admin di Browser D → harus ditolak

---

## Task 2.3 — Ubah Cascade Delete User

| Item | Detail |
|---|---|
| **Severity** | 🟡 **Medium** |
| **Estimasi** | 30 menit |
| **Masalah** | Foreign key `user_id` di tabel `forms` pakai `cascadeOnDelete()` — hapus user otomatis hapus semua form & submission |

### Migration Baru

**File baru:** `database/migrations/2026_07_10_000001_change_user_id_foreign_to_null_on_delete.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        });
    }
};
```

> **Catatan:** Jangan edit migration lama (`2024_01_01_000001_create_forms_table.php`). Buat migration baru.

### Verifikasi
1. Catat ID user dan form miliknya
2. Hapus user: `User::find($id)->delete()`
3. Cek form yang tadinya milik user tersebut → harus tetap ada, `user_id` jadi NULL
4. Cek submission & data → harus tetap ada

---

# 🧱 PRIORITAS 3: Stabilkan Core Flow (1-2 hari)

Perbaikan usability dan bug yang mengganggu flow utama.

---

## Task 3.1 — Duplicate Form Juga Duplikasi Sections

| Item | Detail |
|---|---|
| **Severity** | 🟡 **High** |
| **Estimasi** | 30 menit |
| **File** | `app/Http/Controllers/API/FormCrudController.php:163-187` |
| **Masalah** | `duplicate()` hanya me-replicate fields, tidak me-replicate sections |

### Kode Sesudah (baris 174-178 diganti)
```php
// Duplikasi sections
foreach ($form->sections as $section) {
    $newSection = $section->replicate(['form_id', 'created_at', 'updated_at']);
    $newSection->form_id = $newForm->id;
    $newSection->save();
}

// Duplikasi fields
foreach ($form->fields as $field) {
    $newField = $field->replicate(['form_id', 'created_at', 'updated_at']);
    $newField->form_id = $newForm->id;
    $newField->save();
}
```

### Verifikasi
1. Buat form dengan 3 section, masing-masing punya 2-3 field
2. Duplikasi: `POST /api/v1/forms/{id}/duplicate`
3. Form baru harus punya 3 section yang sama dengan field-field yang terdistribusi sesuai

---

## Task 3.2 — Loading State di PublicForm Submit

| Item | Detail |
|---|---|
| **Severity** | 🟡 **Medium** |
| **Estimasi** | 30 menit |
| **File 1** | `app/Livewire/PublicForm.php` |
| **File 2** | `resources/views/livewire/public-form.blade.php` |
| **Masalah** | Tidak ada properti loading → user bisa double-click submit |

### File 1: Tambah properti di `PublicForm.php`
```php
public bool $loading = false;
```

### File 1: Di awal `submitForm()`
```php
public function submitForm(): void
{
    if (!$this->form || $this->loading) return;
    $this->loading = true;
    // ... sisanya tetap ...
}
```

### File 2: Blade sudah punya `wire:loading.attr="disabled"` — sudah benar

### Verifikasi
Klik submit berkali-kali cepat → hanya 1 submission masuk di DB

---

## Task 3.3 — Konfirmasi Delete Field

| Item | Detail |
|---|---|
| **Severity** | 🟡 **Low** |
| **Estimasi** | 15 menit |
| **File** | `resources/views/livewire/form-editor.blade.php` |
| **Masalah** | Delete field langsung hapus tanpa konfirmasi |

### Perkiraan perubahan (cek dulu struktur tombol delete di file)
```blade
<button type="button" wire:click="deleteField({{ $field->id }})"
    onclick="return confirm('Hapus field \'{{ $field->label }}\'? Semua data untuk field ini akan ikut terhapus.')"
    class="...">
    Hapus
</button>
```

### Verifikasi
1. Klik delete → muncul dialog konfirmasi
2. Cancel → field tidak hilang
3. OK → field hilang

---

## Task 3.4 — Implementasi Redirect URL

| Item | Detail |
|---|---|
| **Severity** | 🟡 **Medium** |
| **Estimasi** | 15 menit |
| **File** | `app/Livewire/PublicForm.php:219` |
| **Masalah** | `confirmation_type=redirect` dan `redirect_url` bisa disimpan tapi tidak berfungsi |

### Kode Sesudah (ganti `$this->submitted = true`)
```php
if ($this->form->confirmation_type === 'redirect' && $this->form->redirect_url) {
    $this->redirect($this->form->redirect_url);
} else {
    $this->submitted = true;
}
```

### Verifikasi
1. Buat form dengan `confirmation_type = redirect`, `redirect_url = https://google.com`
2. Submit → harus redirect ke Google
3. Buat form lain dengan `confirmation_type = message` → setelah submit, tampil halaman terima kasih

---

## Task 3.5 — Empty State Dashboard

| Item | Detail |
|---|---|
| **Severity** | 🟡 **Low** |
| **Estimasi** | 30 menit |
| **File** | `resources/views/dashboard/index.blade.php` |
| **Masalah** | Dashboard baru menampilkan angka 0 tanpa pesan informatif |

### Kode yang Ditambahkan
Letakkan di bagian bawah dashboard, sebelum tabel recent forms:
```blade
@if($totalForms === 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Form</h2>
        <p class="text-sm text-gray-500 mb-4">Buat form pertama Anda untuk mulai mengumpulkan data.</p>
        <a href="{{ route('forms.create') }}" class="inline-block bg-kbb-700 hover:bg-kbb-800 text-white px-6 py-2.5 rounded-lg transition text-sm font-medium">
            Buat Form Baru
        </a>
    </div>
@endif
```

### Verifikasi
1. Login dengan admin yang belum punya form → lihat empty state + tombol "Buat Form Baru"
2. Login dengan admin yang punya form → dashboard normal

---

# 🧪 PRIORITAS 4: Testing Foundation (3-4 hari)

Tulis test coverage untuk flow kritis sebelum nambah fitur baru.

---

## Task 4.1 — Auth Test

| Item | Detail |
|---|---|
| **File baru** | `tests/Feature/AuthTest.php` |
| **Estimasi** | 1 hari |

### Skenario Test

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    // === WEB AUTH ===
    
    /** @test */
    public function web_login_page_can_be_rendered()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function web_user_can_login_with_valid_credentials()
    {
        $user = User::where('email', 'admin@dinas.com')->first();
        $response = $this->post('/login', [
            'email' => 'admin@dinas.com',
            'password' => 'admin12345',
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    /** @test */
    public function web_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'admin@dinas.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // === API AUTH ===

    /** @test */
    public function api_user_can_login()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@dinas.com',
            'password' => 'admin12345',
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['user', 'token']]);
    }

    /** @test */
    public function api_login_rate_limited()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'admin@dinas.com',
                'password' => 'admin12345',
            ]);
        }
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@dinas.com',
            'password' => 'admin12345',
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function api_authenticated_user_can_access_me()
    {
        $user = User::where('email', 'admin@dinas.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'admin@dinas.com');
    }

    /** @test */
    public function api_unauthenticated_user_gets_401()
    {
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }

    // === ROLE MIDDLEWARE ===

    /** @test */
    public function admin_cannot_access_users_route()
    {
        $admin = User::where('email', 'admin@batujajar.com')->first();
        $this->actingAs($admin);
        $response = $this->get('/users');
        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_can_access_users_route()
    {
        $sa = User::where('email', 'admin@dinas.com')->first();
        $this->actingAs($sa);
        $response = $this->get('/users');
        $response->assertStatus(200);
    }
}
```

---

## Task 4.2 — Form CRUD Test

| Item | Detail |
|---|---|
| **File baru** | `tests/Feature/FormCrudTest.php` |
| **Estimasi** | 1 hari |

### Skenario Test
| No | Test | Method | Expected |
|---|---|---|---|
| 1 | Create form | `POST /api/v1/forms` | 201 |
| 2 | Create form gagal (no title) | `POST /api/v1/forms` (empty) | 422 |
| 3 | List forms | `GET /api/v1/forms` | 200, pagination |
| 4 | List forms by status | `GET /api/v1/forms?status=published` | 200, filtered |
| 5 | Show form | `GET /api/v1/forms/{id}` | 200, load fields |
| 6 | Show form not owned | `GET /api/v1/forms/{other_admin_form}` | 403 |
| 7 | Update form | `PUT /api/v1/forms/{id}` | 200 |
| 8 | Delete form (soft) | `DELETE /api/v1/forms/{id}` | 200, trashed |
| 9 | Publish form | `POST /api/v1/forms/{id}/publish` | status=published |
| 10 | Close form | `POST /api/v1/forms/{id}/close` | status=closed |
| 11 | Duplicate form | `POST /api/v1/forms/{id}/duplicate` | 201, new form |
| 12 | Add field | `POST /api/v1/forms/{id}/fields` | 201 |
| 13 | Update field | `PUT /api/v1/forms/{id}/fields/{field}` | 200 |
| 14 | Delete field | `DELETE /api/v1/forms/{id}/fields/{field}` | 200 |
| 15 | Reorder fields | `POST /api/v1/forms/{id}/fields/reorder` | 200 |

---

## Task 4.3 — Submission Test

| Item | Detail |
|---|---|
| **File baru** | `tests/Feature/SubmissionTest.php` |
| **Estimasi** | 1 hari |

### Skenario Test
| No | Test | Expected |
|---|---|---|
| 1 | Submit public form | 201 |
| 2 | Submit tanpa data required | 422 |
| 3 | Submit email invalid | 422 |
| 4 | Submit number invalid | 422 |
| 5 | Submit form expired | 410 |
| 6 | Submit form penuh | 410 |
| 7 | Submit require_auth tanpa login | 401 |
| 8 | Submit limit_one_response 2x | 409 |
| 9 | List submissions | 200 |
| 10 | Show submission | 200 |
| 11 | Delete submission | 200 |
| 12 | Export CSV | 200, file download |
| 13 | Export PDF | 200, file download |
| 14 | Lihat form publik | 200 |
| 15 | Form publik not found | 404 |

---

## Task 4.4 — Access Control Test

| Item | Detail |
|---|---|
| **File baru** | `tests/Feature/AccessControlTest.php` |
| **Estimasi** | 1 hari |

### Skenario Test
| No | Test | Expected |
|---|---|---|
| 1 | Admin A lihat form Admin B (API) | 403 |
| 2 | Admin A lihat form Admin B (Web) | 403 |
| 3 | Admin A edit form Admin B | 403 |
| 4 | Admin A hapus form Admin B | 403 |
| 5 | Super Admin lihat semua form | 200, includes all |
| 6 | Super Admin lihat form admin mana pun | 200 |
| 7 | Admin hanya lihat form sendiri di dashboard | filtered by user_id |
| 8 | Export silang antar admin | 403 |

---

# 🛠 PRIORITAS 5: Technical Debt (5-8 hari)

Refactor dan optimasi untuk jangka panjang. Kerjakan setelah testing coverage cukup.

---

## Task 5.1 — Migrasi user_id → opd_id

| Item | Detail |
|---|---|
| **Severity** | 🟡 **High** |
| **Estimasi** | 3-4 hari |
| **Masalah** | Isolasi data via `user_id`, bukan `opd_id`. Kolom `opd_id` sudah ada di tabel `users` dan `forms` tapi tidak dipakai |

### Background
Saat ini 1 OPD = 1 admin = 1 user_id. Jika nanti perlu >1 admin per OPD, filter `user_id` tidak akan berfungsi. Solusinya: filter via `opd_id`.

### Step 1 — Seeder OPD
**File baru:** `database/seeders/OPDSeeder.php`

Isi tabel `opds` dengan 47 OPD (sama dengan daftar di `DinasUserSeeder`).

### Step 2 — Update DinasUserSeeder
**File:** `database/seeders/DinasUserSeeder.php`

Setelah create user, set `opd_id`:
```php
$user->opd_id = OPD::where('code', $acct['slug'])->first()?->id;
$user->save();
```

### Step 3 — Ubah FormPolicy (utama)
**File:** `app/Policies/FormPolicy.php`

```php
public function view(User $user, Form $form): bool
{
    if ($user->role->value === 'super_admin') return true;
    if ($user->opd_id && $form->opd_id) {
        return $user->opd_id === $form->opd_id;
    }
    return $user->id === $form->user_id; // fallback
}
```

Ulangi untuk `update()` dan `delete()`.

### Step 4 — Ubah Query Filter di Controller
File yang diubah:
- `app/Http/Controllers/PageController.php` — 15+ titik `where('user_id', ...)`
- `app/Http/Controllers/API/FormCrudController.php:18-23`
- `app/Http/Controllers/API/DashboardController.php`

### Step 5 — Update Form Create
**File:** `app/Http/Controllers/API/FormCrudController.php:73-89`

Tambah `opd_id` saat create form:
```php
'opd_id' => $request->user()->opd_id,
```

---

## Task 5.2 — Wire Domain Services ke Controller

| Item | Detail |
|---|---|
| **Severity** | 🟡 **Medium** |
| **Estimasi** | 2-3 hari |
| **Masalah** | `app/Domains/` berisi 6 service dengan logika bisnis, tapi controller masih pakai Eloquent langsung |

### Mapping Controller → Service

| Controller | Service |
|---|---|
| `API/AuthController` | `Domains\Auth\Services\AuthService`, `AuthorizationService` |
| `API/FormCrudController` | `Domains\InternalForms\Services\FormManagementService` |
| `API/SubmissionApiController` | `Domains\Submissions\Services\SubmissionService`, `Domains\PublicForms\Services\PublicFormService` |
| `API/DashboardController` | `Domains\Reporting\Services\ReportingService` |
| `PageController` | `Domains\Reporting\Services\ReportingService` |
| `Livewire/FormEditor` | `Domains\InternalForms\Services\FormManagementService` |
| `Livewire/PublicForm` | `Domains\PublicForms\Services\PublicFormService` |

### Contoh perubahan
```php
// Sebelum
class FormCrudController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $form = Form::create([...]);
    }
}

// Sesudah
use App\Domains\InternalForms\Services\FormManagementService;

class FormCrudController extends Controller
{
    public function __construct(
        private FormManagementService $formManagementService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $form = $this->formManagementService->createForm(
            $request->user(),
            $request->validated()
        );
    }
}
```

---

## Task 5.3 — Implementasi Validasi yang Belum Ada

| Item | Detail |
|---|---|
| **Severity** | 🟡 **Medium** |
| **Estimasi** | 2-3 hari |

### 5.3a — Allowed Domains

**Kondisi:** Ada di `Form.php:50` (casts array). Tidak dicek.

**Perubahan di `SubmissionApiController.php:store()` — setelah cek `require_auth`:**
```php
if ($form->allowed_domains && $request->user()) {
    $emailDomain = explode('@', $request->user()->email)[1] ?? null;
    if ($emailDomain && !in_array($emailDomain, $form->allowed_domains)) {
        return response()->json(['message' => 'Domain email tidak diizinkan.'], 403);
    }
}
```

### 5.3b — Header Image

**Kondisi:** Ada di `Form.php:34` (fillable). Tidak ada upload logic.

Implementasi:
1. Tambah endpoint upload di `FormCrudController` (store image, simpan path)
2. Tambah input file di form editor (Blade)
3. Tampilkan image di public form header

### 5.3c — Theme Color

**Kondisi:** Ada di `Form.php:35` (fillable). Tidak dipakai.

Implementasi:
1. Tambah color picker di form settings (Blade)
2. Apply ke CSS form publik via inline style

### 5.3d — Field Conditions

**Kondisi:** Migration `add_conditions_to_form_fields` sudah buat kolom. Logika skip belum ada.

Implementasi:
1. Parse JSON conditions di `PublicForm` blade
2. Sembunyikan field via JS jika kondisi tidak terpenuhi
3. Skip validasi untuk field yang di-sembunyikan di `submitForm()`

---

## Task 5.4 — Optimasi Query Dashboard

| Item | Detail |
|---|---|
| **Severity** | 🟡 **Low** |
| **Estimasi** | 1-2 hari |
| **File** | `app/Http/Controllers/PageController.php:22-92` |
| **Masalah** | Dashboard menjalankan 10+ query SQL terpisah |

### Optimasi 1: Gabung hitungan form
```php
// Sebelum: 4 query
$totalForms = Form::count();
$publishedForms = Form::where('status', 'published')->count();
$draftForms = Form::where('status', 'draft')->count();
$closedForms = Form::where('status', 'closed')->count();

// Sesudah: 1 query
$formCounts = Form::selectRaw("COUNT(*) as total")
    ->selectRaw("SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published")
    ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft")
    ->selectRaw("SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed")
    ->first();
```

### Optimasi 2: Gabung hitungan submission
```php
$submissionCounts = FormSubmission::selectRaw("COUNT(*) as total")
    ->selectRaw("SUM(CASE WHEN DATE(submitted_at) = CURDATE() THEN 1 ELSE 0 END) as today")
    ->selectRaw("SUM(CASE WHEN submitted_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as this_week", [
        now()->startOfWeek(), now()->endOfWeek()
    ])
    ->first();
```

### Optimasi 3: Submission per hari dalam 1 query
```php
// Sebelum: loop 7 hari = 7 query
foreach (range(6, 0) as $i) {
    $count = \App\Models\FormSubmission::whereDate('submitted_at', now()->subDays($i))->count();
}

// Sesudah: 1 query
$submissionsByDate = \App\Models\FormSubmission::selectRaw("DATE(submitted_at) as date, COUNT(*) as count")
    ->where('submitted_at', '>=', now()->subDays(6))
    ->groupBy('date')
    ->pluck('count', 'date');
```

---

## Task 5.5 — Optimasi CSV Export

| Item | Detail |
|---|---|
| **Severity** | 🟡 **Low** |
| **Estimasi** | 1 jam |
| **File** | `app/Http/Controllers/API/FormCrudController.php:266-293` |
| **File** | `app/Http/Controllers/PageController.php:244-273` |
| **Masalah** | `php://output` tanpa buffer flushing → memory leak untuk data besar |

### Perubahan
```php
$callback = function () use ($form, $fields, $headers) {
    $handle = fopen('php://output', 'w');
    fputcsv($handle, $headers);

    $form->submissions()->chunk(100, function ($submissions) use ($handle, $fields) {
        foreach ($submissions as $submission) {
            $row = [...];
            fputcsv($handle, $row);
        }
        ob_flush();  // ← tambah
        flush();     // ← tambah
    });

    fclose($handle);
};
```

---

# 📊 RINGKASAN SEMUA FILE YANG AKAN DIUBAH

| Prioritas | File | Task |
|---|---|---|
| **1** | `app/Livewire/PublicForm.php` | 1.1, 1.3 |
| **1** | `app/Http/Controllers/API/FormCrudController.php` | 1.2 |
| **1** | `app/Http/Controllers/PageController.php` | 1.2 |
| **1** | `resources/views/forms/submissions/show.blade.php` | 1.4 |
| **1** | `resources/views/exports/submissions-pdf.blade.php` | 1.4 |
| **1** | `resources/views/forms/analytics.blade.php` | 1.4 |
| **1** | `routes/web.php` | 1.3 |
| **2** | `app/Http/Controllers/API/AuthController.php` | 2.1 |
| **2** | `app/Http/Controllers/WebAuthController.php` | 2.2 |
| **2** | Migration baru (1 file) | 2.3 |
| **3** | `app/Http/Controllers/API/FormCrudController.php` | 3.1 |
| **3** | `app/Livewire/PublicForm.php` | 3.2, 3.4 |
| **3** | `resources/views/livewire/public-form.blade.php` | 3.2 |
| **3** | `resources/views/livewire/form-editor.blade.php` | 3.3 |
| **3** | `resources/views/dashboard/index.blade.php` | 3.5 |
| **4** | `tests/Feature/AuthTest.php` (baru) | 4.1 |
| **4** | `tests/Feature/FormCrudTest.php` (baru) | 4.2 |
| **4** | `tests/Feature/SubmissionTest.php` (baru) | 4.3 |
| **4** | `tests/Feature/AccessControlTest.php` (baru) | 4.4 |
| **5** | `database/seeders/OPDSeeder.php` (baru) | 5.1 |
| **5** | `database/seeders/DinasUserSeeder.php` | 5.1 |
| **5** | `app/Policies/FormPolicy.php` | 5.1 |
| **5** | 6 Controller + 2 Livewire (inject service) | 5.2 |
| **5** | `app/Http/Controllers/API/SubmissionApiController.php` | 5.3a |
| **5** | `app/Http/Controllers/API/FormCrudController.php` | 5.3b |
| **5** | `resources/views/livewire/form-editor.blade.php` | 5.3b, 5.3c |
| **5** | `resources/views/livewire/public-form.blade.php` | 5.3c, 5.3d |
| **5** | `app/Http/Controllers/PageController.php` | 5.4 |
| **5** | `app/Http/Controllers/API/FormCrudController.php` | 5.5 |
| **5** | `app/Http/Controllers/PageController.php` | 5.5 |

---

## Timeline

```
Minggu 1:  ████████████████████  Prioritas 1 (Security Critical)      ~3 hari
Minggu 2:  ████████████████████  Prioritas 2 (Hardening)              ~2 hari
           ████████████████████  Prioritas 3 (Stabilkan Flow)         ~2 hari
Minggu 3:  ████████████████████  Prioritas 4 (Testing)                ~4 hari
Minggu 4-6: ████████████████████ Prioritas 5 (Technical Debt)         ~5-8 hari
```
