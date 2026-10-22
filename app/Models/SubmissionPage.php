<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionPage extends Model
{
    use HasFactory;

    protected $table = 'submission_pages';

    protected $fillable = [
        'submission_id',
        'page_number',
        'annotation_json',
        'stroke_count',
        'page_version',
    ];

    protected $casts = [
        'page_number'  => 'integer',
        'stroke_count' => 'integer',
        'page_version' => 'integer',
    ];

    public function submission()
    {
        return $this->belongsTo(StudentAssignmentSubmission::class, 'submission_id');
    }

    /**
     * Get decoded annotation strokes data array safely.
     */
    public function getAnnotationData(): array
    {
        if (empty($this->annotation_json)) {
            return [];
        }
        $decoded = json_decode($this->annotation_json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
