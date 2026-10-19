<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * REA S-01: cegah IDOR pada detail/penghapusan submission lintas form.
 * Sebelum perbaikan, GET/DELETE /api/v1/forms/{formA}/submissions/{submissionB}
 * berhasil selama user punya izin atas formA (submission tidak di-scope).
 */
class SubmissionOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Form $formA;

    private Form $formB;

    private FormSubmission $submissionB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();

        $this->formA = Form::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'user_id' => $this->owner->id,
            'title' => 'Form A',
            'slug' => 'form-a-'.\Illuminate\Support\Str::random(6),
            'status' => 'published',
        ]);

        $this->formB = Form::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'user_id' => $this->owner->id,
            'title' => 'Form B',
            'slug' => 'form-b-'.\Illuminate\Support\Str::random(6),
            'status' => 'published',
        ]);

        $this->submissionB = FormSubmission::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'form_id' => $this->formB->id,
            'user_id' => $this->owner->id,
            'submitted_at' => now(),
        ]);
    }

    public function test_show_submission_from_other_form_returns_404(): void
    {
        Sanctum::actingAs($this->owner);

        $this->getJson("/api/v1/forms/{$this->formA->id}/submissions/{$this->submissionB->id}")
            ->assertNotFound();
    }

    public function test_show_submission_of_correct_form_returns_200(): void
    {
        Sanctum::actingAs($this->owner);

        $this->getJson("/api/v1/forms/{$this->formB->id}/submissions/{$this->submissionB->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $this->submissionB->id);
    }

    public function test_destroy_submission_from_other_form_returns_404(): void
    {
        Sanctum::actingAs($this->owner);

        $this->deleteJson("/api/v1/forms/{$this->formA->id}/submissions/{$this->submissionB->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('form_submissions', ['id' => $this->submissionB->id]);
    }

    public function test_destroy_submission_by_non_owner_returns_403(): void
    {
        $otherAdmin = User::factory()->create();
        Sanctum::actingAs($otherAdmin);

        $this->deleteJson("/api/v1/forms/{$this->formB->id}/submissions/{$this->submissionB->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('form_submissions', ['id' => $this->submissionB->id]);
    }

    public function test_owner_can_destroy_submission_of_own_form(): void
    {
        Sanctum::actingAs($this->owner);

        $this->deleteJson("/api/v1/forms/{$this->formB->id}/submissions/{$this->submissionB->id}")
            ->assertOk();

        $this->assertDatabaseMissing('form_submissions', ['id' => $this->submissionB->id]);
    }
}
