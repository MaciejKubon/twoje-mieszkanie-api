<?php

namespace App\Providers;

use App\Models\Objects;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::define('create-object', function ($user) {
            return $user->role === "owner";
        });
        Gate::define('update-object', function (User $user, Objects $object) {
            return   $user->role === "owner" && $user->id == $object->id_owner;
        });
        Gate::define('delete-object', function (User $user, Objects $object) {
            return $user->role == "owner" && $user->id == $object->id_owner;
        });
    }
}
