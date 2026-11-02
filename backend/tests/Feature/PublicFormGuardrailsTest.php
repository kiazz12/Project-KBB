<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicFormGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->owner = User::factory()->create();
    }

    private function makeForm(array $overrides = []): Form
    {
        return Form::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->owner->id,
            'title' => 'Form Uji',
            'slug' => 'form-uji-'.Str::random(6),
            'status' => 'published',
        ], $overrides));
    }

    private function assertPublicFormError(Form $form, string $message): void
    {
        $this->get('/form/'.$form->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('PublicForm')
                ->where('form', null)
                ->where('error', fn ($error) => str_contains($error, $message))
            );
    }

    public function test_form_not_open_yet_is_reported_on_inertia_page(): void
    {
        $form = $this->makeForm(['starts_at' => now()->addDay()]);

        $this->assertPublicFormError($form, 'belum dibuka');
    }

    public function test_closed_form_is_reported_on_inertia_page(): void
    {
        $form = $this->makeForm(['ends_at' => now()->subDay()]);

        $this->assertPublicFormError($form, 'tidak menerima jawaban');
    }

    public function test_full_form_is_reported_on_inertia_page(): void
    {
        $form = $this->makeForm(['max_submissions' => 1]);
        FormSubmission::create([
            'uuid' => (string) Str::uuid(),
            'form_id' => $form->id,
            'submitted_at' => now(),
        ]);

        $this->assertPublicFormError($form, 'batas maksimum jawaban');
    }

    public function test_guest_cannot_post_to_a_form_requiring_authentication(): void
    {
        $form = $this->makeForm(['require_auth' => true]);

        $this->from('/form/'.$form->slug)
            ->post('/form/'.$form->slug, ['responses' => []])
            ->assertRedirect('/form/'.$form->slug)
            ->assertSessionHas('error', 'Silakan login terlebih dahulu untuk mengisi formulir ini.');

        $this->assertDatabaseCount('form_submissions', 0);
    }

    public function test_user_cannot_post_twice_when_limited_to_one_response(): void
    {
        $user = User::factory()->create();
        $form = $this->makeForm(['limit_one_response' => true]);
        FormSubmission::create([
            'uuid' => (string) Str::uuid(),
            'form_id' => $form->id,
            'user_id' => $user->id,
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->from('/form/'.$form->slug)
            ->post('/form/'.$form->slug, ['responses' => []])
            ->assertRedirect('/form/'.$form->slug)
            ->assertSessionHas('error', 'Anda sudah pernah mengisi formulir ini.');

        $this->assertDatabaseCount('form_submissions', 1);
    }

    public function test_successful_submission_shares_confirmation_state(): void
    {
        $form = $this->makeForm();

        $this->from('/form/'.$form->slug)
            ->post('/form/'.$form->slug, ['responses' => []])
            ->assertRedirect('/form/'.$form->slug)
            ->assertSessionHas('submitted', true);

        $this->get('/form/'.$form->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('PublicForm')
                ->where('flash.submitted', true)
            );
    }

    public function test_form_closed_after_page_load_rejects_submission(): void
    {
        $form = $this->makeForm();
        $this->get('/form/'.$form->slug)
            ->assertInertia(fn (Assert $page) => $page
                ->component('PublicForm')
                ->where('form.slug', $form->slug)
                ->where('error', null)
            );

        $form->update(['ends_at' => now()->subMinute()]);

        $this->from('/form/'.$form->slug)
            ->post('/form/'.$form->slug, ['responses' => []])
            ->assertRedirect('/form/'.$form->slug)
            ->assertSessionHas('error', 'Form ini sudah tidak menerima jawaban.');

        $this->assertDatabaseCount('form_submissions', 0);
    }
}
