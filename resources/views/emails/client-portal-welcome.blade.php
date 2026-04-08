<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1a1a2e; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h1 style="font-size: 18px;">Client portal access</h1>
    <p>{{ $client->organization->name }} has invited you to the client portal for <strong>{{ $client->name }}</strong>.</p>
    <p><strong>Sign in:</strong> <a href="{{ url('/client-portal/login') }}">{{ url('/client-portal/login') }}</a></p>
    <p><strong>Email:</strong> {{ $portalUser->email }}</p>
    <p><strong>Temporary password:</strong> <code style="background: #f1f1f4; padding: 2px 6px; border-radius: 4px;">{{ $plainPassword }}</code></p>
    <p style="font-size: 13px; color: #666;">Please change your password after first login when that option is available.</p>
    <p style="font-size: 13px; color: #888;">— {{ config('app.name') }}</p>
</body>
</html>
