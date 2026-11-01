<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * REA S-05: per_page dibatasi 1..100 pada seluruh endpoint daftar API.
 */
class ApiPaginationLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_forms_per_page_above_limit_is_rejected(): void
    {
        $admin = User::factory()->create();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/forms?per_page=1000')->assertStatus(422);
        $this->getJson('/api/v1/forms?per_page=100')->assertOk();
    }

    public function test_users_per_page_above_limit_is_rejected(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        Sanctum::actingAs($superAdmin);

        $this->getJson('/api/v1/users?per_page=1000')->assertStatus(422);
    }

    public function test_submissions_per_page_above_limit_is_rejected(): void
    {
        $admin = User::factory()->create();
        $form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $admin->id,
            'title' => 'Form Pagination',
            'slug' => 'form-pagination-'.Str::random(6),
            'status' => 'published',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/v1/forms/{$form->id}/submissions?per_page=1000")
            ->assertStatus(422);
    }
}
