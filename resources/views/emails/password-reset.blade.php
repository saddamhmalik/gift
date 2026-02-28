<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your password</title>
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
            <h1>Reset your password</h1>
            <p>You requested a password reset for {{ $email }}. Click the button below to choose a new password. This link expires in {{ $expireMinutes }} minutes.</p>
            <p><a href="{{ $resetUrl }}" class="btn">Reset password</a></p>
            <p class="muted">If you didn't request this, you can safely ignore this email. Your password will not be changed.</p>
        </div>
    </div>
</body>
</html>
