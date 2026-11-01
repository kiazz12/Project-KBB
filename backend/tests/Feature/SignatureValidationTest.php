<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * REA S-07: field tanda tangan adalah teks bebas; panjangnya dibatasi agar
 * tidak menyimpan payload sangat besar.
 */
class SignatureValidationTest extends TestCase
{
    use RefreshDatabase;

    private Form $form;

    private FormField $field;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create();

        $this->form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $owner->id,
            'title' => 'Form Tanda Tangan',
            'slug' => 'form-ttd-'.Str::random(6),
            'status' => 'published',
        ]);

        $this->field = $this->form->fields()->create([
            'type' => 'signature',
            'label' => 'Tanda Tangan',
            'required' => true,
            'order' => 1,
        ]);
    }

    public function test_signature_longer_than_255_characters_is_rejected(): void
    {
        $this->from('/form/'.$this->form->slug)
            ->post('/form/'.$this->form->slug, [
                'responses' => [$this->field->id => str_repeat('a', 256)],
            ])
            ->assertSessionHasErrors("responses.{$this->field->id}");

        $this->assertDatabaseCount('form_submissions', 0);
    }

    public function test_normal_signature_is_accepted(): void
    {
        $this->from('/form/'.$this->form->slug)
            ->post('/form/'.$this->form->slug, [
                'responses' => [$this->field->id => str_repeat('a', 255)],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('form_submissions', 1);
    }
}
