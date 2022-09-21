<?php

/** @noinspection PhpDocSignatureIsNotCompleteInspection */

declare(strict_types=1);

namespace Database\Seeders\Page;

use App\Models\Page\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    private const PAGES = [
        ['name' => 'Home',            'url' => '/home',           'description' => ''],
        ['name' => 'Private Sellers', 'url' => '/privatesellers', 'description' => ''],
        ['name' => 'Dealers',         'url' => '/dealers',        'description' => ''],
        ['name' => 'FAQ',             'url' => '/faq',            'description' => ''],
        ['name' => 'About',           'url' => '/about',          'description' => ''],
        ['name' => 'Affiliates',      'url' => '/affiliates',     'description' => ''],
        ['name' => 'Privacy Policy',  'url' => '/privacypolicy',  'description' => ''],
        ['name' => 'Terms of Use',    'url' => '/termsofuse',     'description' => ''],
        ['name' => 'Affiliates',      'url' => '/affiliates',     'description' => ''],
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
                'description' => $page['description']
            ]);
        }
    }

    private function cleanTables(): void
    {
        DB::table('pages')->delete();
    }
}
