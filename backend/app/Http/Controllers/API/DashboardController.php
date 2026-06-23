<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $isSuper = $request->user()->role === 'super_admin';
        $userId = $request->user()->id;

        $formIds = $isSuper ? Form::select('id') : Form::where('user_id', $userId)->select('id');
        $totalForms = $isSuper ? Form::count() : Form::where('user_id', $userId)->count();
        $publishedForms = $isSuper ? Form::where('status', 'published')->count() : Form::where('user_id', $userId)->where('status', 'published')->count();
        $totalSubmissions = FormSubmission::whereIn('form_id', $formIds)->count();
        $submissionsToday = FormSubmission::whereIn('form_id', $formIds)
            ->whereDate('submitted_at', today())
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_forms' => $totalForms,
                'published_forms' => $publishedForms,
                'submissions_today' => $submissionsToday,
                'total_submissions' => $totalSubmissions,
            ],
            'message' => 'Statistik dashboard berhasil diambil.',
        ]);
    }

    public function recentForms(Request $request): JsonResponse
    {
        $query = Form::withCount(['fields', 'submissions']);
        if ($request->user()->role !== 'super_admin') {
            $query->where('user_id', $request->user()->id);
        }
        $forms = $query->latest()->limit(5)->get();

        return response()->json([
            'success' => true,
            'data' => $forms,
            'message' => 'Form terbaru berhasil diambil.',
        ]);
    }
}
