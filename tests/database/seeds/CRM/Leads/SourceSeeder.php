<?php

declare(strict_types=1);

namespace Tests\database\seeds\CRM\Leads;

use App\Models\CRM\Leads\LeadSource;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read array<LeadSource> $missingSources
 * @property-read array<LeadSource> $createdSources
 */
class SourceSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var LeadSource[]
     */
    private $missingSources = [];

    /**
     * @var LeadSource[]
     */
    private $createdSources = [];

    /**
     * @var LeadSource[]
     */
    private $defaultSources = [];


    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
        $this->defaultSources = LeadSource::where('user_id', 0)->get();
    }

    public function seed(): void
    {
        $userId = $this->dealer->getKey();

        $seeds = [
            ['source' => 'Craigslist', 'parent_id' => 1],
            ['source' => 'Facebook - Podium'],
            ['source' => 'RVTrader.com'],
            ['source' => 'Facebook', 'parent_id' => 2, 'action' => 'create'],
            ['source' => 'TrailerCentral', 'action' => 'create'],
            ['source' => 'HorseTrailerWorld', 'action' => 'create'],
        ];

        collect($seeds)->each(function (array $seed) use($userId): void {
            // Create Source
            if(isset($seed['action']) && $seed['action'] === 'create') {
                // Make Source
                $source = factory(LeadSource::class)->create([
                    'user_id' => $userId,
                    'source_name' => $seed['source'],
                    'parent_id' => $seed['parent_id'] ?? 0
                ]);

                $this->createdSources[] = $source;
                return;
            }

            // Make Source
            $source = factory(LeadSource::class)->make([
                'user_id' => $userId,
                'source_name' => $seed['source'],
                'parent_id' => $seed['parent_id'] ?? 0
            ]);

            $this->missingSources[] = $source;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();

        // Database clean up
        LeadSource::where('user_id', $dealerId)->delete();
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::where('dealer_id', $dealerId)->delete();
        User::destroy($dealerId);
    }
}
