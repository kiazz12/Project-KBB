<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SessionLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SessionLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_sessions_do_not_block_super_admin_login(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        DB::table('sessions')->insert([
            'id' => 'expired-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => base64_encode('{}'),
            'last_activity' => now()->subMinutes(config('session.lifetime') + 5)->timestamp,
        ]);

        $this->assertTrue(SessionLimitService::canLogin($user));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_active_super_admin_session_blocks_second_login(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        DB::table('sessions')->insert([
            'id' => 'active-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => base64_encode('{}'),
            'last_activity' => now()->timestamp,
        ]);

        $this->assertFalse(SessionLimitService::canLogin($user));
    }
}
