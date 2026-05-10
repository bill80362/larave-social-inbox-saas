<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_platform_user_in_same_channel_fails(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $this->actingAs($user);

        $channel = Channel::factory()->create(['workspace_id' => $workspace->id]);
        $platformUserId = 'user_abc';

        Contact::factory()->create(['workspace_id' => $workspace->id, 'channel_id' => $channel->id, 'platform_user_id' => $platformUserId]);

        $this->expectException(UniqueConstraintViolationException::class);

        Contact::factory()->create(['workspace_id' => $workspace->id, 'channel_id' => $channel->id, 'platform_user_id' => $platformUserId]);
    }

    public function test_same_platform_user_id_in_different_channels_is_allowed(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $this->actingAs($user);

        $channelA = Channel::factory()->create(['workspace_id' => $workspace->id]);
        $channelB = Channel::factory()->create(['workspace_id' => $workspace->id]);
        $platformUserId = 'user_abc';

        Contact::factory()->create(['workspace_id' => $workspace->id, 'channel_id' => $channelA->id, 'platform_user_id' => $platformUserId]);
        Contact::factory()->create(['workspace_id' => $workspace->id, 'channel_id' => $channelB->id, 'platform_user_id' => $platformUserId]);

        $this->assertDatabaseCount('contacts', 2);
    }
}
