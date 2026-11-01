<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * REA S-02: field pada /api/v1/forms/{form}/fields/{field} harus ter-scope
 * ke form di URL. Sebelum perbaikan, field milik form lain dapat diubah
 * atau dihapus hanya dengan menebak ID (IDOR).
 */
class ApiFieldOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Form $formA;

    private Form $formB;

    private FormField $fieldB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();

        $this->formA = $this->makeForm('Form A');
        $this->formB = $this->makeForm('Form B');

        $this->fieldB = $this->formB->fields()->create([
            'type' => 'text',
            'label' => 'Field milik B',
            'order' => 1,
        ]);
    }

    private function makeForm(string $title): Form
    {
        return Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->owner->id,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'status' => 'published',
        ]);
    }

    public function test_update_field_from_other_form_returns_404(): void
    {
        Sanctum::actingAs($this->owner);

        $this->putJson("/api/v1/forms/{$this->formA->id}/fields/{$this->fieldB->id}", [
            'label' => 'Diubah dari form lain',
        ])->assertNotFound();

        $this->assertDatabaseHas('form_fields', [
            'id' => $this->fieldB->id,
            'label' => 'Field milik B',
        ]);
    }

    public function test_delete_field_from_other_form_returns_404(): void
    {
        Sanctum::actingAs($this->owner);

        $this->deleteJson("/api/v1/forms/{$this->formA->id}/fields/{$this->fieldB->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('form_fields', ['id' => $this->fieldB->id]);
    }

    public function test_owner_can_update_field_of_the_same_form(): void
    {
        Sanctum::actingAs($this->owner);

        $this->putJson("/api/v1/forms/{$this->formB->id}/fields/{$this->fieldB->id}", [
            'label' => 'Label baru',
        ])->assertOk();

        $this->assertDatabaseHas('form_fields', [
            'id' => $this->fieldB->id,
            'label' => 'Label baru',
        ]);
    }
}
