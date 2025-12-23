<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Message",
    title: "Message",
    description: "Message model"
)]
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


    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(property: "from_user_id", example: 3)]
    public int $from_user_id;

    #[OA\Property(property: "to_user_id", example: 2)]
    public int $to_user_id;

    #[OA\Property(example: "testowa wiadomość")]
    public string $message;

    #[OA\Property(property: "sent_at", format: "date-time", example: "2025-11-24 16:06:50")]
    public string $sent_at;

    #[OA\Property(property: "is_read", example: false)]
    public bool $is_read;

    public function from_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
    public function to_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
