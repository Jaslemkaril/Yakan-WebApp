<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendGridService
{
    /**
     * Send an email using SendGrid's v3 HTTP API.
     * This bypasses SMTP entirely, using HTTPS (port 443) which works on Railway.
     */
    public static function send(string $to, string $subject, string $htmlContent, ?string $textContent = null): bool
    {
        $apiKey = config('services.sendgrid.api_key', config('mail.mailers.smtp.password'));
        $fromEmail = config('mail.from.address');
        $fromName = config('mail.from.name');

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

            if ($response->status() === 202 || $response->successful()) {
                Log::info('SendGrid API: Email sent', ['to' => $to, 'subject' => $subject]);
                return true;
            }

            Log::error('SendGrid API: Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => $to,
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('SendGrid API: Exception', ['error' => $e->getMessage(), 'to' => $to]);
            return false;
        }
    }

    /**
     * Render a Blade view to HTML and send via SendGrid API.
     */
    public static function sendView(string $to, string $subject, string $view, array $data = []): bool
    {
        $html = view($view, $data)->render();
        return self::send($to, $subject, $html);
    }
}
