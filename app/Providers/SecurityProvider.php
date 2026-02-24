<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SecurityProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('administrator-only', function (User $user) {
            return $user->role->name == 'administrator'
                ? Response::allow()
                : Response::deny('Hanya administrator web yang memiliki akses');
        });
        Gate::define('administrator-petugas', function (User $user) {
            return $user->role->name == 'administrator' || $user->role->name == 'petugas'
                ? Response::allow()
                : Response::deny('Kamu tidak punya akses, hanya petugas yang memiliki akses');
        });
        Gate::define('administrator-petugas-bendahara', function (User $user) {
            return array_any(['administrator', 'petugas', 'bendahara'], fn($item)=> $user->role->name == $item)
                ? Response::allow()
                : Response::deny('Kamu tidak punya akses, hanya petugas dan bendahara yang memiliki akses');
        });
        Gate::define('administrator-petugas-unit', function (User $user) {
            return array_any(['administrator', 'petugas', 'unit'], fn($item)=> $user->role->name == $item)
                ? Response::allow()
                : Response::deny('Kamu tidak punya akses, hanya petugas dan unit yang memiliki akses');
        });
        Gate::define('administrator-unit', function (User $user) {
            return array_any(['administrator', 'unit'], fn($item)=> $user->role->name == $item)
                ? Response::allow()
                : Response::deny('Kamu tidak punya akses, hanya unit yang memiliki akses');
        });
    }
}
