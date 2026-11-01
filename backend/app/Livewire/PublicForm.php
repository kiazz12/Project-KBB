<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\SubmissionData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class PublicForm extends Component
{
    use WithFileUploads;

    public string $slug;

    public ?Form $form = null;

    public array $responses = [];

    public bool $submitted = false;

    public string $error = '';

    public int $currentStep = 1;

    public array $steps = [];

    public int $totalSteps = 1;

    public string $company_website = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $form = Form::with(['fields.section', 'sections'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $form) {
            $this->error = 'Form tidak ditemukan atau tidak tersedia.';

            return;
        }

        if ($form->isNotOpenYet()) {
            $this->error = 'Pengisian formulir ini belum dibuka. Silakan kembali sesuai jadwal.';

            return;
        }

        if ($form->isExpired()) {
            $this->error = 'Form ini sudah tidak menerima jawaban.';

            return;
        }

        if ($form->isFull()) {
            $this->error = 'Form ini sudah mencapai batas maksimum jawaban.';

            return;
        }

        $this->form = $form;

        $sections = $form->sections;
        $noSectionFields = $form->fields->whereNull('section_id');

        if ($sections->isEmpty()) {
            $this->steps = [['title' => 'Form', 'fields' => $form->fields->reject->is_admin_only]];
            $this->totalSteps = 1;
        } else {
            $this->steps = [];
            foreach ($sections as $section) {
                $visibleFields = $form->fields->where('section_id', $section->id)->reject->is_admin_only;
                if ($visibleFields->isEmpty()) {
                    continue;
                }
                $this->steps[] = [
                    'title' => $section->title,
                    'fields' => $visibleFields,
                ];
            }
            if ($noSectionFields->isNotEmpty()) {
                $this->steps[] = [
                    'title' => 'Lainnya',
                    'fields' => $noSectionFields->reject->is_admin_only,
                ];
            }
            $this->totalSteps = count($this->steps);
        }
    }

    public function render()
    {
        return view('livewire.public-form');
    }

    public function updatedResponses($value, $key): void
    {
        $this->calculateComputedFields();
    }

    private function calculateComputedFields(): void
    {
        if (! $this->form) {
            return;
        }

        foreach ($this->form->fields as $field) {
            if ($field->type->value !== 'computed' || empty($field->formula)) {
                continue;
            }

            $refFieldId = $field->formula['ref_field_id'] ?? null;
            $operation = $field->formula['operation'] ?? 'multiply';
            $constantValue = $field->formula['value'] ?? null;

            if (! $refFieldId) {
                continue;
            }

            $refValue = (float) ($this->responses[(string) $refFieldId] ?? 0);

            if ($constantValue !== null) {
                $refValue2 = (float) $constantValue;
            } else {
                $refField2Id = $field->formula['ref_field_id_2'] ?? null;
                $refValue2 = $refField2Id ? (float) ($this->responses[(string) $refField2Id] ?? 0) : 0;
            }

            $result = match ($operation) {
                'multiply' => $refValue * $refValue2,
                'add' => $refValue + $refValue2,
                'subtract' => $refValue - $refValue2,
                'divide' => $refValue2 != 0 ? $refValue / $refValue2 : 0,
                default => $refValue,
            };

            $this->responses[$field->id] = number_format($result, 0, '.', '');
        }
    }

    public function nextStep(): void
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->validateStep($this->currentStep);
            $this->currentStep++;
        }
    }

    public function prevStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    private function validateStep(int $stepIndex): void
    {
        $step = $this->steps[$stepIndex - 1] ?? null;
        if (! $step) {
            return;
        }

        $rules = [];
        $messages = [];

        foreach ($step['fields'] as $field) {
            if ($field->type->value === 'computed' || $field->is_admin_only) {
                continue;
            }

            $key = "responses.{$field->id}";
            $fieldRules = [];

            if (in_array($field->type->value, ['file', 'signature'])) {
                if ($field->required) {
                    $fieldRules[] = 'required';
                }
                if ($field->type->value === 'file') {
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:2048';
                    $fieldRules[] = 'mimes:jpg,jpeg,png,pdf,doc,docx';
                }
            } else {
                if ($field->required) {
                    $fieldRules[] = 'required';
                }
                $fieldRules[] = match ($field->type->value) {
                    'email' => 'email',
                    'number' => 'numeric',
                    default => 'string',
                };
                if ($field->min_length) {
                    $fieldRules[] = "min:{$field->min_length}";
                }
                if ($field->max_length) {
                    $fieldRules[] = "max:{$field->max_length}";
                }
            }

            if (! empty($fieldRules)) {
                $rules[$key] = implode('|', $fieldRules);
            }

            if ($field->required) {
                $messages["{$key}.required"] = "{$field->label} wajib diisi.";
            }
            if ($field->type->value === 'file') {
                $messages["{$key}.file"] = "{$field->label} harus berupa file.";
                $messages["{$key}.mimes"] = "{$field->label} harus berupa file JPG, PNG, atau PDF.";
                $messages["{$key}.max"] = "{$field->label} maksimal 2MB.";
            }
        }

        if (! empty($rules)) {
            $this->validate($rules, $messages);
        }
    }

    public function submitForm(): void
    {
        if (! $this->form) {
            return;
        }

        if (! empty($this->company_website)) {
            $this->error = 'Pengiriman ditolak.';

            return;
        }

        // REA S-03/S-04/S-05: guardrail diulang saat submit — status/form bisa
        // berubah selama pengguna mengisi (ditutup, kuota penuh, atau belum dibuka).
        if (! $this->form->isAcceptingSubmissions()) {
            $this->error = match (true) {
                $this->form->isNotOpenYet() => 'Pengisian formulir ini belum dibuka.',
                $this->form->isExpired() => 'Form ini sudah tidak menerima jawaban.',
                default => 'Form ini sudah mencapai batas maksimum jawaban.',
            };

            return;
        }

        if ($this->form->require_auth && ! Auth::check()) {
            $this->error = 'Silakan login terlebih dahulu untuk mengisi formulir ini.';

            return;
        }

        if ($this->form->limit_one_response && Auth::check()
            && $this->form->submissions()->where('user_id', Auth::id())->exists()) {
            $this->error = 'Anda sudah pernah mengisi formulir ini.';

            return;
        }

        $this->calculateComputedFields();

        $validationRules = [];
        $validationMessages = [];

        foreach ($this->form->fields as $field) {
            if ($field->type->value === 'computed' || $field->is_admin_only) {
                continue;
            }

            $key = "responses.{$field->id}";
            $rules = [];

            if (in_array($field->type->value, ['file', 'signature'])) {
                if ($field->required) {
                    $rules[] = 'required';
                }
                if ($field->type->value === 'file') {
                    $rules[] = 'file';
                    $rules[] = 'max:2048';
                    $rules[] = 'mimes:jpg,jpeg,png,pdf,doc,docx';
                }
            } else {
                if ($field->required) {
                    $rules[] = 'required';
                }
                $rules[] = match ($field->type->value) {
                    'email' => 'email',
                    'number' => 'numeric',
                    default => 'string',
                };
                if ($field->min_length) {
                    $rules[] = "min:{$field->min_length}";
                }
                if ($field->max_length) {
                    $rules[] = "max:{$field->max_length}";
                }
            }

            $validationRules[$key] = implode('|', $rules);

            if ($field->required) {
                $validationMessages["{$key}.required"] = "{$field->label} wajib diisi.";
            }
            if ($field->type->value === 'file') {
                $validationMessages["{$key}.file"] = "{$field->label} harus berupa file.";
                $validationMessages["{$key}.mimes"] = "{$field->label} harus berupa file JPG, PNG, atau PDF.";
                $validationMessages["{$key}.max"] = "{$field->label} maksimal 2MB.";
            }
        }

        $this->validate($validationRules, $validationMessages);

        // REA: transaksi agar header + detail submission tersimpan konsisten.
        DB::transaction(function () {
            $submission = FormSubmission::create([
                'uuid' => Str::uuid(),
                'form_id' => $this->form->id,
                'user_id' => Auth::check() ? Auth::id() : null,
                'ip_address' => $this->form->collect_ip ? request()->ip() : null,
                'user_agent' => $this->form->collect_ip ? request()->userAgent() : null,
                'submitted_at' => now(),
            ]);

            foreach ($this->responses as $fieldId => $value) {
                $fieldModel = $this->form->fields->firstWhere('id', $fieldId);

                if ($fieldModel && $fieldModel->is_admin_only) {
                    continue;
                }

                if ($fieldModel && $fieldModel->type->value === 'file' && $value instanceof TemporaryUploadedFile) {
                    $path = $value->store('uploads', 'local');
                    $value = $path;
                }

                SubmissionData::create([
                    'submission_id' => $submission->id,
                    'form_field_id' => $fieldId,
                    'value' => is_array($value) ? json_encode($value) : (string) $value,
                ]);
            }
        });

        $this->submitted = true;
    }
}
