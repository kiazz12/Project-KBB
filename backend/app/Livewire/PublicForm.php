<?php

namespace App\Livewire;

use App\Enums\FieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\Participant;
use App\Models\SubmissionData;
use Illuminate\Support\Facades\Auth;
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

    public string $participantSearch = '';

    public array $participantResults = [];

    public ?int $selectedParticipantId = null;

    public bool $showParticipantSearch = false;

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

        if ($form->isExpired()) {
            $this->error = 'Form ini sudah tidak menerima jawaban.';

            return;
        }

        if ($form->isFull()) {
            $this->error = 'Form ini sudah mencapai batas maksimum jawaban.';

            return;
        }

        $this->form = $form;

        $this->showParticipantSearch = false;

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

    public function updatedParticipantSearch(): void
    {
        if (strlen($this->participantSearch) < 2) {
            $this->participantResults = [];

            return;
        }

        $this->participantResults = Participant::where('nama', 'like', "%{$this->participantSearch}%")
            ->limit(20)
            ->get()
            ->toArray();
    }

    public function selectParticipant(int $participantId): void
    {
        $participant = Participant::find($participantId);
        if (! $participant) {
            return;
        }

        $this->selectedParticipantId = $participantId;

        $linkedFormId = $this->form->settings['linked_form_id'] ?? null;
        $presensiData = null;

        if ($linkedFormId) {
            $linkedSubmission = FormSubmission::where('form_id', $linkedFormId)
                ->with('data.formField')
                ->get()
                ->filter(function ($sub) use ($participant) {
                    return $sub->data->contains(function ($d) use ($participant) {
                        return $d->formField && strtolower($d->formField->label) === 'nama lengkap'
                            && strtolower(trim($d->value)) === strtolower(trim($participant->nama));
                    });
                })
                ->first();

            if ($linkedSubmission) {
                $presensiData = [];
                foreach ($linkedSubmission->data as $d) {
                    if ($d->formField) {
                        $presensiData[strtolower($d->formField->label)] = $d->value;
                    }
                }
            }
        }

        foreach ($this->form->fields as $field) {
            $label = strtolower($field->label);

            if ($presensiData) {
                if (str_contains($label, 'nama') && ! str_contains($label, 'nik')) {
                    $this->responses[$field->id] = $presensiData['nama lengkap'] ?? $participant->nama;
                }
                if (str_contains($label, 'instansi') || str_contains($label, 'opd')) {
                    $this->responses[$field->id] = $presensiData['opd / institusi'] ?? $participant->opd_institusi;
                }
                if (str_contains($label, 'jabatan')) {
                    $this->responses[$field->id] = $presensiData['jabatan'] ?? $participant->jabatan;
                }
                if (str_contains($label, 'nik') || $label === 'nik') {
                    $this->responses[$field->id] = $presensiData['no. induk pegawai (nip)'] ?? '';
                }
            } else {
                if (str_contains($label, 'nama') && ! str_contains($label, 'nik')) {
                    $this->responses[$field->id] = $participant->nama;
                }
                if (str_contains($label, 'instansi') || str_contains($label, 'opd')) {
                    $this->responses[$field->id] = $participant->opd_institusi;
                }
                if (str_contains($label, 'jabatan')) {
                    $this->responses[$field->id] = $participant->jabatan;
                }
                if ($label === 'role') {
                    $this->responses[$field->id] = $participant->role;
                }
            }
        }

        $this->calculateComputedFields();
        $this->participantResults = [];
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

        $this->autoSetPresensiStatus($submission);

        $this->submitted = true;
    }

    private function isPresensiForm(): bool
    {
        return str_contains($this->slug, 'presensi') || str_contains($this->slug, 'transfer-knowledge');
    }

    private function autoSetPresensiStatus(FormSubmission $submission): void
    {
        if (! $this->isPresensiForm()) {
            return;
        }

        $statusField = $this->form->fields
            ->where('label', 'Status Kehadiran')
            ->first();

        if (! $statusField) {
            $statusField = FormField::create([
                'form_id' => $this->form->id,
                'type' => FieldType::Radio,
                'label' => 'Status Kehadiran',
                'options' => ['Hadir', 'Izin', 'Sakit', 'Tanpa Keterangan'],
                'is_admin_only' => true,
                'required' => false,
                'order' => $this->form->fields->max('order') + 1,
            ]);
        }

        $existing = SubmissionData::where('submission_id', $submission->id)
            ->where('form_field_id', $statusField->id)
            ->first();

        if (! $existing) {
            SubmissionData::create([
                'submission_id' => $submission->id,
                'form_field_id' => $statusField->id,
                'value' => 'Hadir',
            ]);
        }
    }
}
