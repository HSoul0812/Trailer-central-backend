<?php
namespace Tests\Integration\Services\Parts;

use App\Models\Parts\Part;
use App\Models\Parts\CostHistory;
use App\Services\Parts\PartServiceInterface;
use Tests\database\seeds\Part\PartSeeder;
use Tests\TestCase;

class PartServiceTest extends TestCase
{
    /** @var PartSeeder  */
    private $seeder;

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new PartSeeder(['with' => ['bins']]);
        $this->seeder->seed();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @covers ::moveStatus
     * @group quickbook
     */
    public function testUpdateAddNewCostHistory()
    {
        $dealerId = $this->seeder->getDealerId();
        $part = Part::where(['dealer_id' => $dealerId])->whereHas('bins')->firstOrFail();
        $oldCost = $part->dealer_cost;
        $partData = ['id' => $part->id, 'dealer_cost' => $oldCost + 1];

        // Cost History does not exist yet
        self::assertSame(0, CostHistory::where(['part_id' => $partData['id']])->count());
        $this->getConcreteService()->update($partData, []);

        self::assertSame(1, CostHistory::where(['part_id' => $partData['id']])->count());
        $costHistory = CostHistory::where(['part_id' => $partData['id']])->firstOrFail();

        self::assertSame($costHistory->old_cost, $oldCost);
        self::assertSame($costHistory->new_cost, $partData['dealer_cost']);
    }

    /**
     * @return PartServiceInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteService(): PartServiceInterface
    {
        return $this->app->make(PartServiceInterface::class);
    }
}
