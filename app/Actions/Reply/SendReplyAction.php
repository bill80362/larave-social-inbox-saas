<?php

namespace App\Actions\Reply;

use App\Actions\Reply\Drivers\FacebookReplyDriver;
use App\Actions\Reply\Drivers\GoogleBusinessReplyDriver;
use App\Actions\Reply\Drivers\InstagramReplyDriver;
use App\Actions\Reply\Drivers\LineReplyDriver;
use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Enums\Platform;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use InvalidArgumentException;

class SendReplyAction
{
    public function __invoke(Conversation $conversation, string $content, User $agent): Message
    {
        $channel = $conversation->channel;
        $contact = $conversation->contact;

        $platformMessageId = match ($channel->platform) {
            Platform::Line => (new LineReplyDriver)->send($channel, $contact, $content),
            Platform::Facebook => (new FacebookReplyDriver)->send($channel, $contact, $content),
            Platform::Instagram => (new InstagramReplyDriver)->send($channel, $contact, $content),
            Platform::GoogleBusiness => (new GoogleBusinessReplyDriver)->send($channel, $contact, $content),
            default => throw new InvalidArgumentException("Unsupported platform: {$channel->platform->value}"),
        };

        return Message::create([
            'conversation_id' => $conversation->id,
            'direction' => MessageDirection::Outbound,
            'type' => MessageType::Text,
            'content' => $content,
            'attachments' => null,
            'sender_type' => 'agent',
            'sender_id' => $agent->id,
            'sent_at' => now(),
            'platform_message_id' => $platformMessageId,
        ]);
    }
}
