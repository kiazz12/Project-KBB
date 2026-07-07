<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    public function store(Request $request, Form $form): JsonResponse
    {
        $this->authorize('update', $form);

        $request->validate([
            'type' => 'required|string|in:text,textarea,email,number,date,time,select,radio,checkbox,file,heading,paragraph,signature',
            'label' => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
            'required' => 'boolean',
            'options' => 'nullable|array',
            'order' => 'nullable|integer|min:0',
            'min_length' => 'nullable|integer|min:0',
            'max_length' => 'nullable|integer|min:0',
            'default_value' => 'nullable|string|max:255',
        ]);

        $order = $request->order ?? $form->fields()->max('order') + 1;

        $field = $form->fields()->create([
            'type' => $request->type,
            'label' => $request->label,
            'placeholder' => $request->placeholder,
            'help_text' => $request->help_text,
            'required' => $request->required ?? false,
            'options' => $request->options,
            'order' => $order,
            'min_length' => $request->min_length,
            'max_length' => $request->max_length,
            'default_value' => $request->default_value,
        ]);

        AuditService::log('field.created', $field, "Field '{$field->label}' added to form '{$form->title}'");

        return response()->json([
            'success' => true,
            'data' => $field,
            'message' => 'Field berhasil ditambahkan.',
        ], 201);
    }

    public function update(Request $request, Form $form, FormField $field): JsonResponse
    {
        $this->authorize('update', $form);

        $request->validate([
            'type' => 'sometimes|string|in:text,textarea,email,number,date,time,select,radio,checkbox,file,heading,paragraph,signature',
            'label' => 'sometimes|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
            'required' => 'boolean',
            'options' => 'nullable|array',
            'order' => 'nullable|integer|min:0',
            'min_length' => 'nullable|integer|min:0',
            'max_length' => 'nullable|integer|min:0',
            'default_value' => 'nullable|string|max:255',
        ]);

        $old = $field->toArray();
        $field->update($request->only([
            'type', 'label', 'placeholder', 'help_text', 'required',
            'options', 'order', 'min_length', 'max_length', 'default_value',
        ]));

        AuditService::log('field.updated', $field, "Field '{$field->label}' updated", $old, $field->toArray());

        return response()->json([
            'success' => true,
            'data' => $field,
            'message' => 'Field berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, Form $form, FormField $field): JsonResponse
    {
        $this->authorize('update', $form);

        $field->delete();

        AuditService::log('field.deleted', $field, "Field '{$field->label}' deleted from form '{$form->title}'");

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Field berhasil dihapus.',
        ]);
    }

    public function reorder(Request $request, Form $form): JsonResponse
    {
        $this->authorize('update', $form);

        $request->validate([
            'field_ids' => 'required|array',
            'field_ids.*' => "integer|exists:form_fields,id,form_id,{$form->id}",
        ]);

        foreach ($request->field_ids as $index => $fieldId) {
            FormField::where('id', $fieldId)->where('form_id', $form->id)->update(['order' => $index]);
        }

        AuditService::log('fields.reordered', $form, "Fields reordered on form '{$form->title}'");

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Urutan field berhasil diperbarui.',
        ]);
    }
}
