<?php

namespace Tests\database\seeds\Files;

use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * Class FileSeeder
 * @package Tests\database\seeds\Files
 *
 * @property-read User $dealer
 * @property-read AuthToken $authToken
 */
class FileSeeder extends Seeder
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

    public function seed(): void
    {
        $this->dealer = factory(User::class)->create();

        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);
    }

    public function cleanUp(): void
    {
        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();
        User::destroy($this->dealer->dealer_id);
    }
}
