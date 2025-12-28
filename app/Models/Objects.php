<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Attributes as OA;


#[OA\Schema(
    schema: 'Objects',
    title: 'Obiekty',
    description: 'Struktura danych obiektu nieruchomości',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'id_owner', type: 'integer', example: 5),
        new OA\Property(property: 'type_of_building', type: 'string', enum: ['house', 'apartment', 'room'], example: 'apartment'),
        new OA\Property(property: 'country', type: 'string', example: 'Polska'),
        new OA\Property(property: 'voivodeship', type: 'string', example: 'Mazowieckie'),
        new OA\Property(property: 'city', type: 'string', example: 'Warszawa'),
        new OA\Property(property: 'zip_code', type: 'string', example: '00-001'),
        new OA\Property(property: 'street', type: 'string', example: 'Wiejska'),
        new OA\Property(property: 'house_number', type: 'string', example: '10'),
        new OA\Property(property: 'apartment_number', type: 'string', example: '5', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]


/**
 * @property int $id
 * @property int id_owner
 */
class Objects extends Model
{
    use SoftDeletes;
    protected $table = 'objects';
    protected $fillable = [
        'id',
        'id_owner',
        'type_of_building',
        'country',
        'voivodeship',
        'city',
        'zip_code',
        'street',
        'house_number',
        'apartment_number',
    ];
    protected $casts = [
        'id'           => 'integer',
        'id_owner'     => 'integer',
        'house_number' => 'integer',
    ];

    protected $hidden = [
      'created_at', 'updated_at', 'deleted_at'
    ];


    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_owner');
    }
    public function rentAssignments():HasMany
    {
        return $this->hasMany(RentAssigment::class, 'id_object');
    }
}
