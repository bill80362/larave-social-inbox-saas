<?php

namespace Tests\Feature\Reply;

use App\Actions\Reply\Drivers\FacebookReplyDriver;
use App\Enums\Platform;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class FacebookReplyDriverTest extends TestCase
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
            'platform' => Platform::Facebook,
            'credentials' => json_encode(['page_access_token' => 'fb_token']),
        ]);
        $this->contact = Contact::factory()->create([
            'workspace_id' => $workspace->id,
            'channel_id' => $this->channel->id,
            'platform_user_id' => 'fb_psid_001',
        ]);
    }

    public function test_sends_message_and_returns_message_id(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['message_id' => 'fb-xyz'], 200),
        ]);

        $id = (new FacebookReplyDriver)->send($this->channel, $this->contact, 'Hi FB!');

        $this->assertSame('fb-xyz', $id);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'graph.facebook.com')
            && $request['recipient']['id'] === 'fb_psid_001'
            && $request['message']['text'] === 'Hi FB!'
        );
    }

    public function test_throws_when_page_access_token_is_missing(): void
    {
        $this->channel->update(['credentials' => json_encode([])]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/page_access_token/');

        (new FacebookReplyDriver)->send($this->channel->fresh(), $this->contact, 'Hi');
    }

    public function test_throws_when_api_returns_error(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid token']], 400),
        ]);

        $this->expectException(RequestException::class);

        (new FacebookReplyDriver)->send($this->channel, $this->contact, 'Hi');
    }
}
