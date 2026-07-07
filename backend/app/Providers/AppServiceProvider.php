<?php

namespace App\Providers;

use App\Models\Form;
use App\Models\User;
use App\Observers\UserObserver;
use App\Policies\FormPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Form::class, FormPolicy::class);
        User::observe(UserObserver::class);
    }
}
