<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'qualification',
        'experience_years',
        'preferred_subjects',
        'teaching_level',
        'resume_path',
        'certificate_path',
        'transcript_path',
        'additional_document_path',
        'status',
        'admin_notes',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get an array of preferred teaching levels / years.
     *
     * @return array<string>
     */
    public function getTeachingLevelsListAttribute(): array
    {
        if (!empty($this->teaching_level)) {
            if (is_array($this->teaching_level)) {
                return $this->teaching_level;
            }
            $decoded = json_decode($this->teaching_level, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            return array_values(array_filter(array_map('trim', explode(',', $this->teaching_level))));
        }

        // Contextual fallback from preferred subjects or qualifications for older records
        $text = ($this->preferred_subjects ?? '') . ' ' . ($this->qualification ?? '');
        $inferred = [];
        if (preg_match('/(form\s*[45]|spm|upper\s*secondary|add\s*math|addmath|physics|chemistry|biology)/i', $text)) {
            $inferred[] = 'Form 4–5 (SPM)';
        }
        if (preg_match('/(form\s*[123]|pt3|lower\s*secondary)/i', $text)) {
            $inferred[] = 'Form 1–3 (Lower Secondary)';
        }
        if (preg_match('/(year\s*[456]|upsr|primary|tahap\s*2)/i', $text)) {
            $inferred[] = 'Year 4–6 (Primary)';
        }

        return !empty($inferred) ? array_unique($inferred) : ['All Levels'];
    }
}
