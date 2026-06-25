<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    public function login()
    {
        if (auth()->check()) {
            return redirect('/dashboard');
        }

        return Inertia::render('Login');
    }

    public function dashboard()
    {
        return Inertia::render('Dashboard');
    }

    public function formsIndex()
    {
        return Inertia::render('forms/Index');
    }

    public function formsCreate()
    {
        return Inertia::render('forms/Create');
    }

    public function formsShow(int $id)
    {
        $form = Form::with(['fields' => fn ($q) => $q->orderBy('order')])->findOrFail($id);
        $this->authorize('view', $form);

        return Inertia::render('forms/Show', [
            'form' => $form->loadCount(['fields', 'submissions']),
        ]);
    }

    public function formsEdit(int $id)
    {
        $form = Form::with(['fields' => fn ($q) => $q->orderBy('order')])->findOrFail($id);
        $this->authorize('update', $form);

        return Inertia::render('forms/Edit', [
            'form' => $form,
        ]);
    }

    public function formsAnalytics(int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('view', $form);

        return Inertia::render('forms/Analytics', [
            'id' => $id,
        ]);
    }

    public function submissionsIndex(int $id)
    {
        $form = Form::findOrFail($id);
        $this->authorize('view', $form);

        return Inertia::render('forms/submissions/Index', [
            'formId' => $id,
        ]);
    }

    public function submissionsShow(int $formId, int $id)
    {
        $form = Form::findOrFail($formId);
        $this->authorize('view', $form);

        return Inertia::render('forms/submissions/Show', [
            'formId' => $formId,
            'submissionId' => $id,
        ]);
    }

    public function usersIndex()
    {
        $users = User::latest()->get();

        return Inertia::render('UsersIndex', [
            'users' => $users,
        ]);
    }

    public function usersShow(int $id)
    {
        $user = User::findOrFail($id);

        return Inertia::render('UserDetail', [
            'user' => $user,
        ]);
    }

    public function changePassword()
    {
        return Inertia::render('ChangePassword');
    }

    public function publicForm(string $slug)
    {
        return Inertia::render('PublicForm', [
            'slug' => $slug,
        ]);
    }

    public function logout(Request $request)
    {
        auth()->user()->currentAccessToken()->delete();
        auth()->guard('web')->logout();

        return redirect('/login');
    }
}
