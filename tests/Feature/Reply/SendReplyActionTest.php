<?php

namespace Tests\Feature\Reply;

use App\Actions\Reply\SendReplyAction;
use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Enums\Platform;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SendReplyActionTest extends TestCase
{
    use RefreshDatabase;

    private Workspace $workspace;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspace = Workspace::factory()->create();
        $this->agent = User::factory()->create(['workspace_id' => $this->workspace->id]);
    }

    private function makeConversation(Platform $platform, array $credentials, string $platformUserId = 'user_123'): Conversation
    {
        $channel = Channel::factory()->create([
            'workspace_id' => $this->workspace->id,
            'platform' => $platform,
            'platform_account_id' => 'accounts/123/locations/456',
            'credentials' => json_encode($credentials),
        ]);
        $contact = Contact::factory()->create([
            'workspace_id' => $this->workspace->id,
            'channel_id' => $channel->id,
            'platform_user_id' => $platformUserId,
        ]);

        return Conversation::factory()->create([
            'workspace_id' => $this->workspace->id,
            'channel_id' => $channel->id,
            'contact_id' => $contact->id,
        ]);
    }

    public function test_routes_line_platform_to_line_driver(): void
    {
        Http::fake([
            'api.line.me/*' => Http::response(['sentMessages' => [['id' => 'line-msg-123']]], 200),
        ]);

        $conversation = $this->makeConversation(Platform::Line, ['channel_access_token' => 'test_token']);
        $message = (new SendReplyAction)($conversation, 'Hello LINE', $this->agent);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'direction' => MessageDirection::Outbound->value,
            'type' => MessageType::Text->value,
            'content' => 'Hello LINE',
            'sender_type' => 'agent',
            'sender_id' => $this->agent->id,
            'platform_message_id' => 'line-msg-123',
        ]);
    }

    public function test_routes_facebook_platform_to_facebook_driver(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['message_id' => 'fb-msg-456'], 200),
        ]);

        $conversation = $this->makeConversation(Platform::Facebook, ['page_access_token' => 'fb_token']);
        (new SendReplyAction)($conversation, 'Hello Facebook', $this->agent);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'direction' => MessageDirection::Outbound->value,
            'platform_message_id' => 'fb-msg-456',
        ]);
    }

    public function test_routes_instagram_platform_to_instagram_driver(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['message_id' => 'ig-msg-789'], 200),
        ]);

        $conversation = $this->makeConversation(Platform::Instagram, ['page_access_token' => 'ig_token']);
        (new SendReplyAction)($conversation, 'Hello Instagram', $this->agent);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'direction' => MessageDirection::Outbound->value,
            'platform_message_id' => 'ig-msg-789',
        ]);
    }

    public function test_routes_google_business_platform_to_google_driver(): void
    {
        Http::fake([
            'mybusiness.googleapis.com/*' => Http::response(['name' => 'accounts/123/reviews/r1/reply'], 200),
        ]);

        $conversation = $this->makeConversation(
            Platform::GoogleBusiness,
            ['access_token' => 'google_token'],
            'review_id_001'
        );
        (new SendReplyAction)($conversation, 'Thank you for your review!', $this->agent);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'direction' => MessageDirection::Outbound->value,
            'platform_message_id' => 'accounts/123/reviews/r1/reply',
        ]);
    }

    public function test_outbound_message_has_correct_sender_info(): void
    {
        Http::fake([
            'api.line.me/*' => Http::response(['sentMessages' => [['id' => 'msg-001']]], 200),
        ]);

        $conversation = $this->makeConversation(Platform::Line, ['channel_access_token' => 'token']);
        $message = (new SendReplyAction)($conversation, 'Test content', $this->agent);

        $this->assertSame(MessageDirection::Outbound, $message->direction);
        $this->assertSame(MessageType::Text, $message->type);
        $this->assertSame('agent', $message->sender_type);
        $this->assertSame($this->agent->id, $message->sender_id);
    }
}
