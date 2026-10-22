<?php

namespace App\Notifications;

use App\Mail\OfficialBrandedMail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Services\Email\EmailSendingService;
use App\Services\Email\EmailTemplateRenderer;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
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
        $template = EmailTemplate::where('key', 'auth_password_reset')->first();

        if ($template && $template->status === EmailTemplate::STATUS_ACTIVE) {
            EmailSendingService::send(
                $notifiable->email,
                $template->subject,
                $template,
                $context,
                'authentication',
                $notifiable->id,
                null,
                null,
                $template->id,
                null,
                true // sync send
            );

            return (new MailMessage)
                ->subject($template->subject)
                ->line('Password reset request dispatched.');
        }

        // 2. High-fidelity branded fallback
        $subject = 'Reset Your Password — Olympian Scholars';
        $body = "Dear {{recipient_name}},\n\nWe received a request to reset the password for your Olympian Scholars account.\n\nClick the button below to choose a secure new password. For your security, this password reset link will expire in {{expire_minutes}} minutes.\n\nIf you did not make this request, you may safely ignore this message. Your account remains completely secure.";

        $rendered = EmailTemplateRenderer::render(
            $body,
            $subject,
            $context,
            null,
            'Reset My Password',
            $resetUrl
        );

        $mailable = new OfficialBrandedMail(
            $rendered['subject'],
            $rendered['html'],
            $rendered['plain_text']
        );

        // Record audit log
        $log = EmailLog::create([
            'recipient_email' => $notifiable->email,
            'recipient_name'  => $notifiable->name ?? 'Olympian Scholar',
            'recipient_type'  => $notifiable->role ?? 'user',
            'user_id'         => $notifiable->id,
            'category'        => 'authentication',
            'subject'         => $rendered['subject'],
            'status'          => EmailLog::STATUS_QUEUED,
            'attempts'        => 1,
        ]);

        try {
            Mail::to($notifiable->email)->send($mailable);
            $log->update([
                'status'       => EmailLog::STATUS_SENT,
                'sent_at'      => now(),
                'delivered_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("[ResetPasswordNotification] Delivery failed to {$notifiable->email}: " . $e->getMessage());
            $log->update([
                'status'        => EmailLog::STATUS_FAILED,
                'error_message' => substr($e->getMessage(), 0, 500),
            ]);
            session()->flash('mail_warning', 'Notice: Could not deliver email to mail server (' . $e->getMessage() . '). Please check SMTP configuration.');
        }

        return (new MailMessage)
            ->subject($rendered['subject'])
            ->line('Password reset link sent.');
    }
}
