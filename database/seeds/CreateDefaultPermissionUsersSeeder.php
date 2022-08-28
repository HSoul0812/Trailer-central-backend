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
                'password' => bcrypt('The-Ben2022!*'),
            ],
            [
                'name' => 'Support',
                'email' => 'support@operatebeyond.com',
                'password' => bcrypt('sWIThLEfulON'),
            ],
            [
                'name' => 'Sales',
                'email' => 'sales@operatebeyond.com',
                'password' => bcrypt('eRtFuSEcKWHE'),
            ]
        ];

        foreach ($users as $user) {
            $exists = DB::table('users')->where("email", $user["email"])->first();

            if (!$exists) {
                $userId = DB::table('users')->insertGetId($user);
            } else {
                $userId = $exists->id;
            }

            $this->mergeAdminRole($user['name'], $userId);
        }
    }

    /**
     * Verify if the role already exist and return his id
     *
     * @param string $role
     * @return bool
     */
    private function roleExistAndReturnId(string $role): bool
    {
        $role = DB::table('roles')->where('name', $role);

        if ($role->exists()) {
            return $role->first()->id;
        }

        return false;
    }

    /**
     * Verify if the dealer already exist
     *
     * @param string $role
     * @param int $userId
     * @return void
     */
    private function mergeAdminRole(string $role, int $userId): void
    {
        $roleId = $this->roleExistAndReturnId($role);
        $table = DB::table("model_has_roles");

        $data = [
            "role_id" => $roleId,
            "model_type" => "App\Models\User\NovaUser",
            "model_id" => $userId
        ];

        if (!$table->where($data)->exists()) {
            $table->insert($data);
        }
    }
}
