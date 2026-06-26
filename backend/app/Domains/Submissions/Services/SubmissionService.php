<?php

namespace App\Domains\Submissions\Services;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\SubmissionData;
use Illuminate\Pagination\Paginator;

class SubmissionService
{
    /**
     * Get paginated submissions for a form
     */
    public function getFormSubmissions(Form $form, int $page = 1, int $perPage = 20): Paginator
    {
        return FormSubmission::where('form_id', $form->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get submission detail with all field data
     */
    public function getSubmissionDetail(FormSubmission $submission): FormSubmission
    {
        return $submission->load('data', 'form.fields');
    }

    /**
     * Get submission as array with field labels
     */
    public function getSubmissionAsArray(FormSubmission $submission): array
    {
        $form = $submission->form;
        $data = [];

        foreach ($form->fields as $field) {
            $submissionData = $submission->data()->where('form_field_id', $field->id)->first();
            $data[$field->label] = $submissionData ? $submissionData->value : null;
        }

        return $data;
    }

    /**
     * Delete submission with all related data
     */
    public function deleteSubmission(FormSubmission $submission): bool
    {
        $submission->data()->delete();
        return $submission->delete();
    }

    /**
     * Get submission statistics for a form
     */
    public function getFormStatistics(Form $form): array
    {
        $submissions = $submission->where('form_id', $form->id);
        
        return [
            'total_submissions' => $submissions->count(),
            'submitted_at' => $submissions->min('submitted_at'),
            'latest_submission' => $submissions->max('submitted_at'),
        ];
    }
}
