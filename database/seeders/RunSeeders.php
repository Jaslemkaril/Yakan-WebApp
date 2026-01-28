<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RunSeeders extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeederUpdated::class,
        ]);
    }
}
