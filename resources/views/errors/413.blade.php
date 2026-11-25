<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File too large</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; color: #0f172a; padding: 16px; box-sizing: border-box; }
        .card { max-width: 460px; background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 28px; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        h1 { font-size: 18px; margin: 0 0 8px; }
        p { font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 12px; }
        a { display: inline-block; margin-top: 6px; background: #2563eb; color: #fff; text-decoration: none; font-weight: 700; font-size: 13px; padding: 10px 18px; border-radius: 12px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>That file is too large to upload</h1>
        <p>The server accepts files up to {{ \App\Support\UploadLimit::megabytes() }} MB, so nothing was saved.</p>
        <p>For class videos, upload to YouTube (as Unlisted) or Google Drive and paste the link instead. Otherwise, choose a smaller file.</p>
        <a href="javascript:history.back()">← Go back</a>
    </div>
</body>
</html>
