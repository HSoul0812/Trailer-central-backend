<?php

use App\Models\User\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    private $user;

    public function run()
    {
        $this->user = factory(User::class)->create();
    }

    public function cleanUp()
    {
        $this->user->delete();
    }
}
