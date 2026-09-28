<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject ?? 'Olympia Scholars' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f4f8; color: #1a202c; }
        .wrapper { max-width: 640px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #1a3c6e 0%, #2563eb 100%); padding: 36px 40px; text-align: center; }
        .header img { height: 40px; margin-bottom: 12px; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: 0.3px; }
        .header p { color: rgba(255,255,255,0.75); font-size: 13px; margin-top: 4px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; color: #1a202c; margin-bottom: 20px; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; border-bottom: 1px solid #e2e8f0; }
        .info-row:last-child { border-bottom: none; }
        .info-row .label { color: #64748b; font-weight: 500; }
        .info-row .value { color: #1a202c; font-weight: 600; text-align: right; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 14px; }
        .items-table th { background: #1a3c6e; color: #fff; padding: 10px 12px; text-align: left; font-weight: 600; font-size: 13px; }
        .items-table th:last-child { text-align: right; }
        .items-table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; color: #374151; }
        .items-table td:last-child { text-align: right; font-weight: 600; }
        .items-table .total-row { background: #f8fafc; font-weight: 700; }
        .items-table .total-row td { color: #1a3c6e; font-size: 15px; border-top: 2px solid #1a3c6e; }
        .outstanding-badge { display: inline-block; background: #fef9c3; color: #92400e; border: 1px solid #fde68a; border-radius: 6px; padding: 4px 10px; font-size: 13px; font-weight: 600; margin-top: 6px; }
        .paid-badge { display: inline-block; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; border-radius: 6px; padding: 4px 10px; font-size: 13px; font-weight: 600; }
        .overdue-badge { display: inline-block; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 6px; padding: 4px 10px; font-size: 13px; font-weight: 600; }
        .alert-box { border-radius: 8px; padding: 16px 20px; margin-bottom: 24px; font-size: 14px; line-height: 1.6; }
        .alert-info    { background: #eff6ff; border-left: 4px solid #2563eb; color: #1e40af; }
        .alert-warning { background: #fffbeb; border-left: 4px solid #f59e0b; color: #92400e; }
        .alert-danger  { background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; }
        .alert-success { background: #f0fdf4; border-left: 4px solid #22c55e; color: #166534; }
        .cta-row { display: flex; gap: 12px; margin-bottom: 28px; }
        .btn-primary { display: inline-block; background: #2563eb; color: #fff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; }
        .btn-secondary { display: inline-block; background: #f1f5f9; color: #374151 !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; border: 1px solid #e2e8f0; }
        .bank-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 24px; margin-bottom: 24px; }
        .bank-box h4 { font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 12px; }
        .bank-detail { font-size: 14px; color: #1a202c; margin-bottom: 6px; }
        .bank-detail strong { display: inline-block; min-width: 140px; color: #64748b; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 28px 0; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center; }
        .footer p { font-size: 12px; color: #94a3b8; line-height: 1.6; }
        .footer strong { color: #1a3c6e; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Olympia Scholars</h1>
        <p>Olympia Education Malaysia</p>
    </div>
    <div class="body">
        @yield('content')
    </div>
    <div class="footer">
        <p>
            This email was sent by <strong>{{ $senderName ?? 'Olympia Scholars' }}</strong> on behalf of Olympia Education Malaysia.<br>
            Please do not reply directly to this email. If you need assistance, contact the Scholars administration team.
        </p>
    </div>
</div>
</body>
</html>
