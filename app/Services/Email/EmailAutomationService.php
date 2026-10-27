<?php

namespace App\Services\Email;

use App\Models\EmailAutomation;
use App\Models\EmailAutomationExecution;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EmailAutomationService
{
    /**
     * Trigger a system email automation safely without failing the calling domain workflow.
     */
    public static function trigger(string $automationKey, array $context = [], ?string $idempotencyKey = null, bool $sync = false, bool $isTest = false): array|bool
    {
        try {
            $aliases = [
                'balance_settled' => 'payment_confirmed',
                'class_reminder'  => 'upcoming_class_reminder',
            ];
            $automationKey = $aliases[$automationKey] ?? $automationKey;

            $automation = EmailAutomation::with(['template'])
                ->where('key', $automationKey)
                ->first();

            // Self-healing: if automation or table is unseeded on cPanel, auto-seed default templates & automations
            if (!$automation) {
                try {
                    (new \Database\Seeders\EmailTemplateSeeder())->run();
                    $automation = EmailAutomation::with(['template'])
                        ->where('key', $automationKey)
                        ->first();
                } catch (\Throwable $seederErr) {
                    Log::warning("[EmailAutomationService] Auto-seed failed: " . $seederErr->getMessage());
                }
            }

            if (!$automation) {
                return $isTest ? ['success' => false, 'error' => "Automation '{$automationKey}' not found in database. Please run: php artisan db:seed --class=EmailTemplateSeeder"] : false;
            }

            // Guard: check if enabled by Admin (bypass in test mode)
            if (!$automation->is_enabled && !$isTest) {
                self::recordExecution($automation, 'skipped', '', 'Automation disabled by administrator.', $idempotencyKey);
                return false;
            }

            // Guard: check if template exists and is active (allow draft in test mode)
            $template = $automation->template;
            if (!$template) {
                // Try self-healing template association
                try {
                    (new \Database\Seeders\EmailTemplateSeeder())->run();
                    $automation->refresh();
                    $template = $automation->template;
                } catch (\Throwable $e) {}
            }
            if (!$template) {
                self::recordExecution($automation, 'skipped', '', 'Template is missing.', $idempotencyKey);
                return $isTest ? ['success' => false, 'error' => "No template linked to automation '{$automation->name}'. Please select an active template."] : false;
            }
            if ($template->status !== EmailTemplate::STATUS_ACTIVE && !$isTest) {
                self::recordExecution($automation, 'skipped', '', 'Template is inactive.', $idempotencyKey);
                return false;
            }

            // Guard: Idempotency check (prevent duplicate notifications for same event, skip for tests)
            if ($idempotencyKey && !$isTest) {
                $alreadyExecuted = EmailAutomationExecution::where('email_automation_id', $automation->id)
                    ->where('idempotency_key', $idempotencyKey)
                    ->where('status', 'success')
                    ->exists();

                if ($alreadyExecuted) {
                    Log::info("[EmailAutomationService] Idempotency hit: {$automationKey} with key {$idempotencyKey} already executed. Skipping.");
                    return false;
                }
            }

            // Determine recipient emails from context
            $recipients = self::resolveAutomationRecipients($context);

            if (empty($recipients)) {
                self::recordExecution($automation, 'skipped', '', 'No valid recipient email address found in context.', $idempotencyKey);
                return $isTest ? ['success' => false, 'error' => 'No valid recipient email address found in context.'] : false;
            }

            $successAny = false;
            $lastError  = null;

            foreach ($recipients as $rcpt) {
                $email = strtolower(trim($rcpt['email']));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                $mergedContext = array_merge($context, [
                    'recipient_name'  => $rcpt['name'],
                    'recipient_email' => $email,
                    'recipient_type'  => $rcpt['type'],
                ]);

                // Send email
                $log = EmailSendingService::send(
                    $email,
                    $template->subject,
                    $template,
                    $mergedContext,
                    $automation->category,
                    $rcpt['user_id'] ?? null,
                    $context['student']?->id ?? null,
                    $context['invoice']?->id ?? null,
                    $template->id,
                    $automation->id,
                    $sync
                );

                if ($log->status === EmailLog::STATUS_FAILED) {
                    $lastError = $log->error_message ?? 'Mail transport delivery failed.';
                    self::recordExecution($automation, 'failed', $email, $lastError, $idempotencyKey);
                } else {
                    self::recordExecution($automation, 'success', $email, null, $idempotencyKey);
                    $successAny = true;
                }
            }

            if ($successAny) {
                $automation->increment('success_count');
                $automation->update(['last_triggered_at' => now()]);
            } elseif ($lastError) {
                $automation->increment('failure_count');
            }

            if ($isTest) {
                return [
                    'success' => $successAny,
                    'error'   => $successAny ? null : ($lastError ?: 'Email could not be delivered to mail server.')
                ];
            }

            return $successAny;

        } catch (\Throwable $e) {
            Log::error("[EmailAutomationService] Trigger {$automationKey} failed: " . $e->getMessage());
            if (isset($automation)) {
                $automation->increment('failure_count');
                self::recordExecution($automation, 'failed', '', $e->getMessage(), $idempotencyKey);
            }
            return $isTest ? ['success' => false, 'error' => $e->getMessage()] : false;
        }
    }

    /**
     * Extract recipient user/email records from context.
     */
    protected static function resolveAutomationRecipients(array $context): array
    {
        $recipients = [];

        // 1. Explicit email passed in context
        if (!empty($context['recipient_email'])) {
            $recipients[] = [
                'email'   => $context['recipient_email'],
                'name'    => $context['recipient_name'] ?? 'Recipient',
                'type'    => $context['recipient_type'] ?? 'custom',
                'user_id' => $context['user_id'] ?? null,
            ];
            return $recipients;
        }

        if (!empty($context['teacher_email'])) {
            $recipients[] = [
                'email'   => $context['teacher_email'],
                'name'    => $context['teacher_name'] ?? ($context['user']?->name ?? 'Educator'),
                'type'    => 'teacher',
                'user_id' => $context['user']?->id ?? ($context['user_id'] ?? null),
            ];
            return $recipients;
        }

        if (!empty($context['email'])) {
            $recipients[] = [
                'email'   => $context['email'],
                'name'    => $context['name'] ?? ($context['recipient_name'] ?? 'Recipient'),
                'type'    => $context['recipient_type'] ?? 'custom',
                'user_id' => $context['user_id'] ?? null,
            ];
            return $recipients;
        }

        // 2. User passed in context
        if (isset($context['user']) && $context['user'] instanceof User) {
            $u = $context['user'];
            if ($u->email) {
                $recipients[] = [
                    'email'   => $u->email,
                    'name'    => $u->name,
                    'type'    => $u->role,
                    'user_id' => $u->id,
                ];
            }
        }

        // 3. Student passed in context
        if (isset($context['student'])) {
            $student = $context['student'];
            // Student account email
            if ($student->user && $student->user->email) {
                $recipients[] = [
                    'email'   => $student->user->email,
                    'name'    => $student->user->name,
                    'type'    => 'student',
                    'user_id' => $student->user->id,
                ];
            }
            // Linked parent email(s)
            $parents = \App\Services\BillingNotificationService::getStudentParents($student);
            foreach ($parents as $p) {
                if ($p->email) {
                    $recipients[] = [
                        'email'   => $p->email,
                        'name'    => $p->name,
                        'type'    => 'parent',
                        'user_id' => $p->id,
                    ];
                }
            }
        }

        // 4. Invoice passed in context
        if (isset($context['invoice']) && empty($recipients)) {
            $invoice = $context['invoice'];
            if ($invoice->student) {
                return self::resolveAutomationRecipients(['student' => $invoice->student]);
            }
        }

        // Deduplicate by email
        $unique = [];
        foreach ($recipients as $r) {
            $unique[strtolower(trim($r['email']))] = $r;
        }

        return array_values($unique);
    }

    /**
     * Record execution log for audit and telemetry.
     */
    protected static function recordExecution(
        EmailAutomation $automation,
        string $status,
        string $recipientEmail = '',
        ?string $error = null,
        ?string $idempotencyKey = null
    ): void {
        EmailAutomationExecution::create([
            'email_automation_id' => $automation->id,
            'idempotency_key'     => $idempotencyKey,
            'status'              => $status,
            'recipient_email'     => $recipientEmail,
            'error_message'       => $error ? substr($error, 0, 500) : null,
            'executed_at'         => now(),
            'created_at'          => now(),
        ]);
    }
}
