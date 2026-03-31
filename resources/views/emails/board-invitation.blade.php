<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Invitation</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #7c3aed, #d946ef); padding: 32px 40px; text-align: center;">
                            <h1 style="color: #ffffff; font-size: 24px; margin: 0; font-weight: 700;">SmartBoard</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="color: #1f2937; font-size: 22px; margin: 0 0 8px 0; font-weight: 700;">You've been invited!</h2>
                            <p style="color: #6b7280; font-size: 16px; line-height: 1.6; margin: 0 0 24px 0;">
                                <strong style="color: #374151;">{{ $invitation->inviter->name }}</strong> has invited you to collaborate on the board
                                <strong style="color: #7c3aed;">"{{ $invitation->board->name }}"</strong>.
                            </p>

                            <!-- Board Preview Card -->
                            <div style="border-radius: 12px; overflow: hidden; margin-bottom: 24px; border: 1px solid #e5e7eb;">
                                <div style="background: {{ $invitation->board->background_value }}; height: 80px;"></div>
                                <div style="padding: 16px; background: #f9fafb;">
                                    <p style="margin: 0; font-weight: 600; color: #374151; font-size: 16px;">{{ $invitation->board->name }}</p>
                                    @if($invitation->board->description)
                                        <p style="margin: 4px 0 0 0; color: #6b7280; font-size: 14px;">{{ Str::limit($invitation->board->description, 100) }}</p>
                                    @endif
                                    <p style="margin: 8px 0 0 0; color: #9ca3af; font-size: 13px;">
                                        Role: <strong style="color: #7c3aed;">{{ ucfirst($invitation->role) }}</strong>
                                        &bull; Expires: {{ $invitation->expires_at->format('M j, Y') }}
                                    </p>
                                </div>
                            </div>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/invite/' . $invitation->token) }}"
                                           style="display: inline-block; padding: 14px 40px; background: linear-gradient(135deg, #7c3aed, #d946ef); color: #ffffff; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3);">
                                            Accept Invitation
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #9ca3af; font-size: 13px; margin-top: 24px; text-align: center;">
                                Or copy this link: <br>
                                <a href="{{ url('/invite/' . $invitation->token) }}" style="color: #7c3aed; word-break: break-all;">{{ url('/invite/' . $invitation->token) }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 40px; background-color: #f9fafb; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                This invitation will expire on {{ $invitation->expires_at->format('F j, Y') }}.<br>
                                If you didn't expect this invitation, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
