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
    // Let PHP's built-in server handle the static file
    return false;
}

// Route everything else through Laravel's front controller
require_once __DIR__.'/public/index.php';
