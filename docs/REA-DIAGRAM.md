# REA — Katalog Diagram
## Project KBB · Reverse Engineering Analysis (19 Oktober 2026)

Seluruh diagram di bawah menggunakan Mermaid (dirender otomatis di GitHub/GitLab) dengan tema warna identitas KBB — **Biru `#003778`**, **Emas `#C8A45C`**.

Daftar diagram:

| # | Diagram | Kebutuhan |
|---|---|---|
| 1 | [Konteks Sistem (C4 Level 1)](#1-konteks-sistem-c4-level-1) | Pemulihan ruang lingkup |
| 2 | [Kontainer / Arsitektur](#2-kontainer--arsitektur-aplikasi) | Pemulihan arsitektur |
| 3 | [Komponen — Siklus Hidup Request Web](#3-komponen--siklus-hidup-request-web) | Pemulihan perilaku |
| 4 | [ERD — Basis Data Penuh](#4-erd--basis-data-penuh-14-tabel) | Pemulihan skema |
| 5 | [Sequence — Login Web & Pembatasan Sesi](#5-sequence--login-web--pembatasan-sesi) | Pemulihan perilaku |
| 6 | [Sequence — Pengiriman Formulir Publik](#6-sequence--pengiriman-formulir-publik-livewire) | Pemulihan perilaku + temuan |
| 7 | [Sequence — Ekspor Data & Gerbang Klasifikasi](#7-sequence--ekspor-data--gerbang-klasifikasi) | Pemulihan perilaku |
| 8 | [State Machine — Siklus Hidup Formulir](#8-state-machine--siklus-hidup-formulir) | Pemulihan perilaku |
| 9 | [Decision Tree — Otorisasi](#9-decision-tree--otorisasi-aktual) | Pemulihan keamanan |
| 10 | [Class Diagram — Model Domain](#10-class-diagram--model-domain) | Pemulihan struktur |
| 11 | [Analisis Temuan](#11-analisis-temuan-sebaran-dan-prioritas) | Hasil REA |

---

## 1. Konteks Sistem (C4 Level 1)

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryColor':'#003778','primaryTextColor':'#ffffff','primaryBorderColor':'#002b5e',
  'secondaryColor':'#C8A45C','tertiaryColor':'#eef3fa','lineColor':'#003778',
  'fontFamily':'Inter, Segoe UI, sans-serif','fontSize':'14px'}}}%%
flowchart TB
    PUB(["👥 Pengguna Publik<br/><i>masyarakat / peserta</i>"])
    OPD(["🧑‍💼 Operator OPD<br/><i>47 Organisasi Perangkat Daerah</i>"])
    SA(["🔐 Super Admin<br/><i>Diskominfotik</i>"])
    SYS["<b>Project KBB</b><br/>Sistem Formulir Digital<br/><i>Laravel 13 · Blade + Livewire 4</i>"]
    DB[("🗄️ MySQL 8<br/>14 tabel aplikasi")]
    MAIL["📧 Layanan SMTP<br/><i>notifikasi</i>"]
    DUMP[("💾 Backup mysqldump<br/><i>storage/backups</i>")]
    PART[("👥 Master Peserta<br/><i>tabel participants</i>")]

    PUB -->|"isi form publik<br/>/form/{slug}"| SYS
    OPD -->|"kelola form, ekspor,<br/>lihat submission<br/>/dashboard · /forms/*"| SYS
    SA -->|"panel /admin/*,<br/>kelola user & sistem"| SYS
    SYS --> DB
    SYS -.->|opsional| MAIL
    SYS -->|"backup:run harian"| DUMP
    DB -.->|autocomplete peserta| PART

    classDef kbb fill:#003778,stroke:#002b5e,color:#fff
    classDef gold fill:#C8A45C,stroke:#a8873f,color:#fff
    classDef data fill:#eef3fa,stroke:#003778,color:#003778
    class SYS kbb
    class PUB,OPD,SA gold
    class DB,DUMP,PART,MAIL data
```

---

## 2. Kontainer / Arsitektur Aplikasi

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryColor':'#003778','primaryTextColor':'#ffffff','primaryBorderColor':'#002b5e',
  'secondaryColor':'#C8A45C','tertiaryColor':'#eef3fa','lineColor':'#003778',
  'fontFamily':'Inter, Segoe UI, sans-serif'}}}%%
flowchart TB
    subgraph EDGE["🖥️ Lapisan Presentasi — Blade + Livewire 4 (Tailwind CDN)"]
        direction LR
        LV["<b>Livewire Components</b><br/>FormEditor · CreateForm · PublicForm<br/>SessionTimeout · NotificationBell<br/>ActivityChart · DisplayNamePrompt"]
        BV["<b>Blade Views</b><br/>dashboard · forms/* · admin/*<br/>auth · exports · components"]
    end

    subgraph HTTP["⚙️ Lapisan HTTP — 3 Jalur Rute"]
        direction LR
        W["<b>routes/web.php</b><br/>session auth + FormPolicy<br/><i>/dashboard /forms /form/{slug}</i>"]
        A["<b>routes/api.php</b><br/>Sanctum token + throttle<br/><i>/v1/*</i>"]
        AD["<b>routes/admin.php</b><br/>khusus super_admin<br/><i>/admin/*</i>"]
    end

    subgraph CTRL["🧩 Kontroler"]
        direction LR
        PC["PageController<br/><i>363 baris · otak web</i>"]
        API["API\\*<br/><i>Auth · FormCrud · Field<br/>Submission · Dashboard · User</i>"]
        ADM["Admin\\*<br/><i>Auth · Dashboard · Form · User</i>"]
        MW["Middleware<br/>RoleMiddleware · ForceJsonResponse<br/>TrackSessionActivity"]
    end

    subgraph SVC["🧹 Layanan (yang benar-benar dipakai)"]
        direction LR
        AS["AuditService<br/><i>audit tunggal</i>"]
        NS["NotificationService"]
        SLS["SessionLimitService<br/><i>super_admin=1 · admin=3</i>"]
        POL["FormPolicy · Gate"]
    end

    subgraph DEAD["👻 Kode Mati (tidak direferensikan)"]
        direction LR
        DOM["app/Domains/*<br/>7 service domain"]
        APPS["App\\Services\\<br/>FormService · SubmissionService"]
    end

    subgraph DATA["🗄️ Lapisan Data"]
        direction LR
        MODELS["<b>14 Model Eloquent</b><br/>User OPD Form FormSection FormField<br/>FormSubmission SubmissionData AuditLog<br/>Notification Participant UserSession"]
        MIG["<b>26 Migrasi</b>"]
    end

    EDGE --> HTTP --> CTRL
    CTRL --> SVC
    CTRL -.-> DEAD
    CTRL --> MODELS --> DB[(MySQL)]

    classDef kbb fill:#003778,stroke:#002b5e,color:#fff
    classDef gold fill:#C8A45C,stroke:#a8873f,color:#fff
    classDef data fill:#eef3fa,stroke:#003778,color:#003778
    classDef dead fill:#f5f5f5,stroke:#bdbdbd,color:#757575,stroke-dasharray: 5 5
    class LV,BV,W,A,AD,PC,API,ADM,MW kbb
    class AS,NS,SLS,POL,MODELS,MIG gold
    class DOM,APPS dead
```

---

## 3. Komponen — Siklus Hidup Request Web

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryColor':'#003778','primaryTextColor':'#ffffff','primaryBorderColor':'#002b5e',
  'secondaryColor':'#C8A45C','tertiaryColor':'#eef3fa','lineColor':'#003778',
  'fontFamily':'Inter, Segoe UI, sans-serif'}}}%%
flowchart LR
    R[("Request HTTP<br/><i>GET/POST</i>")] --> GLOBAL["Global Middleware<br/>ValidateCsrfToken · Auth<br/>TrackSessionActivity*"]
    GLOBAL --> GROUP{"Grup Rute"}
    GROUP -->|"publik"| PUB2["/form/{slug}<br/><i>PublicForm (Livewire)</i>"]
    GROUP -->|"auth"| WEB["Grup web<br/>+ <b>FormPolicy::view/update/delete</b>"]
    GROUP -->|"api"| APIG["Grup api<br/>ForceJsonResponse · CORS<br/>auth:sanctum · throttle"]
    GROUP -->|"admin"| ADMG["Grup /admin<br/>role:super_admin"]

    WEB --> AUD["AuditService::log<br/><i>setiap aksi penting</i>"]
    WEB --> NOTIF["NotificationService<br/>→ super_admin lain"]
    PUB2 --> TX["DB::transaction<br/>form_submissions + submission_data"]
    TX --> CONF{"Jenis konfirmasi"}
    CONF -->|message| OK["Pesan terima kasih"]
    CONF -->|redirect| RD["redirect_url form"]

    style R fill:#C8A45C,stroke:#a8873f,color:#fff
    style GLOBAL,WEB,APIG,ADMG,PUB2 fill:#003778,stroke:#002b5e,color:#fff
    style AUD,NOTIF,TX,CONF,OK,RD fill:#eef3fa,stroke:#003778,color:#003778
```

> \* `TrackSessionActivity` kini hanya me-refresh aktivitas pada request **GET** (perbaikan F-19) agar idle-timeout 5 menit berfungsi.

---

## 4. ERD — Basis Data Penuh (14 tabel)

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryKeyColor':'#003778','primaryKeyTextColor':'#ffffff',
  'attributeColor':'#eef3fa','attributeBorderColor':'#003778',
  'foregroundColor':'#003778','lineColor':'#C8A45C',
  'fontFamily':'Inter, Segoe UI, sans-serif'}}}%%
erDiagram
    USERS ||--o{ FORMS : "milik / user_id"
    USERS ||--o{ AUDIT_LOGS : "aktivitas"
    USERS ||--o{ NOTIFICATIONS : "menerima"
    USERS ||--o{ USER_SESSIONS : "login"
    USERS ||--o{ FORM_SUBMISSIONS : "mengisi (nullable)"
    USERS ||--o{ PERSONAL_ACCESS_TOKENS : "token API"
    USERS }o--|| OPDS : "anggota / opd_id"

    OPDS ||--o{ FORMS : "cakupan / opd_id"

    FORMS ||--o{ FORM_SECTIONS : "bagian"
    FORMS ||--o{ FORM_FIELDS : "bidang"
    FORMS ||--o{ FORM_SUBMISSIONS : "kumpulan jawaban"
    FORM_SECTIONS ||--o{ FORM_FIELDS : "memuat / section_id"
    FORM_FIELDS ||--o{ SUBMISSION_DATA : "jawaban per bidang"
    FORM_SUBMISSIONS ||--o{ SUBMISSION_DATA : "berisi"

    ROLE_PERMISSIONS }o--|| USERS : "referensi role (tak terpakai)"

    USERS {
        bigint id PK
        string name
        string display_name
        string email UK
        string password "hashed bcrypt"
        string role "super_admin | admin (default)"
        string nip
        string opd "teks lama"
        bigint opd_id FK "→ opds"
    }
    OPDS {
        bigint id PK
        string name UK
        string code UK
        text description
        timestamp deleted_at "soft delete"
    }
    FORMS {
        bigint id PK
        uuid uuid UK
        bigint user_id FK "pemilik"
        bigint opd_id FK "cakupan OPD (nullable)"
        string title
        text description
        string slug UK "URL publik"
        string status "draft|published|closed"
        enum data_classification "public|internal|sensitive"
        json settings "progress_bar, shuffle, ..."
        text confirmation_message
        string confirmation_type "message|redirect"
        string redirect_url
        string header_image
        string theme_color(7)
        timestamp starts_at "gerbang buka (kini dicek)"
        timestamp ends_at "gerbang tutup"
        unsignedint max_submissions
        boolean require_auth "kini dijalankan di web"
        boolean limit_one_response "kini dijalankan di web"
        boolean collect_ip
        boolean show_kbb_logo
        timestamp deleted_at "soft delete"
    }
    FORM_SECTIONS {
        bigint id PK
        bigint form_id FK
        string title
        text description
        unsignedint order
    }
    FORM_FIELDS {
        bigint id PK
        bigint form_id FK
        bigint section_id FK "nullable"
        string type "14 jenis (FieldType enum)"
        string label
        string placeholder
        text help_text
        boolean required
        json options
        boolean allow_other
        unsignedint order
        json conditions "logika bersyarat (cast array)"
        json formula "field computed"
        boolean is_admin_only "disembunyikan dari publik"
        unsignedint min_length
        unsignedint max_length
        string default_value
    }
    FORM_SUBMISSIONS {
        bigint id PK
        uuid uuid UK
        bigint form_id FK
        bigint user_id FK "nullable / anonim"
        string ip_address "hanya jika collect_ip"
        text user_agent
        timestamp submitted_at
    }
    SUBMISSION_DATA {
        bigint id PK
        bigint submission_id FK
        bigint form_field_id FK
        text value "jawaban"
    }
    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK "nullable"
        string action "auth.login, export.csv, ..."
        string auditable_type "polymorphic"
        bigint auditable_id
        text description
        json old_values
        json new_values
        string ip_address
        text user_agent
    }
    NOTIFICATIONS {
        bigint id PK
        bigint user_id FK
        string type
        text message
        json data "actor_id, form_id, ..."
        timestamp read_at "null = belum dibaca"
    }
    PARTICIPANTS {
        bigint id PK
        int no
        string nama
        string jabatan
        string opd_institusi
        string role
        date tanggal_presensi
    }
    USER_SESSIONS {
        bigint id PK
        bigint user_id FK
        string display_name "per sesi"
        string ip_address
        string user_agent
        timestamp logged_in_at
        timestamp logged_out_at "null = aktif"
    }
    PERSONAL_ACCESS_TOKENS {
        bigint id PK
        bigint tokenable_id FK "morph users"
        string tokenable_type
        string name
        string token UK "sha256"
        string abilities
        timestamp last_used_at
        timestamp expires_at
    }
    ROLE_PERMISSIONS {
        bigint id PK
        string role
        string permission "tabel siap pakai, belum dipakai"
    }
```

> Tabel infrastruktur Laravel (turunan migrasi bawaan): `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`.

---

## 5. Sequence — Login Web & Pembatasan Sesi

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryColor':'#003778','primaryTextColor':'#ffffff','primaryBorderColor':'#002b5e',
  'actorBkgColor':'#003778','actorTextColor':'#ffffff','actorBorder':'#002b5e',
  'signalColor':'#003778','signalTextColor':'#003778',
  'noteBkgColor':'#C8A45C','noteTextColor':'#ffffff',
  'fontFamily':'Inter, Segoe UI, sans-serif'}}}%%
sequenceDiagram
    autonumber
    actor U as 👤 Pengguna
    participant R as routes/web.php
    participant C as WebAuthController
    participant DB as 🗄️ Database
    participant A as AuditService

    Note over U,R: POST /login — kini melewati throttle:5,1 (perbaikan F-10)
    U->>R: POST /login {email, password}
    R->>C: login(Request)
    C->>C: validate(email, password)
    C->>DB: Auth::attempt(credentials)
    alt Kredensial salah
        DB-->>C: false
        C-->>U: back() + "Email atau password salah."
    else Berhasil
        DB-->>C: User
        C->>C: session.regenerate() + last_activity_at = now
        C->>DB: hitung sesi aktif (sessions)
        alt Super Admin & sudah ada sesi lain
            C-->>U: logout + "batas maksimal 1 session"
        else Melewati batas (admin > 3)
            C->>DB: terminasi sesi lama
        end
        C->>DB: INSERT user_sessions (display_name: '')
        C->>A: log('auth.login', user)
        A->>DB: INSERT audit_logs
        C-->>U: redirect /dashboard
    end
```

---

## 6. Sequence — Pengiriman Formulir Publik (Livewire)

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryColor':'#003778','primaryTextColor':'#ffffff','primaryBorderColor':'#002b5e',
  'actorBkgColor':'#C8A45C','actorTextColor':'#ffffff','actorBorder':'#a8873f',
  'signalColor':'#003778','signalTextColor':'#003778',
  'noteBkgColor':'#003778','noteTextColor':'#ffffff',
  'fontFamily':'Inter, Segoe UI, sans-serif'}}}%%
sequenceDiagram
    autonumber
    actor P as 👥 Pengguna Publik
    participant V as Livewire PublicForm
    participant M as 🗄️ MySQL

    Note over P,V: Tahap 1 — Pemuatan (mount) + gerbang (perbaikan F-04, F-08)
    P->>V: GET /form/{slug}
    V->>M: ambil form published + fields + sections
    alt Form tidak ada / bukan published
        V-->>P: kartu "tidak ditemukan"
    else require_auth aktif & belum login 🔒BARU
        V-->>P: "hanya pengguna yang login"
    else starts_at masih masa depan 🔒BARU
        V-->>P: "form belum dibuka"
    else Penuh / kedaluwarsa
        V-->>P: "tidak menerima jawaban"
    else Lolos
        V-->>P: render multi-step (per section)
    end

    Note over P,V: Tahap 2 — Isian & validasi per langkah
    P->>V: isi jawaban (wire:model) · honeypot company_website
    V->>V: updatedResponses → hitung field computed (formula)
    P->>V: klik "Selanjutnya"
    V->>V: validateStep() — required, email, numeric, file 2MB

    Note over P,V: Tahap 3 — Pengiriman (submitForm) + gerbang 🔒BARU
    P->>V: submitForm()
    V->>V: tolak jika honeypot terisi
    V->>V: cek require_auth · limit_one_response · jendela waktu
    V->>V: hitung ulang field computed
    V->>V: validasi seluruh field (ulang)
    V->>M: DB::transaction { INSERT form_submissions + submission_data }
    V-->>P: halaman konfirmasi (message / redirect)
```

---

## 7. Sequence — Ekspor Data & Gerbang Klasifikasi

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryColor':'#003778','primaryTextColor':'#ffffff','primaryBorderColor':'#002b5e',
  'actorBkgColor':'#C8A45C','actorTextColor':'#ffffff','actorBorder':'#a8873f',
  'signalColor':'#003778','signalTextColor':'#003778',
  'noteBkgColor':'#003778','noteTextColor':'#ffffff',
  'fontFamily':'Inter, Segoe UI, sans-serif'}}}%%
