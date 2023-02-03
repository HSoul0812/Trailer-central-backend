<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\CRM\Leads;

use App\Models\CRM\Leads\LeadSource;
use App\Repositories\CRM\Leads\SourceRepository;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use PDOException;
use Tests\database\seeds\CRM\Leads\SourceSeeder;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class SourceRepositoryTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    /**
     * @var SourceSeeder
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

        self::assertInstanceOf(SourceRepository::class, $concreteRepository);
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
     * @covers SourceRepository::getAll
     */
    public function testGetAll(array $params, array $expectedTotals): void
    {
        // Given I have a collection of inventories
        $this->seeder->seed();

        // Get Expected Total
        $extractedTotal = $this->seeder->extractValues($expectedTotals);

        // When I call getAll
        // Then I got a list of source types
        /** @var Collection $sources */
        $sources = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params));

        // And That list should be Collection instance
        self::assertInstanceOf(Collection::class, $sources);

        // And the total of records should be the expected
        self::assertSame($extractedTotal['expected_total'], $sources->count());
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     * @dataProvider validFindParametersProvider
     *
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     * @covers SourceRepository::getAll
     */
    public function testFind(array $params): void
    {
        // Given I have a collection of leads
        $this->seeder->seed();

        // Parse Values
        $values = $this->seeder->extractValues($params);

        // When I call find
        // Then I got a single lead source
        /** @var LeadSource $source */
        $source = $this->getConcreteRepository()->find($values);

        // Find must be LeadSource
        self::assertInstanceOf(LeadSource::class, $source);

        // Source user id matches param user id
        self::assertSame($source->user_id, $values['user_id']);

        // Source name matches param source name
        self::assertSame($source->source_name, $values['source_name']);
    }


    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers SourceRepository::create
     */
    public function testCreate(): void {
        $this->seeder->seed();

        // Given I have a collection of sources
        $sources = $this->seeder->missingSources;

        // Get Source
        $source = end($sources);

        // Lead source does not exist yet
        self::assertSame(0, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());

        // When I call create with valid parameters
        /** @var LeadSource $leadSourceToCustomer */
        $leadSourceForDealer = $this->getConcreteRepository()->create([
            'user_id' => $source->user_id,
            'source_name' => $source->source_name,
            'parent_id' => 0
        ]);

        // Then I should get a class which is an instance of LeadSource
        self::assertInstanceOf(LeadSource::class, $leadSourceForDealer);

        // Lead source did not exist before but does now after create
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers SourceRepository::create
     */
    public function testCreateParent(): void {
        $this->seeder->seed();

        // Given I have a collection of missing sources
        $sources = $this->seeder->missingSources;

        // Get Source w/Parent
        $source = reset($sources);

        // Lead source parent exists already
        self::assertSame(1, LeadSource::where(['lead_source_id' => $source->parent_id])->count());

        // Lead source does not exist yet
        self::assertSame(0, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());

        // When I call create with valid parameters
        /** @var LeadSource $leadSourceToCustomer */
        $leadSourceForDealer = $this->getConcreteRepository()->create([
            'user_id' => $source->user_id,
            'source_name' => $source->source_name,
            'parent_id' => $source->parent_id
        ]);

        // Then I should get a class which is an instance of LeadSource
        self::assertInstanceOf(LeadSource::class, $leadSourceForDealer);

        // Lead source did not exist before but does now after create
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());
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
            $expectedPDOExceptionMessage($properties['user_id'], $properties['source_name']) :
            $expectedPDOExceptionMessage;

        // When I call create with invalid parameters
        // Then I expect see that one exception have been thrown with a specific message
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage($expectedPDOExceptionMessage);


        /** @var null $leadSourceForDealer */
        $leadSourceForDealer = $this->getConcreteRepository()->create([
            'user_id' => $properties['user_id'],
            'source_name' => $properties['source_name'],
        ]);

        // And I should get a null value
        self::assertNull($leadSourceForDealer);
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers SourceRepository::create
     */
    public function testUpdate(): void {
        $this->seeder->seed();

        // Given I have a collection of created sources
        $sources = $this->seeder->createdSources;

        // Get Source
        $source = end($sources);

        // Lead source already exists
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());

        // When I call update with valid parameters
        /** @var LeadSource $leadSource */
        $leadSource = $this->getConcreteRepository()->update([
            'user_id' => $source->user_id,
            'source_name' => $source->source_name,
            'parent_id' => 0
        ]);

        // Then I should get a class which is an instance of LeadSource
        self::assertInstanceOf(LeadSource::class, $leadSource);

        // Lead source should still exist after update
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers SourceRepository::create
     */
    public function testUpdateParent(): void {
        $this->seeder->seed();

        // Given I have a collection of sources
        $sources = $this->seeder->createdSources;

        // Get Source w/Parent
        $source = reset($sources);

        // Lead source parent exists already
        self::assertSame(1, LeadSource::where(['lead_source_id' => $source->parent_id])->count());

        // Lead source already exists
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());

        // When I call update with valid parameters
        /** @var LeadSource $leadSource */
        $leadSource = $this->getConcreteRepository()->update([
            'user_id' => $source->user_id,
            'source_name' => $source->source_name,
            'parent_id' => $source->parent_id
        ]);

        // Then I should get a class which is an instance of LeadSource
        self::assertInstanceOf(LeadSource::class, $leadSource);

        // Lead source should still exist after update
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());
    }

    
    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers SourceRepository::create
     */
    public function testCreateOrUpdateCreate(): void {
        $this->seeder->seed();

        // Given I have a collection of missing sources
        $sources = $this->seeder->missingSources;

        // Get Source
        $source = end($sources);

        // Lead source is missing
        self::assertSame(0, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());

        // When I call create with valid parameters
        /** @var LeadSource $leadSourceToCustomer */
        $leadSourceForDealer = $this->getConcreteRepository()->createOrUpdate([
            'user_id' => $source->user_id,
            'source_name' => $source->source_name,
            'parent_id' => 0
        ]);

        // Then I should get a class which is an instance of LeadSource
        self::assertInstanceOf(LeadSource::class, $leadSourceForDealer);

        // Lead source did not exist before but does now after create
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers SourceRepository::create
     */
    public function testCreateOrUpdateCreateParent(): void {
        $this->seeder->seed();

        // Given I have a collection of missing sources
        $sources = $this->seeder->missingSources;

        // Get Source w/Parent
        $source = reset($sources);

        // Lead source parent exists already
        self::assertSame(1, LeadSource::where(['lead_source_id' => $source->parent_id])->count());

        // Lead source does not exist yet
        self::assertSame(0, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());

        // When I call create with valid parameters
        /** @var LeadSource $leadSourceToCustomer */
        $leadSourceForDealer = $this->getConcreteRepository()->createOrUpdate([
            'user_id' => $source->user_id,
            'source_name' => $source->source_name,
            'parent_id' => $source->parent_id
        ]);

        // Then I should get a class which is an instance of LeadSource
        self::assertInstanceOf(LeadSource::class, $leadSourceForDealer);

        // Lead source did not exist before but does now after create
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers SourceRepository::create
     */
    public function testCreateOrUpdateUpdate(): void {
        $this->seeder->seed();

        // Given I have a collection of sources
        $sources = $this->seeder->createdSources;

        // Get Source
        $source = end($sources);

        // Lead source already exists
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());

        // When I call update with valid parameters
        /** @var LeadSource $leadSource */
        $leadSource = $this->getConcreteRepository()->createOrUpdate([
            'user_id' => $source->user_id,
            'source_name' => $source->source_name,
            'parent_id' => 0
        ]);

        // Then I should get a class which is an instance of LeadSource
        self::assertInstanceOf(LeadSource::class, $leadSource);

        // Lead source should still exist after update
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers SourceRepository::create
     */
    public function testCreateOrUpdateUpdateParent(): void {
        $this->seeder->seed();

        // Given I have a collection of sources
        $sources = $this->seeder->createdSources;

        // Get Source w/Parent
        $source = reset($sources);

        // Lead source parent exists already
        self::assertSame(1, LeadSource::where(['lead_source_id' => $source->parent_id])->count());

        // Lead source already exists
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());

        // When I call update with valid parameters
        /** @var LeadSource $leadSource */
        $leadSource = $this->getConcreteRepository()->createOrUpdate([
            'user_id' => $source->user_id,
            'source_name' => $source->source_name,
            'parent_id' => $source->parent_id
        ]);

        // Then I should get a class which is an instance of LeadSource
        self::assertInstanceOf(LeadSource::class, $leadSource);

        // Lead source should still exist after update
        self::assertSame(1, LeadSource::where(['user_id' => $source->user_id, 'source_name' => $source->source_name])->count());
    }

    /**
     * Examples of parameters with expected total.
     *
     * @return array[]
     */
    public function validQueryParametersProvider(): array
    {
        $dealerIdLambda = static function (SourceSeeder $seeder) {
            return $seeder->dealer->getKey();
        };

        $defaultSourcesLambda = static function (SourceSeeder $seeder) {
            return $seeder->defaultSources->count() + 2;
        };

        return [                 // array $parameters, int $expectedTotal
            'By dummy dealer' => [['user_id' => $dealerIdLambda], ['expected_total' => $defaultSourcesLambda]],
        ];
    }

    /**
     * Examples of parameters with expected total.
     *
     * @return array[]
     */
    public function validFindParametersProvider(): array
    {
        $dealerIdLambda = static function (SourceSeeder $seeder) {
            return $seeder->dealer->getKey();
        };

        $sourceNameLambda = static function (SourceSeeder $seeder): string {
            $sources = $seeder->createdSources;
            return $sources[array_rand($sources, 1)]->source_name;
        };

        return [                                // array $parameters, int $expectedTotal
            'By dummy dealer\'s source name' => [['user_id' => $dealerIdLambda, 'source_name' => $sourceNameLambda], 1],
        ];
    }

    /**
     * Examples of invalid customer-inventory id properties with theirs expected exception messages.
     *
     * @return array[]
     */
    public function invalidPropertiesProvider(): array
    {
        $userIdLambda = static function (SourceSeeder $seeder) {
            return $seeder->dealer->getKey();
        };

        $sourceNameLambda = static function (SourceSeeder $seeder): string {
            $sources = $seeder->createdSources;
            return $sources[array_rand($sources, 1)]->source_name;
        };

        $duplicateEntryLambda = function (int $userId, string $sourceName) {
            return $this->getDuplicateEntryMessage(
                "$userId-$sourceName",
                'crm_lead_sources.user_source'
            );
        };

        return [                      // array $properties, string $expectedPDOExceptionMessage
            'With duplicate entry' => [['user_id' => $userIdLambda, 'source_name' => $sourceNameLambda], $duplicateEntryLambda],
        ];
    }


    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new SourceSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return SourceRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): SourceRepositoryInterface
    {
        return $this->app->make(SourceRepositoryInterface::class);
    }
}