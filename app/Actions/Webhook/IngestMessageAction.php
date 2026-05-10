<?php

namespace App\Actions\Webhook;

use App\Enums\ConversationStatus;
use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Carbon\Carbon;

class IngestMessageAction
{
    public function __invoke(
        Channel $channel,
        string $platformUserId,
        MessageType $type,
        ?string $content,
        Carbon $sentAt,
    ): Message {
        $contact = Contact::firstOrCreate(
            [
                'channel_id' => $channel->id,
                'platform_user_id' => $platformUserId,
            ],
            [
                'workspace_id' => $channel->workspace_id,
            ]
        );

        $conversation = Conversation::where('channel_id', $channel->id)
            ->where('contact_id', $contact->id)
            ->whereIn('status', [ConversationStatus::Open, ConversationStatus::Pending])
            ->latest('last_message_at')
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'workspace_id' => $channel->workspace_id,
                'channel_id' => $channel->id,
                'contact_id' => $contact->id,
                'status' => ConversationStatus::Open,
                'last_message_at' => $sentAt,
            ]);
        }

        return Message::create([
            'conversation_id' => $conversation->id,
            'direction' => MessageDirection::Inbound,
            'type' => $type,
            'content' => $content,
            'sender_type' => 'contact',
            'sender_id' => $contact->id,
            'sent_at' => $sentAt,
        ]);
    }
}
