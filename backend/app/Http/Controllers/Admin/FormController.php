<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Services\AuditService;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function index(Request $request)
    {
        AuditService::log('admin.forms.viewed', null, 'Admin panel: Forms list viewed');

        $query = Form::withCount('submissions')->withTrashed();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $forms = $query->latest()->paginate(20);

        return view('admin.forms.index', compact('forms'));
    }

    public function show(Form $form)
    {
        AuditService::log('admin.form.viewed', $form, "Admin panel: Form '{$form->title}' viewed");
        $form->loadCount('submissions');
        $submissions = $form->submissions()->with('data.formField')->latest()->paginate(20);

        return view('admin.forms.show', compact('form', 'submissions'));
    }
}
