<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_global_scope_filters_by_workspace(): void
    {
        $workspaceA = Workspace::factory()->create();
        $workspaceB = Workspace::factory()->create();

        $userA = User::factory()->create(['workspace_id' => $workspaceA->id]);

        $channelA = Channel::factory()->create(['workspace_id' => $workspaceA->id]);
        $channelB = Channel::factory()->create(['workspace_id' => $workspaceB->id]);

        $contactA = Contact::factory()->create(['workspace_id' => $workspaceA->id, 'channel_id' => $channelA->id]);
        $contactB = Contact::factory()->create(['workspace_id' => $workspaceB->id, 'channel_id' => $channelB->id]);

        Conversation::factory()->create(['workspace_id' => $workspaceA->id, 'channel_id' => $channelA->id, 'contact_id' => $contactA->id]);
        Conversation::factory()->create(['workspace_id' => $workspaceB->id, 'channel_id' => $channelB->id, 'contact_id' => $contactB->id]);

        $this->actingAs($userA);

        $conversations = Conversation::all();

        $this->assertCount(1, $conversations);
        $this->assertEquals($workspaceA->id, $conversations->first()->workspace_id);
    }

    public function test_channel_global_scope_filters_by_workspace(): void
    {
        $workspaceA = Workspace::factory()->create();
        $workspaceB = Workspace::factory()->create();

        $userA = User::factory()->create(['workspace_id' => $workspaceA->id]);

        Channel::factory()->create(['workspace_id' => $workspaceA->id]);
        Channel::factory()->create(['workspace_id' => $workspaceB->id]);

        $this->actingAs($userA);

        $channels = Channel::all();

        $this->assertCount(1, $channels);
        $this->assertEquals($workspaceA->id, $channels->first()->workspace_id);
    }

    public function test_contact_global_scope_filters_by_workspace(): void
    {
        $workspaceA = Workspace::factory()->create();
        $workspaceB = Workspace::factory()->create();

        $userA = User::factory()->create(['workspace_id' => $workspaceA->id]);

        $channelA = Channel::factory()->create(['workspace_id' => $workspaceA->id]);
        $channelB = Channel::factory()->create(['workspace_id' => $workspaceB->id]);

        Contact::factory()->create(['workspace_id' => $workspaceA->id, 'channel_id' => $channelA->id]);
        Contact::factory()->create(['workspace_id' => $workspaceB->id, 'channel_id' => $channelB->id]);

        $this->actingAs($userA);

        $contacts = Contact::all();

        $this->assertCount(1, $contacts);
        $this->assertEquals($workspaceA->id, $contacts->first()->workspace_id);
    }
}
