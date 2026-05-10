<?php

namespace Tests\Feature\Reply;

use App\Actions\Reply\Drivers\InstagramReplyDriver;
use App\Enums\Platform;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class InstagramReplyDriverTest extends TestCase
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
            'platform' => Platform::Instagram,
            'credentials' => json_encode(['page_access_token' => 'ig_token']),
        ]);
        $this->contact = Contact::factory()->create([
            'workspace_id' => $workspace->id,
            'channel_id' => $this->channel->id,
            'platform_user_id' => 'ig_igsid_001',
        ]);
    }

    public function test_sends_message_and_returns_message_id(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['message_id' => 'ig-xyz'], 200),
        ]);

        $id = (new InstagramReplyDriver)->send($this->channel, $this->contact, 'Hi IG!');

        $this->assertSame('ig-xyz', $id);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'graph.facebook.com')
            && $request['recipient']['id'] === 'ig_igsid_001'
            && $request['message']['text'] === 'Hi IG!'
        );
    }

    public function test_throws_when_page_access_token_is_missing(): void
    {
        $this->channel->update(['credentials' => json_encode([])]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/page_access_token/');

        (new InstagramReplyDriver)->send($this->channel->fresh(), $this->contact, 'Hi');
    }

    public function test_throws_when_api_returns_error(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['error' => ['message' => 'Permission denied']], 403),
        ]);

        $this->expectException(RequestException::class);

        (new InstagramReplyDriver)->send($this->channel, $this->contact, 'Hi');
    }
}
