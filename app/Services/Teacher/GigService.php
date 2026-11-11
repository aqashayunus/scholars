<?php

namespace App\Services\Teacher;

use App\Models\Teacher;
use App\Models\TeacherEarning;
use App\Models\TeachingGig;

class GigService
{
    private function getTeacher(): Teacher
    {
        return Teacher::where('user_id', auth()->id())->firstOrFail();
    }

    public function marketplaceData(): array
    {
        $teacher = $this->getTeacher();
        $openGigs = TeachingGig::where('status', 'open')->orderBy('scheduled_at')->get();
        $myGigs = TeachingGig::where('assigned_teacher_id', $teacher->id)->orderByDesc('scheduled_at')->get();

        return compact('teacher', 'openGigs', 'myGigs');
    }

    public function claim(int $id): TeachingGig
    {
        $teacher = $this->getTeacher();
        $gig = TeachingGig::where('id', $id)->where('status', 'open')->firstOrFail();

        $gig->update([
            'status' => 'claimed',
            'assigned_teacher_id' => $teacher->id,
            'claimed_at' => now(),
        ]);

        return $gig;
    }

    public function checkIn(int $id): TeachingGig
    {
        $teacher = $this->getTeacher();
        $gig = TeachingGig::where('id', $id)->where('assigned_teacher_id', $teacher->id)->firstOrFail();

        $gig->update(['status' => 'checked_in', 'checked_in_at' => now()]);

        return $gig;
    }

    public function checkOut(int $id): TeachingGig
    {
        $teacher = $this->getTeacher();
        $gig = TeachingGig::where('id', $id)->where('assigned_teacher_id', $teacher->id)->firstOrFail();

        $gig->update(['status' => 'completed', 'completed_at' => now()]);
        $teacher->increment('total_gigs_completed');

        return $gig;
    }

    public function walletData(): array
    {
        $teacher = $this->getTeacher();
        $earnings = TeacherEarning::where('teacher_id', $teacher->id)->orderByDesc('created_at')->get();

        return compact('teacher', 'earnings');
    }
}
