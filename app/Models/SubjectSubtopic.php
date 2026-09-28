<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectSubtopic extends Model
{
    protected $fillable = ['topic_id', 'title', 'default_status', 'order'];

    public function topic()
    {
        return $this->belongsTo(SubjectTopic::class, 'topic_id');
    }

    public function studentProgress()
    {
        return $this->hasMany(StudentSubtopicProgress::class, 'subtopic_id');
    }
}
