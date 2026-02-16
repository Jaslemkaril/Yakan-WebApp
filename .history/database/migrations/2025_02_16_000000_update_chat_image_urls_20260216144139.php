<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to update image URLs from /storage/ to /chat-image/
     */
    public function up(): void
    {
        // Update chat message image URLs
        DB::statement("
            UPDATE chat_messages 
            SET image_path = REPLACE(image_path, '/storage/chats/', '/chat-image/chats/')
            WHERE image_path LIKE '%/storage/chats/%'
        ");

        // Update payment proof URLs
        DB::statement("
            UPDATE chat_payments 
            SET payment_proof = REPLACE(payment_proof, '/storage/payments/', '/chat-image/payments/')
            WHERE payment_proof LIKE '%/storage/payments/%'
        ");
    }

    /**
     * Reverse the migrations (revert to old URLs)
     */
    public function down(): void
    {
        // Revert chat message image URLs
        DB::statement("
            UPDATE chat_messages 
            SET image_path = REPLACE(image_path, '/chat-image/chats/', '/storage/chats/')
            WHERE image_path LIKE '%/chat-image/chats/%'
        ");

        // Revert payment proof URLs
        DB::statement("
            UPDATE chat_payments 
            SET payment_proof = REPLACE(payment_proof, '/chat-image/payments/', '/storage/payments/')
            WHERE payment_proof LIKE '%/chat-image/payments/%'
        ");
    }
};
