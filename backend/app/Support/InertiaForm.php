<?php

namespace App\Support;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSection;
use App\Models\FormSubmission;

class InertiaForm
{
    /**
     * @return array<string, mixed>
     */
    public static function field(FormField $field): array
    {
        return [
            'id' => $field->id,
            'type' => $field->type->value,
            'label' => $field->label,
            'placeholder' => $field->placeholder,
            'help_text' => $field->help_text,
            'required' => (bool) $field->required,
            'options' => $field->options ?? [],
            'order' => $field->order,
            'section_id' => $field->section_id,
            'formula' => $field->formula,
            'is_admin_only' => (bool) $field->is_admin_only,
            'min_length' => $field->min_length,
            'max_length' => $field->max_length,
            'default_value' => $field->default_value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function section(FormSection $section): array
    {
        return [
            'id' => $section->id,
            'title' => $section->title,
            'description' => $section->description,
            'order' => $section->order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function editor(Form $form): array
    {
        $form->load(['fields.section', 'sections']);

        return [
            'id' => $form->id,
            'title' => $form->title,
            'description' => $form->description,
            'slug' => $form->slug,
            'status' => $form->status->value,
            'status_label' => ucfirst($form->status->value),
            'starts_at' => $form->starts_at?->format('Y-m-d\TH:i'),
            'ends_at' => $form->ends_at?->format('Y-m-d\TH:i'),
            'max_submissions' => $form->max_submissions ?? 0,
            'require_auth' => (bool) $form->require_auth,
            'collect_ip' => (bool) ($form->collect_ip ?? true),
            'show_kbb_logo' => (bool) ($form->show_kbb_logo ?? true),
            'limit_one_response' => (bool) $form->limit_one_response,
            'confirmation_type' => $form->confirmation_type ?? 'message',
            'confirmation_message' => $form->confirmation_message ?? 'Terima kasih, jawaban Anda telah dicatat.',
            'redirect_url' => $form->redirect_url ?? '',
            'public_url' => url('/form/'.$form->slug),
            'fields' => $form->fields->map(fn (FormField $field): array => self::field($field))->values(),
            'sections' => $form->sections->map(fn (FormSection $section): array => self::section($section))->values(),
        ];
    }

    /**
     * @return array<int, array{title: string, fields: array<int, array<string, mixed>>}>
     */
    public static function publicSteps(Form $form): array
    {
        $visible = $form->fields->reject->is_admin_only;
        $sections = $form->sections;
        $noSectionFields = $visible->whereNull('section_id');

        if ($sections->isEmpty()) {
            return [[
                'title' => 'Form',
                'fields' => $visible->map(fn (FormField $field): array => self::field($field))->values()->all(),
            ]];
        }

        $steps = [];
        foreach ($sections as $section) {
            $sectionFields = $visible->where('section_id', $section->id);
            if ($sectionFields->isEmpty()) {
                continue;
            }

            $steps[] = [
                'title' => $section->title,
                'fields' => $sectionFields->map(fn (FormField $field): array => self::field($field))->values()->all(),
            ];
        }

        if ($noSectionFields->isNotEmpty()) {
            $steps[] = [
                'title' => 'Lainnya',
                'fields' => $noSectionFields->map(fn (FormField $field): array => self::field($field))->values()->all(),
            ];
        }

        return $steps;
    }

    /**
     * @return array<string, mixed>
     */
    public static function submissionPreview(FormSubmission $submission): array
    {
        $preview = $submission->data->take(3)->map(fn ($data): array => [
            'label' => $data->formField?->label ?? 'Field',
            'value' => str($data->value ?? '')->stripTags()->limit(80)->toString(),
        ])->values();

        return [
            'id' => $submission->id,
            'uuid_short' => $submission->uuid ? '#'.substr($submission->uuid, 0, 8) : '#'.$submission->id,
            'submitted_at' => $submission->submitted_at?->format('d/m/Y H:i') ?: $submission->created_at?->format('d/m/Y H:i'),
            'preview' => $preview,
            'extra_count' => max(0, $submission->data->count() - 3),
        ];
    }
}
