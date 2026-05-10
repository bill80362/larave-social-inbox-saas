<?php

namespace Tests\Feature;

use App\Enums\Platform;
use App\Filament\Resources\Channels\Pages\ListChannels;
use App\Models\Channel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChannelListTest extends TestCase
{
    use RefreshDatabase;

    private Workspace $workspace;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspace = Workspace::factory()->create();
        $this->agent = User::factory()->create(['workspace_id' => $this->workspace->id]);
        $this->actingAs($this->agent);
    }

    public function test_list_page_loads_successfully(): void
    {
        Livewire::test(ListChannels::class)
            ->assertOk();
    }

    public function test_workspace_channels_are_visible(): void
    {
        $channel = Channel::factory()->create([
            'workspace_id' => $this->workspace->id,
            'platform' => Platform::Instagram,
        ]);

        Livewire::test(ListChannels::class)
            ->assertCanSeeTableRecords([$channel]);
    }

    public function test_channels_from_other_workspace_are_not_visible(): void
    {
        $otherWorkspace = Workspace::factory()->create();
        $otherChannel = Channel::factory()->create(['workspace_id' => $otherWorkspace->id]);

        Livewire::test(ListChannels::class)
            ->assertCanNotSeeTableRecords([$otherChannel]);
    }

    public function test_no_create_header_action_available(): void
    {
        $component = Livewire::test(ListChannels::class);

        $actions = $component->instance()->getCachedHeaderActions();
        $this->assertEmpty($actions);
    }
}
