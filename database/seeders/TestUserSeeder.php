<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'trailercentral',
            'email' => 'tc@trailercentral.com',
            'password' => '$2y$10$LzTxm7C1vAewCl5YmH/WTuNoNH.HdscRB3GplFDYjTsVPD9Cn3IZW',
        ]);
    }
}
