<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_session_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $session = UserSession::create([
            'user_id' => $user->id,
            'display_name' => 'Test User',
            'logged_in_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $session->user);
        $this->assertEquals($user->id, $session->user->id);
    }

    public function test_user_session_has_correct_fillable_fields(): void
    {
        $user = User::factory()->create();
        $session = UserSession::create([
            'user_id' => $user->id,
            'display_name' => 'John Doe',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'logged_in_at' => now(),
        ]);

        $this->assertEquals('John Doe', $session->display_name);
        $this->assertEquals('127.0.0.1', $session->ip_address);
        $this->assertEquals('Mozilla/5.0', $session->user_agent);
        $this->assertNull($session->logged_out_at);
    }

    public function test_user_session_scope_active_filters_logged_out(): void
    {
        $user = User::factory()->create();
        $active = UserSession::create([
            'user_id' => $user->id,
            'display_name' => 'Active',
            'logged_in_at' => now(),
        ]);
        $inactive = UserSession::create([
            'user_id' => $user->id,
            'display_name' => 'Inactive',
            'logged_in_at' => now(),
            'logged_out_at' => now(),
        ]);

        $activeSessions = UserSession::active()->get();
        $this->assertCount(1, $activeSessions);
        $this->assertEquals($active->id, $activeSessions->first()->id);
    }

    public function test_user_has_many_sessions(): void
    {
        $user = User::factory()->create();
        UserSession::create([
            'user_id' => $user->id,
            'display_name' => 'Session 1',
            'logged_in_at' => now(),
        ]);
        UserSession::create([
            'user_id' => $user->id,
            'display_name' => 'Session 2',
            'logged_in_at' => now(),
        ]);

        $this->assertCount(2, $user->sessions);
    }
}
