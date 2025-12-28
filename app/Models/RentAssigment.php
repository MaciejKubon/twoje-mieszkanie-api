<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentAssigment extends Model
{
    use SoftDeletes;

    protected $table = 'rent_assigment';
    protected $fillable = [
        'id',
        'id_renter',
        'id_object',
        'confirmed',
        'start_date',
        'end_date'
    ];
    protected $casts = [
        'id' => 'integer',
        'id_renter' => 'integer',
        'id_object' => 'integer',
        'confirmed' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date'
    ];
    protected $hidden = [
        'created_at','updated_at','deleted_at'
    ];

    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_renter');
    }
    public function objectInRentAssigment(): BelongsTo
    {
        $object = $this->belongsTo(Objects::class, 'id_object', 'id');
        return $object;
    }
}
