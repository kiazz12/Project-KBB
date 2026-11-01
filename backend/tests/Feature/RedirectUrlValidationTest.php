<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * REA S-10: redirect_url hanya boleh relatif atau menuju host yang
 * diizinkan (APP_URL / KBB_ALLOWED_REDIRECT_HOSTS).
 */
class RedirectUrlValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Form $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();

        $this->form = Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->owner->id,
            'title' => 'Form Redirect',
            'slug' => 'form-redirect-'.Str::random(6),
            'status' => 'published',
        ]);
    }

    private function updateRedirect(string $url)
    {
        return $this->actingAs($this->owner)
            ->put("/forms/{$this->form->id}/settings", [
                'title' => $this->form->title,
                'confirmation_type' => 'redirect',
                'redirect_url' => $url,
            ]);
    }

    public function test_external_domain_is_rejected(): void
    {
        $this->updateRedirect('https://evil.example/fishing')
            ->assertSessionHasErrors('redirect_url');

        $this->assertNull($this->form->fresh()->redirect_url);
    }

    public function test_javascript_scheme_is_rejected(): void
    {
        $this->updateRedirect('javascript:alert(1)')
            ->assertSessionHasErrors('redirect_url');
    }

    public function test_relative_url_is_allowed(): void
    {
        $this->updateRedirect('/terima-kasih')
            ->assertSessionHasNoErrors();

        $this->assertSame('/terima-kasih', $this->form->fresh()->redirect_url);
    }

    public function test_application_host_is_allowed(): void
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        $this->updateRedirect("http://{$host}/terima-kasih")
            ->assertSessionHasNoErrors();
    }

    public function test_host_from_allowlist_is_allowed(): void
    {
        config(['kbb.allowed_redirect_hosts' => ['mitra.go.id']]);

        $this->updateRedirect('https://mitra.go.id/terima-kasih')
            ->assertSessionHasNoErrors();
    }
}
