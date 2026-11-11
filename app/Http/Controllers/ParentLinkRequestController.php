<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ParentLinkRequestController extends Controller
{
    /**
     * Approve a pending parent-student link request via signed email URL.
     */
    public function approve(int $pivot)
    {
        $row = DB::table('parent_student')->where('id', $pivot)->first();

        if (! $row) {
            return view('parent-link-requests.result', [
                'outcome' => 'not_found',
            ]);
        }

        if ($row->status === 'active') {
            return view('parent-link-requests.result', ['outcome' => 'already_approved']);
        }

        if ($row->status !== 'pending') {
            return view('parent-link-requests.result', ['outcome' => 'already_resolved']);
        }

        DB::table('parent_student')->where('id', $pivot)->update([
            'status' => 'active',
            'updated_at' => now(),
        ]);

        return view('parent-link-requests.result', ['outcome' => 'approved']);
    }

    /**
     * Deny a pending parent-student link request via signed email URL.
     */
    public function deny(int $pivot)
    {
        $row = DB::table('parent_student')->where('id', $pivot)->first();

        if (! $row) {
            return view('parent-link-requests.result', ['outcome' => 'not_found']);
        }

        if ($row->status !== 'pending') {
            return view('parent-link-requests.result', ['outcome' => 'already_resolved']);
        }

        DB::table('parent_student')->where('id', $pivot)->update([
            'status' => 'denied',
            'updated_at' => now(),
        ]);

        return view('parent-link-requests.result', ['outcome' => 'denied']);
    }
}
