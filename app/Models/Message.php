<?php

namespace App\Models;

use App\Enums\MessageDirection;
use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['conversation_id', 'direction', 'type', 'content', 'attachments', 'sender_type', 'sender_id', 'sent_at'])]
class Message extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Message $message) {
            $message->conversation()->update([
                'last_message_at' => $message->sent_at,
            ]);
        });
    }

    protected function casts(): array
    {
        return [
            'direction' => MessageDirection::class,
            'type' => MessageType::class,
            'attachments' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
