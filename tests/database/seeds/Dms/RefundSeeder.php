<?php

namespace Tests\database\seeds\Dms;

use App\Models\CRM\Dms\Refund;
use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Traits\WithGetter;

/**
 * Class RefundSeeder
 * @package Tests\database\seeds\Dms
 *
 * @property-read User $dealer
 * @property-read AuthToken $authToken
 * @property-read Refund $refund
 */
class RefundSeeder
{
    use WithGetter;

    /**
     * @var array
     */
    private $params;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var Refund
     */
    private $refund;

    /**
     * @var AuthToken
     */
    private $authToken;

    /**
     * RefundSeeder constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function seed(): void
    {
        $this->dealer = factory(User::class)->create();

        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        if (isset($this->params['withRefund']) && $this->params['withRefund']) {
            $this->refund = factory(Refund::class)->create([
                'dealer_id' => $this->dealer->dealer_id
            ]);
        }
    }

    public function cleanUp(): void
    {
        Refund::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();
        User::destroy($this->dealer->dealer_id);
    }
}
