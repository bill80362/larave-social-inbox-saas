<?php

namespace Tests\Feature\Channels;

use App\Filament\Resources\Channels\Pages\EditChannel;
use App\Models\Channel;
use App\Models\User;
use App\Models\Workspace;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChannelResourceDeleteTest extends TestCase
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

    public function test_deleting_channel_removes_record(): void
    {
        $channel = Channel::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        Livewire::test(EditChannel::class, ['record' => $channel->id])
            ->callAction(DeleteAction::class)
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('channels', ['id' => $channel->id]);
    }
}
