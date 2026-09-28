<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LearningMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'subject_id',
        'topic_id',
        'teacher_id',
        'title',
        'type',
        'topic',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'visibility',
        'status',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function topicItem()
    {
        return $this->belongsTo(SubjectTopic::class, 'topic_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