sequenceDiagram
    autonumber
    actor O as 🧑‍💼 Operator
    participant W as PageController (web)
    participant P as FormPolicy
    participant E as DataClassification
    participant A as AuditService
    participant M as 🗄️ MySQL

    O->>W: GET /forms/{id}/export/{csv|xlsx|pdf|uang-saku|presensi}
    W->>M: Form::findOrFail(id)
    W->>P: authorize('view', form)
    alt Bukan pemilik form (non super_admin)
        P-->>W: deny
        W-->>O: 403
    end
    W->>E: data_classification->canExport()
    Note over E: 🔴 sensitive = DILARANG ekspor<br/>🟢 public/internal = boleh
    alt Klasifikasi sensitive
        E-->>W: false
        W-->>O: redirect + "tidak dapat diexport"
    else Diizinkan
        W->>M: baca submission_data (chunk / eager)
        W->>A: log('export.*') + notifikasi super_admin
        A->>M: INSERT audit_logs
        W-->>O: unduhan CSV / XLSX / PDF
        Note over W,O: 🔒BARU: seluruh sel melewati<br/>sanitasi anti CSV-injection
    end
```

---

## 8. State Machine — Siklus Hidup Formulir

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryColor':'#003778','primaryTextColor':'#ffffff','primaryBorderColor':'#002b5e',
  'lineColor':'#003778','fontFamily':'Inter, Segoe UI, sans-serif'}}}%%
stateDiagram-v2
    [*] --> Draft : buat form (CreateForm / API store)

    Draft --> Published : publish<br/><i>minimal 1 field idealnya</i>
    Published --> Draft : (tidak tersedia di UI)
    Published --> Closed : close
    Closed --> Published : re-publish

    state "Published — menerima isian" as Open {
        [*] --> Buka : starts_at tercapai
        Buka --> Penuh : jumlah = max_submissions
        Buka --> Kedaluwarsa : ends_at terlampaui 🔒dicek (F-08)
        Buka --> DitutupSistem : status ≠ published
    }

    Published --> SoftDeleted : delete (soft delete)
    Closed --> SoftDeleted : delete
    Draft --> SoftDeleted : delete
    SoftDeleted --> [*] : restore / force delete

    note right of Open
        Gerbang tambahan (perbaikan REA):
        • require_auth → wajib login
        • limit_one_response → 1x per user
        • starts_at → belum dibuka = blokir
    end note
```

