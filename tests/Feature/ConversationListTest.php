<?php

namespace Tests\Feature;

use App\Enums\ConversationStatus;
use App\Enums\Platform;
use App\Filament\Resources\Conversations\Pages\ListConversations;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConversationListTest extends TestCase
{
    use RefreshDatabase;

    private Workspace $workspace;

    private User $agent;

    private Channel $channel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspace = Workspace::factory()->create();
        $this->agent = User::factory()->create(['workspace_id' => $this->workspace->id]);
        $this->actingAs($this->agent);

        $this->channel = Channel::factory()->create([
            'workspace_id' => $this->workspace->id,
            'platform' => Platform::Instagram,
        ]);
    }

    private function makeConversation(array $attributes = []): Conversation
    {
        $contact = Contact::factory()->create([
            'workspace_id' => $this->workspace->id,
            'channel_id' => $this->channel->id,
        ]);

        return Conversation::factory()->create(array_merge([
            'workspace_id' => $this->workspace->id,
            'channel_id' => $this->channel->id,
            'contact_id' => $contact->id,
        ], $attributes));
    }

    public function test_list_page_loads_successfully(): void
    {
        Livewire::test(ListConversations::class)
            ->assertOk();
    }

    public function test_conversations_are_visible_in_table(): void
    {
        $conversation = $this->makeConversation();

        Livewire::test(ListConversations::class)
            ->assertCanSeeTableRecords([$conversation]);
    }

    public function test_conversations_from_other_workspace_are_not_visible(): void
    {
        $otherWorkspace = Workspace::factory()->create();
        $otherChannel = Channel::factory()->create(['workspace_id' => $otherWorkspace->id]);
        $otherContact = Contact::factory()->create(['workspace_id' => $otherWorkspace->id, 'channel_id' => $otherChannel->id]);
        $otherConversation = Conversation::factory()->create([
            'workspace_id' => $otherWorkspace->id,
            'channel_id' => $otherChannel->id,
            'contact_id' => $otherContact->id,
        ]);

        Livewire::test(ListConversations::class)
            ->assertCanNotSeeTableRecords([$otherConversation]);
    }

    public function test_can_filter_by_status(): void
    {
        $open = $this->makeConversation(['status' => ConversationStatus::Open]);
        $resolved = $this->makeConversation(['status' => ConversationStatus::Resolved]);

        Livewire::test(ListConversations::class)
            ->filterTable('status', ConversationStatus::Open->value)
            ->assertCanSeeTableRecords([$open])
            ->assertCanNotSeeTableRecords([$resolved]);
    }

    public function test_can_filter_by_assigned_to(): void
    {
        $assigned = $this->makeConversation(['assigned_to' => $this->agent->id]);
        $unassigned = $this->makeConversation(['assigned_to' => null]);

        Livewire::test(ListConversations::class)
            ->filterTable('assigned_to', $this->agent->id)
            ->assertCanSeeTableRecords([$assigned])
            ->assertCanNotSeeTableRecords([$unassigned]);
    }
}
