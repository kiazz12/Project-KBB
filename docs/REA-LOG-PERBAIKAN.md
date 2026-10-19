# REA — Log Perbaikan
## Project KBB (Reverse Engineering Analysis)

| | |
|---|---|
| **Basis** | Register temuan di [REA-TEMUAN.md](REA-TEMUAN.md) |
| **Tanggal** | 19–20 Oktober 2026 |
| **Cakupan** | 22 temuan diperbaiki (lihat kolom Status di REA-TEMUAN.md) |
| **Verifikasi** | ⚠️ **Statis saja** — PHP & Composer tidak tersedia di mesin analisis. `php artisan test` dan `vendor/bin/pint` **wajib dijalankan** di lingkungan ber-PHP. |

**Konvensi:** setiap entri = **Lokasi → Sebelum → Sesudah → Dampak**. ID `F-xx` dipakai silang di [REA-DIAGRAM.md](REA-DIAGRAM.md).

---

## F-01 — AuditService menerima Model|string|array|null (B-01, kritis)

**Lokasi:** `backend/app/Services/AuditService.php`

**Sebelum:** argumen ke-2 diperlakukan sebagai Model semata; pemanggil historis mengirim array → `get_class(array)` memicu `TypeError` (HTTP 500) pada aksi hapus submission.

```php
// SEBELUM
public static function log(string $action, $model = null, ...)
{
    $type = get_class($model);   // TypeError bila $model array
```

**Sesudah:** array digabung ke `new_values`; string menjadi deskripsi implisit; model tetap mengisi kolom morph.

```php
// SESUDAH
public static function log(string $action, Model|string|array|null $model = null, ...)
{
    if (is_array($model)) { $newValues = array_merge($model, $newValues ?? []); $model = null; }
    elseif (is_string($model)) { $description = $description ?? $model; $model = null; }
    // kolom morph diisi hanya bila $model instanceof Model
```

**Dampak:** aksi hapus submission & pemanggilan ber-array lain tidak lagi memicu 500; audit trail kembali lengkap.
**Uji:** `tests/Unit/AuditServiceTest.php`

---

## F-02 — IDOR detail & hapus submission ditutup di API (S-01, S-02, kritis)

**Lokasi:** `backend/app/Http/Controllers/API/SubmissionApiController.php`

**Sebelum:** `authorize('view'/'update', $form)` lalu `FormSubmission::findOrFail($id)` **global** — submission form mana pun bisa dibaca/dihapus selama pelaku punya izin atas *form di URL*.

**Sesudah:** submission di-scope relasi form (`$form->submissions()->findOrFail(...)`), `destroy` memakai `authorize('delete')`, dan `index` menyaring submission belum terkirim (`submitted_at` terisi).

```php
// SESUDAH (show & destroy)
$submission = $form->submissions()->with(...)->findOrFail($submissionId);
$this->authorize('delete', $form);   // destroy
```

**Dampak:** lintas-form 404; penghapusan sesuai matriks otorisasi.
**Uji:** `tests/Feature/SubmissionOwnershipTest.php`

---

## F-03 — IDOR & izin hapus ditutup di jalur web (S-01, S-06)

**Lokasi:** `backend/app/Http/Controllers/PageController.php` (`submissionsShow`, `deleteSubmission`, `bulkDeleteSubmissions`)

**Sebelum:** `Submission::find($id)` global; hapus memakai `authorize('view')`; parameter audit salah nama sehingga konteks log tidak lengkap.

**Sesudah:** scope `$form->submissions()->findOrFail($id)`, `authorize('delete', $form)` pada tunggal & massal, parameter audit diperbaiki.

**Dampak:** konsisten dengan F-02; sesuai matriks otorisasi.
**Uji:** `tests/Feature/SubmissionOwnershipTest.php` (jalur API; jalur web mengikuti pola sama)

---

## F-04 — Guardrail form di komponen Livewire publik (S-03, S-04, S-05)

**Lokasi:** `backend/app/Livewire/PublicForm.php`

**Sebelum:** `mount()` hanya cek `published` + `isFull()/isExpired()`; `submitForm()` langsung memvalidasi & menyimpan — `require_auth`, `limit_one_response`, dan `starts_at` **tidak pernah dicek** di jalur web.

