<?php

namespace App\Jobs;

use App\Actions\Webhook\IngestMessageAction;
use App\Enums\MessageType;
use App\Models\Channel;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessLineWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly Channel $channel,
        public readonly array $payload,
    ) {}

    public function handle(IngestMessageAction $action): void
    {
        foreach ($this->payload['events'] ?? [] as $event) {
            if (($event['type'] ?? '') !== 'message') {
                continue;
            }

            $message = $event['message'] ?? [];
            $lineType = $message['type'] ?? '';

            $type = match ($lineType) {
                'text' => MessageType::Text,
                'image' => MessageType::Image,
                'sticker' => MessageType::Sticker,
                default => null,
            };

            if ($type === null) {
                continue;
            }

            $content = $lineType === 'text' ? ($message['text'] ?? null) : null;
            $platformUserId = $event['source']['userId'] ?? null;

            if (! $platformUserId) {
                continue;
            }

            $sentAt = isset($event['timestamp'])
                ? Carbon::createFromTimestampMs($event['timestamp'])
                : now();

            $action($this->channel, $platformUserId, $type, $content, $sentAt);
        }
    }
}
