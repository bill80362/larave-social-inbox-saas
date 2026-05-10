<?php

namespace Database\Factories;

use App\Enums\ConversationStatus;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
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
            'contact_id' => Contact::factory(),
            'status' => ConversationStatus::Open,
            'assigned_to' => null,
            'last_message_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ConversationStatus::Pending,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ConversationStatus::Resolved,
        ]);
    }
}
