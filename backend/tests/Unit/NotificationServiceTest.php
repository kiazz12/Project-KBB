<?php

namespace Tests\Unit;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserSession;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_notify_super_admins_uses_session_display_name(): void
    {
        $actor = Model::withoutEvents(fn () => User::factory()->create([
            'name' => 'Account Owner',
            'role' => 'admin',
        ]));
        $superAdmin = Model::withoutEvents(fn () => User::factory()->create(['role' => 'super_admin']));

        $session = UserSession::create([
            'user_id' => $actor->id,
            'display_name' => 'Actual Person',
            'logged_in_at' => now(),
        ]);
        Session::put('user_session_id', $session->id);

        $this->actingAs($actor);
        NotificationService::notifySuperAdmins('test_event', 'did something.');

        $notification = Notification::where('user_id', $superAdmin->id)
            ->where('type', 'test_event')
            ->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('Actual Person', $notification->message);
        $this->assertStringContainsString('Admin', $notification->message);
    }

    public function test_notify_super_admins_falls_back_to_user_name_when_no_session(): void
    {
        $actor = Model::withoutEvents(fn () => User::factory()->create([
            'name' => 'Account Owner',
            'display_name' => null,
            'role' => 'admin',
        ]));
        $superAdmin = Model::withoutEvents(fn () => User::factory()->create(['role' => 'super_admin']));

        $this->actingAs($actor);
        NotificationService::notifySuperAdmins('test_event', 'did something.');

        $notification = Notification::where('user_id', $superAdmin->id)
            ->where('type', 'test_event')
            ->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('Account Owner', $notification->message);
    }

    public function test_notify_super_admins_does_not_notify_actor(): void
    {
        $actor = Model::withoutEvents(fn () => User::factory()->create(['role' => 'super_admin']));
        $otherAdmin = Model::withoutEvents(fn () => User::factory()->create(['role' => 'super_admin']));

        $this->actingAs($actor);
        NotificationService::notifySuperAdmins('test_event', 'did something.');

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', ['user_id' => $otherAdmin->id]);
        $this->assertDatabaseMissing('notifications', ['user_id' => $actor->id]);
    }

    public function test_notify_super_admins_includes_data_in_notification(): void
    {
        $actor = Model::withoutEvents(fn () => User::factory()->create(['role' => 'admin']));
        $superAdmin = Model::withoutEvents(fn () => User::factory()->create(['role' => 'super_admin']));

        $this->actingAs($actor);
        NotificationService::notifySuperAdmins('test_event', 'did something.', ['form_id' => 42]);

        $notification = Notification::where('user_id', $superAdmin->id)
            ->where('type', 'test_event')
            ->first();
        $this->assertNotNull($notification);
        $this->assertEquals(42, $notification->data['form_id']);
        $this->assertEquals($actor->id, $notification->data['actor_id']);
    }

    public function test_notify_super_admins_does_nothing_without_authenticated_user(): void
    {
        NotificationService::notifySuperAdmins('test_event', 'did something.');

        $this->assertDatabaseCount('notifications', 0);
    }
}
