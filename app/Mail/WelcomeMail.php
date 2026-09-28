<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Setting;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public ?string $tempPassword;
    public string $platformName;
    public string $supportEmail;
    public string $supportPhone;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?string $tempPassword = null)
    {
        $this->user = $user;
        $this->tempPassword = $tempPassword;
        $this->platformName = Setting::get('platform_name', 'OlympiaScholar');
        $this->supportEmail = Setting::get('support_email', 'support@olympiascholar.com');
        $this->supportPhone = Setting::get('support_phone', '+60 12-345 6789');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . $this->platformName . ' — Get Started Today!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
