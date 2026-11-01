<?php

namespace Tests\Feature;

use App\Enums\FieldType;
use App\Enums\FormStatus;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_login_page_renders_inertia_login_component(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Login')
            ->where('auth.user', null)
        );
    }

    public function test_dashboard_page_renders_inertia_dashboard_component(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->has('recentForms')
            ->has('topForms')
            ->has('latestSubmissions')
            ->has('activity.labels')
            ->has('activity.data')
            ->where('isSuperAdmin', false)
        );
    }

    public function test_dashboard_activity_endpoint_returns_chart_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/dashboard/activity?range=7d');

        $response->assertOk()->assertJsonStructure([
            'labels',
            'data',
            'total',
            'max',
        ]);
    }

    public function test_dashboard_activity_endpoint_rejects_unknown_range_with_default(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/dashboard/activity?range=bogus');

        $response->assertOk();
        $this->assertCount(7, $response->json('labels'));
    }

    public function test_forms_index_page_renders_inertia_forms_index_component(): void
    {
        $user = User::factory()->create();

        $form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Form Uji Coba',
            'slug' => 'form-uji-coba',
            'status' => FormStatus::Draft,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => FieldType::Text,
            'label' => 'Nama Lengkap',
            'required' => true,
            'order' => 1,
        ]);

        $response = $this->actingAs($user)->get('/forms');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Forms/Index')
            ->has('forms.data', 1)
            ->where('forms.data.0.title', 'Form Uji Coba')
            ->where('forms.data.0.status', 'draft')
            ->where('forms.data.0.fields_count', 1)
            ->where('isSuperAdmin', false)
        );
    }

    public function test_form_detail_page_renders_inertia_forms_show_component(): void
    {
        $user = User::factory()->create();

        $form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Form Detail',
            'slug' => 'form-detail',
            'status' => FormStatus::Published,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => FieldType::Text,
            'label' => 'Alamat',
            'required' => true,
            'order' => 1,
        ]);

        $response = $this->actingAs($user)->get("/forms/{$form->id}");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Forms/Show')
            ->where('form.title', 'Form Detail')
            ->where('form.status', 'published')
            ->where('form.fields_count', 1)
            ->has('form.fields', 1)
            ->where('form.fields.0.label', 'Alamat')
            ->where('form.fields.0.type', 'text')
        );
    }

    public function test_users_index_page_renders_inertia_users_index_component(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['name' => 'Admin Biasa']);

        $response = $this->actingAs($superAdmin)->get('/users');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Users/Index')
            ->has('users.data', 2)
            ->where('users.data', fn ($users) => collect($users)->pluck('id')->contains($admin->id))
        );
    }

    public function test_users_index_is_forbidden_for_regular_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertForbidden();
    }

    public function test_user_detail_page_renders_inertia_users_show_component(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['name' => 'Admin Detail']);

        $form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $admin->id,
            'title' => 'Form Milik Admin',
            'slug' => 'form-milik-admin',
            'status' => FormStatus::Published,
        ]);

        $response = $this->actingAs($superAdmin)->get("/users/{$admin->id}");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Users/Show')
            ->where('user.name', 'Admin Detail')
            ->where('user.forms_count', 1)
            ->has('forms.data', 1)
            ->where('forms.data.0.id', $form->id)
        );
    }

    public function test_change_password_page_renders_inertia_component_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/change-password');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ChangePassword')
            ->where('isSuperAdmin', false)
        );
    }

    public function test_change_password_page_skips_current_password_for_super_admin(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->get('/change-password');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ChangePassword')
            ->where('isSuperAdmin', true)
        );
    }

    public function test_notifications_page_renders_inertia_notifications_component(): void
    {
        $user = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'info',
            'message' => 'Pesan uji notifikasi',
        ]);

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Notifications/Index')
            ->where('unreadCount', 1)
            ->has('notifications.data', 1)
            ->where('notifications.data.0.message', 'Pesan uji notifikasi')
            ->where('notifications.data.0.is_read', false)
        );
    }

    public function test_notifications_endpoint_still_returns_json_when_requested(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/notifications');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['data', 'current_page', 'last_page']]);
    }
}
