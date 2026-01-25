<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// First disable foreign key checks
\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');

// Make user_id nullable
\Illuminate\Support\Facades\DB::statement('ALTER TABLE orders MODIFY user_id INT NULL');

// Re-enable foreign key checks
\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "✅ user_id is now nullable!\n";
echo "✅ Mobile orders can now be placed without user_id!\n";



