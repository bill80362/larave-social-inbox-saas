<?php

namespace Tests\Feature;

use App\Enums\MessageType;
use App\Enums\Platform;
use App\Models\Channel;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookInstagramTest extends TestCase
{
    use RefreshDatabase;

    private Channel $channel;

    private string $appSecret = 'ig_app_secret';

    protected function setUp(): void
    {
        parent::setUp();

        $workspace = Workspace::factory()->create();

        $this->channel = Channel::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Instagram,
            'platform_account_id' => 'ig_account_123',
            'credentials' => json_encode(['app_secret' => $this->appSecret]),
            'is_active' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(string $senderId = 'user1', string $text = 'Hi'): array
    {
        return [
            'object' => 'instagram',
            'entry' => [
                [
                    'id' => 'ig_account_123',
                    'messaging' => [
                        [
                            'sender' => ['id' => $senderId],
                            'recipient' => ['id' => 'ig_account_123'],
                            'timestamp' => now()->timestamp * 1000,
                            'message' => ['mid' => 'mid1', 'text' => $text],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function makeSignature(string $body): string
    {
        return 'sha256='.hash_hmac('sha256', $body, $this->appSecret);
    }

    public function test_valid_instagram_webhook_returns_200(): void
    {
        $payload = $this->buildPayload();
        $body = json_encode($payload);

        $this->postJson('/api/webhook/instagram', $payload, [
            'X-Hub-Signature-256' => $this->makeSignature($body),
        ])->assertStatus(200);
    }

    public function test_invalid_signature_returns_403(): void
    {
        $this->postJson('/api/webhook/instagram', $this->buildPayload(), [
            'X-Hub-Signature-256' => 'sha256=invalidsig',
        ])->assertStatus(403);
    }

    public function test_missing_signature_returns_403(): void
    {
        $this->postJson('/api/webhook/instagram', $this->buildPayload())
            ->assertStatus(403);
    }

    public function test_text_message_creates_message_record(): void
    {
        $payload = $this->buildPayload(senderId: 'user1', text: 'Hello IG');
        $body = json_encode($payload);

        $this->postJson('/api/webhook/instagram', $payload, [
            'X-Hub-Signature-256' => $this->makeSignature($body),
        ]);

        $this->assertDatabaseHas('messages', [
            'type' => MessageType::Text->value,
            'content' => 'Hello IG',
        ]);
    }

    public function test_image_attachment_creates_image_message(): void
    {
        $payload = [
            'object' => 'instagram',
            'entry' => [
                [
                    'id' => 'ig_account_123',
                    'messaging' => [
                        [
                            'sender' => ['id' => 'user1'],
                            'recipient' => ['id' => 'ig_account_123'],
                            'timestamp' => now()->timestamp * 1000,
                            'message' => [
                                'mid' => 'mid2',
                                'attachments' => [['type' => 'image', 'payload' => ['url' => 'http://img']]],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $body = json_encode($payload);

        $this->postJson('/api/webhook/instagram', $payload, [
            'X-Hub-Signature-256' => $this->makeSignature($body),
        ]);

        $this->assertDatabaseHas('messages', ['type' => MessageType::Image->value]);
    }
}
