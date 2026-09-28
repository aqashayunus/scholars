<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Guardian Link Request</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, sans-serif; background:#f3f4f6; margin:0; padding:40px 16px;">
    <div style="max-width:480px; margin:0 auto; background:#fff; border-radius:12px; padding:32px; text-align:center; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        @if($outcome === 'approved')
            <h1 style="color:#16a34a;">Request Approved</h1>
            <p>The guardian link request has been approved. The requester now has access.</p>
        @elseif($outcome === 'denied')
            <h1 style="color:#dc2626;">Request Denied</h1>
            <p>The guardian link request has been denied. No access was granted.</p>
        @elseif($outcome === 'already_approved')
            <h1>Already Approved</h1>
            <p>This request was already approved previously.</p>
        @elseif($outcome === 'already_resolved')
            <h1>Already Resolved</h1>
            <p>This request has already been resolved and can no longer be actioned.</p>
        @else
            <h1>Request Not Found</h1>
            <p>This link is invalid or has expired.</p>
        @endif
    </div>
</body>
</html>
