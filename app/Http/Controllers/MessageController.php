<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    // ──────────────────────────────────────────────────
    // STUDENT: page view
    // ──────────────────────────────────────────────────
    public function studentIndex()
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            return redirect()->route($user->getDashboardRoute());
        }

        $student = Student::firstOrCreate([
            'user_id' => $user->id
        ], [
            'level' => 'SPM',
            'total_points' => 0,
        ]);
        $student->load('enrollments.course.teacher.user');

        // All teachers the student is enrolled under
        $contacts = $student->enrollments
            ->map(fn($e) => $e->course->teacher?->user)
            ->filter()
            ->unique('id')
            ->values();

        // Pre-map for JS (avoids Blade @json multi-line closure issue)
        $contactsData = $contacts->map(function ($c) {
            $words = explode(' ', $c->name);
            return [
                'id'       => $c->id,
                'name'     => $c->name,
                'initials' => strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : '')),
                'role'     => $c->role,
            ];
        })->values();

        return view('student.messages', compact('contacts', 'contactsData'));
    }

    // ──────────────────────────────────────────────────
    // TEACHER: page view
    // ──────────────────────────────────────────────────
    public function teacherIndex()
    {
        $me = auth()->id();

        $teacher = Teacher::where('user_id', $me)
            ->with('courses.enrollments.student.user')
            ->firstOrFail();

        // 1. Students enrolled in teacher's courses
        $enrolledUsers = $teacher->courses
            ->flatMap(fn($c) => $c->enrollments)
            ->map(fn($e) => $e->student?->user)
            ->filter()
            ->keyBy('id');

        // 2. Anyone who has messaged the teacher (even if not enrolled)
        $messageContactIds = Message::where(function ($q) use ($me) {
                $q->where('sender_id', $me)->orWhere('receiver_id', $me);
            })
            ->get()
            ->flatMap(fn($m) => [$m->sender_id, $m->receiver_id])
            ->unique()
            ->reject(fn($id) => $id == $me)
            ->values();

        $messageUsers = User::whereIn('id', $messageContactIds)->get()->keyBy('id');

        // Merge both sets — enrolled students first, then anyone from messages
        // unique('id') ensures no duplicates when a student is both enrolled AND has messaged
        $contacts = $enrolledUsers->merge($messageUsers)->unique('id')->values();

        // Pre-map for JS
        $contactsData = $contacts->map(function ($c) {
            $words = explode(' ', $c->name);
            return [
                'id'       => $c->id,
                'name'     => $c->name,
                'initials' => strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : '')),
                'role'     => $c->role,
            ];
        })->values();

        return view('teacher.messages', compact('contacts', 'contactsData'));
    }

    // ──────────────────────────────────────────────────
    // AJAX: Get conversation with a specific user
    // ──────────────────────────────────────────────────
    public function conversation(int $userId)
    {
        $me = auth()->id();

        // Mark incoming messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $me)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::between($me, $userId)
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'sender_id'  => (int) $m->sender_id,
                'mine'       => (int) $m->sender_id === (int) $me,
                'time'       => $m->created_at->format('g:i A'),
                'date'       => $m->created_at->toDateString(),
            ])
            ->values();

        return response()->json($messages);
    }

    // ──────────────────────────────────────────────────
    // AJAX: Send a message
    // ──────────────────────────────────────────────────
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body'        => 'required|string|max:5000',
        ]);

        $message = Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'body'        => $request->body,
        ]);

        return response()->json([
            'id'        => $message->id,
            'body'      => $message->body,
            'sender_id' => $message->sender_id,
            'mine'      => true,
            'time'      => $message->created_at->format('g:i A'),
            'date'      => $message->created_at->toDateString(),
        ], 201);
    }

    // ──────────────────────────────────────────────────
    // AJAX: Unread count for a contact
    // ──────────────────────────────────────────────────
    public function unreadCount(int $userId)
    {
        $count = Message::where('sender_id', $userId)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    // ──────────────────────────────────────────────────
    // AJAX: Contacts with last message preview (for sidebar refresh)
    // ──────────────────────────────────────────────────
    public function contactsPreview(Request $request)
    {
        $me = auth()->id();

        // IDs of contacts the frontend sent us
        $contactIds = $request->input('contact_ids', []);

        $previews = [];
        foreach ($contactIds as $contactId) {
            $latest = Message::between($me, (int)$contactId)
                ->orderByDesc('created_at')
                ->first();

            $unread = Message::where('sender_id', (int)$contactId)
                ->where('receiver_id', $me)
                ->whereNull('read_at')
                ->count();

            $previews[$contactId] = [
                'latest_body' => $latest ? \Str::limit($latest->body, 50) : 'No messages yet',
                'latest_time' => $latest ? $latest->created_at->format('g:i A') : '',
                'unread'      => $unread,
            ];
        }

        return response()->json($previews);
    }
}
