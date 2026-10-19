<?php

namespace Tests\Feature;

use App\Livewire\PublicForm;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * REA S-03/S-04/S-05: guardrail form (require_auth, limit_one_response,
 * belum dibuka/ditutup/kuota penuh) harus berlaku di jalur web Livewire,
 * bukan hanya di API.
 */
class PublicFormGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
    }

    private function makeForm(array $overrides = []): Form
    {
        return Form::create(array_merge([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'user_id' => $this->owner->id,
            'title' => 'Form Uji',
            'slug' => 'form-uji-'.\Illuminate\Support\Str::random(6),
            'status' => 'published',
        ], $overrides));
    }

    public function test_form_not_open_yet_shows_error_on_mount(): void
    {
        $form = $this->makeForm(['starts_at' => now()->addDay()]);

        Livewire::test(PublicForm::class, ['slug' => $form->slug])
            ->assertSee('Pengisian formulir ini belum dibuka')
            ->assertSet('form', null);
    }

    public function test_closed_form_shows_error_on_mount(): void
    {
        $form = $this->makeForm(['ends_at' => now()->subDay()]);

        Livewire::test(PublicForm::class, ['slug' => $form->slug])
            ->assertSee('sudah tidak menerima jawaban')
            ->assertSet('form', null);
    }

    public function test_full_form_shows_error_on_mount(): void
    {
        $form = $this->makeForm(['max_submissions' => 1]);
        FormSubmission::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'form_id' => $form->id,
            'submitted_at' => now(),
        ]);

        Livewire::test(PublicForm::class, ['slug' => $form->slug])
            ->assertSee('batas maksimum jawaban')
            ->assertSet('form', null);
    }

    public function test_guest_cannot_submit_form_requiring_auth(): void
    {
        $form = $this->makeForm(['require_auth' => true]);

        Livewire::test(PublicForm::class, ['slug' => $form->slug])
            ->call('submitForm')
            ->assertSet('error', 'Silakan login terlebih dahulu untuk mengisi formulir ini.')
            ->assertSet('submitted', false);

        $this->assertDatabaseCount('form_submissions', 0);
    }

    public function test_user_cannot_submit_twice_when_limited_to_one_response(): void
    {
        $user = User::factory()->create();
        $form = $this->makeForm(['limit_one_response' => true]);

        FormSubmission::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'form_id' => $form->id,
            'user_id' => $user->id,
            'submitted_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test(PublicForm::class, ['slug' => $form->slug])
            ->call('submitForm')
            ->assertSet('error', 'Anda sudah pernah mengisi formulir ini.')
            ->assertSet('submitted', false);

        $this->assertDatabaseCount('form_submissions', 1);
    }

    public function test_form_closing_after_mount_blocks_submission(): void
    {
        $form = $this->makeForm();

        $component = Livewire::test(PublicForm::class, ['slug' => $form->slug])
            ->assertNotSet('form', null); // form termuat saat mount

        // Form ditutup di tengah jalan (oleh admin lain).
        $form->update(['ends_at' => now()->subMinute()]);

        $component->call('submitForm')
            ->assertSet('submitted', false)
            ->assertSet('error', 'Form ini sudah tidak menerima jawaban.');

        $this->assertDatabaseCount('form_submissions', 0);
    }
}
