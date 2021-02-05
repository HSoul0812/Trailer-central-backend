<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\CRM\Leads;

use App\Models\CRM\Leads\LeadStatus;
use App\Repositories\CRM\Leads\StatusRepository;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\database\seeds\CRM\Leads\StatusSeeder;
use Tests\TestCase;
use Tests\Unit\WithMySqlConstraintViolationsParser;

class StatusRepositoryTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    /**
     * @var StatusSeeder
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

        self::assertInstanceOf(StatusRepository::class, $concreteRepository);
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
     * @covers StatusRepository::getAll
     */
    public function testGetAll(): void
    {
        // Given I have a collection of inventories
        $this->seeder->seed();

        // When I call getAll
        // Then I got a list of status types
        /** @var Collection $statuses */
        $statuses = $this->getConcreteRepository()->getAll();

        // And That list should be Collection instance
        self::assertInstanceOf(Collection::class, $statuses);

        // And the total of records should be the expected
        self::assertSame($expectedTotal, $statuses->count());
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers StatusRepository::create
     */
    public function testCreate(): void {
        $this->seeder->seed();

        // Given I have a collection of statuses
        $statuses = $this->seeder->statuses;

        // And I have a lead id
        $leadId = $this->seeder->lead->getKey();

        // Get Status
        $status = $statuses[array_rand($statuses, 1)];

        // When I call create with valid parameters
        /** @var LeadStatus $leadStatusToCustomer */
        $leadStatusToLead = $this->getConcreteRepository()->create([
            'tc_lead_identifier' => $leadId,
            'status' => $status->status,
            'source' => $status->source,
            'next_contact_date' => $status->next_contact_date,
            'sales_person_id' => $status->sales_person_id,
            'contact_type' => $status->contact_type
        ]);

        // Then I should get a class which is an instance of LeadStatus
        self::assertInstanceOf(LeadStatus::class, $leadStatusToLead);

        // And I should see that lead status related to the lead has incremented in one record
        self::assertSame(1, LeadStatus::where(['tc_lead_identifier' => $leadId])->count());
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new StatusSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return StatusRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): VendorRepositoryInterface
    {
        return $this->app->make(StatusRepositoryInterface::class);
    }
}