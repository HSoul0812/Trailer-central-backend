<?php

/** @noinspection PhpDocSignatureIsNotCompleteInspection */

declare(strict_types=1);

namespace Database\Seeders\Page;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    private const PAGES = [
        ['name' => 'Home',            'url' => '/home'],
        ['name' => 'Private Sellers', 'url' => '/privatesellers'],
        ['name' => 'Dealers',         'url' => '/dealers'],
        ['name' => 'FAQ',             'url' => '/faq'],
        ['name' => 'About',           'url' => '/about'],
        ['name' => 'Affiliates',      'url' => '/affiliates'],
        ['name' => 'Privacy Policy',  'url' => '/privacypolicy'],
        ['name' => 'Terms of Use',    'url' => '/termsofuse'],
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->cleanTables();

        foreach (self::PAGES as $page) {
            DB::table('pages')->insert([
                'name' => $page['name'],
                'url' => $page['url'],
            ]);
        }
    }

    private function cleanTables(): void
    {
        DB::table('pages')->delete();
    }
}
