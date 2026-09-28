<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'application_status', 'qualification', 'experience_years', 'bio',
        'teaching_style', 'rating', 'total_students', 'photo', 'specialization'
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function courses()           { return $this->hasMany(Course::class); }
    public function learningMaterials() { return $this->hasMany(LearningMaterial::class); }
    public function subjects()          { return $this->belongsToMany(Subject::class, 'teacher_subject'); }

    public function getNameAttribute()  { return $this->user->name; }
    public function getEmailAttribute() { return $this->user->email; }

    public function getStarsAttribute(): string
    {
        $full  = floor($this->rating);
        $half  = ($this->rating - $full) >= 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;
        return str_repeat('★', $full) . str_repeat('⯨', $half) . str_repeat('☆', $empty);
    }
}
