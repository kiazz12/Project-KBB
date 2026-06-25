<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\SubmissionData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubmissionService
{
    public function storePublic(Form $form, Request $request): FormSubmission
    {
        $submission = FormSubmission::create([
            'uuid' => (string) Str::uuid(),
            'form_id' => $form->id,
            'user_id' => $request->user()?->id,
            'ip_address' => $form->collect_ip ? $request->ip() : null,
            'user_agent' => $form->collect_ip ? $request->userAgent() : null,
            'submitted_at' => now(),
        ]);

        $fields = $form->fields()->whereNotIn('type', ['heading', 'paragraph', 'section'])->get();

        foreach ($fields as $field) {
            $value = $request->input("f_{$field->id}");

            if ($field->type === 'checkbox' && is_array($value)) {
                $value = implode(',', $value);
            }

            if ($value !== null) {
                SubmissionData::create([
                    'submission_id' => $submission->id,
                    'form_field_id' => $field->id,
                    'value' => $value,
                ]);
            }
        }

        AuditService::log('submission.created', $submission, "New submission on form '{$form->title}'");

        return $submission;
    }

    public function enforceRequireEmail(Form $form, Request $request): ?string
    {
        $settings = $form->settings;
        if (! ($settings['require_email'] ?? false)) {
            return null;
        }

        $emailField = $form->fields()
            ->where('type', 'email')
            ->first();

        if (! $emailField) {
            return null;
        }

        $emailValue = $request->input("f_{$emailField->id}");
        if (! $emailValue) {
            return 'Alamat email wajib diisi.';
        }

        $exists = FormSubmission::whereHas('data', function ($q) use ($emailField, $emailValue) {
            $q->where('form_field_id', $emailField->id)
                ->where('value', $emailValue);
        })->where('form_id', $form->id)->exists();

        if ($exists) {
            return 'Alamat email ini sudah digunakan untuk mengisi form.';
        }

        return null;
    }

    public function list(Form $form, Request $request): array
    {
        $query = $form->submissions()->with('data.field');

        if ($search = $request->search) {
            $query->whereHas('data', function ($q) use ($search) {
                $q->where('value', 'like', "%{$search}%");
            });
        }

        return $query->latest('submitted_at')
            ->paginate($request->per_page ?? 15)
            ->toArray();
    }

    public function get(Form $form, FormSubmission $submission): FormSubmission
    {
        return $submission->load(['data.field', 'user:id,name,email']);
    }

    public function delete(Form $form, FormSubmission $submission): void
    {
        AuditService::log('submission.deleted', $submission, "Submission deleted from form '{$form->title}'");
        $submission->delete();
    }
}
