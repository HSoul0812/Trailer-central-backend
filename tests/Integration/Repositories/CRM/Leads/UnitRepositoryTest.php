<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\CRM\Leads;

use App\Models\CRM\Leads\InventoryLead;
use App\Repositories\CRM\Leads\UnitRepository;
use App\Repositories\CRM\Leads\UnitRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Tests\database\seeds\CRM\Leads\UnitSeeder;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class UnitRepositoryTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    /**
     * @var UnitSeeder
     */
    private $seeder;

    /**
     * Test that SUT is properly bound by the application
     *
     * @group CRM
     * @unitOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    public function testRepositoryInterfaceIsBound(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(UnitRepository::class, $concreteRepository);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @group CRM
     * @unitOfTest IntegrationTestCase
     * @dataProvider validQueryParametersProvider
     *
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     * @covers UnitRepository::getAll
     */
    public function testGetAll(array $params, int $expectedTotal): void
    {
        // Given I have a collection of inventories
        $this->seeder->seed();

        // When I call getAll
        // Then I got a list of unit units
        /** @var Collection $units */
        $units = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params));

        // And That list should be Collection instance
        self::assertInstanceOf(Collection::class, $units);

        // And the total of records should be the expected
        self::assertSame($expectedTotal, $units->count());
    }


    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @unitOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers UnitRepository::create
     */
    public function testCreate(): void {
        $this->seeder->seed();

        // Get Lead
        $leadId = $this->seeder->lead->getKey();

        // Given I have a collection of inventories
        $units = $this->seeder->unrelatedInventories;

        // Get Unit
        $inventoryId = $units[array_rand($units, 1)]->getKey();

        // Lead unit does not exist yet
        self::assertSame(0, InventoryLead::where(['website_lead_id' => $leadId, 'inventory_id' => $inventoryId])->count());

        // When I call create with valid parameters
        /** @var InventoryLead $inventoryLead */
        $inventoryLead = $this->getConcreteRepository()->create([
            'website_lead_id' => $leadId,
            'inventory_id' => $inventoryId
        ]);

        // Then I should get a class which is an instance of InventoryLead
        self::assertInstanceOf(InventoryLead::class, $inventoryLead);

        // Lead unit did not exist before but does now after create
        self::assertSame(1, InventoryLead::where(['website_lead_id' => $leadId, 'inventory_id' => $inventoryId])->count());
    }

    /**
     * Test that SUT is deleting correctly
     *
     * @group CRM
     * @unitOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers UnitRepository::delete
     */
    public function testDelete(): void {
        $this->seeder->seed();

        // Get Lead
        $leadId = $this->seeder->lead->getKey();

        // Given I have a collection of inventories
        $units = $this->seeder->leadRelatedInventories;

        // Inventory Leads already exist
        self::assertSame(count($units), InventoryLead::where(['website_lead_id' => $leadId])->count());

        // When I call create with valid parameters
        $deleted = $this->getConcreteRepository()->delete([
            'website_lead_id' => $leadId
        ]);

        // Then I should get true
        self::assertTrue($deleted);

        // Inventory lead had entries before and are now all gone
        self::assertSame(0, InventoryLead::where(['website_lead_id' => $leadId])->count());
    }


    /**
     * Examples of parameters with expected total.
     *
     * @return array[]
     */
    public function validQueryParametersProvider(): array
    {
        $leadIdLambda = static function (UnitSeeder $seeder) {
            return $seeder->lead->getKey();
        };

        return [               // array $parameters, int $expectedTotal
            'By dummy lead' => [['website_lead_id' => $leadIdLambda], 3],
        ];
    }


    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new UnitSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return UnitRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): UnitRepositoryInterface
    {
        return $this->app->make(UnitRepositoryInterface::class);
    }
}
