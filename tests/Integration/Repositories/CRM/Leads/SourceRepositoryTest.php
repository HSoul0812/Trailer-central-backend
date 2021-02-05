<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\CRM\Leads;

use App\Models\CRM\Leads\LeadSource;
use App\Repositories\CRM\Leads\SourceRepository;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Tests\database\seeds\CRM\Leads\SourceSeeder;
use Tests\TestCase;
use Tests\Unit\WithMySqlConstraintViolationsParser;

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
     * @typeOfTest IntegrationTestCase
     * @dataProvider validQueryParametersProviderl
     *
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     * @covers SourceRepository::getAll
     */
    public function testGetAllWithBasicOperations(array $params, int $expectedTotal): void
    {
        // Given I have a collection of inventories
        $this->seeder->seed();

        // When I call getAll
        // Then I got a list of source types
        /** @var Collection $sources */
        $sources = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params));

        // And That list should be Collection instance
        self::assertInstanceOf(Collection::class, $sources);

        // And the total of records should be the expected
        self::assertSame($expectedTotal, $sources->count());
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @typeOfTest IntegrationTestCase
     * @dataProvider validQueryParametersProviderl
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

        // When I call find
        // Then I got a single lead source
        /** @var LeadSource $source */
        $source = $this->getConcreteRepository()->find($this->seeder->extractValues($params));

        // Find must be LeadSource
        self::assertInstanceOf(LeadSource::class, $source);

        // Source user id matches param user id
        self::assertSame($source->user_id, $params['user_id']);

        // Source name matches param source name
        self::assertSame($source->source_name, $params['source_name']);
    }


    /**
     * Test that SUT is inserting correctly
     *
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
     * Test that SUT is inserting correctly
     *
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
        self::assertSame(1, LeadSource::where(['tc_lead_identifier' => $source->tc_lead_identifier])->count());
    }

    /**
     * Test that SUT is inserting correctly
     *
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