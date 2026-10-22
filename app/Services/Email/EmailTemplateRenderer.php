<?php

namespace App\Services\Email;

use App\Models\EmailTemplate;

class EmailTemplateRenderer
{
    /**
     * Render an email template model or raw strings into both HTML and plain-text.
     */
    public static function render(
        EmailTemplate|string $templateOrBody,
        string $subject = '',
        array $context = [],
        ?string $bannerUrl = null,
        ?string $ctaLabel = null,
        ?string $ctaUrl = null
    ): array {
        if ($templateOrBody instanceof EmailTemplate) {
            $rawBody   = $templateOrBody->body;
            $subject   = $subject ?: $templateOrBody->subject;
            $bannerUrl = $bannerUrl ?: $templateOrBody->banner_url;
            $ctaLabel  = $ctaLabel ?: $templateOrBody->cta_label;
            $ctaUrl    = $ctaUrl ?: $templateOrBody->cta_url;
        } else {
            $rawBody = (string) $templateOrBody;
        }

        // 1. Substitute dynamic variables in subject, body, ctaUrl, and ctaLabel
        $resolvedSubject  = EmailVariableRegistry::render($subject, $context);
        $resolvedBodyText = EmailVariableRegistry::render($rawBody, $context);
        $resolvedCtaUrl   = $ctaUrl ? EmailVariableRegistry::render($ctaUrl, $context) : null;
        $resolvedCtaLabel = $ctaLabel ? EmailVariableRegistry::render($ctaLabel, $context) : null;

        // 2. Format plain text into clean email HTML paragraphs & lists
        $htmlBody = self::formatBodyToHtml($resolvedBodyText);

        // 3. Render HTML view with official Olympia Scholars institutional wrapper
        $html = view('emails.official-template', [
            'subject'   => $resolvedSubject,
            'htmlBody'  => $htmlBody,
            'bannerUrl' => $bannerUrl,
            'ctaLabel'  => $resolvedCtaLabel,
            'ctaUrl'    => $resolvedCtaUrl,
        ])->render();

        // 4. Render plain-text alternative
        $plainText = view('emails.official-template-text', [
            'subject'   => $resolvedSubject,
            'plainBody' => trim($resolvedBodyText),
            'ctaLabel'  => $resolvedCtaLabel,
            'ctaUrl'    => $resolvedCtaUrl,
        ])->render();

        return [
            'subject'    => $resolvedSubject,
            'html'       => $html,
            'plain_text' => $plainText,
        ];
    }

    /**
     * Render and return only the HTML output.
     */
    public static function renderHtml(
        EmailTemplate|string $templateOrBody,
        array $context = [],
        string $subject = '',
        ?string $bannerUrl = null,
        ?string $ctaLabel = null,
        ?string $ctaUrl = null
    ): string {
        $result = self::render($templateOrBody, $subject, $context, $bannerUrl, $ctaLabel, $ctaUrl);
        return $result['html'];
    }

    /**
     * Convert plain-text body with double/single newlines and bullets into clean HTML markup.
     */
    public static function formatBodyToHtml(string $text): string
    {
        // Normalize line endings
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));

        // Split into paragraph blocks separated by 2 or more newlines
        $paragraphs = preg_split('/\n{2,}/', $text);
        $htmlParts = [];

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if ($para === '') {
                continue;
            }

            // Check if paragraph is a bullet list (lines starting with * or - or •)
            $lines = explode("\n", $para);
            $isBulletList = true;
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed !== '' && !preg_match('/^[\*\-\•]\s+/', $trimmed)) {
                    $isBulletList = false;
                    break;
                }
            }

            if ($isBulletList && count($lines) > 0) {
                $listHtml = '<ul style="margin: 0 0 16px 22px; padding: 0; color: #222222; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6;">';
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if ($trimmed === '') continue;
                    $itemText = preg_replace('/^[\*\-\•]\s+/', '', $trimmed);
                    $listHtml .= '<li style="margin-bottom: 6px;">' . nl2br(e($itemText)) . '</li>';
                }
                $listHtml .= '</ul>';
                $htmlParts[] = $listHtml;
            } else {
                // Regular paragraph: preserve single linebreaks inside paragraph
                $escaped = nl2br(e($para));
                $htmlParts[] = '<p style="margin: 0 0 16px 0; line-height: 1.6; color: #222222; font-size: 14px; font-family: Arial, Helvetica, sans-serif;">' . $escaped . '</p>';
            }
        }

        return implode("\n", $htmlParts);
    }
}
