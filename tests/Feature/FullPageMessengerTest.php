<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 2 — the rebuilt /student/messages and /teacher/messages full-page
 * views (over Conversation/ChatMessage, replacing the deleted legacy
 * Message/MessageController stack).
 *
 * DISABLED per repo owner (2026-09-28): product currently focuses solely on
 * the floating chat widget. The routes are commented out in routes/web.php
 * and the teacher sidebar link removed, so route('student.messages') /
 * route('teacher.messages') no longer resolve — these tests are skipped
 * rather than deleted so the coverage is a one-line restore away. The view
 * and its backing partial (resources/views/chat/_full-page-messenger.blade.php)
 * are otherwise untouched and still call the same chat.* endpoints the
 * widget uses.
 */
class FullPageMessengerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_messages_page_renders(): void
    {
        $this->markTestSkipped('Full-page messenger disabled per repo owner — see class docblock. Uncomment routes/web.php + this test to restore.');

        $user = User::create(['name' => 'Msg Student', 'email' => 'msgstudent@test.com', 'role' => 'student', 'password' => Hash::make('password')]);
        Student::create(['user_id' => $user->id, 'level' => 'SPM']);

        $response = $this->actingAs($user)->get(route('student.messages'));

        $response->assertOk();
        $response->assertSee('Messages');
    }

    public function test_teacher_messages_page_renders(): void
    {
        $this->markTestSkipped('Full-page messenger disabled per repo owner — see class docblock. Uncomment routes/web.php + this test to restore.');

        $user = User::create(['name' => 'Msg Teacher', 'email' => 'msgteacher@test.com', 'role' => 'teacher', 'password' => Hash::make('password')]);
        Teacher::create(['user_id' => $user->id, 'specialization' => 'Math', 'qualification' => 'B.Ed', 'rating' => 5.0, 'experience_years' => 1]);

        $response = $this->actingAs($user)->get(route('teacher.messages'));

        $response->assertOk();
        $response->assertSee('Messages');
    }
}
