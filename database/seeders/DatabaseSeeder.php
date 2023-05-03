<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Page\PageSeeder;
use Database\Seeders\SysConfig\BannerSeeder;
use Database\Seeders\SysConfig\FilterSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BannerSeeder::class,
            FilterSeeder::class,
            TestUserSeeder::class,
            PageSeeder::class,
        ]);
    }
}
