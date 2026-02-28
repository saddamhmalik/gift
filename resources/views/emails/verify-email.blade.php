<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email — {{ config('app.name') }}</title>
    <style>
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f4f5; }
        .wrap { max-width: 520px; margin: 0 auto; padding: 32px 20px; }
        .card { background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        h1 { margin: 0 0 8px; font-size: 22px; font-weight: 600; color: #18181b; }
        p { margin: 0 0 16px; font-size: 15px; line-height: 1.5; color: #3f3f46; }
        .btn { display: inline-block; padding: 12px 24px; background: #18181b; color: #fff !important; text-decoration: none; border-radius: 10px; font-weight: 500; font-size: 15px; margin: 8px 0 16px; }
        .muted { color: #71717a; font-size: 14px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>{{ $isNewEmail ? 'Verify your new email address' : 'Verify your email' }}</h1>
            <p>Hi {{ $firstName }},</p>
            <p>Please verify your {{ $isNewEmail ? 'new ' : '' }}email address by clicking the button below. This link expires in 24 hours.</p>
            <p><a href="{{ $verifyUrl }}" class="btn">Verify email</a></p>
            <p class="muted">If you didn't request this, you can safely ignore this email.</p>
        </div>
    </div>
</body>
</html>
