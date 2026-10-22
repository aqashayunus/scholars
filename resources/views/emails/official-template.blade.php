<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no"/>
    <title>{{ $subject ?? 'Olympia Scholars' }}</title>
    <style type="text/css">
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; display: block; }
        body {
            margin: 0; padding: 0; width: 100% !important;
            background-color: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
            color: #222222;
        }
        .email-body-text p {
            margin: 0 0 16px 0; line-height: 1.60;
            color: #222222; font-size: 14px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .email-body-text ul, .email-body-text ol {
            margin: 0 0 16px 24px; padding: 0;
            color: #222222; font-size: 14px; line-height: 1.60;
            font-family: Arial, Helvetica, sans-serif;
        }
        .email-body-text li { margin-bottom: 6px; }
        /* Responsive banner */
        .banner-img { width: 100%; max-width: 600px; height: auto; display: block; }
        @media only screen and (max-width: 620px) {
            .outer-td { padding: 0 !important; }
            .content-padding { padding: 24px 20px !important; }
            .footer-padding { padding: 18px 20px 28px 20px !important; }
            .banner-img { width: 100% !important; height: auto !important; max-width: 100% !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #ffffff; font-family: Arial, Helvetica, sans-serif; color: #222222;">

    {{-- Outer wrapper --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color: #ffffff; width: 100%; margin: 0; padding: 0;">
        <tr>
            <td align="center" class="outer-td" style="padding: 0;">

                {{-- ── 1. Responsive Banner ── --}}
                @php
                    $storedBanner = \App\Models\Setting::get('email_banner_url');
                    $requestBase  = (function_exists('request') && request()->hasHeader('host'))
                        ? request()->schemeAndHttpHost()
                        : rtrim(config('app.url', 'http://localhost'), '/');
                    $fallbackUrl  = rtrim($requestBase, '/') . '/images/email-banner.png';
                    $banner       = $bannerUrl ?? ($storedBanner ?: $fallbackUrl);
                @endphp
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width: 600px; width: 100%;">
                    <tr>
                        <td align="center" style="padding: 0; line-height: 0; font-size: 0;">
                            <img src="{{ $banner }}"
                                 alt="Olympian Scholars — Tuition & Learning Centre"
                                 class="banner-img"
                                 width="600"
                                 style="display: block; width: 100%; max-width: 600px; height: auto; border: 0;" />
                        </td>
                    </tr>
                </table>

                {{-- ── 2. Plain body ── --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width: 600px; width: 100%;">
                    <tr>
                        <td class="content-padding"
                            style="padding: 32px 42px 28px 42px; background-color: #ffffff;">

                            {{-- Subject line as formal heading --}}
                            @if(!empty($subject))
                                <h1 style="margin: 0 0 20px 0; color: #0d1b3e; font-size: 18px; font-weight: 700;
                                            line-height: 1.4; letter-spacing: -0.01em;
                                            font-family: Arial, Helvetica, sans-serif;
                                            border: none; background: none; padding: 0;">
                                    {{ $subject }}
                                </h1>
                            @endif

                            {{-- Body content --}}
                            <div class="email-body-text"
                                 style="color: #222222; font-size: 14px; line-height: 1.60;
                                        font-family: Arial, Helvetica, sans-serif;">
                                {!! $htmlBody !!}
                            </div>

                            {{-- Optional CTA as plain link --}}
                            @if(!empty($ctaUrl) && !empty($ctaLabel))
                                <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.6;
                                           font-family: Arial, Helvetica, sans-serif;
                                           color: #222222;">
                                    &rarr;&nbsp;<a href="{{ $ctaUrl }}" target="_blank"
                                                   style="color: #0d1b3e; text-decoration: underline; font-weight: 600; font-family: Arial, Helvetica, sans-serif;">{{ $ctaLabel }}</a>
                                </p>
                            @endif

                            {{-- Formal sign-off --}}
                            <div style="margin-top: 36px; padding-top: 18px; border-top: 1px solid #e2e8f0;
                                        color: #222222; font-size: 14px; line-height: 1.60;
                                        font-family: Arial, Helvetica, sans-serif;">
                                <p style="margin: 0 0 4px 0;">Yours sincerely,</p>
                                <p style="margin: 0 0 2px 0; font-weight: 700; color: #0d1b3e;">Administration Team</p>
                                <p style="margin: 0 0 2px 0;">Olympian Scholars</p>
                                <p style="margin: 0; color: #64748b; font-size: 13px;">Olympia Education Malaysia</p>
                            </div>

                        </td>
                    </tr>
                </table>

                {{-- ── 3. Plain footer ── --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width: 600px; width: 100%;">
                    <tr>
                        <td class="footer-padding"
                            style="padding: 18px 42px 32px 42px; border-top: 1px solid #e2e8f0;
                                   text-align: center; background-color: #ffffff;">
                            @php
                                $supportEmail = \App\Models\Setting::get('official_support_email', 'info@olympianscholars.com');
                                $websiteUrl   = \App\Models\Setting::get('official_website_url', 'https://olympianscholars.com');
                                $footerNotice = \App\Models\Setting::get('official_email_footer', 'Olympian Scholars • Tuition & Learning Centre • Kuala Lumpur, Malaysia');
                            @endphp

                            <p style="margin: 0 0 6px 0; color: #444444; font-size: 13px; line-height: 1.6;
                                       font-family: Arial, Helvetica, sans-serif;">
                                {{ $footerNotice }}
                            </p>
                            <p style="margin: 0 0 8px 0; color: #666666; font-size: 12px; line-height: 1.6;
                                       font-family: Arial, Helvetica, sans-serif;">
                                Support:&nbsp;<a href="mailto:{{ $supportEmail }}"
                                                  style="color: #0d1b3e; text-decoration: none;">{{ $supportEmail }}</a>
                                &nbsp;|&nbsp;
                                Website:&nbsp;<a href="{{ $websiteUrl }}" target="_blank"
                                                  style="color: #0d1b3e; text-decoration: none;">{{ $websiteUrl }}</a>
                            </p>
                            <p style="margin: 0; color: #94a3b8; font-size: 11px; line-height: 1.5;
                                       font-family: Arial, Helvetica, sans-serif;">
                                This is an official communication from Olympian Scholars.<br/>
                                Please do not reply directly to this automated email.<br/>
                                &copy; {{ date('Y') }} Olympia Education Malaysia. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