---

## 9. Decision Tree — Otorisasi (aktual)

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryColor':'#003778','primaryTextColor':'#ffffff','primaryBorderColor':'#002b5e',
  'secondaryColor':'#C8A45C','tertiaryColor':'#eef3fa','lineColor':'#003778',
  'fontFamily':'Inter, Segoe UI, sans-serif'}}}%%
flowchart TD
    REQ(["Permintaan aksi pada Form / Submission"]) --> Q1{"Role user?"}
    Q1 -->|"super_admin"| ALLOW["✅ DIBERIKAN (semua aksi)"]
    Q1 -->|"admin"| Q2{"form.user_id = user.id?"}

    Q2 -->|"ya (pemilik)"| Q3{"Jenis aksi?"}
    Q3 -->|"view / edit / delete /<br/>export / duplicate"| ALLOW
    Q2 -->|"bukan pemilik"| Q4{"Lihat (view) saja?"}

    Q4 -->|"view / submissions / export"| DENY_VIEW["❌ DITOLAK<br/><i>FormPolicy::view — catatan:<br/>logika OPD DOKUMENjanjikan izin view<br/>satu OPD, belum terimplementasi (D-09)</i>"]
    Q4 -->|"edit / delete /<br/>manage user"| DENY["❌ DITOLAK"]

    ALLOW --> AUD["tercatat di audit_logs"]
    DENY --> LOG403["respons 403"]
    DENY_VIEW --> LOG403

    style REQ fill:#C8A45C,stroke:#a8873f,color:#fff
    style ALLOW fill:#003778,stroke:#002b5e,color:#fff
    style DENY,DENY_VIEW fill:#8b1a1a,stroke:#5e0f0f,color:#fff
    style Q1,Q2,Q3,Q4 fill:#eef3fa,stroke:#003778,color:#003778
    style AUD,LOG403 fill:#eef3fa,stroke:#003778,color:#003778
