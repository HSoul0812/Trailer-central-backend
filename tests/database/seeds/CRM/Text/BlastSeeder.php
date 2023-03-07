<?php

namespace Tests\database\seeds\CRM\Text;

use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Text\Blast;
use App\Models\CRM\Text\BlastBrand;
use App\Models\CRM\Text\BlastCategory;
use App\Models\CRM\Text\BlastSent;
use App\Models\CRM\Text\Template;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Manufacturers\Manufacturers;
use App\Models\User\DealerLocation;
use App\Models\User\NewDealerUser;
use App\Models\User\NewUser;
use App\Models\User\User;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Faker\Generator;
use Tests\database\seeds\Seeder;

/**
 * Class BlastSeeder
 * @package Tests\database\seeds\CRM\Text
 *
 * @property-read User $dealer
 * @property-read Website $website
 * @property-read NewUser $user
 * @property-read Blast[] $inquiredBlasts
 * @property-read Blast[] $purchasedBlasts
 * @property-read Blast[] $deliveredBlasts
 * @property-read Lead[] $wonLeads
 * @property-read Lead[] $otherLeads
 * @property-read Template $template
 */
class BlastSeeder extends Seeder
{
    use WithGetter;

    private const SEND_AFTER_DAYS = 45;

    private const TEST_INVENTORY_CATEGORY_1 = 'test_inventory_category_1';
    private const TEST_INVENTORY_CATEGORY_2 = 'test_inventory_category_2';
    private const TEST_INVENTORY_CATEGORY_3 = 'test_inventory_category_3';

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var Website
     */
    private $website;

    /**
     * @var NewUser
     */
    private $user;

    /**
     * @var NewDealerUser
     */
    private $newDealerUser;

    /**
     * @var DealerLocation
     */
    private $location;

    /**
     * @var Template
     */
    private $template;

    /**
     * @var Blast[]
     */
    private $inquiredBlasts = [];

    /**
     * @var Blast[]
     */
    private $purchasedBlasts = [];

    /**
     * @var Blast[]
     */
    private $deliveredBlasts = [];

    /**
     * @var Lead[]
     */
    private $wonLeads = [];

    /**
     * @var Lead[]
     */
    private $otherLeads = [];

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var string
     */
    private $blastAction;

    /**
     * @var int
     */
    private $blasArchived;

    /**
     * @var bool
     */
    private $withCategories;

    /**
     * @var bool
     */
    private $withBrands;

    /**
     * @var Inventory[]
     */
    private $inventories = [];

