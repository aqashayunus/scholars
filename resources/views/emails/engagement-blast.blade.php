<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            max-width: 580px;
            margin: 32px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #1e293b;
            padding: 28px 36px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-brand {
            color: #ffffff;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .header-tagline {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 400;
            margin-top: 1px;
        }
        .body {
            padding: 36px 40px;
        }
        .greeting {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .message-content {
            font-size: 13.5px;
            color: #334155;
            line-height: 1.75;
            white-space: pre-line;
        }
        .cta-section {
            margin: 32px 0 0;
            text-align: center;
        }
        .cta-button {
            display: inline-block;
            padding: 12px 28px;
            background-color: #2563eb;
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            letter-spacing: 0.1px;
        }
        .divider {
            height: 1px;
            background-color: #f1f5f9;
            margin: 36px 0 0;
        }
        .footer {
            padding: 24px 40px;
            background-color: #f8fafc;
        }
        .footer-text {
            font-size: 11px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .footer-brand {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
        }
        .footer-divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 14px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        {{-- Header --}}
        <div class="header">
            <div>
                <div class="header-brand">{{ $platformName }}</div>
                <div class="header-tagline">Excellence in Academic Coaching</div>
            </div>
        </div>

        {{-- Body --}}
        <div class="body">
            <p class="greeting">Dear {{ $recipientName }},</p>
            <p class="message-content">{{ $emailBody }}</p>

            @if($ctaLink)
            <div class="cta-section">
                <a href="{{ $ctaLink }}" class="cta-button">{{ $ctaLabel }}</a>
            </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="divider"></div>
        <div class="footer">
            <p class="footer-brand">{{ $platformName }}</p>
            <div class="footer-divider"></div>
            <p class="footer-text">
                This email was sent from the {{ $platformName }} admin portal as part of an outreach or re-engagement communication.
                If you believe this email was sent in error, or you wish to enquire further, please contact us at
                <a href="mailto:{{ $platformEmail }}" style="color: #2563eb; text-decoration: none;">{{ $platformEmail }}</a>.
            </p>
            <p class="footer-text" style="margin-top: 8px;">
                &copy; {{ date('Y') }} {{ $platformName }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
