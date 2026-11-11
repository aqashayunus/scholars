<?php

namespace Database\Factories;

use App\Models\StudentAssignmentSubmission;
use App\Models\SubmissionPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubmissionPage>
 */
class SubmissionPageFactory extends Factory
{
    protected $model = SubmissionPage::class;

    public function definition(): array
    {
        return [
            'submission_id' => StudentAssignmentSubmission::factory(),
            'page_number' => 1,
            'annotation_json' => null,
            'stroke_count' => 0,
            'page_version' => 1,
        ];
    }
}
