<?php

namespace App\Console\Commands\User;

use Illuminate\Console\Command;
use App\Models\User\User;
use App\Models\User\AuthToken;

/**
 * class RegenerateAccessTokens
 *
 * @package App\Console\Commands\User
 */
class RegenerateAccessToken extends Command
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
        // Get All Users
        $addCount = 0;
        $users = User::with('dealerUsers')->get();
        foreach ($users as $user) {
            // Get Auth Token for User!
            $authToken = AuthToken::where([
                ['user_id', $user->dealer_id],
                ['user_type', 'dealer']
            ])->first();
            if (!empty($authToken)) {
                $addCount++;
                echo "Regenerating token for dealer id: {$user->dealer_id} ".PHP_EOL;
                $authToken->update([
                    'access_token' => uniqid()
                ]);
            }

            // Loop Dealer Users
            if(!empty($user->dealerUsers)) {
                foreach($user->dealerUsers as $dealerUser) {
                    // Get Auth Token for User!
                    $authToken = AuthToken::where([
                        ['user_id', $dealerUser->dealer_user_id],
                        ['user_type', 'dealer_user']
                    ])->first();
                    if (!empty($authToken)) {
                        $addCount++;
                        echo "Regenerating token for dealer user id: {$dealerUser->dealer_user_id} ".PHP_EOL;
                        $authToken->update([
                            'access_token' => uniqid()
                        ]);
                    }
                }
            }
        }

        echo "Regenerated {$addCount} access tokens".PHP_EOL;
    }
}

