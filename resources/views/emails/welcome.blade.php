<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f4f5; }
        .wrap { max-width: 520px; margin: 0 auto; padding: 32px 20px; }
        .card { background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        h1 { margin: 0 0 8px; font-size: 22px; font-weight: 600; color: #18181b; }
        p { margin: 0 0 16px; font-size: 15px; line-height: 1.5; color: #3f3f46; }
        .muted { color: #71717a; font-size: 14px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>Welcome to {{ config('app.name') }}</h1>
            <p>Hi {{ $user->first_name ?? $user->name }},</p>
            <p>Thanks for signing up. You're all set to start using {{ config('app.name') }}.</p>
            <p class="muted">If you didn't create this account, you can safely ignore this email.</p>
        </div>
    </div>
</body>
</html>