**Sesudah:** `mount()` memanggil `isNotOpenYet()`; `submitForm()` mengulang semua guardrail (kondisi bisa berubah selama pengguna mengisi):

```php
// SESUDAH (submitForm, setelah honeypot)
if (! $this->form->isAcceptingSubmissions()) { /* belum dibuka / ditutup / penuh */ return; }
if ($this->form->require_auth && ! Auth::check()) { /* wajib login */ return; }
if ($this->form->limit_one_response && Auth::check()
    && $this->form->submissions()->where('user_id', Auth::id())->exists()) { /* satu jawaban */ return; }
```

**Dampak:** pengaturan form kini berlaku sama di web & API.
**Uji:** `tests/Feature/PublicFormGuardrailsTest.php`

---

## F-05 — Validasi `opd_id` pada CRUD user API (S-08)

**Lokasi:** `backend/app/Http/Controllers/API/UserController.php` (`store`, `update`)

**Sebelum:** `opd_id` lolos validasi → FK ke tabel `opds` bisa bernilai tak ada (error 500) atau korup relasi.

**Sesudah:** `'opd_id' => 'nullable|integer|exists:opds,id'` pada `store` **dan** `update`.

**Dampak:** input bersih, kegagalan jadi 422 yang terprediksi.

---

## F-06 — Duplikasi form memetakan ulang section (B-04)

**Lokasi:** 4 tempat — `PageController::duplicateForm`, `API\FormCrudController::duplicate`, `App\Services\FormService::duplicate`, `Domains/InternalForms\...\FormManagementService::duplicateForm`

**Sebelum:** field disalin apa adanya → `section_id` menunjuk section **form asal** (duplikat salah tampil / kebocoran silang).

**Sesudah (pola sama di 4 lokasi):** section disalin lebih dulu ke `$sectionMap`, lalu tiap field baru menerima `section_id` hasil pemetaan; `replicate` mengecualikan `uuid`, `slug`, `form_id`, dan (pada domain service) `created_at/updated_at`.

```php
// SESUDAH (inti)
$sectionMap = [];
foreach ($form->sections()->orderBy('order')->get() as $section) {
    $newSection = $section->replicate(['form_id', 'created_at', 'updated_at']);
    $newSection->form_id = $newForm->id; $newSection->save();
    $sectionMap[$section->id] = $newSection->id;
}
foreach ($form->fields as $field) {
    $newField = $field->replicate(['form_id', 'created_at', 'updated_at']);
    $newField->form_id = $newForm->id;
    $newField->section_id = $field->section_id ? ($sectionMap[$field->section_id] ?? null) : null;
    $newField->save();
}
```

**Dampak:** duplikat identik & terisolasi.
**Uji:** `tests/Feature/DuplicateFormTest.php` (web + API)

---

## F-07 — Anti CSV/formula injection terpusat (S-09)

**Lokasi:** `backend/app/Support/Csv.php` (**baru**) → dipakai `PageController::exportCsv`, `FormCrudController::exportCsv`, `App\Services\FormService::exportCsv`, `ReportingService::exportToCsv`

**Sebelum:** nilai peserta ditulis mentah; sel diawali `=`, `+`, `-`, `@`, TAB, CR dieksekusi lembar kerja (OWASP CSV Injection).

**Sesudah:**

```php
// app/Support/Csv.php
public static function sanitize(string|int|float|null $value): string
{
    $value = (string) $value;
    return in_array(mb_substr($value, 0, 1), ['=', '+', '-', '@', "\t", "\r"], true)
        ? "'".$value : $value;
}
```

**Dampak:** ekspor web/API/service memakai satu implementasi.
**Uji:** `tests/Unit/CsvSanitizeTest.php`

---

## F-08 — Helper guardrail waktu & status pada model Form (S-05, bagian S-03/S-04)

**Lokasi:** `backend/app/Models/Form.php`

**Sebelum:** logika buka/tutup/kuota tersebar & `starts_at` tidak dicek di mana pun; API `store`/`showPublic` hanya memeriksa status.

**Sesudah:** dua method menjadi satu-satunya sumber kebenaran gerbang:

```php
public function isNotOpenYet(): bool
    => $this->starts_at !== null && now()->lessThan($this->starts_at);

public function isAcceptingSubmissions(): bool
    => $this->status === FormStatus::Published
        && ! $this->isNotOpenYet() && ! $this->isExpired() && ! $this->isFull();
```

