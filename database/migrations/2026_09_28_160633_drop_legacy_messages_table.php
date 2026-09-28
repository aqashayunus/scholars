<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 2 chat consolidation: the app previously ran two parallel chat
     * stacks with separate data — this legacy `messages` table (sender_id/
     * receiver_id, no relationship checks on who could message whom) and the
     * newer conversations/chat_messages stack (which the floating widget and
     * the rebuilt full-page /student/messages + /teacher/messages both use).
     * All app code has been repointed to the new stack; this retires the old
     * table rather than leaving it as an orphaned, unused duplicate of the
     * same data. See app/Http/Controllers/ChatController.php.
     */
    public function up(): void
    {
        Schema::dropIfExists('messages');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['sender_id', 'receiver_id']);
            $table->index(['receiver_id', 'sender_id']);
        });
    }
};
