<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAssignmentSubmission extends Model
{
    use HasFactory;

    protected $table = 'student_assignment_submissions';

    protected $fillable = [
        'student_assignment_id',
        'assignment_id',
        'student_id',
        'status',
        'current_page',
        'zoom_level',
        'total_pages',
        'version',
        'final_pdf_path',
        'started_at',
        'last_saved_at',
        'submitted_at',
    ];

    protected $casts = [
        'current_page' => 'integer',
        'zoom_level'   => 'float',
        'total_pages'  => 'integer',
        'version'      => 'integer',
        'started_at'   => 'datetime',
        'last_saved_at'=> 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function isDraft(): bool
    {
        return $this->status === 'draft' && $this->submitted_at === null;
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'graded', 'returned']) || $this->submitted_at !== null;
    }

    public function isLocked(): bool
    {
        return $this->isSubmitted();
    }

    public function studentAssignment()
    {
        return $this->belongsTo(StudentAssignment::class, 'student_assignment_id');
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function pages()
    {
        return $this->hasMany(SubmissionPage::class, 'submission_id')->orderBy('page_number');
    }

    public function getFinalPdfUrlAttribute(): ?string
    {
        return $this->final_pdf_path ? route('teacher.submissions.final-pdf', $this->id) : null;
    }
}
