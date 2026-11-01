<?php

namespace App\Providers;

use App\Models\Form;
use App\Models\User;
use App\Observers\UserObserver;
use App\Policies\FormPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Form::class, FormPolicy::class);
        User::observe(UserObserver::class);

        // REA S-05: batasi endpoint API ber-token per pengguna (fallback IP).
        RateLimiter::for('api-auth', function (Request $request) {
            return Limit::perMinute(60)->by(
                (string) ($request->user()?->getAuthIdentifier() ?: $request->ip())
            );
        });
    }
}
