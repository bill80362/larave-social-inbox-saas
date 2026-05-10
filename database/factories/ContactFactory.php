<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'channel_id' => Channel::factory(),
            'platform_user_id' => fake()->uuid(),
            'name' => fake()->name(),
            'avatar_url' => fake()->imageUrl(100, 100, 'people'),
        ];
    }
}
