<?php

namespace Tests\Feature\Reply;

use App\Actions\Reply\Drivers\LineReplyDriver;
use App\Enums\Platform;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class LineReplyDriverTest extends TestCase
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
            'platform' => Platform::Line,
            'credentials' => json_encode(['channel_access_token' => 'test_token']),
        ]);
        $this->contact = Contact::factory()->create([
            'workspace_id' => $workspace->id,
            'channel_id' => $this->channel->id,
            'platform_user_id' => 'Utest123',
        ]);
    }

    public function test_sends_message_and_returns_platform_message_id(): void
    {
        Http::fake([
            'api.line.me/*' => Http::response(['sentMessages' => [['id' => 'line-abc']]], 200),
        ]);

        $driver = new LineReplyDriver;
        $id = $driver->send($this->channel, $this->contact, 'Hello!');

        $this->assertSame('line-abc', $id);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.line.me')
            && $request['to'] === 'Utest123'
            && $request['messages'][0]['text'] === 'Hello!'
        );
    }

    public function test_throws_when_access_token_is_missing(): void
    {
        $this->channel->update(['credentials' => json_encode([])]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/channel_access_token/');

        (new LineReplyDriver)->send($this->channel->fresh(), $this->contact, 'Hello');
    }

    public function test_throws_when_api_returns_error(): void
    {
        Http::fake([
            'api.line.me/*' => Http::response(['message' => 'Invalid token'], 401),
        ]);

        $this->expectException(RequestException::class);

        (new LineReplyDriver)->send($this->channel, $this->contact, 'Hello');
    }
}