    public function __construct(string $blastAction = 'inquired', int $blastArchived = 0, $withCategories = false, $withBrands = false)
    {
        $this->blastAction = $blastAction;
        $this->blasArchived = $blastArchived;
        $this->withCategories = $withCategories;
        $this->withBrands = $withBrands;

        $this->dealer = factory(User::class)->create();
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->dealer_id]);
        $this->user = factory(NewUser::class)->create();
        $this->newDealerUser = factory(NewDealerUser::class)->create(['id' => $this->dealer->getKey(), 'user_id' => $this->user->getKey()]);
        $this->location = factory(DealerLocation::class)->create(['dealer_id' => $this->dealer->getKey()]);
        $this->template = factory(Template::class)->create(['user_id' => $this->user->getKey()]);

        $this->faker = Faker::create();
    }

    public function seed(): void
    {
        // Get 3 Manufacturers
        $manufacturers = Manufacturers::inRandomOrder()->take(3)->pluck('name')->toArray();

        $inventorySeeds = [
            ['category' => self::TEST_INVENTORY_CATEGORY_1, 'manufacturer' => $manufacturers[0]],
            ['category' => self::TEST_INVENTORY_CATEGORY_2, 'manufacturer' => $manufacturers[1]],
            ['category' => self::TEST_INVENTORY_CATEGORY_3, 'manufacturer' => $manufacturers[2]],
        ];

        foreach ($inventorySeeds as $inventorySeed) {
            $this->inventories[] = factory(Inventory::class)->create([
                'dealer_id' => $this->dealer->dealer_id,
                'dealer_location_id' => $this->location->dealer_location_id,
                'category' => $inventorySeed['category'],
                'manufacturer' => $inventorySeed['manufacturer'],
                'brand' => $inventorySeed['manufacturer'],
            ]);
        }

        $seeds = [
            [
                'name' => 'Test Blast 1',
                'action' => $this->blastAction,
                'is_delivered' => false,
                'brand' => $manufacturers[0],
                'category' => self::TEST_INVENTORY_CATEGORY_1
            ],
            [
                'name' => 'Test Blast 3',
                'action' => $this->blastAction,
                'is_delivered' => true,
                'brand' => $manufacturers[2],
                'category' => self::TEST_INVENTORY_CATEGORY_3,
            ],
        ];

        foreach ($seeds as $seed) {
            $blast = factory(Blast::class)->create([
                'user_id' => $this->user->getKey(),
                'campaign_name' => $seed['name'],
                'action' => $seed['action'],
                'from_sms_number' => $this->faker->numberBetween(1111111111, 2147483647),
                'is_delivered' => $seed['is_delivered'],
                'send_after_days' => self::SEND_AFTER_DAYS,
                'location_id' => $this->location->dealer_location_id,
                'template_id' => $this->template->getKey(),
            ]);

            if ($this->blasArchived !== 0) {
                $blast->include_archived = $this->blasArchived;
                $blast->send_date = Carbon::now()->subDays(5)->toDateTimeString();
                $blast->save();
            }

            if ($this->withBrands) {
                factory(BlastBrand::class)->create([
                    'text_blast_id' => $blast->id,
                    'brand' => $seed['brand']
                ]);
            }

            if ($this->withCategories) {
                factory(BlastCategory::class)->create([
                    'text_blast_id' => $blast->id,
                    'category' => $seed['category'],
                ]);
            }

            if ($seed['is_delivered']) {
                $this->deliveredBlasts[] = $blast;
            } elseif ($seed['action'] === 'purchased') {
                $this->purchasedBlasts[] = $blast;
            } else {
                $this->inquiredBlasts[] = $blast;
            }
        }

        if ($this->blastAction === 'purchased') {
            $sentSeeds = [
                ['status' => Lead::STATUS_WON, 'is_archived' => false, 'inventory_id' => $this->inventories[0]->inventory_id],
                ['status' => Lead::STATUS_WON, 'is_archived' => true, 'inventory_id' => $this->inventories[1]->inventory_id],
                ['status' => Lead::STATUS_WON_CLOSED, 'is_archived' => false, 'inventory_id' => $this->inventories[2]->inventory_id],
            ];
        } else {
            $sentSeeds = [
                ['status' => Lead::STATUS_HOT, 'is_archived' => false, 'inventory_id' => $this->inventories[0]->inventory_id],
                ['status' => Lead::STATUS_MEDIUM, 'is_archived' => true, 'inventory_id' => $this->inventories[1]->inventory_id],
                ['status' => Lead::STATUS_COLD, 'is_archived' => false, 'inventory_id' => $this->inventories[2]->inventory_id]
            ];
        }

        collect($sentSeeds)->each(function (array $seed): void {
            $lead = factory(Lead::class)->create([
                'dealer_id' => $this->dealer->getKey(),
                'website_id' => $this->website->getKey(),
                'dealer_location_id' => $this->location->getKey(),
                'is_archived' => $seed['is_archived'],
                'date_submitted' => $this->faker->dateTimeThisMonth->format('Y-m-d H:i:s'),
                'inventory_id' => $seed['inventory_id'],
            ]);

            factory(LeadStatus::class)->create([
                'tc_lead_identifier' => $lead->getKey(),
                'status' => $seed['status'],
            ]);

            if ($seed['status'] === Lead::STATUS_WON || $seed['status'] === Lead::STATUS_WON_CLOSED) {
                $this->wonLeads[] = $lead;
            } else {
                $this->otherLeads[] = $lead;
            }
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $userId = $this->user->getKey();

        foreach ($this->wonLeads as $lead) {
            LeadStatus::where('tc_lead_identifier', $lead->getKey())->delete();
            TextLog::where('lead_id', $lead->getKey())->delete();
        }

        foreach ($this->otherLeads as $lead) {
            LeadStatus::where('tc_lead_identifier', $lead->getKey())->delete();
            TextLog::where('lead_id', $lead->getKey())->delete();
        }

        foreach (array_merge($this->inquiredBlasts, $this->purchasedBlasts) as $blast) {
            BlastSent::where('text_blast_id', $blast->getKey())->delete();
            BlastCategory::where('text_blast_id', $blast->getKey())->delete();
            BlastBrand::where('text_blast_id', $blast->getKey())->delete();
        }

        foreach ($this->inventories as $inventory) {
            Inventory::destroy($inventory->inventory_id);
        }

        Blast::where('user_id', $userId)->delete();
        Template::where('user_id', $userId)->delete();
        Lead::where('dealer_id', $dealerId)->delete();
        DealerLocation::where('dealer_id', $dealerId)->delete();
        NewDealerUser::where(['user_id' => $userId])->delete();
        NewUser::destroy($userId);
        Website::where('dealer_id', $dealerId)->delete();
        User::destroy($dealerId);
    }
}
