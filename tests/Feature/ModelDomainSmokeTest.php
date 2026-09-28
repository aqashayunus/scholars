<?php

namespace Tests\Feature;

use App\Enums\StudentAssignmentStatus;
use App\Enums\TeacherApplicationStatus;
use App\Models\Assignment;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\LiveClass;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\StudentAttendance;
use App\Models\StudentQuiz;
use App\Models\Teacher;
use App\Models\TeacherApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 1 safety net — at least one CRUD/relationship test per model domain
 * that previously had zero coverage: quizzes, assignments, attendance,
 * certificates, live classes, teacher applications. Protects the model
 * layer against Phase 3's enum migration and Phase 4's service extraction.
 */
class ModelDomainSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_with_questions_and_a_student_attempt(): void
    {
        $course = Course::factory()->create();
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title'     => 'Chapter 1 Quiz',
        ]);

        $question = Question::create([
            'quiz_id'        => $quiz->id,
            'question_text'  => 'What is 2 + 2?',
            'option_a'       => '3',
            'option_b'       => '4',
            'option_c'       => '5',
            'option_d'       => '6',
            'correct_option' => 'b',
        ]);

        $this->assertSame($quiz->id, $question->quiz_id);
        $this->assertSame($quiz->id, $question->quiz->id);

        $student = Student::factory()->create();
        $attempt = StudentQuiz::create([
            'student_id'      => $student->id,
            'quiz_id'         => $quiz->id,
            'score'           => 8,
            'total_questions' => 10,
            'completed_at'    => now(),
        ]);

        $this->assertSame(80, $attempt->percent);
        $this->assertSame($student->id, $attempt->student->id);
    }

    public function test_assignment_and_student_assignment_submission(): void
    {
        $course = Course::factory()->create();
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'title'     => 'Homework 1',
        ]);

        $student = Student::factory()->create();
        $studentAssignment = StudentAssignment::create([
            'student_id'    => $student->id,
            'assignment_id' => $assignment->id,
            'status'        => 'submitted',
            'score'         => 90,
        ]);

        $this->assertSame(StudentAssignmentStatus::Submitted, $studentAssignment->status);
        $this->assertSame($assignment->id, $studentAssignment->assignment->id);
        $this->assertSame($course->id, $studentAssignment->assignment->course_id);
    }

    public function test_student_attendance_against_a_live_class(): void
    {
        $course = Course::factory()->create();
        $teacher = Teacher::factory()->create();
        $liveClass = LiveClass::create([
            'course_id'        => $course->id,
            'teacher_id'       => $teacher->id,
            'title'            => 'Live Algebra Session',
            'scheduled_at'     => now()->addDay(),
            'duration_minutes' => 60,
        ]);

        $student = Student::factory()->create();
        $attendance = StudentAttendance::create([
            'student_id'    => $student->id,
            'live_class_id' => $liveClass->id,
            'status'        => 'attended',
            'joined_at'     => now(),
        ]);

        $this->assertSame('attended', $attendance->status);
        $this->assertSame($liveClass->id, $attendance->liveClass->id);
    }

    public function test_certificate_belongs_to_student_and_course(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();

        $certificate = Certificate::create([
            'student_id'  => $student->id,
            'course_id'   => $course->id,
            'title'       => 'Certificate of Completion',
            'cert_number' => 'CERT-TEST-' . $student->id,
            'issued_at'   => now(),
        ]);

        $this->assertSame($student->id, $certificate->student_id);
        $this->assertSame($course->id, $certificate->course_id);
    }

    public function test_teacher_application_lifecycle_pending_to_approved(): void
    {
        $application = TeacherApplication::create([
            'full_name'          => 'Cikgu Test',
            'email'              => 'cikgu.test@example.com',
            'phone'              => '0123456789',
            'qualification'      => 'B.Ed Mathematics',
            'preferred_subjects' => 'Mathematics',
        ]);

        // create() reflects the attributes we passed, not the DB-side default
        // that landed on the row — refresh to read what was actually stored.
        $this->assertSame(TeacherApplicationStatus::Pending, $application->fresh()->status);

        $application->update(['status' => 'approved']);

        $this->assertSame(TeacherApplicationStatus::Approved, $application->fresh()->status);
    }
}
