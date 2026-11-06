<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'type'            => 'direct',
            'context_type'    => null,
            'context_id'      => null,
            'title'           => null,
            'last_message_id' => null,
            'status'          => 'active',
            'created_by'      => User::factory(),
        ];
    }
}
