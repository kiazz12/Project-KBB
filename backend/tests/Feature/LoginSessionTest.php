<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_creates_user_session_record(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('user_sessions', [
            'user_id' => $user->id,
            'display_name' => '',
        ]);

        $session = UserSession::where('user_id', $user->id)->first();
        $this->assertNotNull($session->logged_in_at);
        $this->assertNull($session->logged_out_at);
        $this->assertEquals($this->app['request']->ip(), $session->ip_address);
    }

    public function test_login_stores_session_id_in_laravel_session(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $sessionId = session('user_session_id');
        $this->assertNotNull($sessionId);
        $this->assertDatabaseHas('user_sessions', ['id' => $sessionId]);
    }

    public function test_logout_updates_logged_out_at(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $sessionId = session('user_session_id');
        $this->assertNotNull($sessionId);

        $this->post('/logout');

        $session = UserSession::find($sessionId);
        $this->assertNotNull($session->logged_out_at);
    }

    public function test_failed_login_does_not_create_session(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertDatabaseCount('user_sessions', 0);
    }
}
