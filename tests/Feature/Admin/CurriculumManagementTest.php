<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\FinancialAuditLog;
use App\Models\Subject;
use App\Models\SubjectSubtopic;
use App\Models\SubjectTopic;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurriculumManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ── Course ───────────────────────────────────────────────────────────────

    public function test_admin_can_create_course_and_it_leaves_the_subject_fee_alone(): void
    {
        $subject = Subject::factory()->create(['monthly_fee' => 60]);
        $teacher = Teacher::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'title' => 'SPM Add Maths',
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'level' => 'SPM',
            'monthly_fee' => 150, // stale field from an old form: must be ignored
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('courses', ['title' => 'SPM Add Maths', 'subject_id' => $subject->id]);
        $this->assertEquals(60, $subject->refresh()->monthly_fee);
    }

    public function test_creating_course_without_teacher_falls_back_to_subjects_own_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create();
        $subject->teachers()->attach($teacher->id);

        $response = $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'title' => 'Fallback Course',
            'subject_id' => $subject->id,
            'level' => 'SPM',
            'status' => 'draft',
        ]);

        $response->assertRedirect();
        $course = Course::where('title', 'Fallback Course')->first();
        $this->assertSame($teacher->id, $course->teacher_id);
    }

    public function test_admin_can_update_course_without_clearing_existing_teacher_when_teacher_id_omitted(): void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create(['monthly_fee' => 75]);
        $course = Course::factory()->create(['subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        $response = $this->actingAs($this->admin)->put(route('admin.courses.update', $course->id), [
            'title' => 'Updated Title',
            'subject_id' => $subject->id,
            'level' => 'SPM',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $course->refresh();
        $this->assertSame('Updated Title', $course->title);
        $this->assertSame($teacher->id, $course->teacher_id);
        $this->assertEquals(75, $subject->refresh()->monthly_fee);
    }

    public function test_admin_can_pick_the_teacher_when_creating_a_course(): void
    {
        $subjectTeacher = Teacher::factory()->create();
        $chosen = Teacher::factory()->create();
        $subject = Subject::factory()->create();
        $subject->teachers()->attach($subjectTeacher->id);

        $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'title' => 'Chosen Teacher Course',
            'subject_id' => $subject->id,
            'teacher_id' => $chosen->id,
            'level' => 'SPM',
            'status' => 'published',
        ])->assertSessionHasNoErrors();

        $this->assertSame($chosen->id, Course::where('title', 'Chosen Teacher Course')->value('teacher_id'));
    }

    public function test_admin_can_change_a_courses_teacher(): void
    {
        $old = Teacher::factory()->create();
        $new = Teacher::factory()->create();
        $subject = Subject::factory()->create();
        $course = Course::factory()->create(['subject_id' => $subject->id, 'teacher_id' => $old->id]);

        $this->actingAs($this->admin)->put(route('admin.courses.update', $course->id), [
            'title' => $course->title,
            'subject_id' => $subject->id,
            'teacher_id' => $new->id,
            'level' => 'SPM',
            'status' => 'published',
        ])->assertSessionHasNoErrors();

        $this->assertSame($new->id, $course->refresh()->teacher_id);
    }

    public function test_course_forms_offer_a_teacher_dropdown(): void
    {
        $teacher = Teacher::factory()->create();

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="create_course_teacher_id"', false)
            ->assertSee('id="edit_course_teacher_id"', false)
            ->assertSee('<option value="'.$teacher->id.'">', false);
    }

    public function test_admin_can_delete_course(): void
    {
        $course = Course::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.courses.destroy', $course->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    // ── Subject ──────────────────────────────────────────────────────────────

    public function test_admin_can_create_single_subject_with_auto_generated_slug_and_code(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.subjects.store'), [
            'name' => 'Biology',
            'education_type' => 'secondary',
            'stage' => 'Upper Secondary',
            'form_year' => 'Form 5',
            'category' => 'Core',
            'level' => 'SPM',
            'color' => '#22c55e',
        ]);

        $response->assertRedirect();
        $subject = Subject::where('name', 'Biology')->first();
        $this->assertNotNull($subject);
        $this->assertNotEmpty($subject->slug);
        $this->assertStringStartsWith('KPM-', $subject->code);
    }

    public function test_admin_can_bulk_create_subjects(): void
    {
        $before = Subject::count();

        $response = $this->actingAs($this->admin)->post(route('admin.subjects.store'), [
            'subjects' => [
                ['name' => 'Underwater Basket Weaving', 'form_year' => 'Form 4'],
                ['name' => 'Astro Navigation', 'form_year' => 'Form 4'],
                ['name' => ''], // skipped: no name
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '2 KPM curriculum subject(s) created successfully.');
        $this->assertSame($before + 2, Subject::count());
        $this->assertDatabaseHas('subjects', ['name' => 'Underwater Basket Weaving']);
        $this->assertDatabaseHas('subjects', ['name' => 'Astro Navigation']);
    }

    public function test_duplicate_subject_slug_gets_suffixed(): void
    {
        Subject::factory()->create(['slug' => 'history-form-4']);

        $response = $this->actingAs($this->admin)->post(route('admin.subjects.store'), [
            'name' => 'History',
            'slug' => 'history-form-4',
            'education_type' => 'secondary',
            'stage' => 'Upper Secondary',
            'form_year' => 'Form 4',
            'category' => 'Core',
            'level' => 'SPM',
            'color' => '#000000',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subjects', ['slug' => 'history-form-4-1']);
    }

    private function subjectUpdatePayload(Subject $subject, array $overrides = []): array
    {
        return array_merge([
            'name' => $subject->name,
            'slug' => $subject->slug,
            'education_type' => 'primary',
            'stage' => 'Primary',
            'form_year' => 'Year 4',
            'category' => 'Core',
            'level' => 'KSSR',
            'color' => '#3b82f6',
            'is_active' => '1',
            'monthly_fee' => $subject->monthly_fee ?? 0,
        ], $overrides);
    }

    public function test_admin_sets_the_monthly_fee_on_subject_edit_and_it_is_audit_logged(): void
    {
        $subject = Subject::factory()->create(['monthly_fee' => 60]);

        $response = $this->actingAs($this->admin)->put(
            route('admin.subjects.update', $subject->id),
            $this->subjectUpdatePayload($subject, ['monthly_fee' => 85.50])
        );

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertEquals(85.50, $subject->refresh()->monthly_fee);
        $log = FinancialAuditLog::where('action', 'subject_fee_updated')->where('target_id', $subject->id)->sole();
        $this->assertSame($this->admin->id, $log->user_id);
        $this->assertEquals(['subject' => $subject->name, 'old_fee' => 60, 'new_fee' => 85.5], json_decode($log->details, true));
    }

    public function test_subject_edit_without_a_fee_change_writes_no_audit_log(): void
    {
        $subject = Subject::factory()->create(['monthly_fee' => 60]);

        $this->actingAs($this->admin)->put(
            route('admin.subjects.update', $subject->id),
            $this->subjectUpdatePayload($subject, ['name' => 'Renamed Subject'])
        )->assertSessionHasNoErrors();

        $this->assertSame('Renamed Subject', $subject->refresh()->name);
        $this->assertSame(0, FinancialAuditLog::where('action', 'subject_fee_updated')->count());
    }

    public function test_subject_edit_rejects_a_missing_or_negative_fee(): void
    {
        $subject = Subject::factory()->create(['monthly_fee' => 60]);

        $payload = $this->subjectUpdatePayload($subject);
        unset($payload['monthly_fee']);
        $this->actingAs($this->admin)->put(route('admin.subjects.update', $subject->id), $payload)
            ->assertSessionHasErrors('monthly_fee');

        $this->actingAs($this->admin)->put(route('admin.subjects.update', $subject->id), $this->subjectUpdatePayload($subject, ['monthly_fee' => -5]))
            ->assertSessionHasErrors('monthly_fee');

        $this->assertEquals(60, $subject->refresh()->monthly_fee);
    }

    public function test_old_billing_subject_fees_url_redirects_to_the_subjects_tab(): void
    {
        $this->actingAs($this->admin)->get('/admin/billing/subject-fees')
            ->assertRedirect('/admin/dashboard#courses-subjects');
    }

    public function test_admin_can_delete_subject(): void
    {
        $subject = Subject::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.subjects.destroy', $subject->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    // ── Topic ────────────────────────────────────────────────────────────────

    public function test_storing_topic_without_valid_subject_returns_error(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.topics.store'), [
            'subject_id' => 99999,
            'title' => 'Orphan Topic',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame(0, SubjectTopic::count());
    }

    public function test_admin_can_create_single_topic_with_auto_incremented_chapter(): void
    {
        $subject = Subject::factory()->create();
        SubjectTopic::create(['subject_id' => $subject->id, 'chapter_number' => 3, 'title' => 'Existing']);

        $response = $this->actingAs($this->admin)->post(route('admin.topics.store'), [
            'subject_id' => $subject->id,
            'title' => 'New Chapter',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subject_topics', ['title' => 'New Chapter', 'chapter_number' => 4]);
    }

    public function test_admin_can_bulk_create_topics(): void
    {
        $subject = Subject::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.topics.store'), [
            'subject_id' => $subject->id,
            'topics' => [
                ['title' => 'Chapter A'],
                ['title' => 'Chapter B'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertSame(2, SubjectTopic::where('subject_id', $subject->id)->count());
    }

    public function test_admin_can_delete_topic(): void
    {
        $subject = Subject::factory()->create();
        $topic = SubjectTopic::create(['subject_id' => $subject->id, 'chapter_number' => 1, 'title' => 'To Remove']);

        $response = $this->actingAs($this->admin)->delete(route('admin.topics.destroy', $topic->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('subject_topics', ['id' => $topic->id]);
    }

    // ── Subtopic ─────────────────────────────────────────────────────────────

    public function test_admin_can_create_single_subtopic(): void
    {
        $subject = Subject::factory()->create();
        $topic = SubjectTopic::create(['subject_id' => $subject->id, 'chapter_number' => 1, 'title' => 'Chapter 1']);

        $response = $this->actingAs($this->admin)->post(route('admin.topics.subtopics.store', $topic->id), [
            'title' => 'Checkpoint 1',
            'default_status' => 'Not Started',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subject_subtopics', ['topic_id' => $topic->id, 'title' => 'Checkpoint 1']);
    }

    public function test_admin_can_bulk_create_subtopics(): void
    {
        $subject = Subject::factory()->create();
        $topic = SubjectTopic::create(['subject_id' => $subject->id, 'chapter_number' => 1, 'title' => 'Chapter 1']);

        $response = $this->actingAs($this->admin)->post(route('admin.topics.subtopics.store', $topic->id), [
            'subtopics' => [
                ['title' => 'Checkpoint A'],
                ['title' => 'Checkpoint B'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertSame(2, SubjectSubtopic::where('topic_id', $topic->id)->count());
    }

    public function test_admin_can_update_subtopic(): void
    {
        $subject = Subject::factory()->create();
        $topic = SubjectTopic::create(['subject_id' => $subject->id, 'chapter_number' => 1, 'title' => 'Chapter 1']);
        $subtopic = $topic->subtopics()->create(['title' => 'Old', 'default_status' => 'Not Started', 'order' => 1]);

        $response = $this->actingAs($this->admin)->put(route('admin.subtopics.update', $subtopic->id), [
            'title' => 'Renamed',
            'default_status' => 'Mastered',
            'order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subject_subtopics', ['id' => $subtopic->id, 'title' => 'Renamed']);
    }

    public function test_admin_can_delete_subtopic(): void
    {
        $subject = Subject::factory()->create();
        $topic = SubjectTopic::create(['subject_id' => $subject->id, 'chapter_number' => 1, 'title' => 'Chapter 1']);
        $subtopic = $topic->subtopics()->create(['title' => 'Removable', 'default_status' => 'Not Started', 'order' => 1]);

        $response = $this->actingAs($this->admin)->delete(route('admin.subtopics.destroy', $subtopic->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('subject_subtopics', ['id' => $subtopic->id]);
    }
}
