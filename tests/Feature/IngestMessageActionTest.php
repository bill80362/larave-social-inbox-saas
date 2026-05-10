<?php

namespace Tests\Feature;

use App\Actions\Webhook\IngestMessageAction;
use App\Enums\ConversationStatus;
use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Enums\Platform;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngestMessageActionTest extends TestCase
{
    use RefreshDatabase;

    private Channel $channel;

    private IngestMessageAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $workspace = Workspace::factory()->create();

        $this->channel = Channel::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Line,
            'is_active' => true,
        ]);

        $this->action = new IngestMessageAction;
    }

    public function test_creates_contact_conversation_and_message_for_new_user(): void
    {
        ($this->action)($this->channel, 'newuser1', MessageType::Text, 'Hello', now());

        $this->assertDatabaseHas('contacts', [
            'channel_id' => $this->channel->id,
            'platform_user_id' => 'newuser1',
        ]);

        $this->assertDatabaseHas('conversations', [
            'channel_id' => $this->channel->id,
            'status' => ConversationStatus::Open->value,
        ]);

        $this->assertDatabaseHas('messages', [
            'direction' => MessageDirection::Inbound->value,
            'type' => MessageType::Text->value,
            'content' => 'Hello',
        ]);
    }

    public function test_reuses_existing_open_conversation_for_subsequent_messages(): void
    {
        ($this->action)($this->channel, 'user1', MessageType::Text, 'First', now());
        ($this->action)($this->channel, 'user1', MessageType::Text, 'Second', now());

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('messages', 2);
    }

    public function test_creates_new_conversation_when_no_open_conversation_exists(): void
    {
        $contact = Contact::factory()->create([
            'workspace_id' => $this->channel->workspace_id,
            'channel_id' => $this->channel->id,
            'platform_user_id' => 'user1',
        ]);

        Conversation::factory()->create([
            'workspace_id' => $this->channel->workspace_id,
            'channel_id' => $this->channel->id,
            'contact_id' => $contact->id,
            'status' => ConversationStatus::Resolved,
        ]);

        ($this->action)($this->channel, 'user1', MessageType::Text, 'New message', now());

        $this->assertDatabaseCount('conversations', 2);
        $this->assertDatabaseHas('conversations', [
            'contact_id' => $contact->id,
            'status' => ConversationStatus::Open->value,
        ]);
    }

    public function test_reuses_pending_conversation(): void
    {
        $contact = Contact::factory()->create([
            'workspace_id' => $this->channel->workspace_id,
            'channel_id' => $this->channel->id,
            'platform_user_id' => 'user1',
        ]);

        $existing = Conversation::factory()->create([
            'workspace_id' => $this->channel->workspace_id,
            'channel_id' => $this->channel->id,
            'contact_id' => $contact->id,
            'status' => ConversationStatus::Pending,
        ]);

        ($this->action)($this->channel, 'user1', MessageType::Text, 'Hi again', now());

        $this->assertDatabaseCount('conversations', 1);

        $message = Message::first();
        $this->assertEquals($existing->id, $message->conversation_id);
    }

    public function test_message_is_created_with_inbound_direction(): void
    {
        $message = ($this->action)($this->channel, 'user1', MessageType::Text, 'Test', now());

        $this->assertEquals(MessageDirection::Inbound, $message->direction);
        $this->assertEquals('contact', $message->sender_type);
    }
}
