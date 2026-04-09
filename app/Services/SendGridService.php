<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendGridService
{
    /**
     * Send an email and return a detailed delivery attempt result.
     * success=true means the configured mail transport accepted the send request.
     */
    public static function sendDetailed(string $to, string $subject, string $htmlContent, ?string $textContent = null): array
    {
        $mailer = (string) config('mail.default', 'smtp');
        $host = (string) config('mail.mailers.smtp.host');
        $port = (string) config('mail.mailers.smtp.port');
        $fromEmail = config('mail.from.address');
        $fromName = config('mail.from.name');

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Log::error('Mail send: Invalid recipient email', ['to' => $to]);
            return [
                'success' => false,
                'status' => null,
                'message_id' => null,
                'error' => 'Invalid recipient email',
            ];
        }

        if (empty($fromEmail) || str_contains((string) $fromEmail, 'example.com')) {
            Log::error('Mail send: Invalid MAIL_FROM_ADDRESS', ['from' => $fromEmail]);
            return [
                'success' => false,
                'status' => null,
                'message_id' => null,
                'error' => 'Invalid sender email configuration',
            ];
        }

        try {
            // Send using whatever SMTP provider is configured (Brevo/SendGrid/Gmail/etc).
            Mail::mailer($mailer)->send([], [], function ($message) use ($to, $subject, $htmlContent, $textContent) {
                $message->to($to)->subject($subject);
                $message->html($htmlContent);
            });

            Log::info('Mail send: Email accepted by transport', [
                'to' => $to,
                'subject' => $subject,
                'mailer' => $mailer,
                'host' => $host,
                'port' => $port,
            ]);

            return [
                'success' => true,
                'status' => 200,
                'message_id' => null,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('Mail send: Exception', [
                'error' => $e->getMessage(),
                'to' => $to,
                'mailer' => $mailer,
                'host' => $host,
                'port' => $port,
            ]);

            return [
                'success' => false,
                'status' => null,
                'message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send an email using the configured Laravel mail transport.
     */
    public static function send(string $to, string $subject, string $htmlContent, ?string $textContent = null): bool
    {
        $result = self::sendDetailed($to, $subject, $htmlContent, $textContent);
        return (bool) ($result['success'] ?? false);
    }

    /**
     * Render a Blade view to HTML and send via SendGrid API.
     */
    public static function sendView(string $to, string $subject, string $view, array $data = []): bool
    {
        $html = view($view, $data)->render();
        return self::send($to, $subject, $html);
    }

    /**
     * Render a Blade view to HTML and send via SendGrid API with detailed result.
     */
    public static function sendViewDetailed(string $to, string $subject, string $view, array $data = []): array
    {
        $html = view($view, $data)->render();
        return self::sendDetailed($to, $subject, $html);
    }
}
