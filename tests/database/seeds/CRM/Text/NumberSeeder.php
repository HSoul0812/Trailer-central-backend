<?php

declare(strict_types=1);

namespace Tests\database\seeds\CRM\Text;

use App\Models\CRM\Text\Number;
use App\Models\CRM\Text\NumberTwilio;
use App\Models\User\DealerLocation;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Traits\WithGetter;
use Illuminate\Support\Collection;
use Tests\database\seeds\Seeder;
use App\Models\User\User;
use App\Models\User\NewUser;

/**
 * @property-read User $dealer
 * @property-read NewUser $user
 * @property-read Collection<Number> $createdNumbers
 * @property-read Collection<NumberTwilio> $twilioNumbers
 * @property-read string $dealerNumber
 * @property-read DealerLocation $dealerLocation
 */
class NumberSeeder extends Seeder
{
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
     * @var array<Number>
     */
    private $createdNumbers = [];

    /**
     * @var array<NumberTwilio>
     */
    private $twilioNumbers = [];

    /**
     * @var string
     */
    private $dealerNumber = '+19037153035';

    /**
     * @var DealerLocation
     */
    private $dealerLocation;

    /**
     * NumberSeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->user = factory(NewUser::class)->create();
        $this->dealerLocation = factory(DealerLocation::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'sms_phone' => $this->dealerNumber
        ]);

        $newDealerUserRepo = app(NewDealerUserRepositoryInterface::class);
        $newDealerUser = $newDealerUserRepo->create([
           'user_id' => $this->user->getKey(),
           'salt' => md5((string)$this->user->getKey()),
           'auto_import_hide' => 0,
           'auto_msrp' => 0
        ]);

        $this->dealer->newDealerUser()->save($newDealerUser);
    }

    /**
     * @return void
     */
    public function seed(): void
    {
        // Twilio Number Seeds
        for ($i = 0; $i < 4; $i++) {
            $twilioNumber = factory(NumberTwilio::class)->create();
            $this->twilioNumbers[] = $twilioNumber;
        }

        // Number Seeds
        $seeds = collect([
            [
                'dealer_id' => $this->dealer->getKey(),
                'twilio_number' => $this->twilioNumbers[0]->phone_number
            ],
            [
                'dealer_id' => $this->dealer->getKey(),
                'twilio_number' => $this->twilioNumbers[1]->phone_number
            ],
            [ 'dealer_id' => $this->dealer->getKey() ],
            [ 'dealer_id' => $this->dealer->getKey() ],
            [ 'dealer_id' => $this->dealer->getKey() ],
        ]);

        $seeds->each(function (array $seed): void {
            $number = factory(Number::class)->create($seed);
            // The refresh is needed to make sure the generated phone
            // number is the expected length after saving it.
            $this->createdNumbers[] = $number;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $userId = $this->user->getKey();

        Number::where('dealer_id', $this->dealer->getKey())->delete();
        NewUser::destroy($userId);
        User::destroy($dealerId);
        DealerLocation::where('dealer_id', $this->dealer->getKey())->delete();

        foreach ($this->twilioNumbers as $phone) {
            NumberTwilio::where('phone_number', $phone)->delete();
        }
    }
}
