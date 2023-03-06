<?php

namespace App\Console\Commands\User;

use DB;
use Illuminate\Console\Command;

/**
 * class RegenerateDealerAccessToken
 *
 * @package App\Console\Commands\User
 */
class RegenerateDealerAccessToken extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "user:regenerate-access-token";

    /**
     * @return void
     */
    public function handle()
    {
        DB::statement("UPDATE auth_token
        SET access_token = UUID()
        WHERE `user_type` IN ( 'dealer', 'dealer_user' );");

        echo "Regenerated all dealer access tokens" . PHP_EOL;
    }
}

