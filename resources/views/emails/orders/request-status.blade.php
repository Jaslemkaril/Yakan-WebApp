<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Request Status Update' }}</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f5f5;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:#800000;color:#ffffff;padding:16px 24px;font-size:20px;font-weight:700;">
                            Yakan Order Update
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 12px;font-size:15px;line-height:1.6;">Hi {{ $customerName ?? 'Customer' }},</p>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">{{ $introText ?? 'Your request status has been updated.' }}</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:0 0 16px;">
                                <tr>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-size:13px;font-weight:600;width:35%;">Order Ref</td>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;font-size:13px;">{{ $orderRef ?? ('#' . ($orderId ?? 'N/A')) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-size:13px;font-weight:600;">Request Type</td>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;font-size:13px;">{{ $requestType ?? 'Request' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-size:13px;font-weight:600;">Decision</td>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;font-size:13px;font-weight:700;color:{{ ($decision ?? '') === 'Approved' ? '#047857' : '#b91c1c' }};">{{ $decision ?? 'Updated' }}</td>
                                </tr>
                                @if(!empty($reason))
                                <tr>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-size:13px;font-weight:600;">Reason</td>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;font-size:13px;">{{ $reason }}</td>
                                </tr>
                                @endif
                                @if(!empty($adminNote))
                                <tr>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-size:13px;font-weight:600;">Admin Note</td>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;font-size:13px;">{{ $adminNote }}</td>
                                </tr>
                                @endif
                                @if(!empty($approvedAmount))
                                <tr>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-size:13px;font-weight:600;">Approved Amount</td>
                                    <td style="padding:8px 10px;border:1px solid #e5e7eb;font-size:13px;">PHP {{ number_format((float) $approvedAmount, 2) }}</td>
                                </tr>
                                @endif
                            </table>

                            @if(!empty($extraMessage))
                                <p style="margin:0 0 12px;font-size:14px;line-height:1.6;">{{ $extraMessage }}</p>
                            @endif

                            <p style="margin:0;font-size:13px;line-height:1.6;color:#6b7280;">Thank you for shopping with Yakan.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
