<?php

namespace Database\Factories;

use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'direction' => MessageDirection::Inbound,
            'type' => MessageType::Text,
            'content' => fake()->sentence(),
            'attachments' => null,
            'sender_type' => 'contact',
            'sender_id' => 1,
            'sent_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'platform_message_id' => null,
        ];
    }

    public function outbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => MessageDirection::Outbound,
            'sender_type' => 'agent',
        ]);
    }
}
