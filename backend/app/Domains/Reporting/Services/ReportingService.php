<?php

namespace App\Domains\Reporting\Services;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Support\Collection;

class ReportingService
{
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(User $user): array
    {
        if ($user->role === 'super_admin') {
            $formsCount = Form::count();
            $submissionsCount = FormSubmission::count();
            $usersCount = User::count();
        } else {
            $formsCount = Form::where('user_id', $user->id)->count();
            $submissionsCount = FormSubmission::whereHas('form', fn($q) => $q->where('user_id', $user->id))->count();
            $usersCount = 1;
        }

        return [
            'total_forms' => $formsCount,
            'total_submissions' => $submissionsCount,
            'total_users' => $usersCount,
            'published_forms' => Form::where('status', 'published')->count(),
        ];
    }

    /**
     * Get recent forms
     */
    public function getRecentForms(User $user, int $limit = 5): Collection
    {
        $query = Form::query();

        if ($user->role !== 'super_admin') {
            $query->where('user_id', $user->id);
        }

        return $query->latest('updated_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get form analytics
     */
    public function getFormAnalytics(Form $form): array
    {
        $submissions = $form->submissions;
        
        return [
            'form_id' => $form->id,
            'form_title' => $form->title,
            'status' => $form->status,
            'total_submissions' => $submissions->count(),
            'published_at' => $form->published_at,
            'closed_at' => $form->closed_at,
            'submission_rate' => $this->calculateSubmissionRate($form),
        ];
    }

    /**
     * Calculate submission rate
     */
    protected function calculateSubmissionRate(Form $form): string
    {
        $publishedAt = $form->published_at;
        if (!$publishedAt) {
            return '0%';
        }

        $days = now()->diffInDays($publishedAt);
        if ($days === 0) {
            return '0%';
        }

        $rate = ($form->submissions->count() / $days) * 100;
        return round($rate, 2) . '%';
    }

    /**
     * Export form data to CSV
     */
    public function exportToCsv(Form $form): string
    {
        $submissions = $form->submissions()->with('data')->get();
        $fields = $form->fields;

        $csv = [];
        
        // Header
        $header = ['Submission ID', 'Submitted At'];
        foreach ($fields as $field) {
            $header[] = $field->label;
        }
        $csv[] = implode(',', $header);

        // Data rows
        foreach ($submissions as $submission) {
            $row = [
                $submission->id,
                $submission->submitted_at->format('Y-m-d H:i:s'),
            ];

            foreach ($fields as $field) {
                $data = $submission->data()->where('form_field_id', $field->id)->first();
                $row[] = $data ? '"' . str_replace('"', '""', $data->value) . '"' : '';
            }

            $csv[] = implode(',', $row);
        }

        return implode("\n", $csv);
    }
}
