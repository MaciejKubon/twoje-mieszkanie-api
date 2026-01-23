<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RentAssigment',
    title: 'RentAssigment Model',
    description: 'Model reprezentujący przypisanie wynajmu do obiektu',
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true, example: 1),
        new OA\Property(property: 'id_renter', description: 'ID najemcy (użytkownika)', type: 'integer', example: 10),
        new OA\Property(property: 'id_object', description: 'ID wynajmowanego obiektu', type: 'integer', example: 5),
        new OA\Property(property: 'confirmed', description: 'Czy umowa jest potwierdzona', type: 'boolean', example: true),
        new OA\Property(property: 'start_date', description: 'Data rozpoczęcia najmu', type: 'string', format: 'date', example: '2024-01-01'),
        new OA\Property(property: 'end_date', description: 'Data zakończenia najmu', type: 'string', format: 'date', example: '2024-12-31')
    ]
)]
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
