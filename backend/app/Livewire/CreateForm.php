<?php

namespace App\Livewire;

use App\Models\Form;
use App\Services\AuditService;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateForm extends Component
{
    public string $title = '';

    public string $description = '';

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ];
    }

    public function render()
    {
        return view('livewire.create-form')
            ->layout('layouts.app');
    }

    public function save()
    {
        $this->validate();

        $slug = Str::slug($this->title);
        $baseSlug = $slug;
        $counter = 1;
        while (Form::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $form = Form::create([
            'uuid' => Str::uuid(),
            'user_id' => auth()->id(),
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $slug,
            'status' => 'draft',
        ]);

        AuditService::log('form_created', $form, "Form '{$form->title}' created");

        return redirect()->route('forms.edit', $form)
            ->with('success', 'Form berhasil dibuat.');
    }
}
