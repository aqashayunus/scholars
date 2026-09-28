<div style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333333; white-space: pre-line;">
Dear {{ $user->name }},

Welcome to {{ $platformName }}! We are thrilled to have you join our academic learning platform.

@if($tempPassword)
Your account has been provisioned with the following temporary login details:

Login Email: {{ $user->email }}
Temporary Password: {{ $tempPassword }}

Please log in and change your password immediately.
@endif

You can access your portal here:
{{ url('/login') }}

Best regards,
The {{ $platformName }} Team

--------------------------------------------------
Need help? Contact support at {{ $supportEmail }} or via phone at {{ $supportPhone }}.
</div>
