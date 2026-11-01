<?php

namespace Tests\Feature;

use App\Enums\FieldType;
use App\Enums\FormStatus;
use App\Models\Form;
use App\Models\FormField;
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
}
