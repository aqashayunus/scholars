<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordedClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'subject_id',
        'title',
        'topic_name',
        'video_url',
        'thumbnail',
        'duration_minutes',
        'views_count',
        'summary',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
