<?php

namespace Tests\database\seeds\Marketing\Craigslist;

use App\Models\Inventory\Inventory;
use App\Models\Marketing\Craigslist\Profile;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\Marketing\Craigslist\Session;
use App\Models\Marketing\Craigslist\Transaction;
use App\Models\User\AuthToken;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;
use App\Models\User\User;

/**
 * Class Queue
 *
 * @package Tests\database\seeds\Marketing\Craigslist
 *
 * @property-read User $dealer
 * @property-read AuthToken $authToken
 * @property-read Profile $profile
 * @property-read Session $session
 */
class TransactionSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var AuthToken
     */
    private $authToken;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Profile
     */
    private $profile;

    public function seed(): void
    {
        $this->dealer = factory(User::class)->create();
        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => 'dealer',
        ]);

        $this->transaction = factory(Transaction::class)->create([
            'dealer_id' => $this->dealer->dealer_id,
            'type' => Transaction::TYPE_POST
        ]);

        $this->queue = factory(Queue::class)->create([
            'dealer_id' => $this->dealer->dealer_id
        ]);

        $this->session = factory(Session::class)->create([
            'session_dealer_id' => $this->dealer->dealer_id,
            'session_profile_id' => $this->queue->profile_id
        ]);

        $this->profile = $this->queue->profile;
    }

    public function cleanUp(): void
    {
        Transaction::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        Session::where(['session_dealer_id' => $this->dealer->dealer_id])->delete();
        Queue::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        Profile::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        Inventory::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => 'dealer'])->delete();
        User::destroy($this->dealer->dealer_id);
    }
}
