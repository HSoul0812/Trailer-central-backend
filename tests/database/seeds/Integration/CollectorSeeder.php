<?php

namespace Tests\database\seeds\Integration;

use App\Models\Integration\Collector\Collector;
use App\Models\Integration\Collector\CollectorSpecification;
use App\Models\Integration\Collector\CollectorSpecificationAction;
use App\Models\Integration\Collector\CollectorSpecificationRule;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * Class CollectorSeeder
 * @package Tests\database\seeds\Integration
 *
 * @property-read User $dealer
 * @property-read DealerLocation $dealerLocation
 * @property-read Collector $collector
 * @property-read CollectorSpecification $collectorSpecification
 * @property-read CollectorSpecificationAction $collectorSpecificationAction
 * @property-read CollectorSpecificationRule $collectorSpecificationRule
 */
class CollectorSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var DealerLocation
     */
    private $dealerLocation;

    public function seed(): void
    {
        $this->dealer = factory(User::class)->create();

        $this->dealerLocation = factory(DealerLocation::class)->create([
            'dealer_id' => $this->dealer->dealer_id
        ]);
    }

    public function cleanUp(): void
    {
        Collector::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        DealerLocation::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        User::destroy($this->dealer->dealer_id);
    }
}
