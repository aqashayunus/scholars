<div style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333333; white-space: pre-line;">
Dear {{ $guardian->name }},

Someone has requested guardian access to {{ $student->user?->name }}'s account on {{ $platformName }}, using their name and IC number.

Requester name: {{ $requester->name }}
Requester email: {{ $requester->email }}

If you recognise and approve of this request (for example, another parent, guardian, or family member), click below to approve:
{{ $approveUrl }}

If you do NOT recognise this request, click below to deny it. No account access will be granted.
{{ $denyUrl }}

If you take no action, this request will remain pending and will not grant any access.

Best regards,
The {{ $platformName }} Team

--------------------------------------------------
Need help? Contact support at {{ $supportEmail }}.
</div>
