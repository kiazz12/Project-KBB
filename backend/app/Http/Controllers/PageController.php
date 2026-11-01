<?php

namespace App\Http\Controllers;

use App\Exports\SubmissionsExport;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSection;
use App\Models\FormSubmission;
use App\Models\SubmissionData;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Support\Csv;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class PageController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect(Auth::user()->isSuperAdmin() ? route('admin.dashboard') : route('dashboard'));
        }

        return Inertia::render('Login');
    }

    public function dashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $isSuper = $user->isSuperAdmin();
        $userId = Auth::id();

        $totalForms = $isSuper ? Form::count() : Form::where('user_id', $userId)->count();
        $publishedForms = $isSuper ? Form::where('status', 'published')->count() : Form::where('user_id', $userId)->where('status', 'published')->count();
        $draftForms = $isSuper ? Form::where('status', 'draft')->count() : Form::where('user_id', $userId)->where('status', 'draft')->count();
        $closedForms = $isSuper ? Form::where('status', 'closed')->count() : Form::where('user_id', $userId)->where('status', 'closed')->count();

        $totalSubmissions = $isSuper
            ? FormSubmission::count()
            : FormSubmission::whereIn('form_id', Form::where('user_id', $userId)->select('id'))->count();
        $submissionsToday = $isSuper
            ? FormSubmission::whereDate('submitted_at', today())->count()
            : FormSubmission::whereIn('form_id', Form::where('user_id', $userId)->select('id'))->whereDate('submitted_at', today())->count();
        $submissionsThisWeek = $isSuper
            ? FormSubmission::whereBetween('submitted_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
            : FormSubmission::whereIn('form_id', Form::where('user_id', $userId)->select('id'))->whereBetween('submitted_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $totalUsers = $isSuper ? User::count() : null;

        $recentForms = Form::withCount(['fields', 'submissions'])
            ->when(! $isSuper, fn ($q) => $q->where('user_id', $userId))
            ->latest()
            ->limit(5)
            ->get();

        $topForms = Form::with('user:id,name')
            ->withCount('submissions')
            ->when(! $isSuper, fn ($q) => $q->where('user_id', $userId))
            ->whereHas('submissions')
            ->orderByDesc('submissions_count')
            ->limit(5)
            ->get();

        $latestSubmissions = FormSubmission::with(['form:id,title,user_id', 'form.user:id,name'])
            ->whereIn('form_id', function ($q) use ($isSuper, $userId) {
                if ($isSuper) {
                    $q->select('id')->from('forms');
                } else {
                    $q->select('id')->from('forms')->where('user_id', $userId);
                }
            })
            ->latest('submitted_at')
            ->limit(6)
            ->get();

        $formsWithSubmissions = $isSuper
            ? Form::where('status', 'published')->whereHas('submissions')->count()
            : Form::where('user_id', $userId)->where('status', 'published')->whereHas('submissions')->count();
        $avgSubsPerForm = $totalForms > 0 ? round($totalSubmissions / $totalForms, 1) : 0;
        $adminCount = $isSuper ? User::where('role', 'admin')->count() : null;

        $mapForm = fn (Form $form): array => [
            'id' => $form->id,
            'title' => $form->title,
            'status' => $form->status->value,
            'status_label' => ucfirst($form->status->value),
            'submissions_count' => $form->submissions_count ?? 0,
            'fields_count' => $form->fields_count ?? null,
            'owner' => $form->relationLoaded('user') ? $form->user?->name : null,
            'url' => route('forms.show', $form),
        ];

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_forms' => $totalForms,
                'published_forms' => $publishedForms,
                'draft_forms' => $draftForms,
                'closed_forms' => $closedForms,
                'total_submissions' => $totalSubmissions,
                'submissions_today' => $submissionsToday,
                'submissions_this_week' => $submissionsThisWeek,
                'total_users' => $totalUsers,
                'admin_count' => $adminCount,
                'forms_with_submissions' => $formsWithSubmissions,
                'avg_submissions_per_form' => $avgSubsPerForm,
            ],
            'recentForms' => $recentForms->map($mapForm)->values(),
            'topForms' => $topForms->map($mapForm)->values(),
            'latestSubmissions' => $latestSubmissions->map(fn (FormSubmission $submission): array => [
                'id' => $submission->id,
                'form_id' => $submission->form_id,
                'form_title' => $submission->form?->title ?? 'Form dihapus',
                'owner' => $submission->form?->user?->name,
                'submitted_at' => $submission->submitted_at?->translatedFormat('l, d M Y H:i'),
                'submitted_at_iso' => $submission->submitted_at?->toIso8601String(),
                'url' => route('forms.submissions.index', $submission->form_id),
            ])->values(),
            'activity' => $this->activityData('7d'),
            'today' => now()->translatedFormat('l, d F Y'),
            'isSuperAdmin' => $isSuper,
        ]);
    }

    public function activity(Request $request)
    {
        $range = (string) $request->query('range', '7d');

        if (! in_array($range, ['7d', '1m', '3m', '6m', '1y'], true)) {
            $range = '7d';
        }

        return response()->json($this->activityData($range));
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>, total: int, max: int}
     */
    private function activityData(string $range): array
    {
        /** @var User $user */
        $user = Auth::user();
        $isSuper = $user->isSuperAdmin();
        $userId = Auth::id();

        $now = now();
        $start = match ($range) {
            '7d' => $now->copy()->subDays(6)->startOfDay(),
            '1m' => $now->copy()->subDays(29)->startOfDay(),
            '3m' => $now->copy()->subDays(89)->startOfDay(),
            '6m' => $now->copy()->subDays(179)->startOfDay(),
            '1y' => $now->copy()->subDays(364)->startOfDay(),
            default => $now->copy()->subDays(6)->startOfDay(),
        };

        $query = FormSubmission::query()->where('submitted_at', '>=', $start);
        if (! $isSuper) {
            $query->whereIn('form_id', Form::where('user_id', $userId)->select('id'));
        }

        $raw = [];
        foreach ($query->pluck('submitted_at') as $submittedAt) {
            if (! $submittedAt) {
                continue;
            }

            $date = $submittedAt->format('Y-m-d');
            $raw[$date] = ($raw[$date] ?? 0) + 1;
        }

        $labels = [];
        $data = [];

        if (in_array($range, ['7d', '1m'], true)) {
            $period = $range === '7d' ? 7 : 30;
            for ($i = $period - 1; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i);
                $labels[] = $date->translatedFormat($range === '7d' ? 'D' : 'd M');
                $data[] = $raw[$date->format('Y-m-d')] ?? 0;
            }
        } elseif ($range === '3m') {
            for ($i = 12; $i >= 0; $i--) {
                $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
                $weekEnd = $now->copy()->subWeeks($i)->endOfWeek();
                $labels[] = $weekStart->translatedFormat('d M');

                $count = 0;
                $cursor = $weekStart->copy();
                while ($cursor->lte($weekEnd) && $cursor->lte($now)) {
                    $count += $raw[$cursor->format('Y-m-d')] ?? 0;
                    $cursor->addDay();
                }

                $data[] = $count;
            }
        } else {
            $months = $range === '6m' ? 6 : 12;
            for ($i = $months - 1; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $labels[] = $month->translatedFormat('M Y');

                $count = 0;
                foreach ($raw as $dateString => $value) {
                    $date = Carbon::parse($dateString);
                    if ($date->betweenIncluded($month->copy()->startOfMonth(), $month->copy()->endOfMonth())) {
                        $count += $value;
                    }
                }

                $data[] = $count;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data),
            'max' => max($data ?: [0]),
        ];
    }

    public function formsIndex(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $isSuper = $user->isSuperAdmin();
        $query = Form::with('user:id,name')->withCount(['fields', 'submissions']);

        if (! $isSuper) {
            $query->where('user_id', Auth::id());
        }

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $forms = $query->latest()->paginate(15)->withQueryString();

        AuditService::log('forms.viewed', null, 'Forms list viewed');

        return Inertia::render('Forms/Index', [
            'forms' => $forms,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'isSuperAdmin' => $isSuper,
        ]);
    }

    public function formsCreate()
    {
        return Inertia::render('Forms/Create');
    }

    public function formsStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $slug = Str::slug($request->title);
        $baseSlug = $slug ?: 'formulir';
        $counter = 1;
        while (Form::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'slug' => $slug,
            'status' => 'draft',
        ]);

        AuditService::log('form_created', $form, "Form '{$form->title}' created");
        NotificationService::notifySuperAdmins('form_created', "membuat form baru \"{$form->title}\".", [
            'form_id' => $form->id,
            'form_title' => $form->title,
        ]);

        return redirect()->route('forms.edit', $form)->with('success', 'Form berhasil dibuat.');
    }

    public function formsEdit(int $id)
    {
        $form = Form::with([
            'fields' => fn ($q) => $q->orderBy('order'),
            'sections' => fn ($q) => $q->orderBy('order'),
        ])->findOrFail($id);

        $this->authorize('update', $form);

        return Inertia::render('Forms/Edit', [
            'form' => [
                'id' => $form->id,
                'title' => $form->title,
                'description' => $form->description ?? '',
                'slug' => $form->slug,
                'status' => $form->status->value,
                'status_label' => ucfirst($form->status->value),
                'starts_at' => $form->starts_at?->format('Y-m-d\TH:i'),
                'ends_at' => $form->ends_at?->format('Y-m-d\TH:i'),
                'max_submissions' => $form->max_submissions ?? 0,
                'require_auth' => (bool) $form->require_auth,
                'collect_ip' => (bool) $form->collect_ip,
                'show_kbb_logo' => (bool) $form->show_kbb_logo,
                'limit_one_response' => (bool) $form->limit_one_response,
                'confirmation_type' => $form->confirmation_type ?? 'message',
                'confirmation_message' => $form->confirmation_message ?? 'Terima kasih, jawaban Anda telah dicatat.',
                'redirect_url' => $form->redirect_url ?? '',
                'public_url' => url('/form/'.$form->slug),
                'fields' => $form->fields->map(fn ($f): array => [
                    'id' => $f->id,
                    'section_id' => $f->section_id,
                    'type' => $f->type->value,
                    'label' => $f->label,
                    'placeholder' => $f->placeholder ?? '',
                    'help_text' => $f->help_text ?? '',
                    'required' => (bool) $f->required,
                    'options' => $f->options ?? [],
                    'order' => $f->order,
                    'formula' => $f->formula,
                ])->values(),
                'sections' => $form->sections->map(fn ($s): array => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'description' => $s->description ?? '',
                    'order' => $s->order,
                ])->values(),
            ],
        ]);
    }

    public function updateSettings(Request $request, int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('update', $form);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'max_submissions' => 'nullable|integer|min:0',
            'require_auth' => 'boolean',
            'collect_ip' => 'boolean',
            'show_kbb_logo' => 'boolean',
            'limit_one_response' => 'boolean',
            'confirmation_type' => 'in:message,redirect',
            'confirmation_message' => 'nullable|string|max:1000',
            // REA S-10: cegah open redirect. URL relatif atau host yang
            // diizinkan (APP_URL + KBB_ALLOWED_REDIRECT_HOSTS) saja.
            'redirect_url' => ['nullable', 'string', 'max:500', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || $value === '') {
                    return;
                }

                if (str_starts_with($value, '/') && ! str_starts_with($value, '//')) {
                    return;
                }

                $parts = parse_url($value);
                $scheme = strtolower((string) ($parts['scheme'] ?? ''));
                $host = strtolower((string) ($parts['host'] ?? ''));

                if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
                    $fail('URL pengalihan harus berupa URL http/https yang valid.');

                    return;
                }

                $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
                $allowed = array_filter(array_merge(
                    config('kbb.allowed_redirect_hosts', []),
                    $appHost !== '' ? [$appHost] : []
                ));

                if (! in_array($host, $allowed, true)) {
                    $fail('Domain URL pengalihan tidak diizinkan.');
                }
            }],
        ]);

        $form->update([
            'title' => $request->title,
            'description' => $request->description,
            'starts_at' => $request->starts_at ?: null,
            'ends_at' => $request->ends_at ?: null,
            'max_submissions' => $request->max_submissions ?: null,
            'require_auth' => $request->boolean('require_auth'),
            'collect_ip' => $request->boolean('collect_ip'),
            'show_kbb_logo' => $request->boolean('show_kbb_logo'),
            'limit_one_response' => $request->boolean('limit_one_response'),
            'confirmation_type' => $request->confirmation_type ?? 'message',
            'confirmation_message' => $request->confirmation_message ?? 'Terima kasih, jawaban Anda telah dicatat.',
            'redirect_url' => $request->redirect_url ?: null,
        ]);

        AuditService::log('form_settings_updated', $form, "Form '{$form->title}' settings updated");

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pengaturan berhasil disimpan.']);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function publishForm(int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('update', $form);

        $form->update(['status' => 'published']);

        AuditService::log('form_published', $form, "Form '{$form->title}' published");
        NotificationService::notifySuperAdmins('form_published', "menerbitkan form \"{$form->title}\".", [
            'form_id' => $form->id,
            'form_title' => $form->title,
        ]);

        return back()->with('success', 'Form berhasil diterbitkan.');
    }

    public function closeForm(int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('update', $form);

        $form->update(['status' => 'closed']);

        AuditService::log('form_closed', $form, "Form '{$form->title}' closed");
        NotificationService::notifySuperAdmins('form_closed', "menutup form \"{$form->title}\".", [
            'form_id' => $form->id,
            'form_title' => $form->title,
        ]);

        return back()->with('success', 'Form berhasil ditutup.');
    }

    public function storeField(Request $request, int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('update', $form);

        $request->validate([
            'type' => 'required|string|in:text,textarea,email,number,date,time,select,radio,checkbox,file,heading,paragraph,signature,computed',
            'label' => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:500',
            'required' => 'boolean',
            'options' => 'nullable|array',
            'section_id' => 'nullable|integer',
            'formula' => 'nullable|array',
        ]);

        $maxOrder = $form->fields()->max('order') ?? 0;

        $field = FormField::create([
            'form_id' => $form->id,
            'section_id' => $request->section_id ?: null,
            'type' => $request->type,
            'label' => $request->label,
            'placeholder' => $request->placeholder,
            'help_text' => $request->help_text,
            'required' => $request->boolean('required'),
            'options' => $request->options,
            'order' => $maxOrder + 1,
            'formula' => $request->formula,
        ]);

        AuditService::log('form_field_created', $form, "Field '{$field->label}' added");

        return back()->with('success', 'Field berhasil ditambahkan.');
    }

    public function updateField(Request $request, int $formId, int $fieldId)
    {
        $form = Form::findOrFail($formId);
        $this->authorize('update', $form);

        $field = $form->fields()->findOrFail($fieldId);

        $request->validate([
            'type' => 'required|string|in:text,textarea,email,number,date,time,select,radio,checkbox,file,heading,paragraph,signature,computed',
            'label' => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:500',
            'required' => 'boolean',
            'options' => 'nullable|array',
            'section_id' => 'nullable|integer',
            'formula' => 'nullable|array',
        ]);

        $field->update([
            'section_id' => $request->section_id ?: null,
            'type' => $request->type,
            'label' => $request->label,
            'placeholder' => $request->placeholder,
            'help_text' => $request->help_text,
            'required' => $request->boolean('required'),
            'options' => $request->options,
            'formula' => $request->formula,
        ]);

        AuditService::log('form_field_updated', $form, "Field '{$field->label}' updated");

        return back()->with('success', 'Field berhasil diperbarui.');
    }

    public function deleteField(int $formId, int $fieldId)
    {
        $form = Form::findOrFail($formId);
        $this->authorize('update', $form);

        $field = $form->fields()->findOrFail($fieldId);
        $label = $field->label;
        $field->delete();

        $fields = $form->fields()->orderBy('order')->get();
        foreach ($fields as $i => $f) {
            if ($f->order !== $i + 1) {
                $f->update(['order' => $i + 1]);
            }
        }

        AuditService::log('form_field_deleted', $form, "Field '{$label}' deleted");

        return back()->with('success', 'Field berhasil dihapus.');
    }

    public function reorderFields(Request $request, int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('update', $form);

        $orderIds = $request->input('order', []);
        foreach ($orderIds as $index => $fieldId) {
            $form->fields()->where('id', $fieldId)->update(['order' => $index + 1]);
        }

        return back()->with('success', 'Urutan field diperbarui.');
    }

    public function cloneField(int $formId, int $fieldId)
    {
        $form = Form::findOrFail($formId);
        $this->authorize('update', $form);

        $field = $form->fields()->findOrFail($fieldId);
        $maxOrder = $form->fields()->max('order') ?? 0;

        $cloned = $field->replicate(['created_at', 'updated_at']);
        $cloned->label = $field->label.' (Salinan)';
        $cloned->order = $maxOrder + 1;
        $cloned->save();

        AuditService::log('form_field_cloned', $form, "Field '{$field->label}' duplicated");

        return back()->with('success', 'Field berhasil diduplikasi.');
    }

    public function storeSection(Request $request, int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('update', $form);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $maxOrder = $form->sections()->max('order') ?? 0;

        $section = FormSection::create([
            'form_id' => $form->id,
            'title' => $request->title,
            'description' => $request->description,
            'order' => $maxOrder + 1,
        ]);

        AuditService::log('form_section_created', $form, "Section '{$section->title}' added");

        return back()->with('success', 'Section berhasil ditambahkan.');
    }

    public function updateSection(Request $request, int $formId, int $sectionId)
    {
        $form = Form::findOrFail($formId);
        $this->authorize('update', $form);

        $section = $form->sections()->findOrFail($sectionId);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $section->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        AuditService::log('form_section_updated', $form, "Section '{$section->title}' updated");

        return back()->with('success', 'Section berhasil diperbarui.');
    }

    public function deleteSection(int $formId, int $sectionId)
    {
        $form = Form::findOrFail($formId);
        $this->authorize('update', $form);

        $section = $form->sections()->findOrFail($sectionId);
        $title = $section->title;

        $section->fields()->update(['section_id' => null]);
        $section->delete();

        $sections = $form->sections()->orderBy('order')->get();
        foreach ($sections as $i => $s) {
            $s->update(['order' => $i + 1]);
        }

        AuditService::log('form_section_deleted', $form, "Section '{$title}' deleted");

        return back()->with('success', 'Section berhasil dihapus.');
    }

    public function reorderSections(Request $request, int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('update', $form);

        $orderIds = $request->input('order', []);
        foreach ($orderIds as $index => $sectionId) {
            $form->sections()->where('id', $sectionId)->update(['order' => $index + 1]);
        }

        return back()->with('success', 'Urutan section diperbarui.');
    }

    public function formsAnalytics(int $id)
    {
        $form = Form::with('fields')->findOrFail($id);
        $this->authorize('view', $form);

        AuditService::log('form.analytics.viewed', $form, "Analytics viewed for form '{$form->title}'");

        $totalSubmissions = $form->submissions()->count();
        $submissionsByDate = $form->submissions()
            ->selectRaw('DATE(submitted_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item): array => [
                'date' => Carbon::parse($item->date)->format('d/m'),
                'count' => (int) $item->count,
            ])
            ->values();

        $maxByDate = max($submissionsByDate->pluck('count')->all() ?: [0]);

        $fieldAnalytics = [];
        foreach ($form->fields as $field) {
            $values = $form->submissions()
                ->join('submission_data', 'form_submissions.id', '=', 'submission_data.submission_id')
                ->where('submission_data.form_field_id', $field->id)
                ->selectRaw('submission_data.value, COUNT(*) as count')
                ->groupBy('submission_data.value')
                ->orderByDesc('count')
                ->get();
            $counts = [];
            foreach ($values as $v) {
                $counts[(string) $v->value] = (int) $v->count;
            }
            $fieldAnalytics[] = [
                'field_id' => $field->id,
                'field_label' => $field->label,
                'field_type' => $field->type->value,
                'counts' => $counts,
            ];
        }

        return Inertia::render('Forms/Analytics', [
            'form' => [
                'id' => $form->id,
                'title' => $form->title,
                'status' => $form->status->value,
                'status_label' => ucfirst($form->status->value),
                'fields_count' => $form->fields->count(),
            ],
            'totalSubmissions' => $totalSubmissions,
            'submissionsByDate' => $submissionsByDate,
            'maxByDate' => $maxByDate,
            'fieldAnalytics' => $fieldAnalytics,
        ]);
    }

    public function formsShow(int $id)
    {
        $form = Form::with(['fields' => fn ($q) => $q->orderBy('order')])
            ->withCount(['fields', 'submissions', 'sections'])
            ->findOrFail($id);
        $this->authorize('view', $form);

        AuditService::log('form.viewed', $form, "Form '{$form->title}' viewed");

        return Inertia::render('Forms/Show', [
            'form' => [
                'id' => $form->id,
                'title' => $form->title,
                'description' => $form->description,
                'slug' => $form->slug,
                'status' => $form->status->value,
                'status_label' => ucfirst($form->status->value),
                'max_submissions' => $form->max_submissions,
                'ends_at' => $form->ends_at?->format('d/m/Y'),
                'trashed' => $form->trashed(),
                'public_url' => url('/form/'.$form->slug),
                'exports' => [
                    'csv' => route('forms.export.csv', $form),
                    'xlsx' => route('forms.export.xlsx', $form),
                    'pdf' => route('forms.export.pdf', $form),
                ],
                'fields_count' => $form->fields_count,
                'submissions_count' => $form->submissions_count,
                'sections_count' => $form->sections_count,
                'fields' => $form->fields->map(fn ($field): array => [
                    'id' => $field->id,
                    'type' => $field->type->value,
                    'label' => $field->label,
                    'required' => (bool) $field->required,
                    'order' => $field->order,
                ])->values(),
            ],
        ]);
    }

    public function duplicateForm(int $id)
    {
        $form = Form::with('fields')->findOrFail($id);
        $this->authorize('view', $form);

        $newForm = $form->replicate(['uuid', 'slug', 'created_at', 'updated_at']);
        $newForm->uuid = (string) Str::uuid();
        $newForm->slug = Str::slug($form->title).'-'.Str::random(6);
        $newForm->title = $form->title.' (Copy)';
        $newForm->status = 'draft';
        $newForm->save();

        // Salin section lebih dulu, lalu petakan ulang section_id tiap field
        // ke section baru agar tidak menunjuk form lama (temuan REA B-04).
        $sectionMap = [];
        foreach ($form->sections()->orderBy('order')->get() as $section) {
            $newSection = $section->replicate(['form_id', 'created_at', 'updated_at']);
            $newSection->form_id = $newForm->id;
            $newSection->save();
            $sectionMap[$section->id] = $newSection->id;
        }

        foreach ($form->fields as $field) {
            $newField = $field->replicate(['form_id', 'created_at', 'updated_at']);
            $newField->form_id = $newForm->id;
            if ($field->section_id !== null) {
                $newField->section_id = $sectionMap[$field->section_id] ?? null;
            }
            $newField->save();
        }

        AuditService::log('form_duplicated', $newForm, "Form '{$form->title}' duplicated to '{$newForm->title}'");
        NotificationService::notifySuperAdmins('form_duplicated', "menggandakan form \"{$form->title}\".", ['form_id' => $form->id, 'form_title' => $form->title]);

        return redirect()->route('forms.edit', $newForm)
            ->with('success', 'Form berhasil digandakan sebagai draft.');
    }

    public function submissionsIndex(int $id)
    {
        $form = Form::with(['fields' => fn ($q) => $q->orderBy('order')])->findOrFail($id);
        $this->authorize('view', $form);

        AuditService::log('submissions.viewed', $form, "Submissions list viewed for form '{$form->title}'");

        $query = $form->submissions()->with('data.formField');

        if ($search = request('search')) {
            $submissionIds = SubmissionData::where('value', 'like', "%{$search}%")
                ->whereIn('submission_id', $form->submissions()->pluck('id'))
                ->pluck('submission_id')
                ->unique();
            $query->whereIn('id', $submissionIds);
        }

        if ($from = request('from')) {
            $query->whereDate('submitted_at', '>=', $from);
        }

        if ($to = request('to')) {
            $query->whereDate('submitted_at', '<=', $to);
        }

        $submissions = $query->latest('submitted_at')->paginate(20)->withQueryString();

        $submissions->through(fn (FormSubmission $sub): array => [
            'id' => $sub->id,
            'uuid' => $sub->uuid,
            'submitted_at' => $sub->submitted_at?->translatedFormat('d M Y H:i'),
            'submitted_at_iso' => $sub->submitted_at?->toIso8601String(),
            'ip_address' => $sub->ip_address,
            'data' => $sub->data->keyBy('form_field_id')->map(fn ($d) => $d->value),
            'url' => route('forms.submissions.show', [$form->id, $sub->id]),
        ]);

        return Inertia::render('Forms/Submissions/Index', [
            'form' => [
                'id' => $form->id,
                'title' => $form->title,
                'status' => $form->status->value,
                'status_label' => ucfirst($form->status->value),
                'fields' => $form->fields->map(fn ($f): array => [
                    'id' => $f->id,
                    'label' => $f->label,
                    'type' => $f->type->value,
                ])->values(),
                'exports' => [
                    'csv' => route('forms.export.csv', $form),
                    'xlsx' => route('forms.export.xlsx', $form),
                    'pdf' => route('forms.export.pdf', $form),
                ],
            ],
            'submissions' => $submissions,
            'filters' => [
                'search' => request('search', ''),
                'from' => request('from', ''),
                'to' => request('to', ''),
            ],
        ]);
    }

    public function bulkDeleteSubmissions(Request $request, int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('delete', $form);

        $ids = (array) $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->route('forms.submissions.index', $form)->with('error', 'Pilih minimal satu submission.');
        }

        $submissions = $form->submissions()->whereIn('id', $ids)->get();
        foreach ($submissions as $submission) {
            $submission->data()->delete();
            $submission->delete();
        }

        AuditService::log('submissions_bulk_deleted', $form, "Deleted {$submissions->count()} submissions from form '{$form->title}'", null, [
            'form_id' => $form->id,
            'count' => $submissions->count(),
        ]);

        NotificationService::notifySuperAdmins('submissions_deleted', "menghapus {$submissions->count()} submission dari form \"{$form->title}\".", ['form_id' => $form->id, 'form_title' => $form->title, 'count' => $submissions->count()]);

        return redirect()->route('forms.submissions.index', $form)->with('success', $submissions->count().' submission berhasil dihapus.');
    }

    public function deleteSubmission(Request $request, int $formId, int $id)
    {
        $form = Form::findOrFail($formId);
        $this->authorize('delete', $form);

        // Wajib di-scope ke form agar tidak bisa menghapus submission form lain
        // hanya dengan menebak ID (temuan REA S-01 — IDOR).
        $submission = $form->submissions()->findOrFail($id);
        $submission->data()->delete();
        $submission->delete();

        AuditService::log('submission_deleted', $form, "Submission #{$id} deleted from form '{$form->title}'", null, [
            'form_id' => $form->id,
            'submission_id' => $submission->id,
        ]);

        NotificationService::notifySuperAdmins('submission_deleted', "menghapus submission dari form \"{$form->title}\".", ['form_id' => $form->id, 'form_title' => $form->title]);

        return redirect()->route('forms.submissions.index', $form)->with('success', 'Submission berhasil dihapus.');
    }

    public function submissionsShow(int $formId, int $id)
    {
        $form = Form::with(['fields' => fn ($q) => $q->orderBy('order')])->findOrFail($formId);
        $this->authorize('view', $form);

        $submission = $form->submissions()->with('data.formField')->findOrFail($id);

        AuditService::log('submission.viewed', $submission, "Submission #{$id} viewed on form '{$form->title}'");

        return Inertia::render('Forms/Submissions/Show', [
            'form' => [
                'id' => $form->id,
                'title' => $form->title,
            ],
            'submission' => [
                'id' => $submission->id,
                'uuid' => $submission->uuid,
                'submitted_at' => $submission->submitted_at?->translatedFormat('l, d F Y H:i'),
                'ip_address' => $submission->ip_address,
                'user_agent' => $submission->user_agent,
                'answers' => $form->fields->map(function ($field) use ($submission): array {
                    $item = $submission->data->firstWhere('form_field_id', $field->id);
                    $val = $item?->value;

                    return [
                        'field_id' => $field->id,
                        'label' => $field->label,
                        'type' => $field->type->value,
                        'value' => $val,
                        'is_file' => $field->type->value === 'file',
                        'file_url' => ($field->type->value === 'file' && $val)
                            ? url(Str::startsWith($val, 'uploads/') ? $val : 'uploads/'.$val)
                            : null,
                    ];
                })->values(),
            ],
        ]);
    }

    public function usersIndex()
    {
        $users = User::latest()->paginate(20);

        $users->through(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nip' => $user->nip,
            'opd' => $user->opd,
            'role' => $user->role->value,
            'is_super_admin' => $user->isSuperAdmin(),
            'created_at' => $user->created_at?->format('d/m/Y'),
        ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function usersShow(int $id)
    {
        $user = User::withCount('forms')->findOrFail($id);
        $forms = $user->forms()->withCount(['fields', 'submissions'])->latest()->paginate(15);

        $forms->through(fn (Form $form): array => [
            'id' => $form->id,
            'title' => $form->title,
            'status' => $form->status->value,
            'status_label' => ucfirst($form->status->value),
            'fields_count' => $form->fields_count,
            'submissions_count' => $form->submissions_count,
            'url' => route('forms.show', $form),
        ]);

        return Inertia::render('Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip,
                'opd' => $user->opd,
                'role' => $user->role->value,
                'is_super_admin' => $user->isSuperAdmin(),
                'forms_count' => $user->forms_count,
                'created_at' => $user->created_at?->format('d/m/Y'),
            ],
            'forms' => $forms,
        ]);
    }

    public function changePassword()
    {
        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('ChangePassword', [
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    public function exportCsv(Request $request, int $id): mixed
    {
        $form = Form::findOrFail($id);
        $this->authorize('view', $form);

        if ($form->data_classification && ! $form->data_classification->canExport()) {
            return redirect()->back()->with('error', 'Form dengan klasifikasi ini tidak dapat diexport.');
        }

        $fields = $form->fields()->orderBy('order')->get();
        $headers = array_merge(['Submission UUID', 'Submitted At'], $fields->pluck('label')->toArray());

        $callback = function () use ($form, $fields, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            $form->submissions()->chunk(100, function ($submissions) use ($handle, $fields) {
                foreach ($submissions as $submission) {
                    $row = [$submission->uuid, $submission->submitted_at];
                    $data = $submission->data->keyBy('form_field_id');
                    foreach ($fields as $field) {
                        // REA: antisipasi CSV/formula injection pada nilai peserta.
                        $row[] = Csv::sanitize($data->get($field->id)?->value ?? '');
                    }
                    fputcsv($handle, $row);
                }
            });

            fclose($handle);
        };

        AuditService::log('export.csv', $form, "Form '{$form->title}' exported as CSV");
        NotificationService::notifySuperAdmins('data_exported', "mengunduh data form \"{$form->title}\" format CSV.", ['form_id' => $form->id, 'form_title' => $form->title, 'format' => 'csv']);

        return response()->streamDownload($callback, "{$form->slug}-submissions.csv", [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportXlsx(Request $request, int $id): mixed
    {
        $form = Form::findOrFail($id);
        $this->authorize('view', $form);

        if ($form->data_classification && ! $form->data_classification->canExport()) {
            return redirect()->back()->with('error', 'Form dengan klasifikasi ini tidak dapat diexport.');
        }

        AuditService::log('export.xlsx', $form, "Form '{$form->title}' exported as XLSX");
        NotificationService::notifySuperAdmins('data_exported', "mengunduh data form \"{$form->title}\" format XLSX.", ['form_id' => $form->id, 'form_title' => $form->title, 'format' => 'xlsx']);

        return Excel::download(new SubmissionsExport($form), "{$form->slug}-submissions.xlsx");
    }

    public function exportPdf(Request $request, int $id): mixed
    {
        $form = Form::findOrFail($id);
        $this->authorize('view', $form);

        if ($form->data_classification && ! $form->data_classification->canExport()) {
            return redirect()->back()->with('error', 'Form dengan klasifikasi ini tidak dapat diexport.');
        }

        $fields = $form->fields()->orderBy('order')->get();
        $submissions = $form->submissions()->with('data')->latest()->get();

        $pdf = Pdf::loadView('exports.submissions-pdf', [
            'form' => $form,
            'fields' => $fields,
            'submissions' => $submissions,
        ]);

        AuditService::log('export.pdf', $form, "Form '{$form->title}' exported as PDF");
        NotificationService::notifySuperAdmins('data_exported', "mengunduh data form \"{$form->title}\" format PDF.", ['form_id' => $form->id, 'form_title' => $form->title, 'format' => 'pdf']);

        return $pdf->download("{$form->slug}-submissions.pdf");
    }

    public function publicForm(string $slug)
    {
        $form = Form::with([
            'fields' => fn ($q) => $q->orderBy('order'),
            'sections' => fn ($q) => $q->orderBy('order'),
        ])->where('slug', $slug)->first();

        if (! $form) {
            return Inertia::render('PublicForm', [
                'error' => 'Formulir tidak ditemukan atau tidak tersedia.',
                'form' => null,
            ]);
        }

        if ($form->status->value !== 'published') {
            return Inertia::render('PublicForm', [
                'error' => 'Formulir ini belum diterbitkan atau telah ditutup.',
                'form' => null,
            ]);
        }

        if ($form->isNotOpenYet()) {
            return Inertia::render('PublicForm', [
                'error' => 'Pengisian formulir ini belum dibuka. Silakan kembali sesuai jadwal.',
                'form' => null,
            ]);
        }

        if ($form->isExpired()) {
            return Inertia::render('PublicForm', [
                'error' => 'Formulir ini sudah tidak menerima jawaban.',
                'form' => null,
            ]);
        }

        if ($form->isFull()) {
            return Inertia::render('PublicForm', [
                'error' => 'Formulir ini sudah mencapai batas maksimum jawaban.',
                'form' => null,
            ]);
        }

        if ($form->require_auth && ! Auth::check()) {
            return Inertia::render('PublicForm', [
                'error' => 'Silakan login terlebih dahulu untuk mengisi formulir ini.',
                'form' => null,
                'requiresAuth' => true,
            ]);
        }

        if ($form->limit_one_response && Auth::check()
            && $form->submissions()->where('user_id', Auth::id())->exists()) {
            return Inertia::render('PublicForm', [
                'error' => 'Anda sudah pernah mengisi formulir ini.',
                'form' => null,
            ]);
        }

        return Inertia::render('PublicForm', [
            'error' => null,
            'form' => [
                'id' => $form->id,
                'title' => $form->title,
                'description' => $form->description,
                'slug' => $form->slug,
                'show_kbb_logo' => (bool) $form->show_kbb_logo,
                'confirmation_type' => $form->confirmation_type ?? 'message',
                'confirmation_message' => $form->confirmation_message ?? 'Terima kasih, jawaban Anda telah dicatat.',
                'redirect_url' => $form->redirect_url,
                'sections' => $form->sections->map(fn ($s): array => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'description' => $s->description,
                    'order' => $s->order,
                ])->values(),
                'fields' => $form->fields->reject->is_admin_only->map(fn ($f): array => [
                    'id' => $f->id,
                    'section_id' => $f->section_id,
                    'type' => $f->type->value,
                    'label' => $f->label,
                    'placeholder' => $f->placeholder ?? '',
                    'help_text' => $f->help_text ?? '',
                    'required' => (bool) $f->required,
                    'options' => $f->options ?? [],
                    'order' => $f->order,
                    'formula' => $f->formula,
                ])->values(),
            ],
        ]);
    }

    public function submitPublicForm(Request $request, string $slug)
    {
        if (! empty($request->input('company_website'))) {
            return back()->with('error', 'Pengiriman ditolak.');
        }

        $form = Form::with('fields')->where('slug', $slug)->firstOrFail();

        if (! $form->isAcceptingSubmissions()) {
            $msg = match (true) {
                $form->isNotOpenYet() => 'Pengisian formulir ini belum dibuka.',
                $form->isExpired() => 'Form ini sudah tidak menerima jawaban.',
                default => 'Form ini sudah mencapai batas maksimum jawaban.',
            };

            return back()->with('error', $msg);
        }

        if ($form->require_auth && ! Auth::check()) {
            return back()->with('error', 'Silakan login terlebih dahulu untuk mengisi formulir ini.');
        }

        if ($form->limit_one_response && Auth::check()
            && $form->submissions()->where('user_id', Auth::id())->exists()) {
            return back()->with('error', 'Anda sudah pernah mengisi formulir ini.');
        }

        $responses = $request->input('responses', []);
        if (! is_array($responses)) {
            $responses = [];
        }

        $rules = [];
        $messages = [];
        foreach ($form->fields as $field) {
            if ($field->is_admin_only || $field->type->value === 'computed') {
                continue;
            }

            $fieldKey = "responses.{$field->id}";
            $fieldRules = [];

            if (in_array($field->type->value, ['file', 'signature'], true)) {
                if ($field->required) {
                    $fieldRules[] = 'required';
                }
                if ($field->type->value === 'file') {
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:2048';
                    $fieldRules[] = 'mimes:jpg,jpeg,png,pdf,doc,docx';
                }
            } else {
                if ($field->required) {
                    $fieldRules[] = 'required';
                }
                $fieldRules[] = match ($field->type->value) {
                    'email' => 'email',
                    'number' => 'numeric',
                    default => 'string',
                };
            }

            if (! empty($fieldRules)) {
                $rules[$fieldKey] = implode('|', $fieldRules);
            }
            // REA S-07: tanda tangan berupa teks bebas, batasi panjang agar
            // tidak menyimpan payload sangat besar ke submission_data.
            if ($field->type->value === 'signature') {
                $rules[$fieldKey] = trim(($rules[$fieldKey] ?? '').'|max:255', '|');
                $messages["{$fieldKey}.max"] = "{$field->label} maksimal 255 karakter.";
            }
            if ($field->required) {
                $messages["{$fieldKey}.required"] = "{$field->label} wajib diisi.";
            }
            if ($field->type->value === 'file') {
                $messages["{$fieldKey}.file"] = "{$field->label} harus berupa file.";
                $messages["{$fieldKey}.mimes"] = "{$field->label} harus berupa file JPG, PNG, atau PDF.";
                $messages["{$fieldKey}.max"] = "{$field->label} maksimal 2MB.";
            }
        }

        $request->validate($rules, $messages);

        DB::transaction(function () use ($form, $request, $responses) {
            $submission = FormSubmission::create([
                'uuid' => (string) Str::uuid(),
                'form_id' => $form->id,
                'user_id' => Auth::check() ? Auth::id() : null,
                'ip_address' => $form->collect_ip ? $request->ip() : null,
                'user_agent' => $form->collect_ip ? $request->userAgent() : null,
                'submitted_at' => now(),
            ]);

            foreach ($form->fields as $field) {
                if ($field->is_admin_only) {
                    continue;
                }

                $value = null;
                if ($field->type->value === 'file' && $request->hasFile("responses.{$field->id}")) {
                    $file = $request->file("responses.{$field->id}");
                    $path = $file->store('uploads', 'local');
                    $value = $path;
                } elseif ($field->type->value === 'computed') {
                    $refId = $field->formula['ref_field_id'] ?? null;
                    $op = $field->formula['operation'] ?? 'multiply';
                    $cVal = $field->formula['value'] ?? null;
                    $ref1 = (float) ($responses[(string) $refId] ?? 0);
                    $ref2 = $cVal !== null ? (float) $cVal : (float) ($responses[(string) ($field->formula['ref_field_id_2'] ?? '')] ?? 0);
                    $calc = match ($op) {
                        'multiply' => $ref1 * $ref2,
                        'add' => $ref1 + $ref2,
                        'subtract' => $ref1 - $ref2,
                        'divide' => $ref2 != 0 ? $ref1 / $ref2 : 0,
                        default => $ref1,
                    };
                    $value = number_format($calc, 0, '.', '');
                } else {
                    $val = $responses[$field->id] ?? null;
                    $value = is_array($val) ? json_encode($val) : (string) $val;
                }

                SubmissionData::create([
                    'submission_id' => $submission->id,
                    'form_field_id' => $field->id,
                    'value' => (string) $value,
                ]);
            }

            AuditService::log('submission_created', $submission, "New submission for form '{$form->title}'");
            NotificationService::notifySuperAdmins('new_submission', "Respon baru masuk untuk form \"{$form->title}\".", [
                'form_id' => $form->id,
                'submission_id' => $submission->id,
            ]);
        });

        return back()->with('submitted', true)->with('success', $form->confirmation_message ?? 'Terima kasih, jawaban Anda telah dicatat.');
    }

    public function sessionPing(Request $request)
    {
        $request->session()->put('last_activity_at', now()->timestamp);

        return response()->json(['ok' => true]);
    }

    public function sessionExtend(Request $request)
    {
        $request->session()->put('last_activity_at', now()->timestamp);

        return response()->json(['ok' => true]);
    }

    public function sessionDisplayName(Request $request)
    {
        $request->validate([
            'displayName' => 'required|string|min:2|max:255',
        ]);

        $sessionId = session('user_session_id');
        if ($sessionId) {
            UserSession::where('id', $sessionId)->update([
                'display_name' => $request->displayName,
            ]);
        }

        return response()->json(['ok' => true, 'display_name' => $request->displayName]);
    }
}
