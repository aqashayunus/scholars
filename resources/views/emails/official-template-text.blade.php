{{ $subject ?? 'Olympia Scholars' }}
==================================================

{{ $plainBody }}

@if(!empty($ctaUrl) && !empty($ctaLabel))
{{ $ctaLabel }}: {{ $ctaUrl }}
@endif

Yours sincerely,
Administration Team
Olympia Scholars
Olympia Education Malaysia
{{ \App\Models\Setting::get('official_support_email', config('mail.from.address', 'support@olympiascholars.edu.my')) }}

--------------------------------------------------
{{ \App\Models\Setting::get('official_email_footer', 'Olympia Scholars • Dedicated to Educational Excellence • Kuala Lumpur, Malaysia') }}
Website: {{ \App\Models\Setting::get('official_website_url', config('app.url', 'https://olympiascholars.edu.my')) }}
This is an official institutional communication from Olympia Scholars.
