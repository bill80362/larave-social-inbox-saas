<?php

namespace Database\Factories;

use App\Enums\Platform;
use App\Models\Channel;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Channel>
 */
class ChannelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = fake()->randomElement(Platform::cases());

        return [
            'workspace_id' => Workspace::factory(),
            'platform' => $platform,
            'name' => fake()->company().' '.$platform->label(),
            'platform_account_id' => fake()->uuid(),
            'credentials' => json_encode(['access_token' => fake()->sha256()]),
            'is_active' => true,
        ];
    }
}
