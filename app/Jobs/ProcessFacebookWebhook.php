<?php

namespace App\Jobs;

use App\Actions\Webhook\IngestMessageAction;
use App\Enums\MessageType;
use App\Models\Channel;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessFacebookWebhook implements ShouldQueue
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
        foreach ($this->payload['entry'] ?? [] as $entry) {
            foreach ($entry['messaging'] ?? [] as $messaging) {
                $message = $messaging['message'] ?? null;

                if (! $message) {
                    continue;
                }

                $platformUserId = $messaging['sender']['id'] ?? null;

                if (! $platformUserId) {
                    continue;
                }

                $type = $this->resolveType($message);

                if ($type === null) {
                    continue;
                }

                $content = $type === MessageType::Text ? ($message['text'] ?? null) : null;
                $sentAt = isset($messaging['timestamp'])
                    ? Carbon::createFromTimestampMs($messaging['timestamp'])
                    : now();

                $action($this->channel, $platformUserId, $type, $content, $sentAt);
            }
        }
    }

    private function resolveType(array $message): ?MessageType
    {
        if (isset($message['text'])) {
            return MessageType::Text;
        }

        foreach ($message['attachments'] ?? [] as $attachment) {
            if (($attachment['type'] ?? '') === 'image') {
                return MessageType::Image;
            }
        }

        return null;
    }
}
