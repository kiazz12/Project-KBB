<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSection;
use App\Services\AuditService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class FormEditor extends Component
{
    use AuthorizesRequests;

    public Form $form;
    public string $tab = 'fields';
    public ?int $editingFieldId = null;

    public string $fieldType = 'text';
    public string $fieldLabel = '';
    public string $fieldPlaceholder = '';
    public string $fieldHelpText = '';
    public bool $fieldRequired = false;
    public string $fieldOptionsText = '';
    public ?int $fieldSectionId = null;

    public string $settingsTitle = '';
    public string $settingsDescription = '';
    public ?string $settingsStartsAt = null;
    public ?string $settingsEndsAt = null;
    public int $settingsMaxSubmissions = 0;
    public bool $settingsRequireAuth = false;
    public bool $settingsCollectIp = true;
    public bool $settingsShowKbbLogo = true;
    public bool $settingsLimitOneResponse = false;
    public string $settingsConfirmationType = 'message';
    public string $settingsConfirmationMessage = '';
    public string $settingsRedirectUrl = '';

    public string $message = '';
    public string $messageType = 'success';
    public bool $showPublishModal = false;
    public string $publishedUrl = '';

    public ?int $editingSectionId = null;
    public string $sectionTitle = '';
    public string $sectionDescription = '';
    public bool $showSectionForm = false;

    protected function rules()
    {
        return [
            'fieldType' => 'required|string|in:text,textarea,email,number,date,time,select,radio,checkbox,file,heading,paragraph,signature',
            'fieldLabel' => 'required|string|max:255',
            'fieldPlaceholder' => 'nullable|string|max:255',
            'fieldHelpText' => 'nullable|string|max:500',
            'fieldRequired' => 'boolean',
            'fieldOptionsText' => 'nullable|string',
            'settingsTitle' => 'required|string|max:255',
        ];
    }

    public function mount(Form $form): void
    {
        $this->authorize('update', $form);
        $this->form = $form->load(['fields.section', 'sections']);
        $this->settingsTitle = $form->title;
        $this->settingsDescription = $form->description ?? '';
        $this->settingsStartsAt = $form->starts_at?->format('Y-m-d\TH:i');
        $this->settingsEndsAt = $form->ends_at?->format('Y-m-d\TH:i');
        $this->settingsMaxSubmissions = $form->max_submissions ?? 0;
        $this->settingsRequireAuth = $form->require_auth ?? false;
        $this->settingsCollectIp = $form->collect_ip ?? true;
        $this->settingsShowKbbLogo = $form->show_kbb_logo ?? true;
        $this->settingsLimitOneResponse = $form->limit_one_response ?? false;
        $this->settingsConfirmationType = $form->confirmation_type ?? 'message';
        $this->settingsConfirmationMessage = $form->confirmation_message ?? 'Terima kasih, jawaban Anda telah dicatat.';
        $this->settingsRedirectUrl = $form->redirect_url ?? '';
    }

    public function render()
    {
        $this->form->load(['fields.section', 'sections']);
        return view('livewire.form-editor')
            ->layout('layouts.app');
    }

    public function selectTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function editField(int $fieldId): void
    {
        if ($fieldId === -1) {
            $this->resetFieldForm();
            return;
        }
        $field = $this->form->fields()->findOrFail($fieldId);
        $this->editingFieldId = $fieldId;
        $this->fieldType = $field->type->value;
        $this->fieldLabel = $field->label;
        $this->fieldPlaceholder = $field->placeholder ?? '';
        $this->fieldHelpText = $field->help_text ?? '';
        $this->fieldRequired = $field->required ?? false;
        $this->fieldOptionsText = $field->options ? implode("\n", $field->options) : '';
        $this->fieldSectionId = $field->section_id;
    }

    public function cancelEdit(): void
    {
        $this->resetFieldForm();
    }

    public function saveField(): void
    {
        $this->authorize('update', $this->form);
        $this->validateOnly('fieldType');
        $this->validateOnly('fieldLabel');

        $options = null;
        if (in_array($this->fieldType, ['select', 'radio', 'checkbox'])) {
            $options = array_filter(array_map('trim', explode("\n", $this->fieldOptionsText)));
            if (empty($options)) {
                $this->message = 'Tipe field ini membutuhkan minimal 1 opsi.';
                $this->messageType = 'error';
                return;
            }
        }

        $data = [
            'type' => $this->fieldType,
            'label' => $this->fieldLabel,
            'placeholder' => $this->fieldPlaceholder,
            'help_text' => $this->fieldHelpText,
            'required' => $this->fieldRequired,
            'options' => $options,
            'section_id' => $this->fieldSectionId ?: null,
        ];

        if ($this->editingFieldId) {
            $field = $this->form->fields()->findOrFail($this->editingFieldId);
            $field->update($data);
            AuditService::log('form_field_updated', $this->form, "Field '{$field->label}' updated");
            $this->message = 'Field berhasil diperbarui.';
        } else {
            $maxOrder = $this->form->fields()->max('order') ?? 0;
            $data['form_id'] = $this->form->id;
            $data['order'] = $maxOrder + 1;
            FormField::create($data);
            AuditService::log('form_field_created', $this->form, "Field '{$data['label']}' added");
            $this->message = 'Field berhasil ditambahkan.';
        }

        $this->messageType = 'success';
        $this->resetFieldForm();
    }

    public function deleteField(int $fieldId): void
    {
        $this->authorize('update', $this->form);
        $field = $this->form->fields()->findOrFail($fieldId);
        $label = $field->label;
        $field->delete();
        AuditService::log('form_field_deleted', $this->form, "Field '{$label}' deleted");
        $this->message = 'Field berhasil dihapus.';
        $this->messageType = 'success';

        if ($this->editingFieldId === $fieldId) {
            $this->resetFieldForm();
        }

        $this->renumberFields();
    }

    public function moveFieldUp(int $fieldId): void
    {
        $fields = $this->form->fields()->orderBy('order')->get();
        $index = $fields->search(fn($f) => $f->id === $fieldId);
        if ($index === false || $index === 0) return;

        $temp = $fields[$index];
        $fields[$index] = $fields[$index - 1];
        $fields[$index - 1] = $temp;

        $this->saveFieldOrder($fields);
    }

    public function moveFieldDown(int $fieldId): void
    {
        $fields = $this->form->fields()->orderBy('order')->get();
        $index = $fields->search(fn($f) => $f->id === $fieldId);
        if ($index === false || $index === $fields->count() - 1) return;

        $temp = $fields[$index];
        $fields[$index] = $fields[$index + 1];
        $fields[$index + 1] = $temp;

        $this->saveFieldOrder($fields);
    }

    private function saveFieldOrder($fields): void
    {
        foreach ($fields as $i => $field) {
            $field->update(['order' => $i + 1]);
        }
    }

    private function renumberFields(): void
    {
        $fields = $this->form->fields()->orderBy('order')->get();
        foreach ($fields as $i => $field) {
            if ($field->order !== $i + 1) {
                $field->update(['order' => $i + 1]);
            }
        }
    }

    public function publishForm(): void
    {
        $this->authorize('update', $this->form);
        $this->form->update(['status' => 'published']);
        AuditService::log('form_published', $this->form, "Form '{$this->form->title}' published");
        $this->publishedUrl = url('/form/' . $this->form->slug);
        $this->showPublishModal = true;
    }

    public function closePublishModal(): void
    {
        $this->showPublishModal = false;
    }

    public function closeForm(): void
    {
        $this->authorize('update', $this->form);
        $this->form->update(['status' => 'closed']);
        AuditService::log('form_closed', $this->form, "Form '{$this->form->title}' closed");
        $this->message = 'Form berhasil ditutup.';
        $this->messageType = 'success';
    }

    public function saveSettings(): void
    {
        $this->authorize('update', $this->form);
        $this->validate();

        $this->form->update([
            'title' => $this->settingsTitle,
            'description' => $this->settingsDescription,
            'starts_at' => $this->settingsStartsAt ?: null,
            'ends_at' => $this->settingsEndsAt ?: null,
            'max_submissions' => $this->settingsMaxSubmissions ?: null,
            'require_auth' => $this->settingsRequireAuth,
            'collect_ip' => $this->settingsCollectIp,
            'show_kbb_logo' => $this->settingsShowKbbLogo,
            'limit_one_response' => $this->settingsLimitOneResponse,
            'confirmation_type' => $this->settingsConfirmationType,
            'confirmation_message' => $this->settingsConfirmationMessage,
            'redirect_url' => $this->settingsRedirectUrl ?: null,
        ]);

        AuditService::log('form_settings_updated', $this->form, "Form '{$this->form->title}' settings updated");
        $this->message = 'Pengaturan berhasil disimpan.';
        $this->messageType = 'success';
    }

    public function addSection(): void
    {
        $this->editingSectionId = null;
        $this->sectionTitle = '';
        $this->sectionDescription = '';
        $this->showSectionForm = true;
    }

    public function editSection(int $sectionId): void
    {
        $section = $this->form->sections()->findOrFail($sectionId);
        $this->editingSectionId = $sectionId;
        $this->sectionTitle = $section->title;
        $this->sectionDescription = $section->description ?? '';
        $this->showSectionForm = true;
    }

    public function cancelSection(): void
    {
        $this->showSectionForm = false;
        $this->editingSectionId = null;
        $this->sectionTitle = '';
        $this->sectionDescription = '';
    }

    public function saveSection(): void
    {
        $this->authorize('update', $this->form);
        $this->validate([
            'sectionTitle' => 'required|string|max:255',
            'sectionDescription' => 'nullable|string|max:500',
        ]);

        if ($this->editingSectionId) {
            $section = $this->form->sections()->findOrFail($this->editingSectionId);
            $section->update([
                'title' => $this->sectionTitle,
                'description' => $this->sectionDescription,
            ]);
            AuditService::log('form_section_updated', $this->form, "Section '{$section->title}' updated");
            $this->message = 'Section berhasil diperbarui.';
        } else {
            $maxOrder = $this->form->sections()->max('order') ?? 0;
            FormSection::create([
                'form_id' => $this->form->id,
                'title' => $this->sectionTitle,
                'description' => $this->sectionDescription,
                'order' => $maxOrder + 1,
            ]);
            AuditService::log('form_section_created', $this->form, "Section '{$this->sectionTitle}' added");
            $this->message = 'Section berhasil ditambahkan.';
        }

        $this->messageType = 'success';
        $this->cancelSection();
    }

    public function deleteSection(int $sectionId): void
    {
        $this->authorize('update', $this->form);
        $section = $this->form->sections()->findOrFail($sectionId);
        $title = $section->title;

        $section->fields()->update(['section_id' => null]);

        $section->delete();
        AuditService::log('form_section_deleted', $this->form, "Section '{$title}' deleted");
        $this->message = 'Section berhasil dihapus.';
        $this->messageType = 'success';
    }

    public function moveSectionUp(int $sectionId): void
    {
        $sections = $this->form->sections()->orderBy('order')->get();
        $index = $sections->search(fn($s) => $s->id === $sectionId);
        if ($index === false || $index === 0) return;

        $temp = $sections[$index];
        $sections[$index] = $sections[$index - 1];
        $sections[$index - 1] = $temp;

        foreach ($sections as $i => $section) {
            $section->update(['order' => $i + 1]);
        }
    }

    public function moveSectionDown(int $sectionId): void
    {
        $sections = $this->form->sections()->orderBy('order')->get();
        $index = $sections->search(fn($s) => $s->id === $sectionId);
        if ($index === false || $index === $sections->count() - 1) return;

        $temp = $sections[$index];
        $sections[$index] = $sections[$index + 1];
        $sections[$index + 1] = $temp;

        foreach ($sections as $i => $section) {
            $section->update(['order' => $i + 1]);
        }
    }

    private function resetFieldForm(): void
    {
        $this->editingFieldId = null;
        $this->fieldType = 'text';
        $this->fieldLabel = '';
        $this->fieldPlaceholder = '';
        $this->fieldHelpText = '';
        $this->fieldRequired = false;
        $this->fieldOptionsText = '';
        $this->fieldSectionId = null;
    }
}
