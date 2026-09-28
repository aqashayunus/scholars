<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAssignment extends Model
{
    protected $fillable = [
        'student_id',
        'assignment_id',
        'status',
        'score',
        'tahap_kepandaian',
        'answers_json',
        'time_taken_seconds',
        'submitted_at',
        'submission_notes',
        'teacher_feedback',
        'file_path',
        'file_name',
    ];
    protected $casts = [
        'submitted_at'       => 'datetime',
        'answers_json'       => 'array',
        'time_taken_seconds' => 'integer',
        'score'              => 'integer',
    ];

    public function getTahapBadgeClass(): string
    {
        return match($this->tahap_kepandaian) {
            'Cemerlang'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Baik'            => 'bg-blue-50 text-blue-700 border-blue-200',
            'Sederhana'       => 'bg-amber-50 text-amber-700 border-amber-200',
            'Perlu Bimbingan' => 'bg-rose-50 text-rose-700 border-rose-200',
            default           => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    public function getTahapDescription(): string
    {
        return match($this->tahap_kepandaian) {
            'Cemerlang'       => 'Tahap Penguasaan Tinggi (Kefahaman & Aplikasi Mantap)',
            'Baik'            => 'Tahap Penguasaan Sederhana Tinggi (Kefahaman Baik)',
            'Sederhana'       => 'Tahap Penguasaan Asas (Memerlukan Pengukuhan Konsep)',
            'Perlu Bimbingan' => 'Tahap Penguasaan Rendah (Memerlukan Bimbingan Intensif)',
            default           => 'Belum Diuji',
        };
    }

    public function student()    { return $this->belongsTo(Student::class); }
    public function assignment() { return $this->belongsTo(Assignment::class); }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}
