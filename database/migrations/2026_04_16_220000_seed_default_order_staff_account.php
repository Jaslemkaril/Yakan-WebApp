<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $email = 'ordersteffie@gmail.com';
        $now = now();
        $existing = DB::table('users')->where('email', $email)->first();
        $verifiedAt = $existing && !empty($existing->email_verified_at)
            ? $existing->email_verified_at
            : $now;

        $payload = [
            'name' => 'Order Staff',
            'first_name' => 'Order',
            'last_name' => 'Staff',
            'password' => Hash::make('staff12345'),
            'role' => 'order_staff',
            'email_verified_at' => $verifiedAt,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('users')->where('id', $existing->id)->update($payload);
            return;
        }

        DB::table('users')->insert(array_merge($payload, [
            'email' => $email,
            'created_at' => $now,
        ]));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep the account intact to avoid accidental production lockouts.
    }
};
