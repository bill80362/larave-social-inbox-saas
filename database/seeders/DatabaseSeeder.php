<?php

namespace Database\Seeders;

use App\Enums\ConversationStatus;
use App\Enums\MessageDirection;
use App\Enums\Platform;
use App\Enums\UserRole;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Note;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1 Workspace
        $workspace = Workspace::factory()->create([
            'name' => 'Demo 企業',
            'slug' => 'demo',
        ]);

        // 1 Owner + 1 Agent
        $owner = User::factory()->create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'workspace_id' => $workspace->id,
            'role' => UserRole::Owner,
        ]);

        $agent = User::factory()->create([
            'name' => 'Agent User',
            'email' => 'agent@example.com',
            'workspace_id' => $workspace->id,
            'role' => UserRole::Agent,
        ]);

        // 2 Channels (LINE + Google Business)
        $lineChannel = Channel::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Line,
            'name' => 'Demo LINE 官方帳號',
            'platform_account_id' => 'line-demo-001',
        ]);

        $googleChannel = Channel::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::GoogleBusiness,
            'name' => 'Demo Google 商家',
            'platform_account_id' => 'google-demo-001',
        ]);

        // 5 Contacts (3 LINE + 2 Google)
        $lineContacts = Contact::factory(3)->create([
            'workspace_id' => $workspace->id,
            'channel_id' => $lineChannel->id,
        ]);

        $googleContacts = Contact::factory(2)->create([
            'workspace_id' => $workspace->id,
            'channel_id' => $googleChannel->id,
        ]);

        $allContacts = $lineContacts->merge($googleContacts);

        // 10 Conversations (open/pending/resolved)
        $statuses = [
            ConversationStatus::Open,
            ConversationStatus::Open,
            ConversationStatus::Open,
            ConversationStatus::Open,
            ConversationStatus::Pending,
            ConversationStatus::Pending,
            ConversationStatus::Pending,
            ConversationStatus::Resolved,
            ConversationStatus::Resolved,
            ConversationStatus::Resolved,
        ];

        foreach ($statuses as $index => $status) {
            $contact = $allContacts[$index % $allContacts->count()];
            $channel = $contact->channel_id === $lineChannel->id ? $lineChannel : $googleChannel;

            $conversation = Conversation::factory()->create([
                'workspace_id' => $workspace->id,
                'channel_id' => $channel->id,
                'contact_id' => $contact->id,
                'status' => $status,
                'assigned_to' => $index % 3 === 0 ? $agent->id : null,
            ]);

            // 3–5 Messages per conversation
            $messageCount = fake()->numberBetween(3, 5);
            for ($m = 0; $m < $messageCount; $m++) {
                $isOutbound = $m % 2 === 1;
                Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'direction' => $isOutbound ? MessageDirection::Outbound : MessageDirection::Inbound,
                    'sender_type' => $isOutbound ? 'agent' : 'contact',
                    'sender_id' => $isOutbound ? $agent->id : $contact->id,
                    'sent_at' => now()->subMinutes(($messageCount - $m) * 5),
                ]);
            }

            // 1 Note per conversation
            Note::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $agent->id,
            ]);
        }
    }
}
