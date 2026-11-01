<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FormController extends Controller
{
    public function index(Request $request)
    {
        AuditService::log('admin.forms.viewed', null, 'Admin panel: Forms list viewed');

        $query = Form::withCount('submissions')->with('user:id,name')->withTrashed();

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

        $forms = $query->latest()->paginate(20)->withQueryString();

        $forms->through(fn (Form $form): array => [
            'id' => $form->id,
            'title' => $form->title,
            'description' => $form->description,
            'owner' => $form->user?->name ?? 'Deleted',
            'status' => $form->status->value,
            'status_label' => ucfirst($form->status->value),
            'submissions_count' => $form->submissions_count ?? 0,
            'created_at' => $form->created_at?->format('d/m/Y'),
            'trashed' => $form->trashed(),
            'url' => route('admin.forms.show', $form),
        ]);

        return Inertia::render('Admin/Forms/Index', [
            'forms' => $forms,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ],
        ]);
    }

    public function show(Form $form)
    {
        AuditService::log('admin.form.viewed', $form, "Admin panel: Form '{$form->title}' viewed");
        $form->loadCount('submissions');
        $form->load('user:id,name');

        $submissions = $form->submissions()->with('data.formField')->latest()->paginate(20);

        $submissions->through(fn (FormSubmission $submission): array => [
            'id' => $submission->id,
            'created_at' => $submission->created_at?->format('d/m/Y H:i'),
            'data' => $submission->data->map(fn ($data): array => [
                'label' => $data->formField?->label ?? 'Field',
                'value' => Str::limit(strip_tags((string) $data->value), 100),
            ])->values(),
        ]);

        return Inertia::render('Admin/Forms/Show', [
            'form' => [
                'id' => $form->id,
                'title' => $form->title,
                'description' => $form->description,
                'owner' => $form->user?->name ?? 'Deleted',
                'status' => $form->status->value,
                'status_label' => ucfirst($form->status->value),
                'slug' => $form->slug,
                'max_submissions' => $form->max_submissions,
                'ends_at' => $form->ends_at?->format('d/m/Y'),
                'submissions_count' => $form->submissions_count,
            ],
            'submissions' => $submissions,
        ]);
    }
}
