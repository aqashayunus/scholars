<?php

namespace App\Services\Email;

use App\Models\Invoice;
use App\Models\LiveClass;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;

class EmailVariableRegistry
{
    /**
     * Get the complete dictionary of all available variables grouped by context.
     */
    public static function all(): array
    {
        return [
            'Parent and Student' => [
                'parent_name'   => 'Parent full name',
                'parent_email'  => 'Parent email address',
                'parent_phone'  => 'Parent contact number',
                'student_name'  => 'Student full name',
                'student_id'    => 'Student system reference ID',
                'student_email' => 'Student email address',
                'student_level' => 'Academic level / Form / Grade',
            ],
            'Programme and Academic' => [
                'programme_name'     => 'Programme or package title',
                'subject_name'       => 'Subject name',
                'class_name'         => 'Class or course title',
                'teacher_name'       => 'Assigned educator / mentor name',
                'assessment_name'    => 'Assignment or assessment title',
                'score'              => 'Assessment score or grade result',
                'attendance_summary' => 'Attendance summary percentage or status',
                'reporting_period'   => 'Reporting cycle (e.g. Term 1 / October 2026)',
            ],
            'Billing and Invoices' => [
                'invoice_number'     => 'Official invoice number (e.g. INV-2026-0001)',
                'invoice_amount'     => 'Total invoice amount formatted with currency',
                'amount_paid'        => 'Amount successfully paid',
                'outstanding_amount' => 'Remaining balance due',
                'due_date'           => 'Invoice payment due date',
                'payment_date'       => 'Date and time payment was recorded',
                'payment_reference'  => 'Bank or gateway transaction reference',
                'payment_status'     => 'Status of invoice or payment',
                'receipt_number'     => 'Official receipt number (e.g. REC-2026-0001)',
            ],
            'Live Class and Timetable' => [
                'class_date'     => 'Date of live class session',
                'class_time'     => 'Start time of live class session',
                'class_schedule' => 'Formatted date and time duration',
                'meeting_url'    => 'Live video session link',
                'class_status'   => 'Status of class (Upcoming / Rescheduled / Cancelled)',
            ],
            'Teacher and Educator' => [
                'teacher_name'  => 'Educator full name',
                'teacher_email' => 'Educator login email address',
                'teacher_phone' => 'Educator contact number',
                'temp_password' => 'Generated temporary / initial login password',
                'ic_number'     => 'Educator IC or Passport number',
            ],
            'General Institutional' => [
                'recipient_name' => 'Recipient full name',
                'temp_password'  => 'Generated temporary / initial login password',
                'current_date'   => 'Current date formatted (e.g. 05 Oct 2026)',
                'current_year'   => 'Current four-digit year (e.g. 2026)',
                'portal_url'     => 'Olympia Scholars portal login URL',
                'reset_url'      => 'Password reset security link',
                'expire_minutes' => 'Token validity duration in minutes',
                'support_email'  => 'Official institution support contact email',
                'website_url'    => 'Official institution website link',
                'platform_name'  => 'Institution name (Olympia Scholars)',
            ],
        ];
    }

    /**
     * Get a flat array of variable keys and labels.
     */
    public static function flatList(): array
    {
        $flat = [];
        foreach (self::all() as $category => $variables) {
            foreach ($variables as $key => $desc) {
                $flat[$key] = [
                    'key'         => $key,
                    'placeholder' => '{{' . $key . '}}',
                    'label'       => $desc,
                    'category'    => $category,
                ];
            }
        }
        return $flat;
    }

