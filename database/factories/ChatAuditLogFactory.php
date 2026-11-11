<?php

namespace Database\Factories;

use App\Models\ChatAuditLog;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatAuditLog>
 */
class ChatAuditLogFactory extends Factory
{
    protected $model = ChatAuditLog::class;

    public function definition(): array
    {
        return [
            'viewer_id' => User::factory(),
            'child_id' => User::factory(),
            'conversation_id' => Conversation::factory(),
            'action' => 'conversation_viewed',
            'metadata' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
