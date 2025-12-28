<?php

namespace App\Services;

use App\Models\Objects;
use App\Models\RentAssigment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RentAssigmentService
{
    public function getRentsAssignedToObjects(int $user_id): Collection
    {
        return Objects::where('id_owner', $user_id)
            ->whereNull('deleted_at')
            ->with(['rentAssignments' => function($query) {
                $query->orderBy('start_date', 'desc');
            }])
            ->get();
    }
    public function getDetailsForRenter(int $renterId): Collection
    {
        return RentAssigment::where('id_renter', $renterId)
            ->with(['objectInRentAssigment.owner'])
            ->get();
    }
    public function getDetailsForOwner(int $rentAssigmentId): \App\Models\RentAssigment
    {
        $rentAssigment = RentAssigment::findOrFail($rentAssigmentId);
        $rentAssigment->load(['objectInRentAssigment', 'renter']);
        return $rentAssigment;
    }
    public function AssigningRenterToRentAssigment(string $rent_email): int|null{
        $user = User::where('email',$rent_email)->first();
        if($user) {
            $id_renter = $user->id;
        }else{
            $id_renter = null;
        }
        return $id_renter;
    }


}
