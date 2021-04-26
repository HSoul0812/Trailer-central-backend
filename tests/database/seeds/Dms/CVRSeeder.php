<?php

declare(strict_types=1);

namespace Tests\database\seeds\Dms;

use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Common\MonitoredJobSeeder;
use App\Models\CRM\Dms\UnitSale;

/**
 * @property-read array<User> $dealers
 * @property-read array<string, Collection<MonitoredJob>> $jobs
 */
class CVRSeeder extends MonitoredJobSeeder
{
    use WithGetter;
    
    private $unitSale;

    public function seed(): void
    {
        parent::seed();
        $dealer = current($this->dealers);
        
        $this->unitSale = factory(UnitSale::class)->create(['dealer_id' => $dealer->dealer_id]);
    }

    public function seedDealers(): void
    {
        $this->dealers[] = factory(User::class)->create();
        $this->dealers[] = factory(User::class)->create();
    }

    public function cleanUp(): void
    {
        $this->unitSale->delete();
        parent::cleanUp();        
    }
}
