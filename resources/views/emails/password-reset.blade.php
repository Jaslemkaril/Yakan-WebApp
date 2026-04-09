<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - Yakan</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    @php
        $displayName = trim((string) (
            $user->first_name
            ?? $user->name
            ?? strtok((string) ($user->email ?? ''), '@')
            ?? 'Customer'
        ));
        if ($displayName === '') {
            $displayName = 'Customer';
        }
    @endphp

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f4f5f7;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:640px;background-color:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#7a0018 0%,#5a0012 100%);padding:26px 28px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="font-size:30px;font-weight:700;line-height:1;color:#ffffff;letter-spacing:0.2px;">Yakan</td>
                                </tr>
                                <tr>
                                    <td style="padding-top:14px;font-size:26px;line-height:1.2;font-weight:700;color:#ffffff;">Password Reset Request</td>
                                </tr>
                                <tr>
                                    <td style="padding-top:8px;font-size:14px;line-height:1.5;color:#f8d7dc;">A secure reset link was requested for your account.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 28px 8px 28px;font-size:16px;line-height:1.6;color:#1f2937;">
                            Hello {{ $displayName }},
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 6px 28px;font-size:15px;line-height:1.7;color:#4b5563;">
                            We received a request to reset your password. Click the button below to continue.
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:24px 28px 20px 28px;">
                            <a href="{{ $resetUrl }}" style="display:inline-block;background-color:#7a0018;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;line-height:1;padding:14px 28px;border-radius:10px;">Reset Password</a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 18px 28px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#fff5f5;border:1px solid #f3d6da;border-radius:10px;">
                                <tr>
                                    <td style="padding:12px 14px;font-size:13px;line-height:1.6;color:#7f1d1d;">
                                        This link expires in 60 minutes. If you did not request this, you can safely ignore this email.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 8px 28px;font-size:13px;line-height:1.6;color:#6b7280;">
                            If the button does not work, copy and paste this link into your browser:
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 24px 28px;">
                            <div style="font-size:12px;line-height:1.7;color:#7a0018;word-break:break-all;background-color:#fafafa;border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;">{{ $resetUrl }}</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="border-top:1px solid #eceff3;padding:18px 28px 24px 28px;font-size:12px;line-height:1.7;color:#9ca3af;">
                            <div style="font-weight:700;color:#6b7280;">Yakan E-commerce</div>
                            <div>This email was sent to {{ $user->email ?? 'your account email' }}.</div>
                            <div>If you need help, contact support@yakan.com.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
