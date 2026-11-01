<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayNamePromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_name_is_saved_to_current_session_record(): void
    {
        $user = User::factory()->create(['display_name' => null]);
        $session = UserSession::create([
            'user_id' => $user->id,
            'display_name' => '',
            'logged_in_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['user_session_id' => $session->id])
            ->postJson('/session/display-name', ['displayName' => 'John Doe']);

        $response->assertOk()->assertJsonPath('display_name', 'John Doe');
        $this->assertDatabaseHas('user_sessions', [
            'id' => $session->id,
            'display_name' => 'John Doe',
        ]);
        $this->assertNull($user->fresh()->display_name);
    }

    public function test_display_name_endpoint_validates_minimum_length(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/session/display-name', ['displayName' => 'A'])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['displayName']]);
    }
}
