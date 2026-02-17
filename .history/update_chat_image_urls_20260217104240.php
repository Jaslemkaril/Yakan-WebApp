<?php

/**
 * Update chat message image URLs to use new /chat-image route
 * Run this once to fix existing messages with old /storage/ URLs
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ChatMessage;
use App\Models\ChatPayment;

echo "Starting URL update...\n\n";

// Update chat messages
$messages = ChatMessage::whereNotNull('image_path')
    ->where('image_path', 'like', '%/storage/chats/%')
    ->get();

echo "Found " . $messages->count() . " chat messages with old URLs\n";

foreach ($messages as $message) {
    $oldUrl = $message->image_path;
    
    // Extract filename from old URL
    // Example: https://yakan-webapp-production.up.railway.app/storage/chats/1771221314_sample.png
    preg_match('/\/storage\/chats\/(.+)$/', $oldUrl, $matches);
    
    if (isset($matches[1])) {
        $filename = $matches[1];
        $newUrl = route('chat.image', ['folder' => 'chats', 'filename' => $filename]);
        
        $message->image_path = $newUrl;
        $message->save();
        
        echo "✓ Updated message #{$message->id}: {$filename}\n";
        echo "  Old: {$oldUrl}\n";
        echo "  New: {$newUrl}\n\n";
    }
}

// Update payment proofs
$payments = ChatPayment::whereNotNull('payment_proof')
    ->where('payment_proof', 'like', '%/storage/payments/%')
    ->get();

echo "\nFound " . $payments->count() . " payment proofs with old URLs\n";

foreach ($payments as $payment) {
    $oldUrl = $payment->payment_proof;
    
    // Extract filename from old URL
    preg_match('/\/storage\/payments\/(.+)$/', $oldUrl, $matches);
    
    if (isset($matches[1])) {
        $filename = $matches[1];
        $newUrl = route('chat.image', ['folder' => 'payments', 'filename' => $filename]);
        
        $payment->payment_proof = $newUrl;
        $payment->save();
        
        echo "✓ Updated payment #{$payment->id}: {$filename}\n";
        echo "  Old: {$oldUrl}\n";
        echo "  New: {$newUrl}\n\n";
    }
}

echo "\n✅ Update complete!\n";
