<?php

namespace App\Console\Commands\User;

use Illuminate\Console\Command;
use App\Models\User\User;
use App\Models\User\AuthToken;

class CreateAccessToken extends Command{
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "user:create-access-token";
    
    public function handle() 
    {
        // Get All Users
        $addCount = 0;
        $users = User::with('dealerUsers')->all();
        foreach ($users as $user) {
            // Get Auth Token for User!
            $authToken = AuthToken::where('user_id', $user->dealer_id)
                                  ->where('user_type', 'dealer')->first();
            if (empty($authToken)) {
                $addCount++;
                echo "Adding token for dealer id: {$user->dealer_id} ".PHP_EOL;
                AuthToken::create([
                    'user_id' => $user->dealer_id,
                    'user_type' => 'dealer',
                    'access_token' => md5($user->dealer_id.uniqid())
                ]);
            }

            // Loop Dealer Users
            if(!empty($user->dealerUsers)) {
                foreach($user->dealerUsers as $dealerUser) {
                    // Get Auth Token for User!
                    $authToken = AuthToken::where('user_id', $dealerUser->dealer_user_id)
                                          ->where('user_type', 'dealer_user')->first();
                    if (empty($authToken)) {
                        $addCount++;
                        echo "Adding token for dealer user id: {$dealerUser->dealer_user_id} ".PHP_EOL;
                        AuthToken::create([
                            'user_id' => $dealerUser->dealer_user_id,
                            'user_type' => 'dealer_user',
                            'access_token' => md5($dealerUser->dealer_user_id.uniqid())
                        ]);
                    }
                }
            }
        }
        echo "Added {$addCount} access tokens".PHP_EOL;
    }
}

