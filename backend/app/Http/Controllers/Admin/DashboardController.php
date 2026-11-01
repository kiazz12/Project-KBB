<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalForms = Form::withTrashed()->count();
        $totalPublished = Form::where('status', 'published')->count();
        $totalSubmissions = FormSubmission::count();
        $recentForms = Form::withCount('submissions')->with('user:id,name')->withTrashed()->latest()->limit(10)->get();
        $usersByRole = User::selectRaw('role, count(*) as total')->groupBy('role')->get();
        $submissionsToday = FormSubmission::whereDate('submitted_at', today())->count();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users' => $totalUsers,
                'total_forms' => $totalForms,
                'total_published' => $totalPublished,
                'total_submissions' => $totalSubmissions,
                'submissions_today' => $submissionsToday,
            ],
            'recentForms' => $recentForms->map(fn (Form $form): array => [
                'id' => $form->id,
                'title' => $form->title,
                'owner' => $form->user?->name ?? 'Unknown',
                'status' => $form->status->value,
                'status_label' => ucfirst($form->status->value),
                'submissions_count' => $form->submissions_count ?? 0,
                'url' => route('forms.show', $form),
            ])->values(),
            'usersByRole' => $usersByRole->map(function ($item): array {
                $role = $item->role instanceof UserRole ? $item->role->value : (string) $item->role;

                return [
                    'role' => $role,
                    'label' => ucfirst(str_replace('_', ' ', $role)),
                    'total' => (int) $item->total,
                ];
            })->values(),
            'activity' => $this->activityData('7d'),
            'today' => now()->translatedFormat('d F Y'),
        ]);
    }

    public function activity(Request $request)
    {
        $range = (string) $request->query('range', '7d');

        if (! in_array($range, ['7d', '1m', '3m', '6m', '1y'], true)) {
            $range = '7d';
        }

        return response()->json($this->activityData($range));
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>, total: int, max: int}
     */
    private function activityData(string $range): array
    {
        $now = now();
        $start = match ($range) {
            '7d' => $now->copy()->subDays(6)->startOfDay(),
            '1m' => $now->copy()->subDays(29)->startOfDay(),
            '3m' => $now->copy()->subDays(89)->startOfDay(),
            '6m' => $now->copy()->subDays(179)->startOfDay(),
            '1y' => $now->copy()->subDays(364)->startOfDay(),
            default => $now->copy()->subDays(6)->startOfDay(),
        };

        $raw = [];
        foreach (FormSubmission::query()->where('submitted_at', '>=', $start)->pluck('submitted_at') as $submittedAt) {
            if (! $submittedAt) {
                continue;
            }

            $date = $submittedAt->format('Y-m-d');
            $raw[$date] = ($raw[$date] ?? 0) + 1;
        }

        $labels = [];
        $data = [];

        if (in_array($range, ['7d', '1m'], true)) {
            $period = $range === '7d' ? 7 : 30;
            for ($i = $period - 1; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i);
                $labels[] = $date->translatedFormat($range === '7d' ? 'D' : 'd M');
                $data[] = $raw[$date->format('Y-m-d')] ?? 0;
            }
        } elseif ($range === '3m') {
            for ($i = 12; $i >= 0; $i--) {
                $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
                $weekEnd = $now->copy()->subWeeks($i)->endOfWeek();
                $labels[] = $weekStart->translatedFormat('d M');

                $count = 0;
                $cursor = $weekStart->copy();
                while ($cursor->lte($weekEnd) && $cursor->lte($now)) {
                    $count += $raw[$cursor->format('Y-m-d')] ?? 0;
                    $cursor->addDay();
                }

                $data[] = $count;
            }
        } else {
            $months = $range === '6m' ? 6 : 12;
            for ($i = $months - 1; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $labels[] = $month->translatedFormat('M Y');

                $count = 0;
                foreach ($raw as $dateString => $value) {
                    $date = Carbon::parse($dateString);
                    if ($date->betweenIncluded($month->copy()->startOfMonth(), $month->copy()->endOfMonth())) {
                        $count += $value;
                    }
                }

                $data[] = $count;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data),
            'max' => max($data ?: [0]),
        ];
    }
}
