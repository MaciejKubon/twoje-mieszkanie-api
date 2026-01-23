<?php

namespace App\Providers;

use App\Models\FullRent;
use App\Models\Objects;
use App\Models\RentAssigment;
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
        //Object
        Gate::define('create-object', function ($user) {
            return $user->role === "owner";
        });
        Gate::define('update-object', function (User $user, Objects $object) {
            return   $user->role === "owner" && $user->id == $object->id_owner;
        });
        Gate::define('delete-object', function (User $user, Objects $object) {
            return $user->role == "owner" && $user->id == $object->id_owner;
        });

        //Rent Assigment
        Gate::define('show-all-rentAssigment', function (User $user) {
            return $user->role === "owner";
        });
        Gate::define('show-rentAssigment', function (User $user, RentAssigment $rentAssigment) {
            $rentAssigment->load('objectInRentAssigment');
            return ($user->role === "rentier" && $user->id == $rentAssigment->id_renter)
                || ($user->role === "owner" && $user->id == $rentAssigment->objectInRentAssigment->id_owner);
        });
        Gate::define('create-rentAssigment', function (User $user, int $id_object) {
            $object = Objects::where('id', $id_object)->first();
            return $user->role === "owner" && $user->id == $object->id_owner;
        });
        Gate::define('delete-rentAssigment', function (User $user, RentAssigment $rentAssigment) {
            return $user->role === "owner" && $user->id == $rentAssigment->objectInRentAssigment->id_owner;
        });
        Gate::define('update-rentAssigment', function (User $user, RentAssigment $rentAssigment) {
            return ($user->role === "rentier" && $user->id == $rentAssigment->id_renter)
                || ($user->role === "owner" && $user->id == $rentAssigment->objectInRentAssigment->id_owner);
        });

        //FullRent
        Gate::define('show-all-fullRent', function (User $user, FullRent $fullRent) {
            return $user->id === $fullRent->rent_assigment->objectInRentAssigment->id_owner
                || $user->id == $fullRent->rent_assigment->id_renter;
        });
        Gate::define('show-fullRent', function (User $user) {
           return true;
        });
        Gate::define('create-fullRent', function (User $user, int $id_rent) {
            $rent_assigment = RentAssigment::find($id_rent);
            if (!$rent_assigment) {
                return false;
            }
            return $user->id === $rent_assigment->objectInRentAssigment?->id_owner;
        });
        Gate::define('update-fullRent', function (User $user, FullRent $fullRent) {
            return $user->id === $fullRent->rentAssignment->objectInRentAssigment->id_owner;
        });
        Gate::define('delete-fullRent', function (User $user, FullRent $fullRent) {
            return $user->id === $fullRent->rentAssignment->objectInRentAssigment->id_owner;
        });
        Gate::define('confirm-paid-fullRent', function (User $user, FullRent $fullRent) {
            return $user->id === $fullRent->rentAssignment->id_renter;
        });
    }
}
