<?php

namespace Tests\Feature;

use App\Enums\MessageType;
use App\Enums\Platform;
use App\Models\Channel;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookFacebookTest extends TestCase
{
    use RefreshDatabase;

    private Channel $channel;

    private string $appSecret = 'fb_app_secret';

    protected function setUp(): void
    {
        parent::setUp();

        $workspace = Workspace::factory()->create();

        $this->channel = Channel::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Facebook,
            'platform_account_id' => 'page123',
            'credentials' => json_encode(['app_secret' => $this->appSecret]),
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    private function buildPayload(string $senderId = 'user1', string $text = 'Hi'): array
    {
        return [
            'object' => 'page',
            'entry' => [
                [
                    'id' => 'page123',
                    'messaging' => [
                        [
                            'sender' => ['id' => $senderId],
                            'recipient' => ['id' => 'page123'],
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

    public function test_valid_facebook_webhook_returns_200(): void
    {
        $payload = $this->buildPayload();
        $body = json_encode($payload);

        $this->postJson('/api/webhook/facebook', $payload, [
            'X-Hub-Signature-256' => $this->makeSignature($body),
        ])->assertStatus(200);
    }

    public function test_invalid_signature_returns_403(): void
    {
        $this->postJson('/api/webhook/facebook', $this->buildPayload(), [
            'X-Hub-Signature-256' => 'sha256=invalidsig',
        ])->assertStatus(403);
    }

    public function test_missing_signature_returns_403(): void
    {
        $this->postJson('/api/webhook/facebook', $this->buildPayload())
            ->assertStatus(403);
    }

    public function test_text_message_creates_message_record(): void
    {
        $payload = $this->buildPayload(senderId: 'user1', text: 'Hello Facebook');
        $body = json_encode($payload);

        $this->postJson('/api/webhook/facebook', $payload, [
            'X-Hub-Signature-256' => $this->makeSignature($body),
        ]);

        $this->assertDatabaseHas('messages', [
            'type' => MessageType::Text->value,
            'content' => 'Hello Facebook',
        ]);
    }

    public function test_image_attachment_creates_image_message(): void
    {
        $payload = [
            'object' => 'page',
            'entry' => [
                [
                    'id' => 'page123',
                    'messaging' => [
                        [
                            'sender' => ['id' => 'user1'],
                            'recipient' => ['id' => 'page123'],
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

        $this->postJson('/api/webhook/facebook', $payload, [
            'X-Hub-Signature-256' => $this->makeSignature($body),
        ]);

        $this->assertDatabaseHas('messages', ['type' => MessageType::Image->value]);
    }

    public function test_challenge_get_returns_hub_challenge(): void
    {
        config(['services.facebook.webhook_verify_token' => 'mytoken']);

        $this->get('/api/webhook/facebook?hub_mode=subscribe&hub_verify_token=mytoken&hub_challenge=CHALLENGE123')
            ->assertStatus(200)
            ->assertSee('CHALLENGE123');
    }

    public function test_challenge_with_wrong_token_returns_403(): void
    {
        config(['services.facebook.webhook_verify_token' => 'mytoken']);

        $this->get('/api/webhook/facebook?hub_mode=subscribe&hub_verify_token=wrongtoken&hub_challenge=X')
            ->assertStatus(403);
    }
}
