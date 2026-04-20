<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_messages')) {
            return;
        }

        $exists = DB::selectOne(
            "SELECT COUNT(1) AS aggregate
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = 'chat_messages'
               AND index_name = 'chat_messages_chat_sender_id_idx'"
        );

        if ((int) ($exists->aggregate ?? 0) > 0) {
            return;
        }

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index(['chat_id', 'sender_type', 'id'], 'chat_messages_chat_sender_id_idx');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('chat_messages')) {
            return;
        }

        $exists = DB::selectOne(
            "SELECT COUNT(1) AS aggregate
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = 'chat_messages'
               AND index_name = 'chat_messages_chat_sender_id_idx'"
        );

        if ((int) ($exists->aggregate ?? 0) === 0) {
            return;
        }

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('chat_messages_chat_sender_id_idx');
        });
    }
};
