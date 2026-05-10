<?php

namespace Tests\Feature;

use App\Enums\ConversationStatus;
use App\Filament\Resources\Conversations\Pages\ViewConversation;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Note;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConversationViewTest extends TestCase
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
        $contact = Contact::factory()->create([
            'workspace_id' => $this->workspace->id,
            'channel_id' => $channel->id,
        ]);
        $this->conversation = Conversation::factory()->create([
            'workspace_id' => $this->workspace->id,
            'channel_id' => $channel->id,
            'contact_id' => $contact->id,
        ]);
    }

    public function test_view_page_loads_successfully(): void
    {
        Livewire::test(ViewConversation::class, ['record' => $this->conversation->id])
            ->assertOk();
    }

    public function test_can_change_status(): void
    {
        Livewire::test(ViewConversation::class, ['record' => $this->conversation->id])
            ->callAction('changeStatus', data: ['status' => ConversationStatus::Resolved->value])
            ->assertHasNoActionErrors();

        $this->assertEquals(ConversationStatus::Resolved, $this->conversation->fresh()->status);
    }

    public function test_change_status_requires_status(): void
    {
        Livewire::test(ViewConversation::class, ['record' => $this->conversation->id])
            ->callAction('changeStatus', data: ['status' => null])
            ->assertHasActionErrors(['status' => 'required']);
    }

    public function test_can_assign_conversation(): void
    {
        Livewire::test(ViewConversation::class, ['record' => $this->conversation->id])
            ->callAction('assign', data: ['assigned_to' => $this->agent->id])
            ->assertHasNoActionErrors();

        $this->assertEquals($this->agent->id, $this->conversation->fresh()->assigned_to);
    }

    public function test_can_add_internal_note(): void
    {
        Livewire::test(ViewConversation::class, ['record' => $this->conversation->id])
            ->callAction('addNote', data: ['body' => '這是一個測試備註'])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(Note::class, [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->agent->id,
            'body' => '這是一個測試備註',
        ]);
    }

    public function test_add_note_requires_body(): void
    {
        Livewire::test(ViewConversation::class, ['record' => $this->conversation->id])
            ->callAction('addNote', data: ['body' => ''])
            ->assertHasActionErrors(['body' => 'required']);
    }
}
