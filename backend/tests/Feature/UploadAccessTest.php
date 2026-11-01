<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\SubmissionData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * REA S-08/S-09: unduhan unggahan hanya untuk pemilik, dipaksa attachment,
 * dan path traversal di luar folder uploads ditolak.
 */
class UploadAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherAdmin;

    private Form $form;

    private FormField $field;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->otherAdmin = User::factory()->create();

        $this->form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->owner->id,
            'title' => 'Form Upload',
            'slug' => 'form-upload-'.Str::random(6),
            'status' => 'published',
        ]);

        $this->field = $this->form->fields()->create([
            'type' => 'file',
            'label' => 'Berkas',
            'order' => 1,
        ]);
    }

    private function attachFile(string $value): void
    {
        $submission = FormSubmission::create([
            'uuid' => (string) Str::uuid(),
            'form_id' => $this->form->id,
            'user_id' => $this->owner->id,
            'submitted_at' => now(),
        ]);

        SubmissionData::create([
            'submission_id' => $submission->id,
            'form_field_id' => $this->field->id,
            'value' => $value,
        ]);
    }

    public function test_owner_downloads_file_as_attachment_with_nosniff(): void
    {
        Storage::disk('local')->put('uploads/berkas-uji.txt', 'isi berkas');
        $this->attachFile('uploads/berkas-uji.txt');

        $this->actingAs($this->owner)
            ->get('/uploads/berkas-uji.txt')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Disposition', 'attachment; filename="berkas-uji.txt"');

        Storage::disk('local')->delete('uploads/berkas-uji.txt');
    }

    public function test_other_admin_cannot_download_file(): void
    {
        Storage::disk('local')->put('uploads/berkas-uji.txt', 'isi berkas');
        $this->attachFile('uploads/berkas-uji.txt');

        $this->actingAs($this->otherAdmin)
            ->get('/uploads/berkas-uji.txt')
            ->assertForbidden();

        Storage::disk('local')->delete('uploads/berkas-uji.txt');
    }

    public function test_path_outside_uploads_directory_is_rejected(): void
    {
        Storage::disk('local')->put('rahasia.txt', 'jangan bocor');
        $this->attachFile('uploads/../rahasia.txt');

        $this->actingAs($this->owner)
            ->get('/uploads/../rahasia.txt')
            ->assertNotFound();

        Storage::disk('local')->delete('rahasia.txt');
    }
}
