<?php

namespace App\Jobs;

use App\Actions\Webhook\IngestMessageAction;
use App\Enums\MessageType;
use App\Models\Channel;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessGoogleBusinessWebhook implements ShouldQueue
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
        $review = $this->payload['review'] ?? $this->payload;
        $reviewId = $review['reviewId'] ?? ($this->payload['reviewId'] ?? null);

        if (! $reviewId) {
            return;
        }

        $reviewerComment = $review['comment'] ?? ($this->payload['reviewerComment'] ?? null);
        $platformUserId = $review['reviewer']['profilePhotoUrl'] ?? $reviewId;

        $sentAt = isset($review['createTime'])
            ? Carbon::parse($review['createTime'])
            : now();

        $action($this->channel, $reviewId, MessageType::Review, $reviewerComment, $sentAt);
    }
}
