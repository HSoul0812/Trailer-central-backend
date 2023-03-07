<?php

use Illuminate\Database\Seeder;

class CreateLumenRolesSeeder extends Seeder
{
    const MERGE_ADMIN_ROLE = 0; // 1 Yes | 0 No
    const ROLES = [
        [
            'name' => 'Admin',
            'guard_name' => 'nova',
        ],
        [
            'name' => 'Support',
            'guard_name' => 'nova',
        ],
        [
            'name' => 'Sales',
            'guard_name' => 'nova',
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert Roles
        foreach (self::ROLES as $role) {
            if (!$this->roleExist($role['name'])) {
                DB::table('roles')->insert($role);
            }
        }

        // Assign Admin role to a given user id
        if (self::MERGE_ADMIN_ROLE) {
            $this->mergeAdminRole('Admin', 0);
        }
    }

    /**
     * Verify if the role already exist
     *
     * @param string $role
     * @return bool
     */
    private function roleExist(string $role): bool
    {
        return DB::table('roles')->where('name', $role)->exists();
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
