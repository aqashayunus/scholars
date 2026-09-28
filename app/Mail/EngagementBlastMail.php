<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EngagementBlastMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $emailSubject;
    public string $emailBody;
    public string $ctaLink;
    public string $ctaLabel;
    public string $platformName;
    public string $platformEmail;

    /**
     * Create a new message instance.
     *
     * @param  User    $recipient   The user receiving this email
     * @param  string  $subject     Email subject line (raw, no variable interpolation)
     * @param  string  $body        Email body with {name}, {role}, {platform} placeholders
     * @param  string  $ctaLink     Call-to-action URL (e.g. login URL), optional
     * @param  string  $ctaLabel    CTA button text, optional
     */
    public function __construct(
        public User   $recipient,
        string        $subject,
        string        $body,
        string        $ctaLink  = '',
        string        $ctaLabel = 'Return to Platform',
    ) {
        $this->platformName  = Setting::get('platform_name', config('app.name', 'Olympia Scholar'));
        $this->platformEmail = config('mail.from.address', 'noreply@olympianscholars.my');
        $this->recipientName = $recipient->name;
        $this->emailSubject  = $subject;

        // Interpolate placeholders in body
        $this->emailBody = str_replace(
            ['{name}', '{role}', '{platform}'],
            [$recipient->name, ucfirst($recipient->role), $this->platformName],
            $body
        );

        $this->ctaLink  = $ctaLink  ?: url('/');
        $this->ctaLabel = $ctaLabel ?: 'Return to Platform';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.engagement-blast',
        );
    }
}
