<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * REA S-03: route submit API publik kini membaca bearer token secara
 * opsional, sehingga `require_auth` dan `limit_one_response` benar-benar
 * berfungsi tanpa memblokir responden anonim.
 */
class ApiSubmissionAuthTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $respondent;

    private Form $form;

    private FormField $field;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->respondent = User::factory()->create();

        $this->form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->owner->id,
            'title' => 'Form API Auth',
            'slug' => 'form-api-auth-'.Str::random(6),
            'status' => 'published',
            'require_auth' => true,
            'limit_one_response' => true,
        ]);

        $this->field = $this->form->fields()->create([
            'type' => 'text',
            'label' => 'Nama',
            'required' => true,
            'order' => 1,
        ]);
    }

    public function test_anonymous_submission_is_rejected_when_auth_is_required(): void
    {
        $this->postJson("/api/v1/forms/public/{$this->form->slug}", [
            'fields' => [$this->field->id => 'Anonim'],
        ])->assertUnauthorized();

        $this->assertDatabaseCount('form_submissions', 0);
    }

    public function test_bearer_token_satisfies_require_auth_and_records_the_user(): void
    {
        $token = $this->respondent->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/forms/public/{$this->form->slug}", [
                'fields' => [$this->field->id => 'Budi'],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('form_submissions', [
            'form_id' => $this->form->id,
            'user_id' => $this->respondent->id,
        ]);
    }

    public function test_limit_one_response_rejects_second_submission_from_same_token_user(): void
    {
        $token = $this->respondent->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/forms/public/{$this->form->slug}", [
                'fields' => [$this->field->id => 'Pertama'],
            ])
            ->assertCreated();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/forms/public/{$this->form->slug}", [
                'fields' => [$this->field->id => 'Kedua'],
            ])
            ->assertStatus(409);

        $this->assertDatabaseCount('form_submissions', 1);
    }
}
