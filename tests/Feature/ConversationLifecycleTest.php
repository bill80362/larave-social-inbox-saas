<?php

namespace Tests\Feature;

use App\Enums\ConversationStatus;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private Workspace $workspace;

    private User $agent;

    private Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspace = Workspace::factory()->create();
        $this->agent = User::factory()->create(['workspace_id' => $this->workspace->id]);
        $this->actingAs($this->agent);

        $channel = Channel::factory()->create(['workspace_id' => $this->workspace->id]);
        $contact = Contact::factory()->create(['workspace_id' => $this->workspace->id, 'channel_id' => $channel->id]);
        $this->conversation = Conversation::factory()->create([
            'workspace_id' => $this->workspace->id,
            'channel_id' => $channel->id,
            'contact_id' => $contact->id,
        ]);
    }

    public function test_conversation_starts_as_open(): void
    {
        $this->assertEquals(ConversationStatus::Open, $this->conversation->status);
    }

    public function test_status_transitions_open_to_pending_to_resolved(): void
    {
        $this->conversation->update(['status' => ConversationStatus::Pending]);
        $this->assertEquals(ConversationStatus::Pending, $this->conversation->fresh()->status);

        $this->conversation->update(['status' => ConversationStatus::Resolved]);
        $this->assertEquals(ConversationStatus::Resolved, $this->conversation->fresh()->status);
    }

    public function test_status_can_reopen_from_resolved(): void
    {
        $this->conversation->update(['status' => ConversationStatus::Resolved]);
        $this->conversation->update(['status' => ConversationStatus::Open]);

        $this->assertEquals(ConversationStatus::Open, $this->conversation->fresh()->status);
    }

    public function test_assign_conversation_to_agent(): void
    {
        $this->conversation->update(['assigned_to' => $this->agent->id]);

        $this->assertEquals($this->agent->id, $this->conversation->fresh()->assigned_to);
    }

    public function test_unassign_conversation(): void
    {
        $this->conversation->update(['assigned_to' => $this->agent->id]);
        $this->conversation->update(['assigned_to' => null]);

        $this->assertNull($this->conversation->fresh()->assigned_to);
    }

    public function test_last_message_at_updates_when_message_created(): void
    {
        $this->assertNull($this->conversation->fresh()->last_message_at);

        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sent_at' => now(),
        ]);

        $this->assertNotNull($this->conversation->fresh()->last_message_at);
        $this->assertEquals(
            $message->sent_at->toDateTimeString(),
            $this->conversation->fresh()->last_message_at->toDateTimeString()
        );
    }
}