Dipakai di `PublicForm::mount/submitForm` (F-04) dan `SubmissionApiController::store/showPublic`. Diagram status form di [REA-DIAGRAM.md](REA-DIAGRAM.md) merujuk entri ini.

---

## F-09 — Field admin-only tak lagi bocor / bisa diisi via API (B-09)

**Lokasi:** `backend/app/Http/Controllers/API/SubmissionApiController.php` (`showPublic`, `store`)

**Sebelum:** `showPublic` mengembalikan seluruh definisi field termasuk `is_admin_only`; `store` membangun rules & menyimpan nilai untuk semua field.

**Sesudah:** `showPublic` menyaring field admin-only dari respons; `store` melewati field admin-only saat membangun rules **dan** saat persistensi; cek `require_email` memakai `?? null` agar tidak membaca key yang tidak ada.

**Dampak:** field internal (mis. catatan verifikasi OPD) tidak terlihat/diisi peserta.

---

## F-10 — Rate limit pada login web & panel admin (S-07)

**Lokasi:** `backend/routes/web.php`, `backend/routes/admin.php`

**Sebelum:** `POST /login` web & `/admin/login` tanpa throttle (API sudah `throttle:5,1`).

**Sesudah:** keduanya `->middleware('throttle:5,1')`.

**Dampak:** brute-force kata sandi dibatasi di semua jalur masuk.

---

## F-11 — Penyimpanan submission dalam satu transaksi DB

**Lokasi:** `SubmissionApiController::store`, `PublicForm::submitForm`

**Sebelum:** submission header + N baris `submission_data` ditulis bertahap — kegagalan di tengah meninggalkan data setengah jadi.

**Sesudah:** seluruh persistensi dibungkus `DB::transaction(function () use (...) { ... })`.

**Dampak:** semua-atau-tidak-sama-sekali; relasi tidak pernah yatim.

---

## F-12 — Tipe `computed` & cast `conditions` (B-03)

**Lokasi:** `API\FieldController` (2 daftar `in:` validasi), `Models\FormField` (cast)

**Sebelum:** API menolak tipe `computed` yang valid di enum; `conditions` (JSON) tidak di-cast sehingga `null`/string tidak konsisten.

**Sesudah:** `'text,textarea,...,computed'` pada kedua daftar; `'conditions' => 'array'` di `casts()`.

---

## F-13 — Guardrail auto-save pengaturan form (B-02)

**Lokasi:** `FormEditor::autoSaveSettings`

**Sebelum:** `wire:poll.30s` menyimpan apa pun — judul kosong atau `ends_at ≤ starts_at` langsung merusak form aktif.

**Sesudah:** skip judul kosong (tanpa pesan galat yang mengganggu); `starts_at ≥ ends_at` ditolak + event `autosave-error` didispatch agar UI bisa menampilkan peringatan.

---

## F-14 — Perbaikan kode mati `App\Services\SubmissionService` (B-05)

**Sebelum:** `with('data.field')` (relasi `field` tidak ada — aslinya `formField`) dan `$field->type === 'checkbox'` (perbandingan enum vs string — selalu `false`).

**Sesudah:** `with('data.formField')` (3 tempat) dan `$field->type->value === 'checkbox'`.

---

## F-15 — Perbaikan kode mati `ReportingService` (B-06)

**Sebelum:** `$user->role === 'super_admin'` (enum vs string — selalu salah); query `published_forms`/`published_at` yang kolomnya tidak ada; `diffInDays` bisa negatif; CSV mentah.

**Sesudah:** `$user->isSuperAdmin()`; `Form::published()`; fallback `created_at` + `abs(...)`; ekspor memakai `Csv::sanitize` (F-07).

---

## F-16 — Perbaikan kode mati `FormManagementService` (B-07)

**Sebelum:** menulis kolom `config` yang tidak ada di `form_fields`.

**Sesudah:** menulis `options`; `duplicateForm` memetakan ulang section (F-06) dan mengecualikan `uuid`/`slug` saat `replicate`.

---

## F-17 — `App\Services\FormService` sinkron dengan jalur aktif (B-04, S-09)

**Sesudah:** `duplicate` memetakan ulang section (F-06); `exportCsv` memakai `Csv::sanitize` (F-07) — aman bila diaktifkan kembali (D-06).

---

