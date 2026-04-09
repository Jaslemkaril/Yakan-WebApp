<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransactionalMailService
{
    /**
     * Send an email and return a detailed delivery attempt result.
     * success=true means the configured mail transport accepted the send request.
     */
    public static function sendDetailed(string $to, string $subject, string $htmlContent, ?string $textContent = null): array
    {
        self::syncRuntimeMailConfigFromEnv();

        $mailer = (string) config('mail.default', 'smtp');
        $host = (string) config('mail.mailers.smtp.host');
        $port = (string) config('mail.mailers.smtp.port');
        $username = (string) config('mail.mailers.smtp.username');
        $fromEmail = config('mail.from.address');

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
            Mail::mailer($mailer)->html($htmlContent, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            Log::info('Mail send: Email accepted by transport', [
                'to' => $to,
                'subject' => $subject,
                'mailer' => $mailer,
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'from' => $fromEmail,
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
                'username' => $username,
                'from' => $fromEmail,
                'exception' => get_class($e),
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
     * Render a Blade view to HTML and send.
     */
    public static function sendView(string $to, string $subject, string $view, array $data = []): bool
    {
        $html = view($view, $data)->render();
        return self::send($to, $subject, $html);
    }

    /**
     * Render a Blade view to HTML and send with detailed result.
     */
    public static function sendViewDetailed(string $to, string $subject, string $view, array $data = []): array
    {
        $html = view($view, $data)->render();
        return self::sendDetailed($to, $subject, $html);
    }

    /**
     * Railway-safe runtime sync for mail config to avoid stale cached values.
     */
    private static function syncRuntimeMailConfigFromEnv(): void
    {
        $runtime = [
            'mail.default' => env('MAIL_MAILER'),
            'mail.mailers.smtp.host' => env('MAIL_HOST'),
            'mail.mailers.smtp.port' => env('MAIL_PORT'),
            'mail.mailers.smtp.username' => env('MAIL_USERNAME'),
            'mail.mailers.smtp.password' => env('MAIL_PASSWORD'),
            'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION'),
            'mail.from.address' => env('MAIL_FROM_ADDRESS'),
            'mail.from.name' => env('MAIL_FROM_NAME'),
        ];

        foreach ($runtime as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            config([$key => $value]);
        }
    }
}
