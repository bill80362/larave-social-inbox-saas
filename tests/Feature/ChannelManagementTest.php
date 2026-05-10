<?php

namespace Tests\Feature;

use App\Enums\Platform;
use App\Models\Channel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_platform_account_in_same_workspace_fails(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $this->actingAs($user);

        $accountId = 'page_123';

        Channel::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Facebook,
            'platform_account_id' => $accountId,
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        Channel::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Facebook,
            'platform_account_id' => $accountId,
        ]);
    }

    public function test_same_platform_account_id_in_different_workspaces_is_allowed(): void
    {
        $workspaceA = Workspace::factory()->create();
        $workspaceB = Workspace::factory()->create();
        $user = User::factory()->create(['workspace_id' => $workspaceA->id]);
        $this->actingAs($user);

        $accountId = 'page_123';

        Channel::factory()->create(['workspace_id' => $workspaceA->id, 'platform' => Platform::Facebook, 'platform_account_id' => $accountId]);
        Channel::factory()->create(['workspace_id' => $workspaceB->id, 'platform' => Platform::Facebook, 'platform_account_id' => $accountId]);

        $this->assertDatabaseCount('channels', 2);
    }

    public function test_same_workspace_can_have_different_platforms(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create(['workspace_id' => $workspace->id]);
        $this->actingAs($user);

        Channel::factory()->create(['workspace_id' => $workspace->id, 'platform' => Platform::Facebook, 'platform_account_id' => 'acc_1']);
        Channel::factory()->create(['workspace_id' => $workspace->id, 'platform' => Platform::Instagram, 'platform_account_id' => 'acc_1']);

        $this->assertCount(2, Channel::all());
    }
}
