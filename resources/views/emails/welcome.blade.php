<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no"/>
    <title>Welcome to {{ $platformName }}</title>
    <style type="text/css">
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; display: block; }
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
        }
        @media only screen and (max-width: 620px) {
            .wrapper-table { width: 100% !important; }
            .content-padding { padding: 24px 20px !important; }
            .header-padding { padding: 28px 20px !important; }
            .credential-col { display: block !important; width: 100% !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; width: 100%; margin: 0; padding: 24px 12px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="wrapper-table" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">

                    <!-- Brand Header Banner -->
                    <tr>
                        <td class="header-padding" align="left" style="background: linear-gradient(135deg, #0d1b3e 0%, #1e3a8a 100%); padding: 36px 40px; color: #ffffff;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; color: #93c5fd; margin-bottom: 6px;">
                                            Official Welcome Notice
                                        </div>
                                        <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 700; line-height: 1.25; letter-spacing: -0.01em;">
                                            {{ $platformName }}
                                        </h1>
                                        <p style="margin: 4px 0 0 0; color: #cbd5e1; font-size: 13px;">
                                            Excellence in Academic Mentorship &amp; Education
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="content-padding" style="padding: 36px 40px 28px 40px; background-color: #ffffff;">

                            <!-- Greeting -->
                            <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.6; color: #0f172a;">
                                Dear <strong>{{ $user->name }}</strong>,
                            </p>

                            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.65; color: #334155;">
                                Welcome to <strong>{{ $platformName }}</strong>!
                                @if($user->role === 'teacher')
                                    We are pleased to inform you that your educator application has been approved. Your educator account is now active and ready to access your teaching portal, student schedules, and learning materials.
                                @else
                                    We are thrilled to welcome you to our academic learning platform. Your account is now provisioned and ready to use.
                                @endif
                            </p>

                            <!-- Credentials Box (if password provided) -->
                            @if($tempPassword)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 24px 0; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 16px 20px; background-color: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a;">
                                                    Your Login Credentials
                                                </td>
                                                <td align="right">
                                                    <span style="display: inline-block; padding: 3px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase; background-color: #dbeafe; color: #1e40af; border-radius: 6px;">
                                                        {{ ucfirst($user->role ?? 'Member') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding-bottom: 12px; font-size: 13px; color: #64748b; width: 140px; font-weight: 500;">
                                                    Login Portal URL:
                                                </td>
                                                <td style="padding-bottom: 12px; font-size: 13px; font-weight: 600; color: #0f172a;">
                                                    <a href="{{ url('/login') }}" target="_blank" style="color: #2563eb; text-decoration: underline;">{{ url('/login') }}</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 12px; font-size: 13px; color: #64748b; font-weight: 500;">
                                                    Registered Email:
                                                </td>
                                                <td style="padding-bottom: 12px; font-size: 13px; font-weight: 700; color: #0f172a; font-family: monospace;">
                                                    {{ $user->email }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 13px; color: #64748b; font-weight: 500;">
                                                    Initial Password:
                                                </td>
                                                <td style="font-size: 15px; font-weight: 700; color: #1e3a8a; font-family: monospace; letter-spacing: 1px;">
                                                    <span style="background-color: #e0e7ff; padding: 4px 10px; border-radius: 6px; border: 1px dashed #6366f1;">
                                                        {{ $tempPassword }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>

                                        <p style="margin: 16px 0 0 0; padding-top: 14px; border-top: 1px dashed #cbd5e1; font-size: 12px; line-height: 1.5; color: #64748b;">
                                            <strong>Note:</strong> Your initial password is formatted as the <em>first 2 letters of your name (uppercase) + the last 6 digits of your IC/Passport</em>. For optimal security, please update your password after logging in.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 28px 0 20px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/login') }}" target="_blank"
                                           style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 700; padding: 14px 32px; border-radius: 10px; box-shadow: 0 2px 8px rgba(37,99,235,0.25);">
                                            Log In to Your Account &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Getting Started Instructions -->
                            <div style="margin-top: 28px; padding: 18px 20px; background-color: #f8fafc; border-left: 4px solid #2563eb; border-radius: 0 8px 8px 0;">
                                <h3 style="margin: 0 0 8px 0; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #0f172a; letter-spacing: 0.5px;">
                                    Quick Next Steps
                                </h3>
                                <ul style="margin: 0; padding-left: 18px; font-size: 13px; line-height: 1.6; color: #334155;">
                                    <li style="margin-bottom: 4px;">Click the button above to access the login page.</li>
                                    <li style="margin-bottom: 4px;">Sign in with your registered email and the initial password above.</li>
                                    @if($user->role === 'teacher')
                                        <li style="margin-bottom: 4px;">Visit your <strong>Profile</strong> page to review your bio, qualifications, and uploaded teaching documents.</li>
                                        <li>Explore your <strong>Students</strong>, <strong>Tasks</strong>, and <strong>Curriculum Workspaces</strong>.</li>
                                    @else
                                        <li>Complete any pending profile details to get the most out of your portal.</li>
                                    @endif
                                </ul>
                            </div>

                            <p style="margin: 28px 0 0 0; font-size: 14px; line-height: 1.6; color: #334155;">
                                Best regards,<br>
                                <strong>The {{ $platformName }} Team</strong>
                            </p>

                        </td>
                    </tr>

                    <!-- Support & Help Section -->
                    <tr>
                        <td style="padding: 20px 40px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; line-height: 1.5;">
                            <strong style="color: #334155;">Need help or have questions?</strong><br>
                            Reach our support team at <a href="mailto:{{ $supportEmail }}" style="color: #2563eb; text-decoration: underline;">{{ $supportEmail }}</a>
                            @if($supportPhone)
                                or via phone / WhatsApp at <strong style="color: #334155;">{{ $supportPhone }}</strong>
                            @endif
                        </td>
                    </tr>

                    <!-- Legal Footer -->
                    <tr>
                        <td align="center" style="padding: 20px 40px; background-color: #0f172a; color: #94a3b8; font-size: 11px; line-height: 1.5;">
                            &copy; {{ date('Y') }} {{ $platformName }}. All rights reserved.<br>
                            This is an automated system notification sent to {{ $user->email }}.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
