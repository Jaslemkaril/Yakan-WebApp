<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendGridService
{
    /**
     * Send an email and return a detailed delivery attempt result.
     * success=true means provider accepted the request.
     */
    public static function sendDetailed(string $to, string $subject, string $htmlContent, ?string $textContent = null): array
    {
        $apiKey = config('services.sendgrid.api_key', config('mail.mailers.smtp.password'));
        $fromEmail = config('mail.from.address');
        $fromName = config('mail.from.name');

        if (empty($apiKey) || !str_starts_with((string) $apiKey, 'SG.')) {
            Log::error('SendGrid API: Missing or invalid API key format');
            return [
                'success' => false,
                'status' => null,
                'message_id' => null,
                'error' => 'Missing/invalid SendGrid API key',
            ];
        }

        if (empty($fromEmail) || str_contains((string) $fromEmail, 'example.com')) {
            Log::error('SendGrid API: Invalid MAIL_FROM_ADDRESS', ['from' => $fromEmail]);
            return [
                'success' => false,
                'status' => null,
                'message_id' => null,
                'error' => 'Invalid sender email configuration',
            ];
        }

        $content = [];
        if ($textContent) {
            $content[] = ['type' => 'text/plain', 'value' => $textContent];
        }
        $content[] = ['type' => 'text/html', 'value' => $htmlContent];

        $payload = [
            'personalizations' => [
                ['to' => [['email' => $to]]],
            ],
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName,
            ],
            'subject' => $subject,
            'content' => $content,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post('https://api.sendgrid.com/v3/mail/send', $payload);

            $messageId = $response->header('x-message-id');
            if ($response->status() === 202 || $response->successful()) {
                Log::info('SendGrid API: Email accepted', [
                    'to' => $to,
                    'subject' => $subject,
                    'status' => $response->status(),
                    'message_id' => $messageId,
                ]);

                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message_id' => $messageId,
                    'error' => null,
                ];
            }

            Log::error('SendGrid API: Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => $to,
            ]);

            return [
                'success' => false,
                'status' => $response->status(),
                'message_id' => $messageId,
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('SendGrid API: Exception', ['error' => $e->getMessage(), 'to' => $to]);
            return [
                'success' => false,
                'status' => null,
                'message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send an email using SendGrid's v3 HTTP API.
     * This bypasses SMTP entirely, using HTTPS (port 443) which works on Railway.
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
