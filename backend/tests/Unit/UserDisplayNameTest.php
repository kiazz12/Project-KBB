<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class UserDisplayNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_display_name_returns_session_display_name_when_available(): void
    {
        $user = User::factory()->create(['name' => 'Account Owner']);
        $session = UserSession::create([
            'user_id' => $user->id,
            'display_name' => 'Actual Person',
            'logged_in_at' => now(),
        ]);
        Session::put('user_session_id', $session->id);

        $this->assertEquals('Actual Person', $user->currentDisplayName());
    }

    public function test_current_display_name_falls_back_to_users_display_name_when_session_empty(): void
    {
        $user = User::factory()->create([
            'name' => 'Account Owner',
            'display_name' => 'Fallback Name',
        ]);
        $session = UserSession::create([
            'user_id' => $user->id,
            'display_name' => '',
            'logged_in_at' => now(),
        ]);
        Session::put('user_session_id', $session->id);

        $this->assertEquals('Fallback Name', $user->currentDisplayName());
    }

    public function test_current_display_name_falls_back_to_name_when_no_session_and_no_display_name(): void
    {
        $user = User::factory()->create([
            'name' => 'Account Owner',
            'display_name' => null,
        ]);

        $this->assertEquals('Account Owner', $user->currentDisplayName());
    }

    public function test_current_display_name_falls_back_to_name_when_session_not_found(): void
    {
        $user = User::factory()->create(['name' => 'Account Owner']);
        Session::put('user_session_id', 99999);

        $this->assertEquals('Account Owner', $user->currentDisplayName());
    }

    public function test_current_display_name_falls_back_to_name_when_no_session_in_laravel_session(): void
    {
        $user = User::factory()->create(['name' => 'Account Owner']);

        $this->assertEquals('Account Owner', $user->currentDisplayName());
    }
}
