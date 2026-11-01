<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * REA S-06: perubahan password wajib menyertakan password lama, termasuk
 * untuk Super Admin.
 */
class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'password' => Hash::make('password-lama'),
        ]);
    }

    public function test_super_admin_must_provide_current_password(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/change-password', [
                'new_password' => 'password-baru',
                'new_password_confirmation' => 'password-baru',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password-lama', $this->superAdmin->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/change-password', [
                'current_password' => 'salah',
                'new_password' => 'password-baru',
                'new_password_confirmation' => 'password-baru',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password-lama', $this->superAdmin->fresh()->password));
    }

    public function test_super_admin_can_change_password_with_correct_current_password(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/change-password', [
                'current_password' => 'password-lama',
                'new_password' => 'password-baru',
                'new_password_confirmation' => 'password-baru',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('password-baru', $this->superAdmin->fresh()->password));
    }
}
