<?php

namespace App\Domains\InternalForms\Services;

use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Support\Str;

class FormManagementService
{
    /**
     * Create new form
     */
    public function createForm(User $user, array $data): Form
    {
        $form = Form::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'slug' => Str::slug($data['title']) . '-' . Str::random(6),
            'status' => 'draft',
        ]);

        return $form;
    }

    /**
     * Update form
     */
    public function updateForm(Form $form, array $data): Form
    {
        $form->update([
            'title' => $data['title'] ?? $form->title,
            'description' => $data['description'] ?? $form->description,
        ]);

        return $form;
    }

    /**
     * Publish form
     */
    public function publishForm(Form $form): Form
    {
        if ($form->fields()->count() === 0) {
            throw new \Exception('Cannot publish form without fields');
        }

        $form->update(['status' => 'published']);
        return $form;
    }

    /**
     * Close form
     */
    public function closeForm(Form $form): Form
    {
        $form->update(['status' => 'closed']);
        return $form;
    }

    /**
     * Duplicate form
     */
    public function duplicateForm(Form $form, User $user): Form
    {
        $newForm = $form->replicate([]);
        $newForm->user_id = $user->id;
        $newForm->slug = Str::slug($form->title) . '-copy-' . Str::random(6);
        $newForm->status = 'draft';
        $newForm->save();

        // Duplicate fields
        $form->fields->each(function ($field) use ($newForm) {
            $newField = $field->replicate([]);
            $newField->form_id = $newForm->id;
            $newField->save();
        });

        return $newForm;
    }

    /**
     * Delete form with all related data
     */
    public function deleteForm(Form $form): bool
    {
        // Delete all submissions first
        $form->submissions()->forceDelete();
        // Delete all fields
        $form->fields()->delete();
        // Delete form
        return $form->delete();
    }

    /**
     * Add field to form
     */
    public function addField(Form $form, array $data): FormField
    {
        $maxOrder = $form->fields()->max('order') ?? 0;
        
        return FormField::create([
            'form_id' => $form->id,
            'label' => $data['label'],
            'type' => $data['type'],
            'required' => $data['required'] ?? false,
            'order' => $maxOrder + 1,
            'config' => $data['config'] ?? null,
        ]);
    }

    /**
     * Update field
     */
    public function updateField(FormField $field, array $data): FormField
    {
        $field->update([
            'label' => $data['label'] ?? $field->label,
            'type' => $data['type'] ?? $field->type,
            'required' => $data['required'] ?? $field->required,
            'config' => $data['config'] ?? $field->config,
        ]);

        return $field;
    }

    /**
     * Delete field
     */
    public function deleteField(FormField $field): bool
    {
        // Delete submission data related to this field
        \App\Models\SubmissionData::where('form_field_id', $field->id)->delete();
        return $field->delete();
    }

    /**
     * Reorder fields
     */
    public function reorderFields(Form $form, array $fieldIds): void
    {
        foreach ($fieldIds as $index => $fieldId) {
            FormField::where('id', $fieldId)
                ->where('form_id', $form->id)
                ->update(['order' => $index + 1]);
        }
    }
}
