<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Free Trial Booking Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; padding: 20px; max-width: 600px; margin: 0;">
    <p>Dear {{ $data['parent_name'] }},</p>

    <p>Thank you for registering for a free trial class with {{ $platformName }}. We have received your booking request and are currently processing it.</p>

    <p><strong>Trial Class Booking Details</strong><br>
    Registered As: {{ ucfirst($data['register_as'] ?? 'Student') }}<br>
    Name: {{ $data['student_name'] }}<br>
    @if(!empty($data['parent_name']) && ($data['register_as'] ?? '') === 'parent')
    Parent/Guardian: {{ $data['parent_name'] }}<br>
    @endif
    @if(!empty($data['school_name']))
    School / Institution: {{ $data['school_name'] }}<br>
    @endif
    Programme & Level: {{ $data['programme'] }} {{ !empty($data['level']) ? '('.$data['level'].')' : '' }}<br>
    Subject: {{ $data['subject'] }}<br>
    Preferred Schedule: {{ $data['schedule'] ?? 'Not Specified' }}<br>
    @if(!empty($data['promo_code']))
    Promo Code: <strong>{{ strtoupper($data['promo_code']) }}</strong> (VIP Benefits Active)<br>
    @endif
    </p>
    @if(!empty($data['notes']))
    <p>Additional Notes: {{ $data['notes'] }}</p>
    @endif

    <p>Our academic coordinator will contact you shortly via phone or WhatsApp at <strong>{{ $data['phone'] }}</strong> within the next 24 hours to confirm your class schedule and provide the necessary access details.</p>

    <p>If you have any immediate questions, feel free to reply directly to this email or contact us at {{ $supportEmail }}.</p>

    <p>Best regards,<br>
    Olympian Scholars Team</p>
</body>
</html>
