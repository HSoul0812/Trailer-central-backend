<?php

namespace Tests\database\seeds\CRM\Email;

use App\Models\CRM\Email\Processed;
use App\Models\CRM\User\EmailFolder;
use App\Models\CRM\User\SalesPerson;
use App\Models\Integration\Auth\AccessToken;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\User\NewUser;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * Class ScrapeRepliesSeeder
 * @package Tests\database\seeds\CRM\Email
 *
 * @property-read User $dealer
 * @property-read NewUser $user
 * @property-read NewDealerUser $newDealerUser
 * @property-read SalesPerson[] $salesPeople
 */
class ScrapeRepliesSeeder extends Seeder
{
    private const SALES_PEOPLE_COUNT = 3;

    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var NewUser
     */
    private $user;

    /**
     * @var NewDealerUser
     */
    private $newDealerUser;

    /**
     * @var CrmUser
     */
    private $crmUser;

    /**
     * @var SalesPerson[]
     */
    private $salesPeople = [];

    /**
     * @var string
     */
    private $tokenType;

    public function __construct($tokenType = 'google')
    {
        $this->dealer = factory(User::class)->create();
        $this->user = factory(NewUser::class)->create();
        $this->newDealerUser = factory(NewDealerUser::class)->create(['id' => $this->dealer->getKey(), 'user_id' => $this->user->getKey()]);
        $this->crmUser = factory(CrmUser::class)->create(['user_id' => $this->user->user_id, 'active' => 1]);

        $this->tokenType = $tokenType;
    }

    public function seed(): void
    {
        for ($i = 0; $i < self::SALES_PEOPLE_COUNT; $i++) {
            $salesperson = factory(SalesPerson::class)->create([
                'user_id' => $this->user->getKey(),
            ]);

            $accessToken = factory(AccessToken::class)->make([
                'dealer_id' => $this->dealer->getKey(),
                'relation_id' => $salesperson->id,
                'token_type' => $this->tokenType,
            ]);

            $folder = factory(EmailFolder::class)->make([
                'sales_person_id' => $salesperson->id,
                'user_id' => $this->user->getKey(),
            ]);

            $salesperson->tokens()->saveMany([$accessToken]);
            $salesperson->folders()->saveMany([$folder]);

            $this->salesPeople[] = $salesperson;
        }
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $userId = $this->user->getKey();

        AccessToken::where(['dealer_id' => $dealerId])->delete();
        EmailFolder::where(['user_id' => $userId])->delete();
        Processed::where(['user_id' => $userId])->delete();
        SalesPerson::where(['user_id' => $userId])->delete();
        CrmUser::where(['user_id' => $userId])->delete();

        NewDealerUser::where(['user_id' => $userId])->delete();
        NewUser::destroy($userId);
        User::destroy($dealerId);
    }
}
