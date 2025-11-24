<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 * schema="Message",
 * title="Message",
 * description="Model wiadomości",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="from_user_id", type="integer", example=3),
 * @OA\Property(property="to_user_id", type="integer", example=2),
 * @OA\Property(property="message", type="string", example="testowa wiadomość"),
 * @OA\Property(property="sent_at", type="string", format="date-time", example="2025-11-24 16:06:50"),
 * @OA\Property(property="is_read", type="boolean", example=false),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-24 16:06:50"),
 * @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-24 16:06:50"),
 * )
 */
class Message extends Model
{
    protected $table = 'message';
    protected $fillable = [
        'id',
        'from_user_id',
        'to_user_id',
        'message',
        'sent_at',
        'is_read'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function from_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
    public function to_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
