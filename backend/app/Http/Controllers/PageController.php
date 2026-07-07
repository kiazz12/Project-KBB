<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function login()
    {
        if (auth()->check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function dashboard()
    {
        $isSuper = auth()->user()->isSuperAdmin();
        $userId = auth()->id();

        $totalForms = $isSuper ? Form::count() : Form::where('user_id', $userId)->count();
        $publishedForms = $isSuper ? Form::where('status', 'published')->count() : Form::where('user_id', $userId)->where('status', 'published')->count();
        $totalSubmissions = $isSuper
            ? \App\Models\FormSubmission::count()
            : \App\Models\FormSubmission::whereIn('form_id', Form::where('user_id', $userId)->select('id'))->count();
        $submissionsToday = $isSuper
            ? \App\Models\FormSubmission::whereDate('submitted_at', today())->count()
            : \App\Models\FormSubmission::whereIn('form_id', Form::where('user_id', $userId)->select('id'))->whereDate('submitted_at', today())->count();

        $recentForms = Form::withCount(['fields', 'submissions'])
            ->when(!$isSuper, fn($q) => $q->where('user_id', $userId))
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('totalForms', 'publishedForms', 'totalSubmissions', 'submissionsToday', 'recentForms'));
    }

    public function formsIndex(Request $request)
    {
        $isSuper = auth()->user()->isSuperAdmin();
        $query = Form::with('user:id,name')->withCount(['fields', 'submissions']);

        if (!$isSuper) {
            $query->where('user_id', auth()->id());
        }

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $forms = $query->latest()->paginate(15)->withQueryString();

        return view('forms.index', compact('forms'));
    }

    public function formsCreate()
    {
        return view('forms.create');
    }

    public function formsShow(int $id)
    {
        $form = Form::with(['fields' => fn($q) => $q->orderBy('order')])
            ->withCount(['fields', 'submissions'])
            ->findOrFail($id);
        $this->authorize('view', $form);

        return view('forms.show', compact('form'));
    }

    public function formsEdit(int $id)
    {
        $form = Form::with(['fields' => fn($q) => $q->orderBy('order')])->findOrFail($id);
        $this->authorize('update', $form);

        return view('forms.edit', compact('form'));
    }

    public function formsAnalytics(int $id)
    {
        $form = Form::with('fields')->findOrFail($id);
        $this->authorize('view', $form);

        $totalSubmissions = $form->submissions()->count();
        $submissionsByDate = $form->submissions()
            ->selectRaw('DATE(submitted_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $fieldAnalytics = [];
        foreach ($form->fields as $field) {
            $values = $form->submissions()
                ->join('submission_data', 'form_submissions.id', '=', 'submission_data.submission_id')
                ->where('submission_data.form_field_id', $field->id)
                ->selectRaw('submission_data.value, COUNT(*) as count')
                ->groupBy('submission_data.value')
                ->orderByDesc('count')
                ->get();
            $counts = [];
            foreach ($values as $v) {
                $counts[$v->value] = $v->count;
            }
            $fieldAnalytics[] = [
                'field_id' => $field->id,
                'field_label' => $field->label,
                'field_type' => $field->type->value,
                'counts' => $counts,
            ];
        }

        return view('forms.analytics', compact('form', 'totalSubmissions', 'submissionsByDate', 'fieldAnalytics'));
    }

    public function submissionsIndex(int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('view', $form);

        $submissions = $form->submissions()->with('data.formField')->latest('submitted_at')->paginate(20);

        return view('forms.submissions.index', compact('form', 'submissions'));
    }

    public function submissionsShow(int $formId, int $id)
    {
        $form = Form::findOrFail($formId);
        $this->authorize('view', $form);

        $submission = \App\Models\FormSubmission::with('data.formField')->findOrFail($id);

        return view('forms.submissions.show', compact('form', 'submission'));
    }

    public function usersIndex()
    {
        $users = User::latest()->paginate(20);

        return view('users.index', compact('users'));
    }

    public function usersShow(int $id)
    {
        $user = User::withCount('forms')->findOrFail($id);
        $forms = $user->forms()->withCount(['fields', 'submissions'])->latest()->paginate(15);

        return view('users.show', compact('user', 'forms'));
    }

    public function changePassword()
    {
        return view('change-password');
    }

    public function publicForm(string $slug)
    {
        return view('public-form', compact('slug'));
    }
}
