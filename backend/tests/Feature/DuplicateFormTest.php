<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * REA B-04: duplikasi form harus menyalin sections dan memetakan ulang
 * section_id setiap field ke section baru (sebelumnya field menunjuk
 * ke section form asal — duplikat rusak/salah tampil).
 */
class DuplicateFormTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Form $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();

        $this->form = Form::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'user_id' => $this->owner->id,
            'title' => 'Form Asal',
            'slug' => 'form-asal-'.\Illuminate\Support\Str::random(6),
            'status' => 'published',
        ]);

        $section = FormSection::create([
            'form_id' => $this->form->id,
            'title' => 'Bagian Satu',
            'order' => 1,
        ]);

        FormField::create([
            'form_id' => $this->form->id,
            'section_id' => $section->id,
            'type' => 'text',
            'label' => 'Nama Lengkap',
            'required' => true,
            'order' => 1,
        ]);

        FormField::create([
            'form_id' => $this->form->id,
            'section_id' => null,
            'type' => 'email',
            'label' => 'Email',
            'required' => false,
            'order' => 2,
        ]);
    }

    public function test_web_duplicate_remaps_sections_to_new_form(): void
    {
        $this->actingAs($this->owner);

        $response = $this->post("/forms/{$this->form->id}/duplicate");
        $response->assertRedirect();

        $newForm = Form::where('title', 'Form Asal (Copy)')->firstOrFail();
        $this->assertSame($this->owner->id, $newForm->user_id);
        $this->assertSame('draft', $newForm->status->value);

        // Sections tersalin ke form baru.
        $newSection = $newForm->sections()->where('title', 'Bagian Satu')->first();
        $this->assertNotNull($newSection);
        $this->assertNotSame($this->form->id, $newSection->form_id);

        // Field ber-section menunjuk ke section form baru, bukan form asal.
        $newField = $newForm->fields()->where('label', 'Nama Lengkap')->first();
        $this->assertNotNull($newField);
        $this->assertSame($newSection->id, $newField->section_id);

        // Field tanpa section tetap null.
        $newFieldNoSection = $newForm->fields()->where('label', 'Email')->first();
        $this->assertNull($newFieldNoSection->section_id);

        // Form asal tidak berubah.
        $this->assertSame(
            1,
            $this->form->sections()->where('title', 'Bagian Satu')->count()
        );
    }

    public function test_api_duplicate_remaps_sections_to_new_form(): void
    {
        \Laravel\Sanctum\Sanctum::actingAs($this->owner);

        $response = $this->postJson("/api/v1/forms/{$this->form->id}/duplicate");
        $response->assertCreated();

        $newForm = Form::findOrFail($response->json('data.id'));

        $newSection = $newForm->sections()->where('title', 'Bagian Satu')->first();
        $this->assertNotNull($newSection);

        $newField = $newForm->fields()->where('label', 'Nama Lengkap')->first();
        $this->assertSame($newSection->id, $newField->section_id);
    }
}
