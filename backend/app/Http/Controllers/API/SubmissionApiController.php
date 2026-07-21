<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\SubmissionData;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubmissionApiController extends Controller
{
    public function index(Request $request, Form $form): JsonResponse
    {
        $this->authorize('view', $form);

        $query = $form->submissions()->with('data');

        if ($from = $request->from) {
            $query->where('submitted_at', '>=', $from);
        }

        if ($to = $request->to) {
            $query->where('submitted_at', '<=', $to);
        }

        $submissions = $query->latest('submitted_at')->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $submissions,
            'message' => 'Daftar submission berhasil diambil.',
        ]);
    }

    public function show(Request $request, Form $form, FormSubmission $submission): JsonResponse
    {
        $this->authorize('view', $form);

        $submission->load('data.formField');

        return response()->json([
            'success' => true,
            'data' => $submission,
            'message' => 'Detail submission berhasil diambil.',
        ]);
    }

    public function destroy(Request $request, Form $form, FormSubmission $submission): JsonResponse
    {
        $this->authorize('update', $form);

        $submission->delete();

        AuditService::log('submission.deleted', $submission, "Submission deleted from form '{$form->title}'");
        NotificationService::notifySuperAdmins('submission_deleted', "menghapus submission dari form \"{$form->title}\".", ['form_id' => $form->id, 'form_title' => $form->title]);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Submission berhasil dihapus.',
        ]);
    }

    public function showPublic(string $slug): JsonResponse
    {
        $form = Form::published()
            ->where('slug', $slug)
            ->with('fields')
            ->firstOrFail();

        if ($form->isExpired() || $form->isFull()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'This form is no longer accepting submissions.',
            ], 410);
        }

        return response()->json([
            'success' => true,
            'data' => $form,
            'message' => 'Detail form berhasil diambil.',
        ]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $form = Form::published()
            ->where('slug', $slug)
            ->with('fields')
            ->firstOrFail();

        if ($form->isExpired() || $form->isFull()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'This form is no longer accepting submissions.',
            ], 410);
        }

        if ($form->require_auth && ! $request->user()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Authentication required to submit this form.',
            ], 401);
        }

        if ($form->limit_one_response && $request->user()) {
            $alreadySubmitted = $form->submissions()
                ->where('user_id', $request->user()->id)
                ->exists();
            if ($alreadySubmitted) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'You have already submitted this form.',
                ], 409);
            }
        }

        $input = $request->all();
        if (isset($input['fields']) && is_array($input['fields'])) {
            $converted = [];
            foreach ($input['fields'] as $fieldId => $value) {
                $converted["field_{$fieldId}"] = $value;
            }
            $request->merge($converted);
        }

        $rules = [];
        foreach ($form->fields as $field) {
            $fieldRules = [];

            if ($field->required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $fieldRules[] = match ($field->type->value) {
                'email' => 'email',
                'number' => 'numeric',
                'date' => 'date',
                'time' => 'date_format:H:i',
                default => 'string',
            };

            if ($field->max_length) {
                $fieldRules[] = "max:{$field->max_length}";
            }
            if ($field->min_length) {
                $fieldRules[] = "min:{$field->min_length}";
            }

            $rules["field_{$field->id}"] = $fieldRules;
        }

        $validated = $request->validate($rules);

        if ($form->settings['require_email'] ?? false) {
            $hasEmail = false;
            foreach ($form->fields as $field) {
                if ($field->type->value === 'email' && ! empty($validated["field_{$field->id}"])) {
                    $hasEmail = true;
                    break;
                }
            }
            if (! $hasEmail) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Email wajib diisi.',
                ], 422);
            }
        }

        $submission = FormSubmission::create([
            'uuid' => (string) Str::uuid(),
            'form_id' => $form->id,
            'user_id' => $request->user()?->id,
            'ip_address' => $form->collect_ip ? $request->ip() : null,
            'user_agent' => $form->collect_ip ? $request->userAgent() : null,
            'submitted_at' => now(),
        ]);

        foreach ($form->fields as $field) {
            SubmissionData::create([
                'submission_id' => $submission->id,
                'form_field_id' => $field->id,
                'value' => $validated["field_{$field->id}"] ?? null,
            ]);
        }

        AuditService::log('submission.created', $submission, "New submission on form '{$form->title}'");

        return response()->json([
            'success' => true,
            'data' => [
                'uuid' => $submission->uuid,
            ],
            'message' => 'Submission received successfully.',
        ], 201);
    }
}
