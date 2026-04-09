<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
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
        $fromEmail = (string) config('mail.from.address', '');

        if ($mailer === '' || str_contains($mailer, '"')) {
            $mailer = 'smtp';
        }

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
            try {
                $mailerInstance = Mail::mailer($mailer);
            } catch (\Throwable $mailerException) {
                // Fallback to smtp if MAIL_MAILER value is invalid/misconfigured.
                $mailer = 'smtp';
                $mailerInstance = Mail::mailer($mailer);
            }

            $mailerInstance->send([], [], function ($message) use ($to, $subject, $htmlContent) {
                $message->to($to)->subject($subject);
                $message->html($htmlContent);
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

            // Railway/runtime networks may block SMTP egress. Try Brevo HTTPS API fallback.
            $fallback = self::sendViaBrevoApi($to, $subject, $htmlContent, $textContent, $fromEmail);
            if (($fallback['success'] ?? false) === true) {
                return $fallback;
            }

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

            if (is_string($value)) {
                $value = self::sanitizeEnvString($value);
            }

            if (is_string($value) && $value === '') {
                continue;
            }

            config([$key => $value]);
        }
    }

    private static function sanitizeEnvString(string $value): string
    {
        $value = trim($value);

        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        return trim($value);
    }

    private static function sendViaBrevoApi(
        string $to,
        string $subject,
        string $htmlContent,
        ?string $textContent,
        string $fromEmail
    ): array {
        $apiKey = self::resolveBrevoApiKey();
        $fromName = (string) config('mail.from.name', 'Yakan');

        if ($apiKey === '') {
            return [
                'success' => false,
                'status' => null,
                'message_id' => null,
                'error' => 'Brevo API fallback unavailable: missing BREVO_API_KEY/API key.',
            ];
        }

        $payload = [
            'sender' => [
                'name' => $fromName,
                'email' => $fromEmail,
            ],
            'to' => [
                ['email' => $to],
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ];

        if (!empty($textContent)) {
            $payload['textContent'] = $textContent;
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->timeout(20)->post('https://api.brevo.com/v3/smtp/email', $payload);

            if ($response->successful()) {
                $messageId = (string) ($response->json('messageId') ?? '');

                Log::info('Mail send: Brevo API fallback accepted', [
                    'to' => $to,
                    'subject' => $subject,
                    'status' => $response->status(),
                    'message_id' => $messageId,
                ]);

                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message_id' => $messageId !== '' ? $messageId : null,
                    'error' => null,
                ];
            }

            Log::error('Mail send: Brevo API fallback failed', [
                'to' => $to,
                'subject' => $subject,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'status' => $response->status(),
                'message_id' => null,
                'error' => $response->body(),
            ];
        } catch (\Throwable $exception) {
            Log::error('Mail send: Brevo API fallback exception', [
                'to' => $to,
                'subject' => $subject,
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => null,
                'message_id' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }

    private static function resolveBrevoApiKey(): string
    {
        $candidates = [
            env('BREVO_API_KEY'),
            env('SENDINBLUE_API_KEY'),
            env('MAIL_PASSWORD'), // optional fallback if using API key in MAIL_PASSWORD
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate)) {
                continue;
            }

            $value = self::sanitizeEnvString($candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}
