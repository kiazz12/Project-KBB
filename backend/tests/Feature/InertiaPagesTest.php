<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