    /**
     * Resolve all placeholders in a string using contextual model records or key-value array.
     */
    public static function render(string $content, array $context = []): string
    {
        $data = self::buildContextData($context);

        // Pattern matching {{variable_name}} with optional spaces
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function ($matches) use ($data) {
            $key = $matches[1];
            if (array_key_exists($key, $data)) {
                return (string) $data[$key];
            }
            // If variable not in context, leave empty or return default institutional fallback
            return '';
        }, $content);
    }

    /**
     * Validate placeholders in a template text.
     * Returns array of unknown placeholders found.
     */
    public static function findUnknownPlaceholders(string $content): array
    {
        $validKeys = array_keys(self::flatList());
        $unknown = [];

        if (preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches)) {
            foreach ($matches[1] as $foundKey) {
                if (!in_array($foundKey, $validKeys, true)) {
                    $unknown[] = $foundKey;
                }
            }
        }

        return array_unique($unknown);
    }

    /**
     * Assemble key-value pairs from provided entities and settings.
     */
    public static function buildContextData(array $context): array
    {
        $data = [
            'current_date'  => now()->format('d M Y'),
            'current_year'  => now()->format('Y'),
            'portal_url'    => url('/login'),
            'support_email' => Setting::get('official_support_email', config('mail.from.address', 'support@olympiascholars.edu.my')),
            'website_url'   => Setting::get('official_website_url', config('app.url', 'https://olympiascholars.edu.my')),
            'platform_name' => Setting::get('platform_name', 'Olympia Scholars'),
        ];

        // 1. User context
        if (isset($context['user']) && $context['user'] instanceof User) {
            $u = $context['user'];
            if ($u->role === 'parent') {
                $data['parent_name']  = $u->name;
                $data['parent_email'] = $u->email;
                $data['parent_phone'] = $u->phone ?? 'N/A';
            } elseif ($u->role === 'student') {
                $data['student_name']  = $u->name;
                $data['student_email'] = $u->email;
            } elseif ($u->role === 'teacher') {
                $data['teacher_name']  = $u->name;
                $data['teacher_email'] = $u->email;
                $data['teacher_phone'] = $u->phone ?? 'N/A';
            }
            if (!isset($data['recipient_name'])) {
                $data['recipient_name'] = $u->name;
            }
        }

        // 2. Student context
        if (isset($context['student']) && $context['student'] instanceof Student) {
            $s = $context['student'];
            $sUser = $s->user;
            $data['student_name']  = $sUser?->name ?? 'Student #' . $s->id;
            $data['student_id']    = 'STU-' . str_pad($s->id, 5, '0', STR_PAD_LEFT);
            $data['student_email'] = $sUser?->email ?? '';
            $data['student_level'] = $s->level ?? 'General';
            $data['programme_name'] = $s->programme ?? 'Olympia Scholar Programme';

            // Find primary parent if not already populated
            if (!isset($data['parent_name'])) {
                $parentUser = $s->primaryParent->first() ?? $s->parent ?? $s->parents->first();
                if ($parentUser) {
                    $data['parent_name']  = $parentUser->name;
                    $data['parent_email'] = $parentUser->email;
                    $data['parent_phone'] = $parentUser->phone ?? $s->parent_phone ?? 'N/A';
                } else {
                    $data['parent_name']  = $data['student_name'] ?? ($sUser?->name ?? 'Scholar');
                    $data['parent_email'] = $data['student_email'] ?? ($sUser?->email ?? '');
                    $data['parent_phone'] = $s->parent_phone ?? 'N/A';
                }
            }
        }

        // 3. Invoice context
        if (isset($context['invoice']) && $context['invoice'] instanceof Invoice) {
            $inv = $context['invoice'];
            $sym = Setting::get('currency_symbol', 'RM');
            $data['invoice_number']     = $inv->invoice_number;
            $data['invoice_amount']     = $sym . ' ' . number_format($inv->total_amount, 2);
            $data['amount_paid']        = $sym . ' ' . number_format($inv->amount_paid, 2);
            $data['outstanding_amount'] = $sym . ' ' . number_format($inv->outstanding_amount, 2);
            $data['due_date']           = $inv->due_date ? Carbon::parse($inv->due_date)->format('d M Y') : 'Due on receipt';
            $data['payment_status']     = strtoupper($inv->status_label ?? $inv->status);

            if ($inv->student && !isset($data['student_name'])) {
                $data['student_name'] = $inv->student->user?->name ?? 'Student';
                $data['student_id']   = 'STU-' . str_pad($inv->student_id, 5, '0', STR_PAD_LEFT);
            }
        }

        // 4. Payment context
        if (isset($context['payment']) && $context['payment'] instanceof Payment) {
            $p = $context['payment'];
            $sym = Setting::get('currency_symbol', 'RM');
            $data['amount_paid']       = $sym . ' ' . number_format($p->amount, 2);
            $data['payment_date']      = $p->paid_at ? $p->paid_at->format('d M Y, h:i A') : now()->format('d M Y');
            $data['payment_reference'] = $p->reference_no ?? 'N/A';
            $data['receipt_number']    = $p->receipt_number ?? 'REC-' . str_pad($p->id, 5, '0', STR_PAD_LEFT);
            $data['payment_status']    = strtoupper($p->status ?? 'PAID');
        }

        // 5. LiveClass context
        if (isset($context['live_class']) && $context['live_class'] instanceof LiveClass) {
            $c = $context['live_class'];
            $data['class_name']    = $c->title;
            $data['class_date']    = $c->scheduled_at ? $c->scheduled_at->format('d M Y') : 'TBA';
            $data['class_time']    = $c->scheduled_at ? $c->scheduled_at->format('h:i A') : 'TBA';
            $data['class_schedule']= $c->scheduled_at ? ($c->scheduled_at->format('d M Y, h:i A') . ' (' . ($c->duration_minutes ?? 60) . ' mins)') : 'TBA';
            $data['meeting_url']   = $c->meeting_url ?? url('/student/dashboard#timetable');
            $data['class_status']  = strtoupper($c->status ?? 'UPCOMING');

            if ($c->teacher && $c->teacher->user) {
                $data['teacher_name'] = $c->teacher->user->name;
            }
            if ($c->course && $c->course->subject) {
                $data['subject_name'] = $c->course->subject->name;
            }
        }

        // 6. Direct scalar overrides
        foreach ($context as $key => $val) {
            if (is_scalar($val) && !is_object($val)) {
                $data[$key] = (string) $val;
            }
        }

        return $data;
    }

    public static function renderAll(array $context): array
    {
        return self::buildContextData($context);
    }
}
