<?php

namespace Tests\Feature;

use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MessageContentTest extends TestCase
{
    use RefreshDatabase;

    private Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        $workspace = Workspace::factory()->create();
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $this->actingAs($user);

        $channel = Channel::factory()->create(['workspace_id' => $workspace->id]);
        $contact = Contact::factory()->create(['workspace_id' => $workspace->id, 'channel_id' => $channel->id]);
        $this->conversation = Conversation::factory()->create([
            'workspace_id' => $workspace->id,
            'channel_id' => $channel->id,
            'contact_id' => $contact->id,
        ]);
    }

    #[DataProvider('messageTypeProvider')]
    public function test_all_message_types_can_be_created(MessageType $type): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'type' => $type,
        ]);

        $this->assertEquals($type, $message->fresh()->type);
    }

    public static function messageTypeProvider(): array
    {
        return array_map(fn ($case) => [$case], MessageType::cases());
    }

    public function test_inbound_message_direction(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'direction' => MessageDirection::Inbound,
        ]);

        $this->assertEquals(MessageDirection::Inbound, $message->fresh()->direction);
    }

    public function test_outbound_message_direction(): void
    {
        $message = Message::factory()->outbound()->create([
            'conversation_id' => $this->conversation->id,
        ]);

        $this->assertEquals(MessageDirection::Outbound, $message->fresh()->direction);
    }

    public function test_message_with_attachments_stored_as_array(): void
    {
        $attachments = [['url' => 'https://example.com/image.jpg', 'type' => 'image']];

        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'type' => MessageType::Image,
            'attachments' => $attachments,
        ]);

        $this->assertEquals($attachments, $message->fresh()->attachments);
    }
}