```

> Perbaikan S-06: penghapusan submission kini menggunakan `authorize('delete', …)` — sebelumnya memakai `view` sehingga admin pemilik form yang sama lintas-OPD sempat bisa menghapus.

---

## 10. Class Diagram — Model Domain

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryColor':'#003778','primaryTextColor':'#ffffff','primaryBorderColor':'#002b5e',
  'secondaryColor':'#C8A45C','tertiaryColor':'#eef3fa','lineColor':'#003778',
  'fontFamily':'Inter, Segoe UI, sans-serif'}}}%%
classDiagram
    class User {
        <<Eloquent · Authenticatable>>
        +string name
        +string display_name
        +UserRole role
        +bigint opd_id
        +isSuperAdmin() bool
        +isAdmin() bool
        +currentDisplayName() string
        +opd() BelongsTo
        +forms() HasMany
        +sessions() HasMany
    }
    class Form {
        <<Eloquent · SoftDeletes>>
        +uuid, title, slug
        +FormStatus status
        +DataClassification data_classification
        +require_auth, limit_one_response, collect_ip
        +starts_at, ends_at, max_submissions
        +isNotOpenYet() bool 🔒BARU
        +isExpired() bool
        +isFull() bool
        +isAcceptingSubmissions() bool 🔒BARU
        +scopePublished() Builder
        +fields() HasMany ordered
        +sections() HasMany ordered
        +submissions() HasMany
    }
    class FormField {
        +FieldType type "14 jenis"
        +label, options, formula, conditions
        +required, is_admin_only, order
        +form() BelongsTo
        +section() BelongsTo
        +submissionData() HasMany
    }
    class FormSection { +title, order }
    class FormSubmission { +uuid, ip_address, user_agent, submitted_at +data() HasMany }
    class SubmissionData { +value +submission() BelongsTo +formField() BelongsTo }
    class AuditLog {
        <<static AuditService::log>>
        +action, auditable_type, auditable_id
        +old_values, new_values
    }
    class DataClassification {
        <<enum>>
        PUBLIC
        INTERNAL
        SENSITIVE
        +canExport() bool
        +retentionDays() int
        +shouldMaskInLogs() bool
    }
    class FieldType {
        <<enum 14 jenis>>
        Text Textarea Email Number Date Time
        Select Radio Checkbox File Heading
        Paragraph Signature Computed
    }
    class UserRole { <<enum>> SuperAdmin Admin }
    class FormStatus { <<enum>> Draft Published Closed }

    User "1" --> "N" Form : forms
    OPD "1" --> "N" User : opds / users
    OPD "1" --> "N" Form : opds / forms
    Form "1" --> "N" FormSection : sections
    Form "1" --> "N" FormField : fields
    FormSection "1" --> "N" FormField : section_id
    Form "1" --> "N" FormSubmission : submissions
    FormSubmission "1" --> "N" SubmissionData : data
    FormField "1" --> "N" SubmissionData : submission_data
    User "1" --> "N" AuditLog : audit_logs
    Form --> DataClassification : data_classification
    FormField --> FieldType : type
    User --> UserRole : role
    Form --> FormStatus : status
```

