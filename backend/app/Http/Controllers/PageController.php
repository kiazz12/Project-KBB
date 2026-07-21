<?php

namespace App\Http\Controllers;

use App\Exports\SubmissionsExport;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\SubmissionData;
use App\Models\User;
use App\Services\AuditService;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function dashboard()
    {
        /** @var User $user */
        $user = Auth::user();
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

        return view('dashboard.index', compact(
            'totalForms', 'publishedForms', 'draftForms', 'closedForms',
            'totalSubmissions', 'submissionsToday', 'submissionsThisWeek',
            'totalUsers', 'recentForms', 'topForms', 'latestSubmissions',
            'formsWithSubmissions', 'avgSubsPerForm'
        ));
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

        return view('forms.index', compact('forms'));
    }

    public function formsCreate()
    {
        return view('forms.create');
    }

    public function formsShow(int $id)
    {
        $form = Form::with(['fields' => fn ($q) => $q->orderBy('order')])
            ->withCount(['fields', 'submissions', 'sections'])
            ->findOrFail($id);
        $this->authorize('view', $form);

        return view('forms.show', compact('form'));
    }

    public function formsEdit(int $id)
    {
        $form = Form::with(['fields' => fn ($q) => $q->orderBy('order')])->findOrFail($id);
        $this->authorize('update', $form);

        return view('forms.edit', compact('form'));
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

        foreach ($form->fields as $field) {
            $newField = $field->replicate(['form_id', 'created_at', 'updated_at']);
            $newField->form_id = $newForm->id;
            $newField->save();
        }

        AuditService::log('form_duplicated', $newForm, "Form '{$form->title}' duplicated to '{$newForm->title}'");
        NotificationService::notifySuperAdmins('form_duplicated', "menggandakan form \"{$form->title}\".", ['form_id' => $form->id, 'form_title' => $form->title]);

        return redirect()->route('forms.edit', $newForm)
            ->with('success', 'Form berhasil digandakan sebagai draft.');
    }

    public function formsAnalytics(int $id)
    {
        $form = Form::with('fields')->findOrFail($id);
        $this->authorize('view', $form);

        $totalSubmissions = $form->submissions()->count();
        $submissionsByDate = $form->submissions()
            ->selectRaw('DATE(submitted_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

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
                $counts[$v->value] = $v->count;
            }
            $fieldAnalytics[] = [
                'field_id' => $field->id,
                'field_label' => $field->label,
                'field_type' => $field->type->value,
                'counts' => $counts,
            ];
        }

        return view('forms.analytics', compact('form', 'totalSubmissions', 'submissionsByDate', 'fieldAnalytics'));
    }

    public function submissionsIndex(int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('view', $form);

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

        $submissions = $query->latest('submitted_at')->paginate(20);

        return view('forms.submissions.index', compact('form', 'submissions'));
    }

    public function bulkDeleteSubmissions(Request $request, int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('view', $form);

        $ids = (array) $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->route('forms.submissions.index', $form)->with('error', 'Pilih minimal satu submission.');
        }

        $submissions = $form->submissions()->whereIn('id', $ids)->get();
        foreach ($submissions as $submission) {
            $submission->data()->delete();
            $submission->delete();
        }

        AuditService::log('submissions_bulk_deleted', [
            'form_id' => $form->id,
            'count' => $submissions->count(),
        ]);

        NotificationService::notifySuperAdmins('submissions_deleted', "menghapus {$submissions->count()} submission dari form \"{$form->title}\".", ['form_id' => $form->id, 'form_title' => $form->title, 'count' => $submissions->count()]);

        return redirect()->route('forms.submissions.index', $form)->with('success', $submissions->count().' submission berhasil dihapus.');
    }

    public function deleteSubmission(Request $request, int $formId, int $id)
    {
        $form = Form::findOrFail($formId);
        $this->authorize('view', $form);

        $submission = FormSubmission::findOrFail($id);
        $submission->data()->delete();
        $submission->delete();

        AuditService::log('submission_deleted', [
            'form_id' => $form->id,
            'submission_id' => $submission->id,
        ]);

        NotificationService::notifySuperAdmins('submission_deleted', "menghapus submission dari form \"{$form->title}\".", ['form_id' => $form->id, 'form_title' => $form->title]);

        return redirect()->route('forms.submissions.index', $form)->with('success', 'Submission berhasil dihapus.');
    }

    public function submissionsShow(int $formId, int $id)
    {
        $form = Form::findOrFail($formId);
        $this->authorize('view', $form);

        $submission = FormSubmission::with('data.formField')->findOrFail($id);

        return view('forms.submissions.show', compact('form', 'submission'));
    }

    public function usersIndex()
    {
        $users = User::latest()->paginate(20);

        return view('users.index', compact('users'));
    }

    public function usersShow(int $id)
    {
        $user = User::withCount('forms')->findOrFail($id);
        $forms = $user->forms()->withCount(['fields', 'submissions'])->latest()->paginate(15);

        return view('users.show', compact('user', 'forms'));
    }

    public function changePassword()
    {
        return view('change-password');
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
                        $row[] = $data->get($field->id)?->value ?? '';
                    }
                    fputcsv($handle, $row);
                }
            });

            fclose($handle);
        };

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

        NotificationService::notifySuperAdmins('data_exported', "mengunduh data form \"{$form->title}\" format XLSX.", ['form_id' => $form->id, 'form_title' => $form->title, 'format' => 'xlsx']);

        return (new SubmissionsExport($form))->download("{$form->slug}-submissions.xlsx");
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

        NotificationService::notifySuperAdmins('data_exported', "mengunduh data form \"{$form->title}\" format PDF.", ['form_id' => $form->id, 'form_title' => $form->title, 'format' => 'pdf']);

        return $pdf->download("{$form->slug}-submissions.pdf");
    }

    public function exportUangSakuPdf(Request $request, int $id): mixed
    {
        $form = Form::findOrFail($id);
        $this->authorize('view', $form);

        $fields = $form->fields()->orderBy('order')->get();
        $submissions = $form->submissions()->with('data.formField')->latest()->get();

        $pdf = Pdf::loadView('exports.uang-saku-pdf', [
            'form' => $form,
            'fields' => $fields,
            'submissions' => $submissions,
        ]);

        NotificationService::notifySuperAdmins('data_exported', "mengunduh data uang saku form \"{$form->title}\".", ['form_id' => $form->id, 'form_title' => $form->title, 'format' => 'uang_saku_pdf']);

        return $pdf->download('tanda-terima-uang-saku-peserta.pdf');
    }

    public function exportPresensiPdf(Request $request, int $id): mixed
    {
        ini_set('memory_limit', '2G');

        $form = Form::findOrFail($id);
        $this->authorize('view', $form);

        $fields = $form->fields()->orderBy('order')->get();

        $submissionIds = $form->submissions()->orderBy('id')->pluck('id');

        $submissionDataMap = [];
        SubmissionData::whereIn('submission_id', $submissionIds)
            ->with('formField')
            ->orderBy('submission_id')
            ->chunk(2000, function ($chunk) use (&$submissionDataMap) {
                foreach ($chunk as $data) {
                    if ($data->formField) {
                        $submissionDataMap[$data->submission_id][] = $data;
                    }
                }
            });

        $submissions = [];
        foreach ($submissionIds as $sid) {
            if (isset($submissionDataMap[$sid])) {
                $submissions[] = (object) ['id' => $sid, 'data' => collect($submissionDataMap[$sid])];
            }
        }

        $pdf = Pdf::loadView('exports.presensi-pdf', [
            'form' => $form,
            'fields' => $fields,
            'submissions' => $submissions,
            'showFooter' => true,
            'offset' => 0,
        ])->setPaper('a4', 'landscape');

        NotificationService::notifySuperAdmins('data_exported', "mengunduh data presensi form \"{$form->title}\".", ['form_id' => $form->id, 'form_title' => $form->title, 'format' => 'presensi_pdf']);

        return $pdf->download('daftar-hadir-presensi.pdf');
    }

    public function publicForm(string $slug)
    {
        return view('public-form', compact('slug'));
    }
}
