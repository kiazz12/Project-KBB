<?php

namespace App\Services;

use App\Models\Form;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormService
{
    public function index(Request $request, Authenticatable $user): array
    {
        $query = Form::withCount(['fields', 'submissions']);

        if ($user->role->value !== 'super_admin') {
            $query->where('user_id', $user->id);
        } else {
            $query->with('user:id,name');
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

        return $query->latest()->paginate($request->per_page ?? 15)->toArray();
    }

    public function create(array $data, Authenticatable $user): Form
    {
        $defaultSettings = [
            'confirmation_type' => 'message',
            'confirmation_message' => 'Terima kasih, jawaban Anda telah dicatat.',
            'show_progress_bar' => true,
            'shuffle_fields' => false,
        ];

        $settings = array_merge($defaultSettings, $data['settings'] ?? []);

        $slug = Str::slug($data['title']).'-'.Str::random(6);

        $form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'slug' => $slug,
            'settings' => $settings,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'max_submissions' => $data['max_submissions'] ?? null,
            'require_auth' => $data['require_auth'] ?? false,
            'collect_ip' => $data['collect_ip'] ?? false,
            'show_kbb_logo' => $data['show_kbb_logo'] ?? true,
            'confirmation_message' => $data['confirmation_message'] ?? $defaultSettings['confirmation_message'],
            'confirmation_type' => $data['confirmation_type'] ?? $defaultSettings['confirmation_type'],
            'redirect_url' => $data['redirect_url'] ?? null,
            'header_image' => $data['header_image'] ?? null,
            'theme_color' => $data['theme_color'] ?? null,
            'limit_one_response' => $data['limit_one_response'] ?? false,
        ]);

        AuditService::log('form.created', $form, "Form '{$form->title}' created");

        return $form;
    }

    public function update(Form $form, array $data): Form
    {
        $old = $form->toArray();
        $form->update($data);
        AuditService::log('form.updated', $form, "Form '{$form->title}' updated", $old, $form->toArray());

        return $form;
    }

    public function delete(Form $form): void
    {
        $title = $form->title;
        $form->delete();
        AuditService::log('form.deleted', $form, "Form '{$title}' deleted");
    }

    public function duplicate(Form $form): Form
    {
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

        return $newForm->load('fields');
    }

    public function publish(Form $form): Form
    {
        $form->update(['status' => 'published']);
        AuditService::log('form.published', $form, "Form '{$form->title}' published");

        return $form;
    }

    public function close(Form $form): Form
    {
        $form->update(['status' => 'closed']);
        AuditService::log('form.closed', $form, "Form '{$form->title}' closed");

        return $form;
    }

    public function analytics(Form $form): array
    {
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

        return [
            'total_submissions' => $totalSubmissions,
            'submissions_by_date' => $submissionsByDate,
            'field_analytics' => $fieldAnalytics,
        ];
    }

    public function exportCsv(Form $form): StreamedResponse
    {
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

    public function exportPdf(Form $form): mixed
    {
        $fields = $form->fields()->orderBy('order')->get();
        $submissions = $form->submissions()->with('data')->latest()->get();

        return Pdf::loadView('exports.submissions-pdf', [
            'form' => $form,
            'fields' => $fields,
            'submissions' => $submissions,
        ])->download("{$form->slug}-submissions.pdf");
    }
}