---

## 11. Analisis Temuan (sebaran & prioritas)

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'fontFamily':'Inter, Segoe UI, sans-serif','primaryColor':'#003778'}}}%%
pie showData
    title 30 Temuan REA per Kategori
    "Keamanan (S)" : 9
    "Cacat fungsional (B)" : 9
    "Arsitektur / hutang teknis (D)" : 12
```

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryColor':'#003778','primaryTextColor':'#ffffff','primaryBorderColor':'#002b5e',
  'secondaryColor':'#C8A45C','tertiaryColor':'#eef3fa','lineColor':'#003778',
  'fontFamily':'Inter, Segoe UI, sans-serif'}}}%%
flowchart LR
    subgraph KRITIS["🔴 Kritis"]
        S1["S-01/S-02 IDOR submission"]
        B1["B-01 AuditService TypeError"]
    end
    subgraph TINGGI["🟠 Tinggi"]
        S3["S-03 require_auth lolos"]
        S4["S-04 limit_one_response lolos"]
        S5["S-05 starts_at tak dicek"]
        B4["B-04 duplikasi form rusak"]
        D1["D-01 idle-timeout mati"]
        S6["S-06 izin hapus terlalu longgar"]
    end
    subgraph SEDANG["🟡 Sedang"]
        S7["S-07 tanpa rate-limit login web"]
        S8["S-08 opd_id tak tervalidasi"]
        S9["S-09 CSV-injection"]
        B9["B-09 field admin-only bocor (API)"]
        D9["D-09 matriks OPD belum jalan"]
        D2["D-02…D-06 duplikasi & kode mati"]
    end

    KRITIS --> FIX["✅ Diperbaiki<br/>sesi REA 19 Okt 2026"]
    TINGGI --> FIX
    SEDANG --> PLAN["⚠️ Diperbaiki<br/>+ rencana lanjutan"]

    style FIX fill:#003778,stroke:#002b5e,color:#fff
    style PLAN fill:#C8A45C,stroke:#a8873f,color:#fff
    style KRITIS fill:#fdecec,stroke:#8b1a1a,color:#8b1a1a
    style TINGGI fill:#fff4e5,stroke:#b45309,color:#b45309
    style SEDANG fill:#fef9e7,stroke:#a16207,color:#a16207
```

```mermaid
%%{init: {'theme':'base','themeVariables':{
  'primaryColor':'#003778','primaryTextColor':'#ffffff','primaryBorderColor':'#002b5e',
  'fontFamily':'Inter, Segoe UI, sans-serif','fontSize':'12px'}}}%%
gantt
    title Roadmap Tindak Lanjut REA
    dateFormat YYYY-MM-DD
    axisFormat %d %b
    todayMarker off

    section Sesi REA
    Reverse engineering & dokumentasi      :done, a1, 2026-10-19, 1d
    Perbaikan temuan prioritas tinggi      :done, a2, 2026-10-19, 1d

    section Fase lanjutan
    Terapkan matriks akses OPD             :crit, b1, 2026-10-20, 5d
    Integrasikan domain service (buang duplikasi) :b2, after b1, 5d
    Uji regresi ekstensif (coverage > 80%) :b3, after b2, 5d
    Retensi & masking data klasifikasi      :b4, 2026-10-27, 3d
    Hardening produksi (enkripsi sesi, headers, monitoring) :b5, after b4, 3d
```

---

*Diagram dibuat untuk dokumen REA Project KBB — 19 Oktober 2026.*
