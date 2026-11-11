<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ParentLinkRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $guardian;

    public User $requester;

    public Student $student;

    public string $platformName;

    public string $supportEmail;

    public string $approveUrl;

    public string $denyUrl;

    public function __construct(User $guardian, User $requester, Student $student, int $pivotId)
    {
        $this->guardian = $guardian;
        $this->requester = $requester;
        $this->student = $student;
        $this->platformName = Setting::get('platform_name', 'OlympiaScholar');
        $this->supportEmail = Setting::get('support_email', 'support@olympiascholar.com');

        $this->approveUrl = URL::temporarySignedRoute(
            'parent-link-requests.approve',
            now()->addDays(7),
            ['pivot' => $pivotId]
        );

        $this->denyUrl = URL::temporarySignedRoute(
            'parent-link-requests.deny',
            now()->addDays(7),
            ['pivot' => $pivotId]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Action required: someone requested guardian access to '.$this->student->user?->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.parent-link-request',
        );
    }
}
