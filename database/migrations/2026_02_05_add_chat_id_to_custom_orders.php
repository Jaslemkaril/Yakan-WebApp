<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            // Add optional reference to chat for orders created from payment
            $table->foreignId('chat_id')->nullable()->constrained('chats')->cascadeOnDelete()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['chat_id']);
            $table->dropColumn('chat_id');
        });
    }
};
