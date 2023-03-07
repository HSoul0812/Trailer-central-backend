<?php

namespace Tests\database\seeds\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\AuthToken;
use App\Models\User\DealerLocation;
use App\Models\User\NewUser;
use App\Models\User\User;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * Class AbstractLeadsSeeder
 * @package Tests\database\seeds\CRM\Leads
 *
 * @property-read User $dealer
 * @property-read Website $website
 * @property-read SalesPerson $sales
 * @property-read Lead $lead
 * @property-read AuthToken $authToken
 */
abstract class AbstractLeadsSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    protected $dealer;

    /**
     * @var AuthToken
     */
    protected $authToken;

    /**
     * @var Website
     */
    protected $website;

    /**
     * @var NewUser
     */
    protected $user;

    /**
     * @var Lead
     */
    protected $lead;

    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->dealer_id]);
        $this->user = factory(NewUser::class)->create();
        $this->lead = factory(Lead::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'website_id' => $this->website->getKey()
        ]);
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $userId = $this->user->getKey();

        NewUser::where('user_id', $userId)->delete();

        Lead::destroy($this->lead->identifier);
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::where('dealer_id', $dealerId)->delete();
        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();
        User::destroy($dealerId);
    }
}
