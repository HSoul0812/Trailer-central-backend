<?php

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'rachel',
            'email' => 'rachel@operatebeyond.com',
            'password' => bcrypt('Squadron99!'),
        ]);
    }
}
