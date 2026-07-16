<?php

namespace App\Domains\PublicForms\Services;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\SubmissionData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicFormService
{
    public function getFormBySlug(string $slug): ?Form
    {
        return Form::where('slug', $slug)
            ->where('status', 'published')
            ->with('fields')
            ->first();
    }

    public function submitForm(Form $form, array $data): ?FormSubmission
    {
        return DB::transaction(function () use ($form, $data) {
            $submission = FormSubmission::create([
                'uuid' => (string) Str::uuid(),
                'form_id' => $form->id,
                'submitted_at' => now(),
            ]);

            foreach ($data as $fieldId => $value) {
                SubmissionData::create([
                    'submission_id' => $submission->id,
                    'form_field_id' => $fieldId,
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]);
            }

            return $submission;
        });
    }

    public function validateSubmission(Form $form, array $data): array
    {
        $errors = [];
        $form->fields->each(function ($field) use ($data, &$errors) {
            if ($field->required && (empty($data[$field->id]) || (is_array($data[$field->id]) && empty(array_filter($data[$field->id]))))) {
                $errors[$field->id] = "{$field->label} is required";
            }
        });

        return $errors;
    }
}
