<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('gcash','maya','paymongo','bank_transfer','cash') NOT NULL DEFAULT 'gcash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('gcash','maya','bank_transfer','cash') NOT NULL DEFAULT 'gcash'");
    }
};
