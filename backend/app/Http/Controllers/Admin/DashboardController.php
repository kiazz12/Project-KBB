<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalForms = Form::withTrashed()->count();
        $totalPublished = Form::where('status', 'published')->count();
        $totalSubmissions = FormSubmission::count();
        $recentForms = Form::withCount('submissions')->withTrashed()->latest()->limit(10)->get();
        $usersByRole = User::selectRaw('role, count(*) as total')->groupBy('role')->get();
        $submissionsToday = FormSubmission::whereDate('submitted_at', today())->count();

        return view('admin.dashboard.index', compact(
            'totalUsers', 'totalForms', 'totalPublished', 'totalSubmissions',
            'recentForms', 'usersByRole', 'submissionsToday'
        ));
    }
}
