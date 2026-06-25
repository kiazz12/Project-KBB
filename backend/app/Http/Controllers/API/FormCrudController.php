<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormCrudController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Form::withCount(['fields', 'submissions']);
        if ($request->user()->role->value === 'super_admin') {
            $query->with('user:id,name');
        } else {
            $query->where('user_id', $request->user()->id);
        }

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $forms = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $forms,
            'message' => 'Daftar form berhasil diambil.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'max_submissions' => 'nullable|integer|min:1',
            'require_auth' => 'boolean',
            'collect_ip' => 'boolean',
            'show_kbb_logo' => 'boolean',
            'confirmation_message' => 'nullable|string',
            'limit_one_response' => 'boolean',
            'confirmation_type' => 'nullable|string|in:message,redirect',
        ]);

        $defaultSettings = [
            'confirmation_type' => 'message',
            'confirmation_message' => 'Terima kasih, jawaban Anda telah dicatat.',
            'show_progress_bar' => true,
            'shuffle_fields' => false,
        ];

        $settings = array_merge($defaultSettings, $request->settings ?? []);

        $slug = Str::slug($request->title).'-'.Str::random(6);

        $form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'slug' => $slug,
            'settings' => $settings,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'max_submissions' => $request->max_submissions,
            'require_auth' => $request->require_auth ?? false,
            'collect_ip' => $request->collect_ip ?? false,
            'show_kbb_logo' => $request->show_kbb_logo ?? true,
            'confirmation_message' => $request->confirmation_message ?? $defaultSettings['confirmation_message'],
            'limit_one_response' => $request->limit_one_response ?? false,
            'confirmation_type' => $request->confirmation_type ?? $defaultSettings['confirmation_type'],
        ]);

        AuditService::log('form.created', $form, "Form '{$form->title}' created");

        return response()->json([
            'success' => true,
            'data' => $form,
            'message' => 'Form berhasil dibuat.',
        ], 201);
    }

    public function show(Request $request, Form $form): JsonResponse
    {
        $this->authorize('view', $form);

        $form->load('fields');

        return response()->json([
            'success' => true,
            'data' => $form,
            'message' => 'Detail form berhasil diambil.',
        ]);
    }

    public function update(Request $request, Form $form): JsonResponse
    {
        $this->authorize('update', $form);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'max_submissions' => 'nullable|integer|min:1',
            'require_auth' => 'boolean',
            'collect_ip' => 'boolean',
            'show_kbb_logo' => 'boolean',
            'confirmation_message' => 'nullable|string',
            'limit_one_response' => 'boolean',
            'confirmation_type' => 'nullable|string|in:message,redirect',
        ]);

        $old = $form->toArray();
        $form->update($request->only([
            'title', 'description', 'settings', 'starts_at', 'ends_at',
            'max_submissions', 'require_auth', 'collect_ip', 'show_kbb_logo',
            'confirmation_message', 'limit_one_response', 'confirmation_type',
        ]));

        AuditService::log('form.updated', $form, "Form '{$form->title}' updated", $old, $form->toArray());

        return response()->json([
            'success' => true,
            'data' => $form,
            'message' => 'Form berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, Form $form): JsonResponse
    {
        $this->authorize('delete', $form);

        $form->delete();

        AuditService::log('form.deleted', $form, "Form '{$form->title}' deleted");

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Form berhasil dihapus.',
        ]);
    }

    public function duplicate(Request $request, Form $form): JsonResponse
    {
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

        AuditService::log('form.duplicated', $newForm, "Form '{$form->title}' duplicated to '{$newForm->title}'");

        return response()->json([
            'success' => true,
            'data' => $newForm->load('fields'),
            'message' => 'Form berhasil digandakan.',
        ], 201);
    }

    public function publish(Request $request, Form $form): JsonResponse
    {
        $this->authorize('update', $form);

        $form->update(['status' => 'published']);

        AuditService::log('form.published', $form, "Form '{$form->title}' published");

        return response()->json([
            'success' => true,
            'data' => $form,
            'message' => 'Form berhasil dipublikasikan.',
        ]);
    }

    public function close(Request $request, Form $form): JsonResponse
    {
        $this->authorize('update', $form);

        $form->update(['status' => 'closed']);

        AuditService::log('form.closed', $form, "Form '{$form->title}' closed");

        return response()->json([
            'success' => true,
            'data' => $form,
            'message' => 'Form berhasil ditutup.',
        ]);
    }

    public function analytics(Request $request, Form $form): JsonResponse
    {
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
                'field_type' => $field->type,
                'counts' => $counts,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_submissions' => $totalSubmissions,
                'submissions_by_date' => $submissionsByDate,
                'field_analytics' => $fieldAnalytics,
            ],
            'message' => 'Data analitik berhasil diambil.',
        ]);
    }

    public function exportCsv(Request $request, Form $form): StreamedResponse
    {
        $this->authorize('view', $form);

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

        return response()->streamDownload($callback, "{$form->slug}-submissions.csv", [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(Request $request, Form $form): mixed
    {
        $this->authorize('view', $form);

        $fields = $form->fields()->orderBy('order')->get();
        $submissions = $form->submissions()->with('data')->latest()->get();

        $pdf = Pdf::loadView('exports.submissions-pdf', [
            'form' => $form,
            'fields' => $fields,
            'submissions' => $submissions,
        ]);

        return $pdf->download("{$form->slug}-submissions.pdf");
    }
}
