<?php

namespace Tests\Feature;

use App\Enums\MessageType;
use App\Enums\Platform;
use App\Models\Channel;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookLineTest extends TestCase
{
    use RefreshDatabase;

    private Channel $channel;

    private string $channelSecret = 'test_channel_secret';

    protected function setUp(): void
    {
        parent::setUp();

        $workspace = Workspace::factory()->create();

        $this->channel = Channel::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Line,
            'platform_account_id' => 'Uline123',
            'credentials' => json_encode(['channel_secret' => $this->channelSecret]),
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function buildPayload(string $userId = 'Uabc', string $type = 'text', string $text = 'Hello'): array
    {
        return [
            'destination' => 'Uline123',
            'events' => [
                [
                    'type' => 'message',
                    'timestamp' => now()->timestamp * 1000,
                    'source' => ['userId' => $userId],
                    'message' => ['type' => $type, 'id' => 'msg1', 'text' => $text],
                ],
            ],
        ];
    }

    private function makeSignature(string $body): string
    {
        return base64_encode(hash_hmac('sha256', $body, $this->channelSecret, true));
    }

    public function test_valid_line_webhook_returns_200(): void
    {
        $payload = $this->buildPayload();
        $body = json_encode($payload);

        $this->postJson('/api/webhook/line', $payload, [
            'X-Line-Signature' => $this->makeSignature($body),
        ])->assertStatus(200);
    }

    public function test_invalid_signature_returns_403(): void
    {
        $payload = $this->buildPayload();

        $this->postJson('/api/webhook/line', $payload, [
            'X-Line-Signature' => 'invalidsignature==',
        ])->assertStatus(403);
    }

    public function test_missing_signature_returns_403(): void
    {
        $this->postJson('/api/webhook/line', $this->buildPayload())
            ->assertStatus(403);
    }

    public function test_text_message_creates_message_record(): void
    {
        $payload = $this->buildPayload(userId: 'UserA', type: 'text', text: 'Hello LINE');
        $body = json_encode($payload);

        $this->postJson('/api/webhook/line', $payload, [
            'X-Line-Signature' => $this->makeSignature($body),
        ]);

        $this->assertDatabaseHas('messages', [
            'type' => MessageType::Text->value,
            'content' => 'Hello LINE',
        ]);
    }

    public function test_sticker_message_creates_message_record(): void
    {
        $payload = [
            'destination' => 'Uline123',
            'events' => [
                [
                    'type' => 'message',
                    'timestamp' => now()->timestamp * 1000,
                    'source' => ['userId' => 'UserA'],
                    'message' => ['type' => 'sticker', 'id' => 'msg2', 'packageId' => '1', 'stickerId' => '1'],
                ],
            ],
        ];
        $body = json_encode($payload);

        $this->postJson('/api/webhook/line', $payload, [
            'X-Line-Signature' => $this->makeSignature($body),
        ]);

        $this->assertDatabaseHas('messages', ['type' => MessageType::Sticker->value]);
    }

    public function test_non_message_event_is_ignored(): void
    {
        $payload = [
            'destination' => 'Uline123',
            'events' => [
                [
                    'type' => 'follow',
                    'timestamp' => now()->timestamp * 1000,
                    'source' => ['userId' => 'UserA'],
                ],
            ],
        ];
        $body = json_encode($payload);

        $this->postJson('/api/webhook/line', $payload, [
            'X-Line-Signature' => $this->makeSignature($body),
        ])->assertStatus(200);

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_inactive_channel_returns_200_without_creating_message(): void
    {
        $this->channel->update(['is_active' => false]);

        $payload = $this->buildPayload();
        $body = json_encode($payload);

        $this->postJson('/api/webhook/line', $payload, [
            'X-Line-Signature' => $this->makeSignature($body),
        ])->assertStatus(200);

        $this->assertDatabaseCount('messages', 0);
    }
}
