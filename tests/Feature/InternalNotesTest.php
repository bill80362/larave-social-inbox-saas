<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Note;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_associates_to_conversation_and_user(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $this->actingAs($user);

        $channel = Channel::factory()->create(['workspace_id' => $workspace->id]);
        $contact = Contact::factory()->create(['workspace_id' => $workspace->id, 'channel_id' => $channel->id]);
        $conversation = Conversation::factory()->create([
            'workspace_id' => $workspace->id,
            'channel_id' => $channel->id,
            'contact_id' => $contact->id,
        ]);

        $note = Note::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'body' => 'Internal note for agent.',
        ]);

        $this->assertEquals($conversation->id, $note->conversation_id);
        $this->assertEquals($user->id, $note->user_id);
        $this->assertEquals('Internal note for agent.', $note->body);
    }

    public function test_conversation_has_many_notes(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $this->actingAs($user);

        $channel = Channel::factory()->create(['workspace_id' => $workspace->id]);
        $contact = Contact::factory()->create(['workspace_id' => $workspace->id, 'channel_id' => $channel->id]);
        $conversation = Conversation::factory()->create([
            'workspace_id' => $workspace->id,
            'channel_id' => $channel->id,
            'contact_id' => $contact->id,
        ]);

        Note::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        $this->assertCount(3, $conversation->notes);
    }
}
