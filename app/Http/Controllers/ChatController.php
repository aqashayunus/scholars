<?php

namespace App\Http\Controllers;

use App\Models\ChatAuditLog;
use App\Models\ChatMessage;
use App\Models\ChatMessageAttachment;
use App\Models\ChatMessageRead;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChatController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // 1. GLOBAL UNREAD COUNT (for FAB badge — polled every 15s when closed)
    // ─────────────────────────────────────────────────────────────────────────

    public function getGlobalUnreadCount(): JsonResponse
    {
        $me = auth()->id();

        $count = ConversationParticipant::where('user_id', $me)
            ->whereNull('archived_at')
            ->whereHas('conversation', fn($q) => $q->whereNull('deleted_at'))
            ->get()
            ->filter(function ($p) use ($me) {
                return ChatMessage::where('conversation_id', $p->conversation_id)
                    ->where('sender_id', '!=', $me)
                    ->where('id', '>', (int)($p->last_read_message_id ?? 0))
                    ->exists();
            })
            ->count();

        return response()->json(['count' => $count]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. CONTACT / CONVERSATION LIST (Role-aware)
    // ─────────────────────────────────────────────────────────────────────────

    public function getContacts(): JsonResponse
    {
        $me   = auth()->user();
        $myId = $me->id;

        try {
            $participantRows = ConversationParticipant::where('user_id', $myId)
                ->whereNull('archived_at')
                ->with([
                    'conversation.participants.user',
                    'conversation.lastMessage.sender',
                ])
                ->get();

            $contacts = $participantRows->map(function ($p) use ($myId) {
                $conv = $p->conversation;
                if (!$conv || $conv->deleted_at !== null) return null;

                $other   = $conv->otherParticipant($myId);
                $lastMsg = $conv->lastMessage;

                $unread = ChatMessage::where('conversation_id', $conv->id)
                    ->where('sender_id', '!=', $myId)
                    ->where('id', '>', (int)($p->last_read_message_id ?? 0))
                    ->count();

                // Educational context preview if available
                $contextLabel = null;
                if ($conv->context_type === 'App\Models\Course' && $conv->context_id) {
                    $course = Course::find($conv->context_id);
                    $contextLabel = $course ? $course->title : null;
                }

                return [
                    'conversation_id'  => $conv->id,
                    'contact_id'       => $other?->id,
                    'contact_name'     => $other?->name ?? $conv->title ?? 'Support Desk',
                    'contact_role'     => $other?->role ?? 'support',
                    'contact_initials' => $this->initials($other?->name ?? ($conv->title ?? 'S')),
                    'conversation_type'=> $conv->type,
                    'context_label'    => $contextLabel,
                    'last_message'     => $lastMsg
                        ? ($lastMsg->type === 'attachment'
                            ? '[Attachment: ' . ($lastMsg->attachments->first()?->original_name ?? 'File') . ']'
                            : Str::limit($lastMsg->body ?? '', 50))
                        : 'No messages yet',
                    'last_time'        => $lastMsg?->created_at?->timezone('Asia/Kuala_Lumpur')->format('g:i A'),
                    'last_date'        => $lastMsg?->created_at?->timezone('Asia/Kuala_Lumpur')->toDateString(),
                    'unread'           => $unread,
                    'status'           => $conv->status,
                    'is_muted'         => $p->muted_at !== null,
                ];
            })->filter()->sortByDesc(function ($c) {
                return ($c['last_date'] ?? '2000-01-01') . ' ' . ($c['last_time'] ?? '00:00 AM');
            })->values();

            return response()->json($contacts);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[ChatController] getContacts error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. DIRECTORY (Authorized contacts to start a NEW conversation)
    // ─────────────────────────────────────────────────────────────────────────

    public function getDirectory(Request $request): JsonResponse
    {
        $me = auth()->user();
        $directory = collect();

        // Always include School Admin support for non-admin users
        if (!$me->isAdmin()) {
            $admins = User::where('role', 'admin')->where('is_active', true)->get()->map(function ($admin) {
                return [
                    'contact_id'       => $admin->id,
                    'contact_name'     => $admin->name,
                    'contact_role'     => 'admin',
                    'contact_initials' => $this->initials($admin->name),
                    'context_label'    => 'School Administration & Support',
                ];
            });
            $directory = $directory->concat($admins);
        }

        // Student: Enrolled course teachers
        if ($me->isStudent()) {
            $student = $me->studentProfile;
            if ($student) {
                $teachers = Enrollment::where('student_id', $student->id)
                    ->whereIn('status', ['enrolled', 'active', 'completed'])
                    ->with('course.teacher.user', 'course')
                    ->get()
                    ->map(function ($e) {
                        $teacherUser = $e->course?->teacher?->user;
                        if (!$teacherUser) return null;

                        return [
                            'contact_id'       => $teacherUser->id,
                            'contact_name'     => $teacherUser->name,
                            'contact_role'     => 'teacher',
                            'contact_initials' => $this->initials($teacherUser->name),
                            'context_label'    => 'Teacher • ' . ($e->course?->title ?? 'Course'),
                            'course_id'        => $e->course_id,
                        ];
                    })->filter();

                $directory = $directory->concat($teachers);
            }
        }

        // Teacher: Enrolled students and their parents
        if ($me->isTeacher()) {
            $teacher = $me->teacherProfile;
            if ($teacher) {
                $enrollments = Enrollment::whereHas('course', fn($q) => $q->where('teacher_id', $teacher->id))
                    ->with(['student.user', 'student.parents', 'course'])
                    ->get();

                foreach ($enrollments as $e) {
                    $studentUser = $e->student?->user;
                    if ($studentUser) {
                        $directory->push([
                            'contact_id'       => $studentUser->id,
                            'contact_name'     => $studentUser->name,
                            'contact_role'     => 'student',
                            'contact_initials' => $this->initials($studentUser->name),
                            'context_label'    => 'Student • ' . ($e->course?->title ?? 'Class'),
                            'course_id'        => $e->course_id,
                        ]);
                    }

                    // Parents of this student
                    if ($e->student && $e->student->parents) {
                        foreach ($e->student->parents as $parent) {
                            $directory->push([
                                'contact_id'       => $parent->id,
                                'contact_name'     => $parent->name,
                                'contact_role'     => 'parent',
                                'contact_initials' => $this->initials($parent->name),
                                'context_label'    => 'Parent of ' . ($studentUser?->name ?? 'Student'),
                                'course_id'        => $e->course_id,
                            ]);
                        }
                    }
                }
            }
        }

        // Parent: Teachers of linked children
        if ($me->isParent()) {
            $children = $me->studentChildren()->with(['enrollments.course.teacher.user', 'enrollments.course', 'user'])->get();
            foreach ($children as $child) {
                foreach ($child->enrollments as $e) {
                    $teacherUser = $e->course?->teacher?->user;
                    if ($teacherUser) {
                        $directory->push([
                            'contact_id'       => $teacherUser->id,
                            'contact_name'     => $teacherUser->name,
                            'contact_role'     => 'teacher',
                            'contact_initials' => $this->initials($teacherUser->name),
                            'context_label'    => 'Teacher of ' . ($child->user?->name ?? 'Child') . ' (' . ($e->course?->title ?? '') . ')',
                            'course_id'        => $e->course_id,
                        ]);
                    }
                }
            }
        }

        // Admin: Full school directory
        if ($me->isAdmin()) {
            $search = $request->query('search');
            $role   = $request->query('role');

            $query = User::where('id', '!=', $me->id)->where('is_active', true);
            if ($search) {
                $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            }
            if ($role) {
                $query->where('role', $role);
            }

            $users = $query->limit(40)->get()->map(function ($u) {
                return [
                    'contact_id'       => $u->id,
                    'contact_name'     => $u->name,
                    'contact_role'     => $u->role,
                    'contact_initials' => $this->initials($u->name),
                    'context_label'    => ucfirst($u->role) . ' • ' . $u->email,
                ];
            });

            return response()->json($users);
        }

        // De-duplicate contacts by contact_id
        $uniqueDirectory = $directory->unique('contact_id')->values();

        return response()->json($uniqueDirectory);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. FIND OR CREATE 1-TO-1 CONVERSATION
    // ─────────────────────────────────────────────────────────────────────────

    public function findOrCreateConversation(Request $request): JsonResponse
    {
        $request->validate(['contact_id' => 'required|exists:users,id']);

        $me        = auth()->user();
        $contactId = (int) $request->contact_id;

        if ($me->id === $contactId) {
            return response()->json(['error' => 'Cannot create a conversation with yourself.'], 422);
        }

        // Check authorization
        $error = $this->authorizeContact($me, $contactId);
        if ($error) {
            return response()->json(['error' => $error], 403);
        }

        try {
            // Find existing direct 1-to-1 conversation (including soft-deleted)
            $existing = Conversation::withTrashed()
                ->whereHas('participants', fn($q) => $q->where('user_id', $me->id))
                ->whereHas('participants', fn($q) => $q->where('user_id', $contactId))
                ->whereNotIn('type', ['system'])
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                return response()->json(['conversation_id' => $existing->id]);
            }

            $contact = User::findOrFail($contactId);
            $type    = $this->resolveConversationType($me, $contact);

            $conv = DB::transaction(function () use ($me, $contact, $type) {
                try {
                    $conv = Conversation::create([
                        'type'       => $type,
                        'status'     => 'open',
                        'created_by' => $me->id,
                    ]);
                } catch (\Throwable $e) {
                    // Fallback to 'direct' if legacy database ENUM lacks specific types (e.g. student_admin)
                    if (str_contains($e->getMessage(), 'Data truncated for column') || str_contains($e->getMessage(), '1265')) {
                        $conv = Conversation::create([
                            'type'       => 'direct',
                            'status'     => 'open',
                            'created_by' => $me->id,
                        ]);
                    } else {
                        throw $e;
                    }
                }

                ConversationParticipant::insert([
                    ['conversation_id' => $conv->id, 'user_id' => $me->id,      'role' => 'member', 'joined_at' => now()->toDateTimeString()],
                    ['conversation_id' => $conv->id, 'user_id' => $contact->id, 'role' => 'member', 'joined_at' => now()->toDateTimeString()],
                ]);

                return $conv;
            });

            return response()->json(['conversation_id' => $conv->id], 201);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[ChatController] findOrCreateConversation error: ' . $e->getMessage(), [
                'user_id'    => $me->id,
                'contact_id' => $contactId,
            ]);

            $errorMsg = $e->getMessage();
            if (str_contains($errorMsg, "doesn't exist") || str_contains($errorMsg, 'Base table or view not found')) {
                $errorMsg = 'Chat database tables are missing on this server. Please run the chat migration or import the SQL tables in phpMyAdmin.';
            }

            return response()->json(['error' => $errorMsg], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. FETCH MESSAGES (Delta Polling & Initial Load)
    // ─────────────────────────────────────────────────────────────────────────

    public function getMessages(Request $request, int $conversationId): JsonResponse
    {
        $me   = auth()->id();
        $conv = Conversation::findOrFail($conversationId);

        abort_unless($conv->hasParticipant($me), 403, 'Unauthorized.');

        $sinceId = (int) $request->query('since_id', 0);
        $perPage = (int) $request->query('per_page', 25);
        $perPage = min($perPage, 50);

        if ($sinceId === 0) {
            // Initial load: fetch newest N messages and reverse to ascending chronological order (oldest -> newest)
            $messages = ChatMessage::where('conversation_id', $conversationId)
                ->with(['sender', 'attachments', 'reads'])
                ->orderBy('id', 'desc')
                ->limit($perPage)
                ->get()
                ->reverse()
                ->values();
        } else {
            // Delta poll: only new messages after since_id in ascending order
            $messages = ChatMessage::where('conversation_id', $conversationId)
                ->with(['sender', 'attachments', 'reads'])
                ->where('id', '>', $sinceId)
                ->orderBy('id', 'asc')
                ->get();
        }

        // Mark incoming messages as read by current user (for blue tick system)
        $unreadIncoming = $messages->where('sender_id', '!=', $me);
        if ($unreadIncoming->isNotEmpty()) {
            foreach ($unreadIncoming as $msg) {
                ChatMessageRead::firstOrCreate(
                    ['message_id' => $msg->id, 'user_id' => $me],
                    ['read_at' => now()]
                );
            }

            // Update participant last read pointer
            $lastId = $messages->max('id');
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $me)
                ->update(['last_read_message_id' => $lastId, 'last_read_at' => now()]);

            // Update message status to 'read'
            ChatMessage::where('conversation_id', $conversationId)
                ->whereIn('id', $unreadIncoming->pluck('id'))
                ->update(['status' => 'read']);
        }

        // Check the other participant's last read message ID for real-time double blue ticks
        $otherParticipant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', '!=', $me)
            ->first();
        $otherLastReadId = (int)($otherParticipant?->last_read_message_id ?? 0);

        $directMaxRead = ChatMessageRead::whereIn('message_id', function ($q) use ($conversationId, $me) {
            $q->select('id')->from('chat_messages')->where('conversation_id', $conversationId)->where('sender_id', $me);
        })->where('user_id', '!=', $me)->max('message_id');

        $otherLastReadId = max($otherLastReadId, (int)($directMaxRead ?? 0));

        $formatted = $messages->map(fn($m) => $this->formatMessage($m, $me));

        return response()->json([
            'messages'           => $formatted,
            'next_cursor'        => $messages->isNotEmpty() ? $messages->max('id') : $sinceId,
            'has_more'           => $sinceId === 0 && ChatMessage::where('conversation_id', $conversationId)->count() > $perPage,
            'other_last_read_id' => $otherLastReadId,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. LOAD OLDER MESSAGES (Scroll-Up Pagination)
    // ─────────────────────────────────────────────────────────────────────────

    public function getOlderMessages(Request $request, int $conversationId): JsonResponse
    {
        $me       = auth()->id();
        $conv     = Conversation::findOrFail($conversationId);
        abort_unless($conv->hasParticipant($me), 403, 'Unauthorized.');

        $beforeId = (int) $request->query('before_id', PHP_INT_MAX);
        $perPage  = 20;

        $messages = ChatMessage::where('conversation_id', $conversationId)
            ->with(['sender', 'attachments', 'reads'])
            ->where('id', '<', $beforeId)
            ->orderBy('id', 'desc')
            ->limit($perPage)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'messages' => $messages->map(fn($m) => $this->formatMessage($m, $me)),
            'has_more' => ChatMessage::where('conversation_id', $conversationId)->where('id', '<', $messages->first()?->id ?? 0)->exists(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. SEND MESSAGE
    // ─────────────────────────────────────────────────────────────────────────

    public function sendMessage(Request $request, int $conversationId): JsonResponse
    {
        // Rate limiting: max 60 messages/minute per user
        $key = 'chat_send:' . auth()->id();
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json(['error' => 'Too many messages sent. Please slow down.'], 429);
        }
        RateLimiter::hit($key, 60);

        $request->validate([
            'body'        => 'required|string|max:5000',
            'reply_to_id' => 'nullable|exists:chat_messages,id',
        ]);

        $me   = auth()->id();
        $conv = Conversation::findOrFail($conversationId);
        abort_unless($conv->hasParticipant($me), 403, 'Unauthorized.');

        $message = DB::transaction(function () use ($request, $me, $conv) {
            $msg = ChatMessage::create([
                'conversation_id' => $conv->id,
                'sender_id'       => $me,
                'body'            => trim($request->body),
                'type'            => 'text',
                'reply_to_id'     => $request->reply_to_id,
                'status'          => 'sent',
            ]);

            // Update conversation pointer and participant read tracker
            $conv->update(['last_message_id' => $msg->id]);
            ConversationParticipant::where('conversation_id', $conv->id)
                ->where('user_id', $me)
                ->update(['last_read_message_id' => $msg->id, 'last_read_at' => now()]);

            return $msg->load(['sender', 'attachments', 'reads']);
        });

        return response()->json($this->formatMessage($message, $me), 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. ATTACHMENT UPLOAD & SECURE DOWNLOAD
    // ─────────────────────────────────────────────────────────────────────────

    public function uploadAttachment(Request $request, int $conversationId): JsonResponse
    {
        // Rate limiting: 10 uploads/min
        $key = 'chat_upload:' . auth()->id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json(['error' => 'Upload rate limit reached. Please wait a moment.'], 429);
        }
        RateLimiter::hit($key, 60);

        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpeg,jpg,png,webp,pdf,doc,docx,xlsx',
        ]);

        $me   = auth()->id();
        $conv = Conversation::findOrFail($conversationId);
        abort_unless($conv->hasParticipant($me), 403, 'Unauthorized.');

        $file         = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $mimeType     = $file->getMimeType();
        $sizeBytes    = $file->getSize();
        $checksum     = hash_file('sha256', $file->getRealPath());

        // Store privately inside storage/app/chat_attachments/{conversationId}/
        $storedPath = $file->store("chat_attachments/{$conversationId}", 'local');

        $message = DB::transaction(function () use ($conv, $me, $storedPath, $originalName, $mimeType, $sizeBytes, $checksum) {
            $msg = ChatMessage::create([
                'conversation_id' => $conv->id,
                'sender_id'       => $me,
                'body'            => $originalName,
                'type'            => 'attachment',
                'status'          => 'sent',
            ]);

            ChatMessageAttachment::create([
                'message_id'    => $msg->id,
                'disk'          => 'local',
                'path'          => $storedPath,
                'original_name' => $originalName,
                'mime_type'     => $mimeType,
                'size_bytes'    => $sizeBytes,
                'checksum'      => $checksum,
            ]);

            $conv->update(['last_message_id' => $msg->id]);
            ConversationParticipant::where('conversation_id', $conv->id)
                ->where('user_id', $me)
                ->update(['last_read_message_id' => $msg->id, 'last_read_at' => now()]);

            return $msg->load(['sender', 'attachments', 'reads']);
        });

        return response()->json($this->formatMessage($message, $me), 201);
    }

    public function downloadAttachment(int $attachmentId): BinaryFileResponse|JsonResponse
    {
        $attachment = ChatMessageAttachment::with('message.conversation')->findOrFail($attachmentId);
        $conv       = $attachment->message?->conversation;
        $me         = auth()->user();

        if (!$conv) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        // Authorization check: participant, parent of student participant, or admin
        $isParticipant = $conv->hasParticipant($me->id);
        $isAdmin       = $me->isAdmin();
        $isMonParent   = false;

        if ($me->isParent()) {
            $childUserIds = $me->studentChildren()->with('user')->get()->pluck('user.id')->filter()->all();
            $isMonParent  = $conv->participants()->whereIn('user_id', $childUserIds)->exists();
        }

        if (!$isParticipant && !$isAdmin && !$isMonParent) {
            return response()->json(['error' => 'Access denied to this attachment.'], 403);
        }

        if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
            return response()->json(['error' => 'Attachment file does not exist on disk.'], 404);
        }

        return response()->download(
            Storage::disk($attachment->disk)->path($attachment->path),
            $attachment->original_name
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9. TYPING INDICATORS (Ephemeral with 5-second TTL)
    // ─────────────────────────────────────────────────────────────────────────

    public function setTyping(Request $request, int $conversationId): JsonResponse
    {
        $me   = auth()->id();
        $conv = Conversation::findOrFail($conversationId);
        abort_unless($conv->hasParticipant($me), 403);

        DB::table('chat_typing_indicators')
            ->updateOrInsert(
                ['conversation_id' => $conversationId, 'user_id' => $me],
                ['updated_at' => now()]
            );

        return response()->json(['ok' => true]);
    }

    public function getTyping(int $conversationId): JsonResponse
    {
        $me   = auth()->id();
        $conv = Conversation::findOrFail($conversationId);
        abort_unless($conv->hasParticipant($me), 403);

        $typing = DB::table('chat_typing_indicators')
            ->where('conversation_id', $conversationId)
            ->where('user_id', '!=', $me)
            ->where('updated_at', '>=', now()->subSeconds(5))
            ->join('users', 'users.id', '=', 'chat_typing_indicators.user_id')
            ->pluck('users.name');

        return response()->json(['typing' => $typing]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 10. MARK AS READ
    // ─────────────────────────────────────────────────────────────────────────

    public function markRead(int $conversationId): JsonResponse
    {
        $me   = auth()->id();
        $conv = Conversation::findOrFail($conversationId);
        abort_unless($conv->hasParticipant($me), 403);

        $lastMsg = $conv->messages()->latest('id')->first();
        if ($lastMsg) {
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $me)
                ->update(['last_read_message_id' => $lastMsg->id, 'last_read_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 11. MUTE / UNMUTE / ARCHIVE
    // ─────────────────────────────────────────────────────────────────────────

    public function muteConversation(int $conversationId): JsonResponse
    {
        $me = auth()->id();
        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $me)
            ->update(['muted_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function unmuteConversation(int $conversationId): JsonResponse
    {
        $me = auth()->id();
        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $me)
            ->update(['muted_at' => null]);

        return response()->json(['ok' => true]);
    }

    public function archiveConversation(int $conversationId): JsonResponse
    {
        $me = auth()->id();
        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $me)
            ->update(['archived_at' => now()]);

        return response()->json(['ok' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 11b. CLEAR CONVERSATION
    // ─────────────────────────────────────────────────────────────────────────

    public function clearChat(int $conversationId): JsonResponse
    {
        $me   = auth()->id();
        $conv = Conversation::findOrFail($conversationId);
        abort_unless($conv->hasParticipant($me), 403, 'Unauthorized.');

        // Soft-delete all active messages in this conversation
        ChatMessage::where('conversation_id', $conversationId)->delete();

        // Reset conversation last message pointer
        $conv->update(['last_message_id' => null]);

        // Reset participant last read message pointers
        ConversationParticipant::where('conversation_id', $conversationId)
            ->update([
                'last_read_message_id' => null,
                'last_read_at'         => now(),
            ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Chat cleared successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 11c. CLEAR ALL CONVERSATIONS (WIPE ALL CHAT BLOCKS & MESSAGES)
    // ─────────────────────────────────────────────────────────────────────────

    public function clearAllChats(Request $request): JsonResponse
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            // Admin: wipe all active conversations and their messages
            $convs = Conversation::whereNull('deleted_at')->get();
            foreach ($convs as $conv) {
                ChatMessage::where('conversation_id', $conv->id)->delete();
                $conv->delete();
            }
        } else {
            // Non-admin: clear all conversations current user participates in
            $convIds = ConversationParticipant::where('user_id', $me->id)
                ->pluck('conversation_id');

            $convs = Conversation::whereIn('id', $convIds)->whereNull('deleted_at')->get();
            foreach ($convs as $conv) {
                ChatMessage::where('conversation_id', $conv->id)->delete();
                $conv->delete();
            }
        }

        return response()->json([
            'ok'      => true,
            'message' => 'All conversations and chat blocks have been cleared.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 11d. DELETE SINGLE CONVERSATION BLOCK
    // ─────────────────────────────────────────────────────────────────────────

    public function deleteConversation(int $conversationId): JsonResponse
    {
        $me   = auth()->user();
        $conv = Conversation::findOrFail($conversationId);
        abort_unless($conv->hasParticipant($me->id) || $me->isAdmin(), 403, 'Unauthorized.');

        // Soft-delete messages and conversation
        ChatMessage::where('conversation_id', $conversationId)->delete();
        $conv->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Conversation deleted successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 12. PARENTAL MONITORING MODULE (Read-Only & Audited)
    // ─────────────────────────────────────────────────────────────────────────

    public function getMonitoredChildren(): JsonResponse
    {
        $me = auth()->user();
        abort_unless($me->isParent(), 403, 'Parent role required.');

        $children = $me->studentChildren()
            ->with('user')
            ->get()
            ->map(fn($s) => [
                'student_id' => $s->id,
                'user_id'    => $s->user?->id,
                'name'       => $s->user?->name ?? 'Unknown',
                'initials'   => $this->initials($s->user?->name ?? '?'),
                'level'      => $s->level,
                'relationship' => $s->pivot->relationship,
            ]);

        return response()->json($children);
    }

    public function getChildConversations(int $studentId): JsonResponse
    {
        $me = auth()->user();
        abort_unless($me->isParent(), 403, 'Parent role required.');

        $student = $me->studentChildren()->where('students.id', $studentId)->first();
        abort_unless($student, 403, 'Unauthorized: child is not linked to your account.');

        $childUserId = $student->user?->id;
        abort_unless($childUserId, 404, 'Child user account not found.');

        // Audit Log
        $this->auditLog($me->id, $childUserId, null, 'monitoring_session_started');

        $participantRows = ConversationParticipant::where('user_id', $childUserId)
            ->with(['conversation.participants.user', 'conversation.lastMessage'])
            ->get();

        $list = $participantRows->map(function ($p) use ($childUserId) {
            $conv  = $p->conversation;
            if (!$conv || $conv->deleted_at !== null) return null;

            $other = $conv->otherParticipant($childUserId);
            $lastMsg = $conv->lastMessage;

            return [
                'conversation_id' => $conv->id,
                'contact_name'    => $other?->name ?? $conv->title ?? 'Support',
                'contact_role'    => $other?->role ?? 'support',
                'contact_initials'=> $this->initials($other?->name ?? '?'),
                'last_message'    => $lastMsg
                    ? ($lastMsg->type === 'attachment' ? '[Attachment]' : Str::limit($lastMsg->body ?? '', 50))
                    : 'No messages yet',
                'last_time'       => $lastMsg?->created_at?->timezone('Asia/Kuala_Lumpur')->format('g:i A'),
            ];
        })->filter()->values();

        return response()->json($list);
    }

    public function getChildConversation(Request $request, int $studentId, int $conversationId): JsonResponse
    {
        $me = auth()->user();
        abort_unless($me->isParent(), 403, 'Parent role required.');

        $student = $me->studentChildren()->where('students.id', $studentId)->first();
        abort_unless($student, 403, 'Unauthorized.');

        $childUserId = $student->user?->id;
        abort_unless($childUserId, 404);

        $conv = Conversation::findOrFail($conversationId);
        abort_unless($conv->hasParticipant($childUserId), 403, 'Child does not participate in this chat.');

        // Audit Log
        $this->auditLog($me->id, $childUserId, $conversationId, 'conversation_viewed');

        $sinceId = (int) $request->query('since_id', 0);
        $perPage = 25;

        $messages = $sinceId === 0
            ? ChatMessage::where('conversation_id', $conversationId)
                ->with(['sender', 'attachments'])
                ->orderBy('id', 'desc')
                ->limit($perPage)
                ->get()
                ->reverse()
                ->values()
            : ChatMessage::where('conversation_id', $conversationId)
                ->with(['sender', 'attachments'])
                ->where('id', '>', $sinceId)
                ->orderBy('id', 'asc')
                ->get();

        $formatted = $messages->map(fn($m) => $this->formatMessage($m, $childUserId));

        return response()->json([
            'messages'    => $formatted,
            'next_cursor' => $messages->isNotEmpty() ? $messages->max('id') : $sinceId,
            'has_more'    => $sinceId === 0 && $conv->messages()->count() > $perPage,
            'monitoring'  => [
                'child_name'     => $student->user?->name,
                'child_initials' => $this->initials($student->user?->name ?? '?'),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 13. ADMIN MODULE (Professional, Foolproof, Systematic, Functional)
    // ─────────────────────────────────────────────────────────────────────────

    public function adminGetContacts(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Admin access required.');

        $search = $request->query('search');
        $status = $request->query('status');
        $role   = $request->query('role'); // student, teacher, parent, support

        $query = Conversation::with(['participants.user', 'lastMessage.sender'])
            ->whereNull('deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('participants.user', fn($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($status && in_array($status, ['open', 'pending', 'resolved', 'closed'])) {
            $query->where('status', $status);
        }

        if ($role) {
            if ($role === 'support') {
                $query->where('type', 'support');
            } else {
                $query->whereHas('participants.user', fn($q) => $q->where('role', $role));
            }
        }

        try {
            $convs = $query->latest('updated_at')->limit(60)->get();

            $list = $convs->map(function ($conv) {
                $parts = $conv->participants->map(fn($p) => [
                    'id'       => $p->user?->id,
                    'name'     => $p->user?->name ?? 'Unknown',
                    'role'     => $p->user?->role ?? 'user',
                    'initials' => $this->initials($p->user?->name ?? '?'),
                ]);

                $lastMsg = $conv->lastMessage;

                // Pick the primary non-admin participant for display title
                $primaryUser = $conv->participants->firstWhere('user.role', '!=', 'admin')?->user
                            ?? $conv->participants->first()?->user;

                return [
                    'conversation_id'   => $conv->id,
                    'contact_id'        => $primaryUser?->id,
                    'contact_name'      => $primaryUser?->name ?? ($conv->title ?? 'Conversation #' . $conv->id),
                    'contact_role'      => $primaryUser?->role ?? 'direct',
                    'contact_initials'  => $this->initials($primaryUser?->name ?? 'A'),
                    'type'              => $conv->type,
                    'status'            => $conv->status,
                    'title'             => $conv->title,
                    'participants'      => $parts,
                    'last_message'      => $lastMsg
                        ? ($lastMsg->type === 'attachment' ? '[Attachment]' : Str::limit($lastMsg->body ?? '', 55))
                        : 'No messages yet',
                    'last_time'         => $lastMsg?->created_at?->timezone('Asia/Kuala_Lumpur')->format('g:i A'),
                    'last_date'         => $lastMsg?->created_at?->timezone('Asia/Kuala_Lumpur')->toDateString(),
                    'unread'            => 0,
                ];
            });

            return response()->json($list);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[ChatController] adminGetContacts error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function adminUpdateStatus(Request $request, int $conversationId): JsonResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $request->validate(['status' => 'required|in:open,pending,resolved,closed']);

        $conv = Conversation::findOrFail($conversationId);
        $conv->update(['status' => $request->status]);

        return response()->json(['ok' => true, 'status' => $conv->status]);
    }

    /**
     * Foolproof admin context drawer: provides instant academic & billing info
     * so admin has complete context during chat without leaving the widget.
     */
    public function adminGetUserContext(int $userId): JsonResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $user = User::with(['studentProfile.enrollments.course', 'teacherProfile.courses', 'studentChildren.user'])->findOrFail($userId);

        $context = [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'phone'    => $user->phone ?? 'Not provided',
            'role'     => $user->role,
            'active'   => $user->is_active,
            'joined'   => $user->created_at->format('d M Y'),
        ];

        if ($user->isStudent()) {
            $student = $user->studentProfile;
            $context['student_details'] = [
                'level'       => $student?->level ?? 'N/A',
                'school'      => $student?->school_name ?? 'N/A',
                'courses'     => $student?->enrollments->map(fn($e) => [
                    'title'    => $e->course?->title ?? 'Class',
                    'progress' => ($e->progress_pct ?? 0) . '%',
                    'status'   => $e->status,
                ]) ?? [],
            ];
        } elseif ($user->isTeacher()) {
            $teacher = $user->teacherProfile;
            $context['teacher_details'] = [
                'qualification'   => $teacher?->qualification ?? 'N/A',
                'specialization'  => $teacher?->specialization ?? 'General',
                'experience'      => ($teacher?->experience_years ?? 0) . ' years',
                'courses_count'   => $teacher?->courses->count() ?? 0,
            ];
        } elseif ($user->isParent()) {
            $context['parent_details'] = [
                'children' => $user->studentChildren->map(fn($c) => [
                    'name'  => $c->user?->name ?? 'Child',
                    'level' => $c->level,
                ]),
            ];
        }

        return response()->json($context);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 14. FORMATTING & BLUE TICK HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function formatMessage(ChatMessage $m, int $currentUserId): array
    {
        $reads  = $m->reads ?? collect();
        $isRead = $reads->where('user_id', '!=', (int)$m->sender_id)->isNotEmpty();
        $isMine = (int)$m->sender_id === $currentUserId;

        // Blue tick system logic:
        // - 'read': Double blue ticks
        // - 'delivered': Double gray ticks
        // - 'sent': Single gray tick
        $tickStatus = null;
        if ($isMine) {
            $tickStatus = match (true) {
                $isRead                    => 'read',
                $m->status === 'delivered' => 'delivered',
                default                    => 'sent',
            };
        }

        return [
            'id'              => $m->id,
            'body'            => $m->body,
            'type'            => $m->type,
            'mine'            => $isMine,
            'sender_id'       => (int)$m->sender_id,
            'sender_name'     => $m->sender?->name,
            'sender_role'     => $m->sender?->role,
            'sender_initials' => $this->initials($m->sender?->name ?? '?'),
            'tick_status'     => $tickStatus,
            'reply_to'        => $m->replyTo ? [
                'id'   => $m->replyTo->id,
                'body' => Str::limit($m->replyTo->body ?? '', 80),
            ] : null,
            'attachments'     => ($m->attachments ?? collect())->map(fn($a) => [
                'id'            => $a->id,
                'original_name' => $a->original_name,
                'mime_type'     => $a->mime_type,
                'is_image'      => $a->isImage(),
                'human_size'    => $a->humanSize(),
                'url'           => route('chat.attachment.download', $a->id),
            ])->values(),
            'time'            => $m->created_at?->timezone('Asia/Kuala_Lumpur')->format('g:i A'),
            'date'            => $m->created_at?->timezone('Asia/Kuala_Lumpur')->toDateString(),
            'deleted'         => $m->deleted_at !== null,
        ];
    }

    private function initials(?string $name): string
    {
        if (!$name) return '?';
        $words = explode(' ', trim($name));
        $first = strtoupper(substr($words[0] ?? '', 0, 1));
        $second = isset($words[1]) ? strtoupper(substr($words[1], 0, 1)) : '';
        return $first . $second;
    }

    private function resolveConversationType(User $me, User $contact): string
    {
        return match (true) {
            $me->isStudent() && $contact->isTeacher() => 'student_teacher',
            $me->isTeacher() && $contact->isStudent() => 'student_teacher',
            $me->isParent()  && $contact->isTeacher() => 'parent_teacher',
            $me->isTeacher() && $contact->isParent()  => 'parent_teacher',
            $me->isParent()  && $contact->isAdmin()   => 'parent_admin',
            $me->isAdmin()   && $contact->isParent()  => 'parent_admin',
            $me->isStudent() && $contact->isAdmin()   => 'student_admin',
            $me->isAdmin()   && $contact->isStudent() => 'student_admin',
            $me->isTeacher() && $contact->isAdmin()   => 'teacher_admin',
            $me->isAdmin()   && $contact->isTeacher() => 'teacher_admin',
            default                                   => 'direct',
        };
    }

    private function authorizeContact(User $me, int $contactId): ?string
    {
        $contact = User::find($contactId);
        if (!$contact) return 'Contact not found.';

        // Admin has full clearance to start conversation with any user
        if ($me->isAdmin()) {
            return null;
        }

        if (!$contact->is_active) return 'This account is currently inactive.';

        if ($me->isStudent()) {
            if ($contact->isAdmin()) return null;
            if ($contact->isTeacher()) {
                $enrolled = $me->studentProfile?->enrollments()
                    ->whereHas('course.teacher', fn($q) => $q->where('user_id', $contactId))
                    ->exists();
                if (!$enrolled) return 'You can only message teachers of your enrolled courses.';
                return null;
            }
            return 'Students can only message enrolled teachers or admin.';
        }

        if ($me->isTeacher()) {
            if ($contact->isAdmin()) return null;
            if ($contact->isStudent() || $contact->isParent()) return null;
            return 'Teachers can only message students, parents, or admin.';
        }

        if ($me->isParent()) {
            if ($contact->isAdmin()) return null;
            if ($contact->isTeacher()) return null;
            return 'Parents can only message teachers or admin.';
        }

        if ($me->isAdmin()) {
            return null;
        }

        return 'Unauthorized to contact this user.';
    }

    private function auditLog(int $viewerId, int $childId, ?int $conversationId, string $action): void
    {
        ChatAuditLog::create([
            'viewer_id'       => $viewerId,
            'child_id'        => $childId,
            'conversation_id' => $conversationId,
            'action'          => $action,
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);
    }
}
