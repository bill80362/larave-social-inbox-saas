<?php

namespace Tests\Feature\Reply;

use App\Enums\MessageDirection;
use App\Enums\Platform;
use App\Filament\Resources\Conversations\Pages\ViewConversation;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ReplyUiTest extends TestCase
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

        $channel = Channel::factory()->create([
            'workspace_id' => $this->workspace->id,
            'platform' => Platform::Line,
            'credentials' => json_encode(['channel_access_token' => 'test_token']),
        ]);
        $contact = Contact::factory()->create([
            'workspace_id' => $this->workspace->id,
            'channel_id' => $channel->id,
            'platform_user_id' => 'Utest',
        ]);
        $this->conversation = Conversation::factory()->create([
            'workspace_id' => $this->workspace->id,
            'channel_id' => $channel->id,
            'contact_id' => $contact->id,
        ]);
    }

    public function test_reply_action_sends_message_and_saves_to_db(): void
    {
        Http::fake([
            'api.line.me/*' => Http::response(['sentMessages' => [['id' => 'line-001']]], 200),
        ]);

        Livewire::test(ViewConversation::class, ['record' => $this->conversation->id])
            ->callAction('reply', data: ['content' => 'Hello customer!'])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->conversation->id,
            'direction' => MessageDirection::Outbound->value,
            'content' => 'Hello customer!',
            'sender_type' => 'agent',
            'sender_id' => $this->agent->id,
        ]);
    }

    public function test_reply_action_requires_content(): void
    {
        Livewire::test(ViewConversation::class, ['record' => $this->conversation->id])
            ->callAction('reply', data: ['content' => ''])
            ->assertHasActionErrors(['content' => 'required']);

        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $this->conversation->id,
            'direction' => MessageDirection::Outbound->value,
        ]);
    }
}
