<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\CRM\Leads;

use App\Models\CRM\Leads\LeadType;
use App\Repositories\CRM\Leads\TypeRepository;
use App\Repositories\CRM\Leads\TypeRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use PDOException;
use Tests\database\seeds\CRM\Leads\TypeSeeder;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class TypeRepositoryTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    /**
     * @var TypeSeeder
     */
    private $seeder;

    /**
     * Test that SUT is properly bound by the application
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    public function testRepositoryInterfaceIsBound(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(TypeRepository::class, $concreteRepository);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     * @dataProvider validQueryParametersProvider
     *
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     * @covers TypeRepository::getAll
     */
    public function testGetAll(array $params, int $expectedTotal): void
    {
        // Given I have a collection of inventories
        $this->seeder->seed();

        // When I call getAll
        // Then I got a list of type types
        /** @var Collection $types */
        $types = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params));

        // And That list should be Collection instance
        self::assertInstanceOf(Collection::class, $types);

        // And the total of records should be the expected
        self::assertSame($expectedTotal, $types->count());
    }


    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers TypeRepository::create
     */
    public function testCreate(): void {
        $this->seeder->seed();

        // Given I have a collection of types
        $types = $this->seeder->missingTypes;

        // Get Type
        $type = end($types);

        // Lead type does not exist yet
        self::assertSame(0, LeadType::where(['lead_id' => $type->lead_id, 'lead_type' => $type->lead_type])->count());

        // When I call create with valid parameters
        /** @var LeadType $leadTypeForLead */
        $leadTypeForLead = $this->getConcreteRepository()->create([
            'lead_id' => $type->lead_id,
            'lead_type' => $type->lead_type
        ]);

        // Then I should get a class which is an instance of LeadType
        self::assertInstanceOf(LeadType::class, $leadTypeForLead);

        // Lead type did not exist before but does now after create
        self::assertSame(1, LeadType::where(['lead_id' => $type->lead_id, 'lead_type' => $type->lead_type])->count());
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

        // Given I have a collection of lead types
        $types = $this->seeder->createdTypes;

        // Lead types already exist
        self::assertSame(count($types), LeadType::where(['lead_id' => $leadId])->count());

        // When I call create with valid parameters
        /** @var int $deleted */
        $deleted = $this->getConcreteRepository()->delete([
            'lead_id' => $leadId
        ]);

        // Then I should get true
        self::assertSame(count($types), $deleted);

        // Lead type had entries before and are now all gone
        self::assertSame(0, LeadType::where(['lead_id' => $leadId])->count());
    }


    /**
     * Test that SUT is throwing a PDOException when some constraint is not being satisfied
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     * @dataProvider invalidPropertiesProvider
     *
     * @param  array  $properties
     * @param  string|callable  $expectedPDOExceptionMessage
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers InventoryRepository::create
     */
    public function testCreateWithException(
        array $properties,
        $expectedPDOExceptionMessage
    ): void {
        // Given I have a collection of inventories
        $this->seeder->seed();

        $properties = $this->seeder->extractValues($properties);
        $expectedPDOExceptionMessage = is_callable($expectedPDOExceptionMessage) ?
            $expectedPDOExceptionMessage($properties['lead_id'], $properties['lead_type']) :
            $expectedPDOExceptionMessage;

        // When I call create with invalid parameters
        // Then I expect see that one exception have been thrown with a specific message
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage($expectedPDOExceptionMessage);


        /** @var null $leadTypeForLead */
        $leadTypeForLead = $this->getConcreteRepository()->create([
            'lead_id' => $properties['lead_id'],
            'lead_type' => $properties['lead_type'],
        ]);

        // And I should get a null value
        self::assertNull($leadTypeForLead);
    }


    /**
     * Examples of parameters with expected total.
     *
     * @return array[]
     */
    public function validQueryParametersProvider(): array
    {
        $leadIdLambda = static function (TypeSeeder $seeder) {
            return $seeder->lead->getKey();
        };

        return [                 // array $parameters, int $expectedTotal
            'By dummy lead' => [['lead_id' => $leadIdLambda], 3],
        ];
    }

    /**
     * Examples of invalid customer-inventory id properties with theirs expected exception messages.
     *
     * @return array[]
     */
    public function invalidPropertiesProvider(): array
    {
        $leadIdLambda = static function (TypeSeeder $seeder) {
            return $seeder->lead->getKey();
        };

        $leadTypeLambda = static function (TypeSeeder $seeder): string {
            $types = $seeder->createdTypes;
            return $types[array_rand($types, 1)]->lead_type;
        };

        $duplicateEntryLambda = function (int $leadId, string $leadType) {
            return $this->getDuplicateEntryMessage(
                "$leadId-$leadType",
                'website_lead_types.lead_type'
            );
        };

        return [                      // array $properties, string $expectedPDOExceptionMessage
            'With duplicate entry' => [['lead_id' => $leadIdLambda, 'lead_type' => $leadTypeLambda], $duplicateEntryLambda],
        ];
    }


    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new TypeSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return TypeRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): TypeRepositoryInterface
    {
        return $this->app->make(TypeRepositoryInterface::class);
    }
}