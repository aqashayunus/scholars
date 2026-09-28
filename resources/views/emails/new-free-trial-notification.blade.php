<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Free Trial Booking Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; padding: 20px; max-width: 600px; margin: 0;">
    <p>Dear Team,</p>

    <p>A new free trial class booking request has been submitted through Olympian Scholars.</p>
    <p>Please review the details below and contact the parent or guardian to arrange and confirm the trial class schedule.</p>

    <p><strong>Trial Class Booking Details</strong><br>
    Registered As: {{ ucfirst($data['register_as'] ?? 'Student') }}<br>
    Name: {{ $data['student_name'] }}<br>
    @if(!empty($data['parent_name']) && ($data['register_as'] ?? '') === 'parent')
    Parent/Guardian: {{ $data['parent_name'] }}<br>
    @endif
    @if(!empty($data['school_name']))
    School / Institution: {{ $data['school_name'] }}<br>
    @endif
    Email Address: {{ $data['email'] }}<br>
    Phone Number: {{ $data['phone'] }}<br>
    Programme & Level: {{ $data['programme'] }} {{ !empty($data['level']) ? '('.$data['level'].')' : '' }}<br>
    Subject: {{ $data['subject'] }}<br>
    Preferred Schedule: {{ $data['schedule'] ?? 'Not Specified' }}<br>
    @if(!empty($data['promo_code']))
    Promo Code: <strong>{{ strtoupper($data['promo_code']) }}</strong><br>
    @endif
    @if(!empty($data['notes']))
    Notes: {{ $data['notes'] }}<br>
    @endif
    </p>

    <p><strong>Action Required</strong><br>
    Please contact the parent or guardian using the contact details provided above to confirm the trial class date and time.</p>

    <p>For student login and account access, please visit:<br>
    <a href="https://scholars.olympia-education.com/login" target="_blank">https://scholars.olympia-education.com/login</a></p>

    <p>Thank you for your prompt attention to this request.</p>

    <p>Best regards,<br>
    Olympian Scholars Team</p>
</body>
</html>
