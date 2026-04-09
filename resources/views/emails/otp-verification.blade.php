<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - Yakan</title>
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

        $verifyUrl = route('verification.otp.form', ['email' => $user->email]);
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
                                    <td style="padding-top:14px;font-size:26px;line-height:1.2;font-weight:700;color:#ffffff;">Email Verification</td>
                                </tr>
                                <tr>
                                    <td style="padding-top:8px;font-size:14px;line-height:1.5;color:#f8d7dc;">Use this one-time code to activate your account securely.</td>
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
                        <td style="padding:0 28px 8px 28px;font-size:15px;line-height:1.7;color:#4b5563;">
                            Thank you for creating your Yakan account. Enter the verification code below on the OTP page to complete your sign up.
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:14px 28px 10px 28px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#7a0018;border-radius:12px;">
                                <tr>
                                    <td align="center" style="padding:14px 18px 6px 18px;font-size:12px;letter-spacing:0.6px;text-transform:uppercase;color:#f8d7dc;font-weight:700;">Your Verification Code</td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding:2px 18px 8px 18px;">
                                        <div style="font-size:36px;line-height:1.2;font-weight:700;letter-spacing:8px;color:#ffffff;">{{ $otp }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding:0 18px 14px 18px;font-size:13px;line-height:1.6;color:#f8d7dc;">Expires in 10 minutes</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:16px 28px 18px 28px;">
                            <a href="{{ $verifyUrl }}" style="display:inline-block;background-color:#7a0018;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;line-height:1;padding:14px 28px;border-radius:10px;">Open Verification Page</a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 18px 28px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#fff5f5;border:1px solid #f3d6da;border-radius:10px;">
                                <tr>
                                    <td style="padding:12px 14px;font-size:13px;line-height:1.6;color:#7f1d1d;">
                                        For your security, never share this code with anyone. Yakan support will never request your OTP by phone, chat, or email.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 10px 28px;font-size:13px;line-height:1.6;color:#6b7280;">
                            If the button does not work, copy and paste this link into your browser:
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 24px 28px;">
                            <div style="font-size:12px;line-height:1.7;color:#7a0018;word-break:break-all;background-color:#fafafa;border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;">{{ $verifyUrl }}</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="border-top:1px solid #eceff3;padding:18px 28px 24px 28px;font-size:12px;line-height:1.7;color:#9ca3af;">
                            <div style="font-weight:700;color:#6b7280;">Yakan E-commerce</div>
                            <div>This email was sent to {{ $user->email ?? 'your account email' }}.</div>
                            <div>If this was not you, you may ignore this email.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>