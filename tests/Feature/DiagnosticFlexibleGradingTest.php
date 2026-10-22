<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\StudentAssignment;
use App\Models\User;
use App\Services\Grading\DiagnosticGradingEngine;
use App\Services\Grading\DiagnosticRuleParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnosticFlexibleGradingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $studentUser;
    protected Student $student;
    protected DiagnosticGradingEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->studentUser = User::factory()->create([
            'role'      => 'student',
            'is_active' => true,
        ]);

        $this->student = Student::firstOrCreate(
            ['user_id' => $this->studentUser->id],
            [
                'parent_id'  => null,
                'level'      => 'Form 4',
                'birth_date' => '2008-01-01',
                'school'     => 'SMK Olympian',
                'gender'     => 'Male',
            ]
        );

        $this->engine = new DiagnosticGradingEngine();
    }

    /**
     * Test 1: Single standard answer [Answer]
     */
    public function test_single_answer_rule()
    {
        $rule = '[Solid]';
        $ast = DiagnosticRuleParser::parse($rule, 1.0);
        $this->assertEquals('alternatives', $ast['type']);

        // Case-insensitive match with whitespace normalization
        $result = $this->engine->gradeQuestion($rule, 'solid', 1.0);
        $this->assertTrue($result['is_correct']);
        $this->assertEquals(1.0, $result['score']);

        $result2 = $this->engine->gradeQuestion($rule, '  SOLID. ', 1.0);
        $this->assertTrue($result2['is_correct']);

        $result3 = $this->engine->gradeQuestion($rule, 'liquid', 1.0);
        $this->assertFalse($result3['is_correct']);
        $this->assertEquals(0.0, $result3['score']);
    }

    /**
     * Test 2: Alternative answers / synonyms [A, B, C]
     */
    public function test_alternative_answers_rule()
    {
        $rule = '[Solid, pepejal]';
        
        $res1 = $this->engine->gradeQuestion($rule, 'solid', 1.0);
        $this->assertTrue($res1['is_correct']);
        $this->assertEquals(1.0, $res1['score']);

        $res2 = $this->engine->gradeQuestion($rule, 'pepejal', 1.0);
        $this->assertTrue($res2['is_correct']);
        $this->assertEquals(1.0, $res2['score']);

        $res3 = $this->engine->gradeQuestion($rule, 'cecair', 1.0);
        $this->assertFalse($res3['is_correct']);
        $this->assertEquals(0.0, $res3['score']);
    }

    /**
     * Test 3: Multiple required answers [A];[B] with partial credit
     */
    public function test_multi_required_answers_with_partial_marks()
    {
        $rule = '[Solid];[Liquid]';

        // Both present -> full marks
        $full = $this->engine->gradeQuestion($rule, 'solid and liquid', 2.0);
        $this->assertTrue($full['is_correct']);
        $this->assertEquals(2.0, $full['score']);

        // Only one present -> 50% partial marks
        $partial = $this->engine->gradeQuestion($rule, 'solid', 2.0);
        $this->assertFalse($partial['is_correct']);
        $this->assertTrue($partial['is_partial']);
        $this->assertEquals(1.0, $partial['score']);
        $this->assertContains('solid', $partial['matched_criteria']);
        $this->assertContains('liquid', $partial['unmatched_criteria']);

        // Neither present -> 0 marks
        $none = $this->engine->gradeQuestion($rule, 'gas', 2.0);
        $this->assertFalse($none['is_correct']);
        $this->assertFalse($none['is_partial']);
        $this->assertEquals(0.0, $none['score']);
    }

    /**
     * Test 4: Criteria marking + [Keyword] @marks
     */
    public function test_criteria_marking_rule()
    {
        $rule = '+ [photosynthesis] @1 + [chlorophyll] @1.5';

        // Full match
        $resBoth = $this->engine->gradeQuestion($rule, 'Photosynthesis occurs in the presence of chlorophyll.', 2.5);
        $this->assertTrue($resBoth['is_correct']);
        $this->assertEquals(2.5, $resBoth['score']);

        // Single criteria match
        $resOne = $this->engine->gradeQuestion($rule, 'Plants perform photosynthesis.', 2.5);
        $this->assertFalse($resOne['is_correct']);
        $this->assertTrue($resOne['is_partial']);
        $this->assertEquals(1.0, $resOne['score']);
        $this->assertContains('photosynthesis', $resOne['matched_criteria']);
    }

    /**
     * Test 5: Numerical tolerance [Number] ±tolerance
     */
    public function test_numerical_tolerance_rule()
    {
        $rule = '[9.8] ±0.1';

        // Exact match
        $r1 = $this->engine->gradeQuestion($rule, '9.8', 1.0);
        $this->assertTrue($r1['is_correct']);

        // Upper bound (9.9)
        $r2 = $this->engine->gradeQuestion($rule, '9.9', 1.0);
        $this->assertTrue($r2['is_correct']);

        // Lower bound (9.7)
        $r3 = $this->engine->gradeQuestion($rule, '9.7', 1.0);
        $this->assertTrue($r3['is_correct']);

        // Outside tolerance (9.65 and 9.95)
        $r4 = $this->engine->gradeQuestion($rule, '9.65', 1.0);
        $this->assertFalse($r4['is_correct']);

        $r5 = $this->engine->gradeQuestion($rule, '9.95', 1.0);
        $this->assertFalse($r5['is_correct']);

        // Strips units e.g. "9.81 m/s^2"
        $r6 = $this->engine->gradeQuestion($rule, '9.81 m/s²', 1.0);
        $this->assertTrue($r6['is_correct']);
    }

    /**
     * Test 6: Exact verbatim match =[Answer]
     */
    public function test_exact_match_rule()
    {
        $rule = '=[DNA]';

        // Exact uppercase
        $r1 = $this->engine->gradeQuestion($rule, 'DNA', 1.0);
        $this->assertTrue($r1['is_correct']);

        // Lowercase should be rejected by exact rule
        $r2 = $this->engine->gradeQuestion($rule, 'dna', 1.0);
        $this->assertFalse($r2['is_correct']);
    }

    /**
     * Test 7: Fuzzy typo-tolerant match ~[Answer]
     */
    public function test_fuzzy_match_rule()
    {
        $rule = '~[Mitochondria]';

        // Exact match
        $r1 = $this->engine->gradeQuestion($rule, 'Mitochondria', 1.0);
        $this->assertTrue($r1['is_correct']);

        // 1-letter typo ("Mitochondra") should match
        $r2 = $this->engine->gradeQuestion($rule, 'Mitochondra', 1.0);
        $this->assertTrue($r2['is_correct']);

        // Completely different word should fail
        $r3 = $this->engine->gradeQuestion($rule, 'Chloroplast', 1.0);
        $this->assertFalse($r3['is_correct']);
    }

    /**
     * Test 8: Manual teacher review [MANUAL]
     */
    public function test_manual_review_rule()
    {
        $rule = '[MANUAL]';
        $result = $this->engine->gradeQuestion($rule, 'Long essay answer by student', 5.0);

        $this->assertTrue($result['is_manual']);
        $this->assertEquals(0.0, $result['score']);
        $this->assertStringContainsString('Manual review', $result['feedback']);
    }

    /**
     * Test 9: Legacy fallback (comma/pipe separated)
     */
    public function test_legacy_fallback_grading()
    {
        $rule = 'solid, liquid';
        $r1 = $this->engine->gradeQuestion($rule, 'solid', 1.0);
        $this->assertTrue($r1['is_correct']);

        $r2 = $this->engine->gradeQuestion($rule, 'liquid', 1.0);
        $this->assertTrue($r2['is_correct']);

        $r3 = $this->engine->gradeQuestion($rule, 'gas', 1.0);
        $this->assertFalse($r3['is_correct']);
    }

    /**
     * Test 10: Admin live preview route POST /admin/diagnostic/test-rule
     */
    public function test_admin_can_preview_grading_rule()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.diagnostic.test-rule'), [
                'rule'           => '+ [chlorophyll] @1 + [sunlight] @1',
                'student_answer' => 'Plants absorb sunlight and chlorophyll helps.',
                'max_marks'      => 2,
                'question_type'  => 'fill_in_blank',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'    => true,
                'is_correct' => true,
                'score'      => 2.0,
                'max_marks'  => 2.0,
            ]);
    }

    /**
     * Test 11: End-to-end student submission with custom marks and symbol rules
     */
    public function test_student_submission_with_flexible_grading()
    {
        $assignment = Assignment::create([
            'title'              => 'Advanced Diagnostic Test',
            'type'               => 'Diagnostic Test',
            'time_limit_minutes' => 30,
            'total_questions'    => 3,
            'is_global'          => false,
            'questions_data'     => [
                [
                    'number'         => 1,
                    'type'           => 'mcq',
                    'title'          => 'Question 1',
                    'correct_answer' => 'B',
                    'marks'          => 1.0,
                ],
                [
                    'number'         => 2,
                    'type'           => 'fill_in_blank',
                    'title'          => 'Question 2',
                    'correct_answer' => '[Solid, pepejal]',
                    'marks'          => 2.0,
                ],
                [
                    'number'         => 3,
                    'type'           => 'fill_in_blank',
                    'title'          => 'Question 3',
                    'correct_answer' => '[9.8] ±0.1',
                    'marks'          => 2.0,
                ],
            ],
        ]);

        $response = $this->actingAs($this->studentUser)
            ->postJson(route('student.diagnostic.submit', $assignment->id), [
                'answers' => [
                    '1' => 'B',       // MCQ correct (1/1)
                    '2' => 'pepejal', // Synonym correct (2/2)
                    '3' => '9.75',    // Within tolerance (2/2)
                ],
                'time_taken_seconds' => 120,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'        => true,
                'score'          => 100, // 5 / 5 = 100%
                'total_score'    => 5.0,
                'total_possible' => 5.0,
                'correct_count'  => 3,
            ]);

        $studentAssignment = StudentAssignment::where('student_id', $this->student->id)
            ->where('assignment_id', $assignment->id)
            ->first();

        $this->assertNotNull($studentAssignment);
        $this->assertEquals('graded', $studentAssignment->status);
        $this->assertEquals(100, $studentAssignment->score);
        $this->assertEquals('Cemerlang', $studentAssignment->tahap_kepandaian);
        $this->assertIsArray($studentAssignment->answers_json);
        $this->assertCount(3, $studentAssignment->answers_json);
    }
}
