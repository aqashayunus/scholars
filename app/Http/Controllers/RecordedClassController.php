<?php

namespace App\Http\Controllers;

use App\Models\RecordedClass;
use App\Models\Subject;
use Illuminate\Http\Request;

class RecordedClassController extends Controller
{
    public function index(Request $request)
    {
        $query = RecordedClass::with('subject');

        if ($request->has('subject') && $request->subject !== 'all') {
            $query->whereHas('subject', function($q) use ($request) {
                $q->where('slug', $request->subject);
            });
        }

        $recordings = $query->orderByDesc('created_at')->get();
        $subjects   = Subject::where('is_active', true)->get();

        return view('student.recordings.index', compact('recordings', 'subjects'));
    }

    public function show($id)
    {
        $recording  = RecordedClass::with('subject', 'course')->findOrFail($id);
        $recording->increment('views_count');
        $related    = RecordedClass::where('id', '!=', $id)->take(3)->get();

        return view('student.recordings.show', compact('recording', 'related'));
    }
}
