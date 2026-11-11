<?php

namespace Database\Factories;

use App\Models\ChatMessage;
use App\Models\ChatMessageAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatMessageAttachment>
 */
class ChatMessageAttachmentFactory extends Factory
{
    protected $model = ChatMessageAttachment::class;

    public function definition(): array
    {
        return [
            'message_id' => ChatMessage::factory(),
            'disk' => 'local',
            'path' => 'chat-attachments/'.fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(1024, 2_000_000),
            'checksum' => null,
        ];
    }
}
