<?php

namespace Tests\Feature\Chat;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Chat\MessagingService had zero direct test references despite being
 * 1,217 lines / 24 methods -- the largest zero-coverage service found by
 * the Phase 4 validation audit. ChatAuthorizationTest already covers
 * findOrCreateConversation()/getDirectory()'s role-matrix authorization;
 * this covers the message lifecycle, mute/archive/clear/delete, the
 * parental monitoring module, and the admin module -- all driven through
 * the real chat.* routes since the service builds its JsonResponse
 * directly (no controller layer left to test against separately).
 */
class MessagingServiceContractTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, string $email): User
    {
        return User::factory()->create(['name' => ucfirst($role) . ' ' . $email, 'email' => $email, 'role' => $role, 'password' => Hash::make('password')]);
    }

    private function makeDirectConversation(User $a, User $b): Conversation
    {
        $conv = Conversation::factory()->create(['type' => 'direct', 'created_by' => $a->id]);
        ConversationParticipant::factory()->create(['conversation_id' => $conv->id, 'user_id' => $a->id]);
        ConversationParticipant::factory()->create(['conversation_id' => $conv->id, 'user_id' => $b->id]);

        return $conv;
    }

    // ── Message lifecycle ───────────────────────────────────────────────

    public function test_a_participant_can_send_a_message_and_it_is_persisted(): void
    {
        $a = $this->makeUser('parent', 'a1@test.com');
        $b = $this->makeUser('student', 'b1@test.com');
        $conv = $this->makeDirectConversation($a, $b);

        $response = $this->actingAs($a)->postJson(route('chat.conversations.messages.send', $conv->id), [
            'body' => 'Hello there',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'sender_id'       => $a->id,
            'body'            => 'Hello there',
            'type'            => 'text',
        ]);
        $this->assertSame($conv->fresh()->last_message_id, $response->json('id'));
    }

    public function test_a_non_participant_cannot_send_a_message(): void
    {
        $a = $this->makeUser('parent', 'a2@test.com');
        $b = $this->makeUser('student', 'b2@test.com');
        $conv = $this->makeDirectConversation($a, $b);
        $outsider = $this->makeUser('teacher', 'out2@test.com');

        $response = $this->actingAs($outsider)->postJson(route('chat.conversations.messages.send', $conv->id), [
            'body' => 'Should not land',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('chat_messages', ['conversation_id' => $conv->id, 'body' => 'Should not land']);
    }

    public function test_getMessages_marks_the_other_participants_messages_as_read(): void
    {
        $a = $this->makeUser('parent', 'a3@test.com');
        $b = $this->makeUser('student', 'b3@test.com');
        $conv = $this->makeDirectConversation($a, $b);

        $msg = ChatMessage::factory()->create([
            'conversation_id' => $conv->id,
            'sender_id'       => $b->id,
            'body'            => 'From B',
            'status'          => 'sent',
        ]);

        $response = $this->actingAs($a)->getJson(route('chat.conversations.messages', $conv->id));

        $response->assertOk();
        $this->assertDatabaseHas('chat_message_reads', ['message_id' => $msg->id, 'user_id' => $a->id]);
        $this->assertSame('read', $msg->fresh()->status);

        $participant = ConversationParticipant::where('conversation_id', $conv->id)->where('user_id', $a->id)->first();
        $this->assertSame($msg->id, $participant->last_read_message_id);
    }

    public function test_getOlderMessages_paginates_backwards_from_before_id(): void
    {
        $a = $this->makeUser('parent', 'a4@test.com');
        $b = $this->makeUser('student', 'b4@test.com');
        $conv = $this->makeDirectConversation($a, $b);

        $messages = collect(range(1, 5))->map(fn () => ChatMessage::factory()->create([
            'conversation_id' => $conv->id,
            'sender_id'       => $b->id,
        ]));
        $oldestId = $messages->min('id');

        $response = $this->actingAs($a)->getJson(route('chat.conversations.messages.older', $conv->id) . '?before_id=' . $oldestId + 100);

        $response->assertOk();
        $this->assertCount(5, $response->json('messages'));
    }

    // ── Attachments ──────────────────────────────────────────────────────

    public function test_uploading_an_attachment_creates_a_message_and_stores_the_file(): void
    {
        Storage::fake('local');
        $a = $this->makeUser('parent', 'a5@test.com');
        $b = $this->makeUser('student', 'b5@test.com');
        $conv = $this->makeDirectConversation($a, $b);

        $response = $this->actingAs($a)->postJson(route('chat.conversations.attachments', $conv->id), [
            'file' => UploadedFile::fake()->image('proof.jpg'),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('chat_messages', ['conversation_id' => $conv->id, 'type' => 'attachment']);
        $this->assertDatabaseHas('chat_message_attachments', ['original_name' => 'proof.jpg']);
    }

    public function test_a_non_participant_cannot_download_an_attachment(): void
    {
        Storage::fake('local');
        $a = $this->makeUser('parent', 'a6@test.com');
        $b = $this->makeUser('student', 'b6@test.com');
        $conv = $this->makeDirectConversation($a, $b);
        $outsider = $this->makeUser('teacher', 'out6@test.com');

        $this->actingAs($a)->postJson(route('chat.conversations.attachments', $conv->id), [
            'file' => UploadedFile::fake()->image('secret.jpg'),
        ]);
        $attachmentId = \App\Models\ChatMessageAttachment::first()->id;

        $response = $this->actingAs($outsider)->get(route('chat.attachment.download', $attachmentId));

        $response->assertStatus(403);
    }

    // ── Mute / Archive / Clear / Delete ─────────────────────────────────

    public function test_muting_a_conversation_sets_muted_at_for_the_current_participant_only(): void
    {
        $a = $this->makeUser('parent', 'a7@test.com');
        $b = $this->makeUser('student', 'b7@test.com');
        $conv = $this->makeDirectConversation($a, $b);

        $response = $this->actingAs($a)->patchJson(route('chat.conversations.mute', $conv->id));

        $response->assertOk();
        $this->assertNotNull(ConversationParticipant::where('conversation_id', $conv->id)->where('user_id', $a->id)->first()->muted_at);
        $this->assertNull(ConversationParticipant::where('conversation_id', $conv->id)->where('user_id', $b->id)->first()->muted_at);
    }

    public function test_clearing_a_chat_deletes_its_messages_but_keeps_the_conversation(): void
    {
        $a = $this->makeUser('parent', 'a8@test.com');
        $b = $this->makeUser('student', 'b8@test.com');
        $conv = $this->makeDirectConversation($a, $b);
        ChatMessage::factory()->create(['conversation_id' => $conv->id, 'sender_id' => $b->id]);

        $response = $this->actingAs($a)->deleteJson(route('chat.conversations.clear', $conv->id));

        $response->assertOk();
        $this->assertSame(0, ChatMessage::where('conversation_id', $conv->id)->count());
        $this->assertNotNull(Conversation::find($conv->id));
        $this->assertNull($conv->fresh()->last_message_id);
    }

    public function test_deleting_a_conversation_soft_deletes_it_and_its_messages(): void
    {
        $a = $this->makeUser('parent', 'a9@test.com');
        $b = $this->makeUser('student', 'b9@test.com');
        $conv = $this->makeDirectConversation($a, $b);
        ChatMessage::factory()->create(['conversation_id' => $conv->id, 'sender_id' => $b->id]);

        $response = $this->actingAs($a)->deleteJson(route('chat.conversations.delete', $conv->id));

        $response->assertOk();
        $this->assertSoftDeleted('conversations', ['id' => $conv->id]);
        $this->assertSame(0, ChatMessage::where('conversation_id', $conv->id)->count());
    }

    public function test_clear_all_chats_wipes_only_the_current_users_own_conversations(): void
    {
        $a = $this->makeUser('parent', 'a10@test.com');
        $b = $this->makeUser('student', 'b10@test.com');
        $myConv = $this->makeDirectConversation($a, $b);

        $c = $this->makeUser('parent', 'c10@test.com');
        $d = $this->makeUser('student', 'd10@test.com');
        $othersConv = $this->makeDirectConversation($c, $d);

        $response = $this->actingAs($a)->deleteJson(route('chat.conversations.clear-all'));

        $response->assertOk();
        $this->assertSoftDeleted('conversations', ['id' => $myConv->id]);
        $this->assertDatabaseHas('conversations', ['id' => $othersConv->id, 'deleted_at' => null]);
    }

    // ── Parental monitoring ──────────────────────────────────────────────

    public function test_parent_can_list_monitored_children(): void
    {
        $parent = $this->makeUser('parent', 'mon1@test.com');
        $childUser = $this->makeUser('student', 'child1@test.com');
        $student = Student::factory()->create(['user_id' => $childUser->id, 'parent_user_id' => $parent->id, 'level' => 'SPM']);
        \Illuminate\Support\Facades\DB::table('parent_student')->insert([
            'parent_user_id' => $parent->id, 'student_id' => $student->id,
            'relationship' => 'Guardian', 'status' => 'active', 'is_primary' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($parent)->getJson(route('chat.monitor.children'));

        $response->assertOk();
        $response->assertJsonFragment(['student_id' => $student->id]);
    }

    public function test_a_non_parent_cannot_access_the_monitoring_endpoint(): void
    {
        $student = $this->makeUser('student', 'nonparent1@test.com');

        $response = $this->actingAs($student)->getJson(route('chat.monitor.children'));

        $response->assertStatus(403);
    }

    public function test_parent_can_view_their_childs_conversation_list(): void
    {
        $parent = $this->makeUser('parent', 'mon2@test.com');
        $childUser = $this->makeUser('student', 'child2@test.com');
        $student = Student::factory()->create(['user_id' => $childUser->id, 'parent_user_id' => $parent->id, 'level' => 'SPM']);
        \Illuminate\Support\Facades\DB::table('parent_student')->insert([
            'parent_user_id' => $parent->id, 'student_id' => $student->id,
            'relationship' => 'Guardian', 'status' => 'active', 'is_primary' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $teacher = $this->makeUser('teacher', 'mon2teacher@test.com');
        $this->makeDirectConversation($childUser, $teacher);

        $response = $this->actingAs($parent)->getJson(route('chat.monitor.child-conversations', $student->id));

        $response->assertOk();
        $this->assertCount(1, $response->json());
    }

    public function test_parent_cannot_view_conversations_of_a_child_not_linked_to_them(): void
    {
        $parent = $this->makeUser('parent', 'mon3@test.com');
        $otherChildUser = $this->makeUser('student', 'child3@test.com');
        $unrelatedStudent = Student::create(['user_id' => $otherChildUser->id, 'level' => 'SPM']);

        $response = $this->actingAs($parent)->getJson(route('chat.monitor.child-conversations', $unrelatedStudent->id));

        $response->assertStatus(403);
    }

    // ── Admin module ──────────────────────────────────────────────────────

    public function test_admin_can_list_all_contacts(): void
    {
        $admin = $this->makeUser('admin', 'admin1@test.com');
        $a = $this->makeUser('parent', 'aAdmin1@test.com');
        $b = $this->makeUser('student', 'bAdmin1@test.com');
        $this->makeDirectConversation($a, $b);

        $response = $this->actingAs($admin)->getJson(route('chat.admin.contacts'));

        $response->assertOk();
        $this->assertNotEmpty($response->json());
    }

    public function test_a_non_admin_cannot_access_the_admin_contacts_endpoint(): void
    {
        $teacher = $this->makeUser('teacher', 'notadmin1@test.com');

        $response = $this->actingAs($teacher)->getJson(route('chat.admin.contacts'));

        $response->assertStatus(403);
    }

    public function test_admin_can_update_a_conversations_status(): void
    {
        $admin = $this->makeUser('admin', 'admin2@test.com');
        $a = $this->makeUser('parent', 'aAdmin2@test.com');
        $b = $this->makeUser('student', 'bAdmin2@test.com');
        $conv = $this->makeDirectConversation($a, $b);

        $response = $this->actingAs($admin)->patchJson(route('chat.admin.conversations.status', $conv->id), [
            'status' => 'resolved',
        ]);

        $response->assertOk();
        $this->assertSame('resolved', $conv->fresh()->status->value ?? $conv->fresh()->status);
    }
}
