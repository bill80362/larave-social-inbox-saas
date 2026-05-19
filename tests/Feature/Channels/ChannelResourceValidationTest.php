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

class ChannelResourceValidationTest extends TestCase
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

    public function test_duplicate_platform_account_in_same_workspace_shows_validation_error(): void
    {
        Channel::factory()->create([
            'workspace_id' => $this->workspace->id,
            'platform' => Platform::Facebook,
            'platform_account_id' => 'page_dup',
        ]);

        Livewire::test(CreateChannel::class)
            ->fillForm([
                'name' => 'Duplicate Channel',
                'platform' => Platform::Facebook->value,
                'platform_account_id' => 'page_dup',
            ])
            ->call('create')
            ->assertHasFormErrors(['platform_account_id']);
    }

    public function test_name_is_required(): void
    {
        Livewire::test(CreateChannel::class)
            ->fillForm([
                'name' => null,
                'platform' => Platform::Line->value,
                'platform_account_id' => 'acc_1',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_platform_is_required(): void
    {
        Livewire::test(CreateChannel::class)
            ->fillForm([
                'name' => 'Test',
                'platform' => null,
                'platform_account_id' => 'acc_1',
            ])
            ->call('create')
            ->assertHasFormErrors(['platform' => 'required']);
    }

    public function test_platform_account_id_is_required(): void
    {
        Livewire::test(CreateChannel::class)
            ->fillForm([
                'name' => 'Test',
                'platform' => Platform::Line->value,
                'platform_account_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['platform_account_id' => 'required']);
    }

    public function test_same_platform_account_id_in_different_workspaces_is_allowed(): void
    {
        $otherWorkspace = Workspace::factory()->create();
        Channel::factory()->create([
            'workspace_id' => $otherWorkspace->id,
            'platform' => Platform::Facebook,
            'platform_account_id' => 'shared_acc',
        ]);

        Livewire::test(CreateChannel::class)
            ->fillForm([
                'name' => 'My FB Channel',
                'platform' => Platform::Facebook->value,
                'platform_account_id' => 'shared_acc',
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }
}
