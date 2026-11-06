<?php

namespace Database\Factories;

use App\Models\FeeStructure;
use App\Models\FeeStructureSubject;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeStructureSubject>
 */
class FeeStructureSubjectFactory extends Factory
{
    protected $model = FeeStructureSubject::class;

    public function definition(): array
    {
        return [
            'fee_structure_id' => FeeStructure::factory(),
            'subject_id'       => Subject::factory(),
            'is_required'      => true,
            'sort_order'       => 0,
        ];
    }
}
