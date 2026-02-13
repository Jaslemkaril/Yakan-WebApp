<?php

namespace App\Services;

class CloudinaryService
{
    protected $cloudName;
    protected $apiKey;
    protected $apiSecret;
    protected $enabled;

    public function __construct()
    {
        $this->cloudName = config('services.cloudinary.cloud_name');
        $this->apiKey = config('services.cloudinary.api_key');
        $this->apiSecret = config('services.cloudinary.api_secret');
        $this->enabled = !empty($this->cloudName) && !empty($this->apiKey) && !empty($this->apiSecret);
    }

    /**
     * Check if Cloudinary is configured
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Upload a file to Cloudinary
     *
     * @param string $filePath Local file path
     * @param string $folder Cloudinary folder name
     * @param string|null $publicId Optional public ID for the file
     * @return array|null Returns ['url' => '...', 'public_id' => '...'] or null on failure
     */
    public function upload(string $filePath, string $folder = 'products', ?string $publicId = null): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        $timestamp = time();
        $params = [
            'folder' => 'yakan/' . $folder,
            'timestamp' => $timestamp,
            'transformation' => 'q_auto,f_auto',
        ];

        if ($publicId) {
            $params['public_id'] = $publicId;
        }

        // Generate signature
        $signature = $this->generateSignature($params);

        $postFields = array_merge($params, [
            'file' => new \CURLFile($filePath),
            'api_key' => $this->apiKey,
            'signature' => $signature,
        ]);

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            \Log::error('Cloudinary upload failed', [
                'error' => $error,
                'httpCode' => $httpCode,
                'response' => $response,
            ]);
            return null;
        }

        $result = json_decode($response, true);

        if (!isset($result['secure_url'])) {
            \Log::error('Cloudinary upload response missing secure_url', ['response' => $result]);
            return null;
        }

        return [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id'] ?? null,
            'width' => $result['width'] ?? null,
            'height' => $result['height'] ?? null,
        ];
    }

    /**
     * Upload from an UploadedFile object (e.g. from request)
     */
    public function uploadFile(\Illuminate\Http\UploadedFile $file, string $folder = 'products', ?string $publicId = null): ?array
    {
        return $this->upload($file->getRealPath(), $folder, $publicId);
    }

    /**
     * Delete an image from Cloudinary
     */
    public function delete(string $publicId): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $timestamp = time();
        $params = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];

        $signature = $this->generateSignature($params);

        $postFields = array_merge($params, [
            'api_key' => $this->apiKey,
            'signature' => $signature,
        ]);

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        return ($result['result'] ?? '') === 'ok';
    }

    /**
     * Generate Cloudinary API signature
     */
    protected function generateSignature(array $params): string
    {
        // Remove file and api_key from signature params
        unset($params['file'], $params['api_key'], $params['signature']);

        // Sort by key
        ksort($params);

        // Build string to sign
        $stringToSign = collect($params)
            ->map(fn($value, $key) => "{$key}={$value}")
            ->implode('&');

        $stringToSign .= $this->apiSecret;

        return sha1($stringToSign);
    }

    /**
     * Get optimized URL with transformations
     */
    public static function optimizeUrl(string $url, int $width = 0, int $height = 0, string $crop = 'fill'): string
    {
        if (!str_contains($url, 'cloudinary.com')) {
            return $url;
        }

        $transforms = ['q_auto', 'f_auto'];
        if ($width > 0) {
            $transforms[] = "w_{$width}";
        }
        if ($height > 0) {
            $transforms[] = "h_{$height}";
        }
        if ($width > 0 || $height > 0) {
            $transforms[] = "c_{$crop}";
        }

        $transformString = implode(',', $transforms);

        // Insert transformation after /upload/
        return preg_replace(
            '/(\/upload\/)/',
            "/upload/{$transformString}/",
            $url
        );
    }
}
