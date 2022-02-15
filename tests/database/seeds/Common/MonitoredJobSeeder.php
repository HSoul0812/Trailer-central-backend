<?php

declare(strict_types=1);

namespace Tests\database\seeds\Common;

use App\Models\Common\MonitoredJob;
use App\Models\User\User;
use App\Traits\WithGetter;
use Illuminate\Support\Collection;
use Tests\database\seeds\Seeder;

/**
 * @property-read array<User> $dealers
 * @property-read array<string, Collection<MonitoredJob>> $jobs
 */
class MonitoredJobSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var array<User>
     */
    protected $dealers = [];

    /**
     * @var array<string, Collection<MonitoredJob>>
     */
    protected $jobs = [];

    public function seed(): void
    {
        $this->seedDealers();

        $dealer1Id = $this->dealers[0]->getKey();
        $dealer2Id = $this->dealers[1]->getKey();

        $this->jobs[$dealer1Id] = factory(MonitoredJob::class, 8)->create(['dealer_id' => $dealer1Id]); // 8 new monitored jobs
        $this->jobs[$dealer2Id] = factory(MonitoredJob::class, 4)->create(['dealer_id' => $dealer2Id]); // 4 new monitored jobs
    }

    public function seedDealers(): void
    {
        $this->dealers[] = factory(User::class)->create();
        $this->dealers[] = factory(User::class)->create();
    }

    public function cleanUp(): void
    {
        $dealersId = collect($this->dealers)->map(static function (User $dealer): int {
            return $dealer->getKey();
        });

        // Database clean up
        MonitoredJob::truncate();
        User::whereIn('dealer_id', $dealersId)->delete();
    }
}
