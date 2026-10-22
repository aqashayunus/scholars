<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || request()->server('HTTP_X_HTTPS') == '1') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\StudentAssignmentSubmission::class,
            \App\Policies\StudentAssignmentSubmissionPolicy::class
        );

        // Branded Official Email Automation for Password Reset
        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $expireMinutes = (int) config('auth.passwords.users.expire', 60);

            $context = [
                'user'            => $notifiable,
                'recipient_name'  => $notifiable->name ?? 'Olympian Scholar',
                'recipient_email' => $notifiable->email,
                'reset_url'       => $resetUrl,
                'cta_url'         => $resetUrl,
                'action_url'      => $resetUrl,
                'expire_minutes'  => (string) $expireMinutes,
                'portal_url'      => url('/login'),
            ];

            // 1. Dynamic template if active in database
            $template = \App\Models\EmailTemplate::where('key', 'auth_password_reset')->first();

            if ($template && $template->status === \App\Models\EmailTemplate::STATUS_ACTIVE) {
                $rendered = \App\Services\Email\EmailTemplateRenderer::render(
                    $template->body,
                    $template->subject,
                    $context,
                    $template->banner_url,
                    $template->cta_label ?: 'Reset My Password',
                    $resetUrl
                );

                $subject   = $rendered['subject'];
                $html      = $rendered['html'];
                $plainText = $rendered['plain_text'];
            } else {
                $subject = 'Reset Your Password — Olympian Scholars';
                $body    = "Dear {{recipient_name}},\n\nWe received a request to reset the password for your Olympian Scholars account.\n\nClick the button below to choose a secure new password. For your security, this password reset link will expire in {{expire_minutes}} minutes.\n\nIf you did not request a password reset, no further action is required. Your account remains completely secure and protected.";

                $rendered = \App\Services\Email\EmailTemplateRenderer::render(
                    $body,
                    $subject,
                    $context,
                    null,
                    'Reset My Password',
                    $resetUrl
                );

                $subject   = $rendered['subject'];
                $html      = $rendered['html'];
                $plainText = $rendered['plain_text'];
            }

            // Create audit trail in email_logs
            try {
                \App\Models\EmailLog::create([
                    'recipient_email' => $notifiable->email,
                    'recipient_name'  => $notifiable->name ?? 'Olympian Scholar',
                    'recipient_type'  => $notifiable->role ?? 'user',
                    'user_id'         => $notifiable->id,
                    'category'        => 'authentication',
                    'subject'         => $subject,
                    'status'          => \App\Models\EmailLog::STATUS_SENT,
                    'sent_at'         => now(),
                    'attempts'        => 1,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[PasswordReset] Failed to write log: ' . $e->getMessage());
            }

            return (new \App\Mail\OfficialBrandedMail($subject, $html, $plainText))
                ->to($notifiable->email, $notifiable->name ?? null);
        });
    }
}
