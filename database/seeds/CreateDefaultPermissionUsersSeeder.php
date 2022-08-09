<?php

use Illuminate\Database\Seeder;

class CreateDefaultPermissionUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@operatebeyond.com',
                'password' => 'The-Ben2022!*',
            ],
            [
                'name' => 'Support',
                'email' => 'admin@operatebeyond.com',
                'password' => 'sWIThLEfulON',
            ],
            [
                'name' => 'Sales',
                'email' => 'admin@operatebeyond.com',
                'password' => 'eRtFuSEcKWHE',
            ]
        ];

        foreach ($users as $user) {
            $exists = DB::table('users')->where('email', $user["email"])->first();

            if (!$exists) {
                DB::table('users')->insert([
                    'name' => $user["name"],
                    'email' => $user["email"],
                    'password' => bcrypt($user["password"]),
                ]);
            }
        }
    }
}
