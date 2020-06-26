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
        $addCount = 0;
        $users = User::all();
        foreach ($users as $user) {
           $authToken = AuthToken::where('user_id', $user->dealer_id)->first();
           if (empty($authToken)) {
               $addCount++;
               echo "Adding token for dealer id: {$user->dealer_id} ".PHP_EOL;
               AuthToken::create([
                'user_id' => $user->dealer_id,
                'access_token' => md5($user->dealer_id.uniqid())
               ]);
           }      
        }
        echo "Added {$addCount} access tokens".PHP_EOL;
    }
}

