<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * Production-ready PHP built-in server router for Railway deployment.
 * This script properly handles static files and routes all other requests
 * through Laravel's public/index.php entry point.
 *
 * Usage: php -S 0.0.0.0:$PORT server.php
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/'
);

// Serve static files directly from public directory
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    $file = __DIR__.'/public'.$uri;
    $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mime = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'otf'   => 'font/otf',
        'txt'   => 'text/plain',
        'json'  => 'application/json',
        'xml'   => 'application/xml',
        'pdf'   => 'application/pdf',
        'map'   => 'application/json',
    ][$ext] ?? 'application/octet-stream';
    header('Content-Type: '   . $mime);
    header('Content-Length: ' . filesize($file));
    header('Cache-Control: public, max-age=31536000');
    readfile($file);
    exit;
}

// Fallback for storage files when public/storage symlink is unavailable
// (common in ephemeral/container environments).
if (str_starts_with($uri, '/storage/')) {
    $relativePath = ltrim(substr($uri, strlen('/storage/')), '/');
    $storageFile = __DIR__.'/storage/app/public/'.$relativePath;

    if (file_exists($storageFile)) {
        $ext  = strtolower(pathinfo($storageFile, PATHINFO_EXTENSION));
        $mime = [
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'svg'   => 'image/svg+xml',
            'ico'   => 'image/x-icon',
            'webp'  => 'image/webp',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'otf'   => 'font/otf',
            'txt'   => 'text/plain',
            'json'  => 'application/json',
            'xml'   => 'application/xml',
            'pdf'   => 'application/pdf',
            'map'   => 'application/json',
        ][$ext] ?? 'application/octet-stream';

        header('Content-Type: '   . $mime);
        header('Content-Length: ' . filesize($storageFile));
        header('Cache-Control: public, max-age=31536000');
        readfile($storageFile);
        exit;
    }
}

// Route everything else through Laravel's front controller
require_once __DIR__.'/public/index.php';
