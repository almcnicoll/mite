<?php

namespace Database\Seeders;

use App\Models\Cause;
use Illuminate\Database\Seeder;

class CauseSeeder extends Seeder
{
    public function run(): void
    {
        $causes = [
            ['name' => 'Oxford Community Church', 'email' => null, 'notes' => ''],
            ['name' => 'Ben & Michelle', 'email' => null, 'notes' => ''],
        ];

        foreach ($causes as $cause) {
            Cause::create($cause);
        }
    }
}