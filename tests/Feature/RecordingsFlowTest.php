<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\RecordedClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Support\UploadLimit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordingsFlowTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $teacher;

    private Course $course;

    private User $studentUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = Teacher::factory()->create()->fresh();
        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::factory()->create(['user_id' => $this->studentUser->id, 'status' => 'active']);
        Enrollment::create(['student_id' => $student->id, 'course_id' => $this->course->id, 'status' => 'active']);
    }

    private function recordingFor(Course $course, array $attrs = []): RecordedClass
    {
        return RecordedClass::factory()->create(array_merge(['course_id' => $course->id, 'subject_id' => $course->subject_id], $attrs));
    }

    // ── Teacher upload ───────────────────────────────────────────────────────

    public function test_teacher_can_publish_a_recording_with_just_a_link(): void
    {
        $this->actingAs($this->teacher->user)->post(route('teacher.recordings.store'), [
            'course_id' => $this->course->id,
            'title' => 'Fractions replay',
            'topic_name' => 'Fractions',
            'video_url' => 'https://youtu.be/abcdefghijk',
            'duration_minutes' => 40,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('recorded_classes', ['title' => 'Fractions replay', 'video_url' => 'https://youtu.be/abcdefghijk']);
    }

    public function test_a_recording_needs_a_link_or_a_file(): void
    {
        // Used to silently fall back to a placeholder YouTube video.
        $this->actingAs($this->teacher->user)->post(route('teacher.recordings.store'), [
            'course_id' => $this->course->id,
            'title' => 'No video',
            'topic_name' => 'Fractions',
            'duration_minutes' => 40,
        ])->assertSessionHasErrors('video_url');

        $this->assertDatabaseMissing('recorded_classes', ['title' => 'No video']);
    }

    public function test_a_google_drive_meet_recording_link_is_accepted(): void
    {
        $link = 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view?usp=meet_tnfm_calendar';

        $this->actingAs($this->teacher->user)->post(route('teacher.recordings.store'), [
            'course_id' => $this->course->id,
            'title' => 'Meet replay',
            'topic_name' => 'Fractions',
            'video_url' => $link,
            'duration_minutes' => 75,
        ])->assertSessionHasNoErrors();

        $rec = RecordedClass::where('title', 'Meet replay')->sole();
        $this->assertSame('https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/preview', $rec->embedUrl());
    }

    public function test_meeting_and_folder_links_are_rejected_with_a_hint(): void
    {
        foreach ([
            'https://meet.google.com/abc-defg-hij' => 'Google Meet link',
            'https://drive.google.com/drive/folders/1AbCdEfGhIjKlMnOp' => 'folder link',
            'https://drive.google.com/drive/my-drive' => 'does not point to a video',
            'https://www.youtube.com/@someChannel' => 'not a video',
        ] as $link => $hint) {
            $this->actingAs($this->teacher->user)->post(route('teacher.recordings.store'), [
                'course_id' => $this->course->id,
                'title' => 'Bad link',
                'topic_name' => 'x',
                'video_url' => $link,
                'duration_minutes' => 60,
            ])->assertSessionHasErrors('video_url');

            $this->assertStringContainsString($hint, session('errors')->first('video_url'), $link);
        }

        $this->assertDatabaseMissing('recorded_classes', ['title' => 'Bad link']);
    }

    public function test_upload_form_has_the_meet_sharing_tip(): void
    {
        $this->actingAs($this->teacher->user)->get(route('teacher.recordings'))
            ->assertOk()
            ->assertSee('How to share your Google Meet recording')
            ->assertSee('linkCheck(newVideoUrl)', false);
    }

    public function test_teacher_cannot_edit_or_delete_another_teachers_recording(): void
    {
        $other = $this->recordingFor(Course::factory()->create(), ['title' => 'Not mine']);

        $this->actingAs($this->teacher->user)->put(route('teacher.recordings.update', $other->id), [
            'course_id' => $this->course->id, 'title' => 'Hijacked', 'topic_name' => 'x', 'duration_minutes' => 10,
        ])->assertNotFound();
        $this->actingAs($this->teacher->user)->delete(route('teacher.recordings.destroy', $other->id))->assertNotFound();

        $this->assertDatabaseHas('recorded_classes', ['id' => $other->id, 'title' => 'Not mine']);
    }

    public function test_teacher_previews_recordings_in_page_instead_of_the_students_only_page(): void
    {
        $this->recordingFor($this->course, ['video_url' => 'https://www.youtube.com/watch?v=abcdefghijk']);

        $this->actingAs($this->teacher->user)->get(route('teacher.recordings'))
            ->assertOk()
            ->assertSee('previewRecording = rec', false)
            ->assertViewHas('recordings', fn ($recordings) => $recordings->first()['embed_url'] === 'https://www.youtube.com/embed/abcdefghijk')
            ->assertDontSee("'/student/recordings/'", false);

        // The student page itself stays students-only.
        $rec = RecordedClass::where('course_id', $this->course->id)->sole();
        $this->actingAs($this->teacher->user)->get(route('student.recordings.show', $rec->id))->assertForbidden();
    }

    public function test_upload_form_shows_the_servers_real_limit(): void
    {
        $this->actingAs($this->teacher->user)->get(route('teacher.recordings'))
            ->assertOk()
            ->assertSee('uploadLimitBytes:', false)
            ->assertSee((string) UploadLimit::bytes(204800), false);
    }

    // ── Student visibility ───────────────────────────────────────────────────

    public function test_student_only_sees_recordings_of_their_own_classes(): void
    {
        $this->recordingFor($this->course, ['title' => 'My class replay']);
        $foreign = $this->recordingFor(Course::factory()->create(), ['title' => 'Someone elses replay']);

        $this->actingAs($this->studentUser)->get(route('student.recordings'))
            ->assertOk()
            ->assertSee('My class replay')
            ->assertDontSee('Someone elses replay');

        $this->actingAs($this->studentUser)->get(route('student.recordings.show', $foreign->id))->assertNotFound();
    }

    public function test_student_player_uses_an_embeddable_youtube_link(): void
    {
        $rec = $this->recordingFor($this->course, ['video_url' => 'https://www.youtube.com/watch?v=abcdefghijk&t=10']);

        $this->actingAs($this->studentUser)->get(route('student.recordings.show', $rec->id))
            ->assertOk()
            ->assertSee('https://www.youtube.com/embed/abcdefghijk', false);
    }

    public function test_embed_url_conversion(): void
    {
        $embed = fn (string $url) => (new RecordedClass(['video_url' => $url]))->embedUrl();

        $this->assertSame('https://www.youtube.com/embed/abcdefghijk', $embed('https://youtu.be/abcdefghijk?si=xyz'));
        $this->assertSame('https://www.youtube.com/embed/abcdefghijk', $embed('https://www.youtube.com/shorts/abcdefghijk'));
        $this->assertSame('https://player.vimeo.com/video/123456', $embed('https://vimeo.com/123456'));
        $this->assertSame('https://drive.google.com/file/d/1AbC_123xyz-QRS/preview', $embed('https://drive.google.com/file/d/1AbC_123xyz-QRS/view?usp=sharing'));
        $this->assertNull($embed('https://zoom.us/rec/share/abc'));
    }

    public function test_upload_limit_parses_php_ini_shorthand(): void
    {
        $this->assertSame(8 * 1024 * 1024, UploadLimit::toBytes('8M'));
        $this->assertSame(2 * 1024 ** 3, UploadLimit::toBytes('2G'));
        $this->assertSame(512 * 1024, UploadLimit::toBytes('512K'));
        $this->assertSame(0, UploadLimit::toBytes(''));
    }

    public function test_too_large_page_is_friendly(): void
    {
        $this->assertStringContainsString('too large to upload', view('errors.413')->render());
    }
}
