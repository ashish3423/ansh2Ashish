<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('records.create', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define(
            'records.update',
            fn (User $user): bool => $user->hasRole(User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN)
        );
        Gate::define(
            'records.delete',
            fn (User $user): bool => $user->hasRole(User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN)
        );
        Gate::define('manage-users', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define(
            'manage-compliance-settings',
            fn (User $user): bool => $user->hasRole(User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN)
        );
    }
}
