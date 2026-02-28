<?php

namespace Database\Seeders;

use App\Models\Setup;
use Illuminate\Database\Seeder;

class SetupSeeder extends Seeder
{
    public function run(): void
    {
        Setup::create([
            'date_from'       => '2024-01-01',
            'amount_per_day'  => 5.00,
        ]);
    }
}