<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DisplayNamePromptTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'display_name' => null,
        ]);
    }

    public function test_display_name_prompt_always_shows_on_mount(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(\App\Livewire\DisplayNamePrompt::class);

        $component->assertSet('show', true);
    }

    public function test_display_name_prompt_saves_to_session_record(): void
    {
        $this->actingAs($this->user);

        $session = UserSession::create([
            'user_id' => $this->user->id,
            'display_name' => '',
            'logged_in_at' => now(),
        ]);
        session()->put('user_session_id', $session->id);

        Livewire::test(\App\Livewire\DisplayNamePrompt::class)
            ->set('displayName', 'John Doe')
            ->call('saveDisplayName');

        $session->refresh();
        $this->assertEquals('John Doe', $session->display_name);
    }

    public function test_display_name_prompt_hides_after_save(): void
    {
        $this->actingAs($this->user);

        $session = UserSession::create([
            'user_id' => $this->user->id,
            'display_name' => '',
            'logged_in_at' => now(),
        ]);
        session()->put('user_session_id', $session->id);

        Livewire::test(\App\Livewire\DisplayNamePrompt::class)
            ->set('displayName', 'John Doe')
            ->call('saveDisplayName')
            ->assertSet('show', false);
    }

    public function test_display_name_prompt_validates_minimum_length(): void
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\DisplayNamePrompt::class)
            ->set('displayName', 'A')
            ->call('saveDisplayName')
            ->assertHasErrors(['displayName']);
    }

    public function test_display_name_prompt_does_not_save_to_users_table(): void
    {
        $this->actingAs($this->user);

        $session = UserSession::create([
            'user_id' => $this->user->id,
            'display_name' => '',
            'logged_in_at' => now(),
        ]);
        session()->put('user_session_id', $session->id);

        Livewire::test(\App\Livewire\DisplayNamePrompt::class)
            ->set('displayName', 'John Doe')
            ->call('saveDisplayName');

        $this->user->refresh();
        $this->assertNull($this->user->display_name);
    }
}
