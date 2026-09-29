<?php

namespace App\Services\Admin;

use App\Models\TeachingGig;

class GigService
{
    public function publish(array $validated): TeachingGig
    {
        $validated['status'] = 'open';
        $validated['surge_bonus'] = $validated['surge_bonus'] ?? 0;

        return TeachingGig::create($validated);
    }

    public function remove(int $id): string
    {
        $gig = TeachingGig::findOrFail($id);
        $title = $gig->title;
        $gig->delete();

        return $title;
    }
}
