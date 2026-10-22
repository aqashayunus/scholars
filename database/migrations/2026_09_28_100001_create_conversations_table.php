<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Conversations (first-class entity) ─────────────────────────────
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // Type determines contact-list filtering and UI context
            $table->enum('type', [
                'direct',          // Generic user ↔ user
                'student_teacher', // Student ↔ Teacher (enrollment context)
                'parent_teacher',  // Parent ↔ Teacher
                'parent_admin',    // Parent ↔ Admin
                'student_admin',   // Student ↔ Admin
                'teacher_admin',   // Teacher ↔ Admin
                'support',         // Admin support ticket-style
                'system',          // System-generated announcements
            ])->default('direct');

            // Optional educational context (polymorphic link)
            $table->string('context_type')->nullable(); // e.g. App\Models\Course
            $table->unsignedBigInteger('context_id')->nullable();

            // Title for group/support conversations; null for 1-to-1
            $table->string('title')->nullable();

            // Quick-access pointer to the last message (avoids JOIN for list view)
            $table->unsignedBigInteger('last_message_id')->nullable();

            // Admin support status
            $table->enum('status', ['open', 'pending', 'resolved', 'closed'])->default('open');

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['context_type', 'context_id']);
        });

        // ── 2. Conversation Participants ──────────────────────────────────────
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Role within the conversation
            $table->enum('role', ['member', 'admin', 'observer'])->default('member');

            // Unread tracking: messages after this id are unread for this participant
            $table->unsignedBigInteger('last_read_message_id')->nullable();
            $table->timestamp('last_read_at')->nullable();

            // User preferences per conversation
            $table->timestamp('muted_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();

            $table->unique(['conversation_id', 'user_id']);
            $table->index(['user_id', 'archived_at']);
        });

        // ── 3. Chat Messages (conversation-centric, replaces old direct model) ─
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');

            // Message content
            $table->text('body')->nullable(); // nullable if attachment-only message
            $table->enum('type', ['text', 'attachment', 'system', 'context_card'])->default('text');

            // Reply threading
            $table->unsignedBigInteger('reply_to_id')->nullable();
            $table->foreign('reply_to_id')->references('id')->on('chat_messages')->onDelete('set null');

            // Lifecycle
            $table->enum('status', ['sending', 'sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->timestamp('edited_at')->nullable();
            $table->softDeletes(); // Soft delete preserves "Message was deleted" placeholder

            $table->timestamps();

            $table->index(['conversation_id', 'id']); // For delta polling: WHERE conversation_id = ? AND id > ?
            $table->index(['sender_id']);
        });

        // ── 4. Message Attachments ────────────────────────────────────────────
        Schema::create('chat_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat_messages')->onDelete('cascade');
            $table->string('disk')->default('local'); // local or s3
            $table->string('path');                   // private storage path
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedInteger('size_bytes');
            $table->string('checksum')->nullable();   // MD5/SHA256 for integrity
            $table->timestamps();
        });

        // ── 5. Message Read Receipts (blue tick tracking) ─────────────────────
        Schema::create('chat_message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat_messages')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('read_at')->useCurrent();

            $table->unique(['message_id', 'user_id']);
            $table->index(['user_id', 'read_at']);
        });

        // ── 6. Typing Indicators (ephemeral, cleared by TTL) ─────────────────
        Schema::create('chat_typing_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['conversation_id', 'user_id']);
        });

        // ── 7. Parental Monitoring Audit Log ──────────────────────────────────
        Schema::create('chat_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->constrained('users')->onDelete('cascade'); // parent
            $table->unsignedBigInteger('child_id');                                    // student (user_id)
            $table->foreign('child_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('conversation_id')->nullable()->constrained('conversations')->onDelete('cascade');
            $table->enum('action', [
                'conversation_viewed',
                'message_viewed',
                'attachment_viewed',
                'monitoring_session_started',
                'monitoring_session_ended',
            ]);
            $table->json('metadata')->nullable(); // Extra context (message_id, attachment_id, etc.)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['viewer_id', 'created_at']);
            $table->index(['child_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_audit_logs');
        Schema::dropIfExists('chat_typing_indicators');
        Schema::dropIfExists('chat_message_reads');
        Schema::dropIfExists('chat_message_attachments');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
};
