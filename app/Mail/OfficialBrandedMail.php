<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfficialBrandedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailSubject;
    public string $htmlContent;
    public string $plainTextContent;
    public ?string $replyToEmail;
    public ?string $senderName;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $emailSubject,
        string $htmlContent,
        string $plainTextContent = '',
        ?string $replyToEmail = null,
        ?string $senderName = null
    ) {
        $this->emailSubject     = $emailSubject;
        $this->htmlContent      = $htmlContent;
        $this->plainTextContent = $plainTextContent;
        $this->replyToEmail     = $replyToEmail;
        $this->senderName       = $senderName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $fromAddress = Setting::get('sender_email_address', config('mail.from.address', 'noreply@olympiascholars.edu.my'));
        $fromName    = $this->senderName ?: Setting::get('sender_display_name', config('mail.from.name', 'Olympia Scholars'));
        $replyTo     = $this->replyToEmail ?: Setting::get('reply_to_email', config('mail.from.address'));

        $html = $this->htmlContent;
        $localBannerPath = $this->resolveLocalBannerPath();

        // If a local banner file exists and we are not using a remote hosted URL,
        // embed it via RFC-compliant CID (Content-ID) inline attachment.
        // This ensures the banner renders reliably in Gmail, Outlook, Yahoo, and mobile clients
        // even when sending from localhost or development environments.
        if ($localBannerPath && file_exists($localBannerPath)) {
            $filename = basename($localBannerPath);
            $mimeType = @mime_content_type($localBannerPath) ?: 'image/png';

            // Derive domain for RFC-compliant Content-ID (RFC 2392 requires id@domain)
            $fromHost = substr(strrchr($fromAddress, '@'), 1);
            $appHost  = parse_url(config('app.url', 'http://olympiascholars.edu.my'), PHP_URL_HOST);
            $domain   = (!empty($fromHost) && !in_array($fromHost, ['localhost', '127.0.0.1']))
                ? $fromHost
                : ((!empty($appHost) && !in_array($appHost, ['localhost', '127.0.0.1'])) ? $appHost : 'olympiascholars.edu.my');

            $cid = 'email-banner@' . $domain;

            // Replace banner img src with cid:email-banner@domain
            $pattern1 = '/(<img[^>]*class=["\'][^"\']*banner-img[^"\']*["\'][^>]*src=["\'])[^"\']*(["\'])/is';
            $html = preg_replace($pattern1, '${1}cid:' . $cid . '${2}', $html);

            if (!str_contains($html, 'cid:' . $cid)) {
                $pattern2 = '/(<img[^>]*src=["\'])[^"\']*(["\'][^>]*class=["\'][^"\']*banner-img[^"\']*["\'])/is';
                $html = preg_replace($pattern2, '${1}cid:' . $cid . '${2}', $html);
            }

            $this->withSymfonyMessage(function ($symfonyMessage) use ($localBannerPath, $filename, $mimeType, $cid) {
                $part = new \Symfony\Component\Mime\Part\DataPart(
                    new \Symfony\Component\Mime\Part\File($localBannerPath),
                    $filename,
                    $mimeType
                );
                $part->asInline();
                $part->setContentId($cid);
                $symfonyMessage->addPart($part);
            });
        }

        $mail = $this->from($fromAddress, $fromName)
            ->subject($this->emailSubject)
            ->html($html);

        if (!empty($this->plainTextContent)) {
            $mail->text('emails.official-template-text', [
                'subject'   => $this->emailSubject,
                'plainBody' => $this->plainTextContent,
            ]);
        }

        if (!empty($replyTo)) {
            $mail->replyTo($replyTo);
        }

        return $mail;
    }

    /**
     * Resolve the local filesystem path of the banner image.
     * Returns null if a remote public URL is configured.
     */
    protected function resolveLocalBannerPath(): ?string
    {
        $storedBanner = Setting::get('email_banner_url');

        // If a remote public URL is explicitly configured, prefer direct HTTP/HTTPS loading
        if ($this->isRemoteUrl($storedBanner)) {
            return null;
        }

        // 1. Check custom uploaded banner in storage
        if ($storedBanner) {
            // Check storage/banners/ path
            if (preg_match('#storage/banners/([^/?#]+)#', $storedBanner, $matches)) {
                $storageFile = storage_path('app/public/banners/' . $matches[1]);
                if (file_exists($storageFile)) {
                    return $storageFile;
                }
            }

            // Check public storage symlink or relative path
            $parsedPath = parse_url($storedBanner, PHP_URL_PATH);
            if ($parsedPath) {
                $candidate = public_path(ltrim($parsedPath, '/'));
                if (file_exists($candidate) && is_file($candidate)) {
                    return $candidate;
                }
            }
        }

        // 2. Default banner in public/images/email-banner.png
        $defaultBanner = public_path('images/email-banner.png');
        if (file_exists($defaultBanner)) {
            return $defaultBanner;
        }

        return null;
    }

    /**
     * Check if a given URL is a remote public URL (not localhost or loopback).
     */
    protected function isRemoteUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }

        $localHosts = ['localhost', '127.0.0.1', '::1'];
        return !in_array(strtolower($host), $localHosts, true);
    }
}
