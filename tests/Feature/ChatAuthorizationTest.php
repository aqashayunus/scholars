<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 2 — chat role-matrix regression coverage.
 *
 * Before this phase: Parent<->Child messaging was blocked entirely (spec
 * requires it), and a teacher could message ANY student or parent in the
 * system regardless of enrollment (ChatController::authorizeContact,
 * previously `if ($contact->isStudent() || $contact->isParent()) return null;`
 * with no whitelist check at all).
 *
 * The Parent<->Child tests deliberately also prove the Phase 0 ParentLink
 * fix and this phase's fix compose correctly: a 'pending' (unapproved)
 * guardian link must grant zero chat access, not just zero dashboard access.
 */
class ChatAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function makeParent(string $email): User
    {
        return User::factory()->create([
            'name' => 'Parent ' . $email, 'email' => $email, 'role' => 'parent', 'password' => Hash::make('password'),
        ]);
    }

    private function makeStudentWithUser(string $email, ?int $parentUserId = null): Student
    {
        $u = User::factory()->create(['name' => 'Student ' . $email, 'email' => $email, 'role' => 'student', 'password' => Hash::make('password')]);
        return Student::factory()->create(['user_id' => $u->id, 'parent_user_id' => $parentUserId, 'level' => 'SPM']);
    }

    private function makeTeacherWithUser(string $email): Teacher
    {
        $u = User::factory()->create(['name' => 'Teacher ' . $email, 'email' => $email, 'role' => 'teacher', 'password' => Hash::make('password')]);
        return Teacher::create(['user_id' => $u->id, 'specialization' => 'Math', 'qualification' => 'B.Ed', 'rating' => 5.0, 'experience_years' => 1]);
    }

    private function findOrCreate(User $as, int $contactId)
    {
        return $this->actingAs($as)->postJson(route('chat.conversations.find-or-create'), ['contact_id' => $contactId]);
    }

    // ── Parent <-> Child ─────────────────────────────────────────────────

    public function test_parent_can_message_their_own_active_child(): void
    {
        $parent = $this->makeParent('p1@test.com');
        $student = $this->makeStudentWithUser('c1@test.com', $parent->id);

        $response = $this->findOrCreate($parent, $student->user_id);

        $response->assertSuccessful();
        $response->assertJsonStructure(['conversation_id']);
    }

    public function test_parent_cannot_message_a_child_that_is_not_theirs(): void
    {
        $parent = $this->makeParent('p2@test.com');
        $otherFamilysChild = $this->makeStudentWithUser('c2@test.com', null);

        $response = $this->findOrCreate($parent, $otherFamilysChild->user_id);

        $response->assertStatus(403);
    }

    public function test_pending_guardian_link_grants_zero_chat_access(): void
    {
        // Composes with Phase 0's ParentLinkService fix: a student already has
        // an active guardian, a second parent's link request is 'pending', and
        // that pending link must not grant chat access to the child either.
        $existingGuardian = $this->makeParent('existing@test.com');
        $student = $this->makeStudentWithUser('c3@test.com', $existingGuardian->id);

        $pendingParent = $this->makeParent('pending@test.com');
        DB::table('parent_student')->insert([
            'parent_user_id' => $pendingParent->id,
            'student_id'     => $student->id,
            'status'         => 'pending',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $response = $this->findOrCreate($pendingParent, $student->user_id);

        $response->assertStatus(403);
    }

    public function test_student_can_message_their_own_parent(): void
    {
        $parent = $this->makeParent('p3@test.com');
        $student = $this->makeStudentWithUser('c4@test.com', $parent->id);
        $studentUser = User::find($student->user_id);

        $response = $this->findOrCreate($studentUser, $parent->id);

        $response->assertSuccessful();
    }

    public function test_student_cannot_message_someone_elses_parent(): void
    {
        $unrelatedParent = $this->makeParent('p4@test.com');
        $student = $this->makeStudentWithUser('c5@test.com', null);
        $studentUser = User::find($student->user_id);

        $response = $this->findOrCreate($studentUser, $unrelatedParent->id);

        $response->assertStatus(403);
    }

    // ── Teacher whitelist ────────────────────────────────────────────────

    public function test_teacher_can_message_a_student_enrolled_in_their_course(): void
    {
        $teacher = $this->makeTeacherWithUser('t1@test.com');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $student = $this->makeStudentWithUser('c6@test.com');
        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'active']);

        $teacherUser = User::find($teacher->user_id);
        $response = $this->findOrCreate($teacherUser, $student->user_id);

        $response->assertSuccessful();
    }

    public function test_teacher_cannot_message_a_student_not_enrolled_in_their_course(): void
    {
        // This is the whitelist fix: previously ANY teacher could message ANY
        // student regardless of enrollment.
        $teacher = $this->makeTeacherWithUser('t2@test.com');
        Course::factory()->create(['teacher_id' => $teacher->id]);
        $unrelatedStudent = $this->makeStudentWithUser('c7@test.com');

        $teacherUser = User::find($teacher->user_id);
        $response = $this->findOrCreate($teacherUser, $unrelatedStudent->user_id);

        $response->assertStatus(403);
    }

    public function test_teacher_can_message_the_parent_of_an_enrolled_student(): void
    {
        $teacher = $this->makeTeacherWithUser('t3@test.com');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $parent = $this->makeParent('p5@test.com');
        $student = $this->makeStudentWithUser('c8@test.com', $parent->id);
        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'active']);

        $teacherUser = User::find($teacher->user_id);
        $response = $this->findOrCreate($teacherUser, $parent->id);

        $response->assertSuccessful();
    }

    public function test_teacher_cannot_message_a_parent_whose_child_is_not_enrolled_with_them(): void
    {
        $teacher = $this->makeTeacherWithUser('t4@test.com');
        Course::factory()->create(['teacher_id' => $teacher->id]);
        $unrelatedParent = $this->makeParent('p6@test.com');
        $this->makeStudentWithUser('c9@test.com', $unrelatedParent->id); // enrolled nowhere

        $teacherUser = User::find($teacher->user_id);
        $response = $this->findOrCreate($teacherUser, $unrelatedParent->id);

        $response->assertStatus(403);
    }

    // ── Directory visibility ────────────────────────────────────────────
    // The authorization check alone isn't enough — a parent/student also
    // needs to actually SEE the other as an option to start a new chat with.
    // getDirectory() previously never surfaced the counterpart at all.

    public function test_parent_directory_includes_their_own_child(): void
    {
        $parent = $this->makeParent('p7@test.com');
        $student = $this->makeStudentWithUser('c10@test.com', $parent->id);

        $response = $this->actingAs($parent)->getJson(route('chat.directory'));

        $response->assertOk();
        $response->assertJsonFragment(['contact_id' => $student->user_id, 'contact_role' => 'student']);
    }

    public function test_student_directory_includes_their_own_parent(): void
    {
        $parent = $this->makeParent('p8@test.com');
        $student = $this->makeStudentWithUser('c11@test.com', $parent->id);
        $studentUser = User::find($student->user_id);

        $response = $this->actingAs($studentUser)->getJson(route('chat.directory'));

        $response->assertOk();
        $response->assertJsonFragment(['contact_id' => $parent->id, 'contact_role' => 'parent']);
    }

    public function test_parent_directory_excludes_a_child_only_pending_approval(): void
    {
        $existingGuardian = $this->makeParent('existing2@test.com');
        $student = $this->makeStudentWithUser('c12@test.com', $existingGuardian->id);

        $pendingParent = $this->makeParent('pending2@test.com');
        DB::table('parent_student')->insert([
            'parent_user_id' => $pendingParent->id,
            'student_id'     => $student->id,
            'status'         => 'pending',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $response = $this->actingAs($pendingParent)->getJson(route('chat.directory'));

        $response->assertOk();
        $response->assertJsonMissing(['contact_id' => $student->user_id, 'contact_role' => 'student']);
    }
}
