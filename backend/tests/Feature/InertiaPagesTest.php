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

    public function test_admin_login_page_renders_inertia_component_for_guest(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Auth/Login')
            ->where('auth.user', null)
        );
    }

    public function test_admin_dashboard_renders_inertia_component_with_stats(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->get('/admin');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
            ->has('recentForms')
            ->has('usersByRole')
            ->has('activity.labels')
            ->has('activity.data')
        );
    }

    public function test_admin_activity_endpoint_returns_chart_data(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->getJson('/admin/activity?range=1m');

        $response->assertOk()->assertJsonStructure([
            'labels',
            'data',
            'total',
            'max',
        ]);
        $this->assertCount(30, $response->json('labels'));
    }

    public function test_admin_pages_are_forbidden_for_regular_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')->assertForbidden();
        $this->actingAs($admin)->get('/admin/users')->assertForbidden();
    }

    public function test_admin_users_pages_render_inertia_components(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create([
            'name' => 'Admin Panel',
            'nip' => '123456',
            'opd' => 'Diskominfo',
        ]);

        $this->actingAs($superAdmin)->get('/admin/users')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->has('users.data', 2)
                ->where('users.data', fn ($users) => collect($users)->pluck('id')->contains($admin->id))
            );

        $this->actingAs($superAdmin)->get('/admin/users/create')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Form')
                ->where('user', null)
            );

        $this->actingAs($superAdmin)->get("/admin/users/{$admin->id}/edit")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Form')
                ->where('user.id', $admin->id)
                ->where('user.name', 'Admin Panel')
                ->where('user.role', 'admin')
                ->where('user.is_super_admin', false)
            );
    }

    public function test_super_admin_is_redirected_from_dashboard_to_admin_panel(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)->get('/dashboard')->assertRedirect('/admin');
    }

    public function test_super_admin_sees_all_forms_in_the_single_forms_page(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['name' => 'Pemilik Form']);

        Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $admin->id,
            'title' => 'Form Milik Admin Lain',
            'slug' => 'form-milik-admin-lain',
            'status' => FormStatus::Published,
        ]);

        $this->actingAs($superAdmin)->get('/forms')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Forms/Index')
                ->where('isSuperAdmin', true)
                ->has('forms.data', 1)
                ->where('forms.data.0.title', 'Form Milik Admin Lain')
                ->where('forms.data.0.user.name', 'Pemilik Form')
            );
    }

    public function test_duplicate_admin_forms_panel_route_has_been_removed(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)->get('/admin/forms')->assertNotFound();
    }
}
