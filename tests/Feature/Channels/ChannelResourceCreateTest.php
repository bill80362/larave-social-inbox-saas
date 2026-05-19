<?php

namespace Tests\Feature\Channels;

use App\Enums\Platform;
use App\Filament\Resources\Channels\Pages\CreateChannel;
use App\Models\Channel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChannelResourceCreateTest extends TestCase
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

    public function test_creating_line_channel_saves_correct_credentials(): void
    {
        Livewire::test(CreateChannel::class)
            ->fillForm([
                'name' => 'My LINE Channel',
                'platform' => Platform::Line->value,
                'platform_account_id' => 'line_bot_123',
                'channel_secret' => 'secret_abc',
                'channel_access_token' => 'token_xyz',
                'destination' => 'U12345',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $channel = Channel::where('platform_account_id', 'line_bot_123')->first();
        $this->assertNotNull($channel);
        $this->assertEquals('My LINE Channel', $channel->name);
        $this->assertEquals(Platform::Line, $channel->platform);

        $credentials = json_decode($channel->credentials, true);
        $this->assertEquals('secret_abc', $credentials['channel_secret']);
        $this->assertEquals('token_xyz', $credentials['channel_access_token']);
        $this->assertEquals('U12345', $credentials['destination']);
    }

    public function test_creating_facebook_channel_saves_correct_credentials(): void
    {
        Livewire::test(CreateChannel::class)
            ->fillForm([
                'name' => 'My FB Page',
                'platform' => Platform::Facebook->value,
                'platform_account_id' => 'page_456',
                'verify_token' => 'vtoken_abc',
                'page_access_token' => 'pat_xyz',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $channel = Channel::where('platform_account_id', 'page_456')->first();
        $this->assertNotNull($channel);

        $credentials = json_decode($channel->credentials, true);
        $this->assertEquals('vtoken_abc', $credentials['verify_token']);
        $this->assertEquals('pat_xyz', $credentials['page_access_token']);
    }

    public function test_creating_channel_sets_workspace_id(): void
    {
        Livewire::test(CreateChannel::class)
            ->fillForm([
                'name' => 'Test Channel',
                'platform' => Platform::Line->value,
                'platform_account_id' => 'acc_999',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('channels', [
            'platform_account_id' => 'acc_999',
            'workspace_id' => $this->workspace->id,
        ]);
    }
}
