<?php

use Database\Seeders\Page\PageSeeder;
use Illuminate\Database\Migrations\Migration;

class SeedPages extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $seed = [
            '--force' => true,
            '--class' => PageSeeder::class,
        ];

        \Artisan::call('db:seed', $seed);
    }
}