## F-18 — Onboarding: `.env.example` & `start.bat` (D-08, sebagian D-07)

**Lokasi:** `backend/.env.example` (**baru**), `start.bat`

**Sebelum:** tanpa `.env.example` → `composer setup` gagal menyalin `.env`; `start.bat` menjalankan `frontend/` yang tidak ada di repo.

**Sesudah:** `.env.example` lengkap sesuai `config/*` (SQLite default, MySQL terkomentari, MAIL log, SESSION/CACHE/QUEUE database); `start.bat` hanya menjalankan backend (`php artisan serve --port=8000`).

---

## F-19 — Idle-timeout 5 menit kini berfungsi (D-01)

**Lokasi:** `TrackSessionActivity`, `SessionTimeout::recordActivity()`, `session-timeout.blade.php`

**Sebelum:** `wire:poll.30s` mengirim request terjadwal → middleware me-refresh `last_activity_at` → pengguna yang membuka tab tanpa menyentuh apa pun tak pernah dianggap tidak aktif.

**Sesudah:**

```php
// TrackSessionActivity — refresh HANYA request GET tanpa header X-Livewire
if ($request->isMethod('GET') && ! $request->header('X-Livewire')) {
    $session->forceFill(['last_activity_at' => now()])->saveQuietly();
}
// Livewire: $wire.call('recordActivity')
// blade JS: mousemove/keydown/scroll/touchstart → throttled ≥20 detik
```

Rincian pada diagram sequence sesi di [REA-DIAGRAM.md](REA-DIAGRAM.md).

---

## F-20 — Indeks performa (D-12)

**Lokasi:** `database/migrations/2026_10_19_000001_add_rea_performance_indexes.php` (**baru**)

9 indeks, idempoten (`Schema::hasIndex`): `form_submissions(form_id, submitted_at)`, `(user_id)`, `submission_data(submission_id)`, `(form_field_id)`, `audit_logs(user_id, created_at)`, `(action)`, `(auditable_type, auditable_id)`, `user_sessions(user_id, logged_out_at)`, `participants(nama)`.

---

## F-21 — README dikoreksi (D-07)

14 jenis bidang (hapus "rating/matriks" yang tak ada), 13 berkas uji, 27 migrasi, catatan `start.bat` (backend saja), `.env.example` kini bawaan, tabel dokumentasi memuat 4 dokumen REA.

---

## F-22 — Uji regresi baru (D-10)

5 berkas (kini 13 total):

| Berkas | Menutup |
|---|---|
| `tests/Feature/SubmissionOwnershipTest.php` | S-01, S-02, S-06 (lintas-form 404, non-pemilik 403) |
| `tests/Feature/PublicFormGuardrailsTest.php` | S-03, S-04, S-05 (mount & submit) |
| `tests/Feature/DuplicateFormTest.php` | B-04 (web & API) |
| `tests/Unit/AuditServiceTest.php` | B-01 (argumen array/model/null) |
| `tests/Unit/CsvSanitizeTest.php` | S-09 (prefix formula dinetralisir) |

---

## Yang TIDAK diperbaiki (sadar)

| Temuan | Alasan |
|---|---|
| **B-08** `PublicFormService` | Kode mati — tak direferensikan controller mana pun. Bug didokumentasikan; perbaikan hanya relevan bila kelas diaktifkan (lihat D-05). |
| **D-09** Matriks OPD | Butuh desain kebijakan + uji regresi bertahap — bukan perbaikan mekanis sesi analisis. |
| **D-04** Duplikasi logika analytics | Solusinya konsolidasi (keputusan arsitektur), bukan patch tambalan. |
| **D-11** `SESSION_ENCRYPT`, masking, retensi | Perubahan konfigurasi operasional yang berdampak lingkungan berjalan — perlu persetujuan operasional. |
| **`public-form.blade.php`** | Kondisi `@if ($error && !$form)` dan blok inline `@if ($error)` (baris ~251) **sudah benar** — guardrail berada di komponen (F-04). |

---

## Checklist verifikasi sebelum rilis

```bash
cd backend
composer install
php artisan test          # 13 berkas — termasuk 5 berkas baru
vendor/bin/pint           # format
php artisan migrate       # indeks F-20 (idempoten, aman diulang)
php artisan serve         # uji manual: /form/{slug} — guardrail F-04
```
