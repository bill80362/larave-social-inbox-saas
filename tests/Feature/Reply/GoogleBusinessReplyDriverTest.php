<?php

namespace Tests\Feature\Reply;

use App\Actions\Reply\Drivers\GoogleBusinessReplyDriver;
use App\Enums\Platform;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GoogleBusinessReplyDriverTest extends TestCase
{
    use RefreshDatabase;

    private Channel $channel;

    private Contact $contact;

    protected function setUp(): void
    {
        parent::setUp();

        $workspace = Workspace::factory()->create();
        $this->channel = Channel::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::GoogleBusiness,
            'platform_account_id' => 'accounts/123/locations/456',
            'credentials' => json_encode(['access_token' => 'google_token']),
        ]);
        $this->contact = Contact::factory()->create([
            'workspace_id' => $workspace->id,
            'channel_id' => $this->channel->id,
            'platform_user_id' => 'review_abc',
        ]);
    }

    public function test_sends_reply_and_returns_reply_name(): void
    {
        Http::fake([
            'mybusiness.googleapis.com/*' => Http::response(['name' => 'accounts/123/locations/456/reviews/review_abc/reply'], 200),
        ]);

        $id = (new GoogleBusinessReplyDriver)->send($this->channel, $this->contact, 'Thank you!');

        $this->assertSame('accounts/123/locations/456/reviews/review_abc/reply', $id);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'mybusiness.googleapis.com')
            && str_contains($request->url(), 'review_abc')
            && $request['comment'] === 'Thank you!'
        );
    }

    public function test_throws_when_access_token_is_missing(): void
    {
        $this->channel->update(['credentials' => json_encode([])]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/access_token/');

        (new GoogleBusinessReplyDriver)->send($this->channel->fresh(), $this->contact, 'Thanks');
    }

    public function test_throws_when_api_returns_error(): void
    {
        Http::fake([
            'mybusiness.googleapis.com/*' => Http::response(['error' => ['message' => 'Unauthorized']], 401),
        ]);

        $this->expectException(RequestException::class);

        (new GoogleBusinessReplyDriver)->send($this->channel, $this->contact, 'Thanks');
    }
}
