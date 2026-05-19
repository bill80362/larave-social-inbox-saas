<?php

namespace Tests\Feature\Channels;

use App\Enums\Platform;
use App\Filament\Resources\Channels\Pages\EditChannel;
use App\Models\Channel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChannelResourceEditTest extends TestCase
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

    public function test_edit_form_restores_line_credential_fields(): void
    {
        $channel = Channel::factory()->create([
            'workspace_id' => $this->workspace->id,
            'platform' => Platform::Line,
            'credentials' => json_encode([
                'channel_secret' => 'my_secret',
                'channel_access_token' => 'my_token',
                'destination' => 'U999',
            ]),
        ]);

        Livewire::test(EditChannel::class, ['record' => $channel->id])
            ->assertFormSet([
                'channel_secret' => 'my_secret',
                'channel_access_token' => 'my_token',
                'destination' => 'U999',
            ]);
    }

    public function test_saving_edit_re_encodes_credentials(): void
    {
        $channel = Channel::factory()->create([
            'workspace_id' => $this->workspace->id,
            'platform' => Platform::Line,
            'credentials' => json_encode([
                'channel_secret' => 'old_secret',
                'channel_access_token' => 'old_token',
            ]),
        ]);

        Livewire::test(EditChannel::class, ['record' => $channel->id])
            ->fillForm([
                'channel_access_token' => 'new_token',
                'channel_secret' => 'old_secret',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $channel->refresh();
        $credentials = json_decode($channel->credentials, true);
        $this->assertEquals('new_token', $credentials['channel_access_token']);
        $this->assertEquals('old_secret', $credentials['channel_secret']);
    }

    public function test_deactivating_channel_updates_is_active(): void
    {
        $channel = Channel::factory()->create([
            'workspace_id' => $this->workspace->id,
            'is_active' => true,
        ]);

        Livewire::test(EditChannel::class, ['record' => $channel->id])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('channels', [
            'id' => $channel->id,
            'is_active' => false,
        ]);
    }
}
