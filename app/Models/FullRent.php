<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FullRent',
    description: 'Model reprezentujący czynsz',
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true, example: 1),
        new OA\Property(property: 'id_rent_assigment', type: 'integer', example: 5),
        new OA\Property(property: 'amount', type: 'number', format: 'float', example: 1500.00),
        new OA\Property(property: 'date', type: 'string', format: 'date', example: '2024-01-10'),
        new OA\Property(property: 'is_accepted', type: 'boolean', example: false),
        new OA\Property(property: 'is_paid', type: 'boolean', example: false),
        new OA\Property(property: 'date_paid', type: 'string', format: 'date-time', example: null, nullable: true),
    ]
)]
class FullRent extends Model
{
    protected $table = 'full_rent';

    protected $fillable = [
        'id',
        'id_rent_assigment',
        'amount',
        'date',
        'is_accepted',
        'is_paid',
        'date_paid'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function rentAssignment(): BelongsTo
    {
        return $this->belongsTo(RentAssigment::class, 'id_rent_assigment');
    }
}
