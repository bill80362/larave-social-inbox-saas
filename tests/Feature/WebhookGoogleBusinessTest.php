<?php

namespace Tests\Feature;

use App\Enums\MessageType;
use App\Enums\Platform;
use App\Models\Channel;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookGoogleBusinessTest extends TestCase
{
    use RefreshDatabase;

    private Channel $channel;

    /** @var \OpenSSLAsymmetricKey */
    private mixed $privateKey;

    private string $kid = 'testkey1';

    protected function setUp(): void
    {
        parent::setUp();

        $workspace = Workspace::factory()->create();

        $this->channel = Channel::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::GoogleBusiness,
            'platform_account_id' => 'acct_123',
            'credentials' => json_encode(['project_id' => 'test']),
            'is_active' => true,
        ]);

        $this->privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function makeJwt(array $payload = []): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT', 'kid' => $this->kid]));
        $claims = $this->base64UrlEncode(json_encode(array_merge([
            'iss' => 'https://accounts.google.com',
            'aud' => 'test',
            'iat' => now()->timestamp,
            'exp' => now()->addHour()->timestamp,
        ], $payload)));

        $data = $header.'.'.$claims;
        openssl_sign($data, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);

        return $data.'.'.$this->base64UrlEncode($signature);
    }

    private function mockGoogleJwks(): void
    {
        $details = openssl_pkey_get_details($this->privateKey);
        $rsa = $details['rsa'];

        $jwks = [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'alg' => 'RS256',
                    'use' => 'sig',
                    'kid' => $this->kid,
                    'n' => rtrim(strtr(base64_encode($rsa['n']), '+/', '-_'), '='),
                    'e' => rtrim(strtr(base64_encode($rsa['e']), '+/', '-_'), '='),
                ],
            ],
        ];

        Cache::forget('google_public_keys');
        Http::fake(['https://www.googleapis.com/oauth2/v3/certs' => Http::response($jwks, 200)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(): array
    {
        return [
            'name' => 'accounts/acct_123/locations/loc1/reviews/rev1',
            'reviewId' => 'rev1',
            'reviewer' => ['displayName' => 'Test User'],
            'starRating' => 'FIVE',
            'comment' => 'Great service!',
            'createTime' => now()->toIso8601String(),
        ];
    }

    public function test_valid_google_webhook_returns_200(): void
    {
        $this->mockGoogleJwks();

        $this->postJson('/api/webhook/google_business', $this->buildPayload(), [
            'Authorization' => 'Bearer '.$this->makeJwt(),
        ])->assertStatus(200);
    }

    public function test_missing_bearer_token_returns_403(): void
    {
        $this->postJson('/api/webhook/google_business', $this->buildPayload())
            ->assertStatus(403);
    }

    public function test_invalid_jwt_returns_403(): void
    {
        $this->mockGoogleJwks();

        $this->postJson('/api/webhook/google_business', $this->buildPayload(), [
            'Authorization' => 'Bearer invalid.jwt.token',
        ])->assertStatus(403);
    }

    public function test_review_payload_creates_review_message(): void
    {
        $this->mockGoogleJwks();

        $this->postJson('/api/webhook/google_business', $this->buildPayload(), [
            'Authorization' => 'Bearer '.$this->makeJwt(),
        ]);

        $this->assertDatabaseHas('messages', [
            'type' => MessageType::Review->value,
            'content' => 'Great service!',
        ]);
    }

    public function test_inactive_channel_returns_200_without_creating_message(): void
    {
        $this->channel->update(['is_active' => false]);
        $this->mockGoogleJwks();

        $this->postJson('/api/webhook/google_business', $this->buildPayload(), [
            'Authorization' => 'Bearer '.$this->makeJwt(),
        ])->assertStatus(200);

        $this->assertDatabaseCount('messages', 0);
    }
}
