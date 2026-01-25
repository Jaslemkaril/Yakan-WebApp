<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseDatabase extends Command
{
    protected $signature = 'diagnose:db';
    protected $description = 'Diagnose database connection and setup';

    public function handle()
    {
        $this->info('Database Diagnostics');
        $this->line('-------------------');

        try {
            $connection = config('database.default');
            $this->info("Connection: {$connection}");

            if ($connection === 'sqlite') {
                $database = config('database.connections.sqlite.database');
                $this->info("Database file: {$database}");
                $this->info("File exists: " . (file_exists($database) ? 'Yes' : 'No'));
                $this->info("File writable: " . (is_writable($database) ? 'Yes' : 'No'));
            }

            DB::connection()->getPdo();
            $this->info("✓ Connection successful");

            // Get table count based on connection type
            if ($connection === 'sqlite') {
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
            } elseif (in_array($connection, ['mysql', 'mariadb'])) {
                $tables = DB::select("SHOW TABLES");
            } elseif ($connection === 'pgsql') {
                $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
            } else {
                $tables = [];
            }
            $this->info("Tables: " . count($tables));

            $users = DB::table('users')->count();
            $this->info("Users: {$users}");

            $products = DB::table('products')->count();
            $this->info("Products: {$products}");

        } catch (\Exception $e) {
            $this->error("✗ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
