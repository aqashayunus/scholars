<?php

namespace Database\Factories;

use App\Models\ChatMessage;
use App\Models\ChatMessageRead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatMessageRead>
 */
class ChatMessageReadFactory extends Factory
{
    protected $model = ChatMessageRead::class;

    public function definition(): array
    {
        return [
            'message_id' => ChatMessage::factory(),
            'user_id' => User::factory(),
            'read_at' => now(),
        ];
    }
}
