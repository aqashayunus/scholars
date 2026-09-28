<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectTopic extends Model
{
    protected $fillable = ['subject_id', 'title', 'chapter_number', 'description'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function subtopics()
    {
        return $this->hasMany(SubjectSubtopic::class, 'topic_id')->orderBy('order', 'asc');
    }

    public function learningMaterials()
    {
        return $this->hasMany(LearningMaterial::class, 'topic_id');
    }
}
