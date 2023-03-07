<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\CRM\Email;

use App\Models\CRM\Email\Campaign;
use App\Models\CRM\Email\CampaignSent;
use App\Repositories\CRM\Email\CampaignRepository;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;
use PDOException;
use Tests\database\seeds\CRM\Email\CampaignSeeder;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class CampaignRepositoryTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    /**
     * @var CampaignSeeder
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

        self::assertInstanceOf(CampaignRepository::class, $concreteRepository);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers CampaignRepository::get
     */
    public function testGet(): void
    {
        // Given I have a collection of leads
        $this->seeder->seed();

        // Given I have a collection of campaign entries
        $campaigns = $this->seeder->createdCampaigns;

        // Get Campaign
        $campaign = reset($campaigns);

        // When I call get
        // Then I got a single campaign
        /** @var Campaign $emailCampaign */
        $emailCampaign = $this->getConcreteRepository()->get(['id' => $campaign->drip_campaigns_id]);

        // Get must be Campaign
        self::assertInstanceOf(Campaign::class, $campaign);

        // Campaign id matches param id
        self::assertSame($emailCampaign->drip_campaigns_id, $campaign->drip_campaigns_id);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers CampaignRepository::get
     */
    public function testGetWithException(): void {
        // When I call create with invalid parameters
        // Then I expect see that one exception have been thrown with a specific message
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [App\Models\CRM\Email\Campaign] 0');

        // When I call get
        // Then I got a single campaign
        /** @var Campaign $emailCampaign */
        $emailCampaign = $this->getConcreteRepository()->get(['id' => 0]);

        // Campaign id matches param id
        self::assertNull($emailCampaign);
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @covers CampaignRepository::sent
     */
    public function testSent(): void {
        $this->seeder->seed();

        // Given I have a collection of campaign sent entries
        $sents = $this->seeder->campaignsUnsent;

        // Get Campaign Sent
        $sent = end($sents);

        // Campaign does not exist yet
        self::assertSame(0, CampaignSent::where([
            'drip_campaigns_id' => $sent->drip_campaigns_id,
            'lead_id' => $sent->lead_id
        ])->count());

        // When I call create with valid parameters
        /** @var CampaignSent $leadCampaignToCustomer */
        $campaignSent = $this->getConcreteRepository()->sent($sent->drip_campaigns_id, $sent->lead_id, $sent->message_id);

        // Then I should get a class which is an instance of LeadCampaign
        self::assertInstanceOf(CampaignSent::class, $campaignSent);

        // Campaign sent did not exist before but does now after sent
        self::assertSame(1, CampaignSent::where([
            'drip_campaigns_id' => $campaignSent->drip_campaigns_id,
            'lead_id' => $campaignSent->lead_id
        ])->count());
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     * @dataProvider duplicatePropertiesProvider
     *
     * @param  array  $properties
     *
     * @covers CampaignRepository::sent
     */
    public function testSentDuplicate(
        array $properties
    ): void {
        $this->seeder->seed();

        $properties = $this->seeder->extractValues($properties);

        // Campaign sent did not exist before but does now after sent
        self::assertSame(1, CampaignSent::where([
            'drip_campaigns_id' => $properties['drip_campaigns_id'],
            'lead_id' => $properties['lead_id']
        ])->count());

        // When I call create with valid parameters
        /** @var CampaignSent $leadCampaignToCustomer */
        $campaignSent = $this->getConcreteRepository()->sent($properties['drip_campaigns_id'], $properties['lead_id']);

        // Campaign sent did exist before and still only one exists now
        self::assertSame(1, CampaignSent::where([
            'drip_campaigns_id' => $campaignSent->drip_campaigns_id,
            'lead_id' => $campaignSent->lead_id
        ])->count());
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers CampaignRepository::wasSent
     */
    public function testWasSent(): void {
        $this->seeder->seed();

        // Given I have a collection of campaign sent entries
        $sents = $this->seeder->campaignsSent;

        // Get Campaign Sent
        $sent = end($sents);

        // When I call wasSent with valid parameters
        /** @var bool $wasSent */
        $wasSent = $this->getConcreteRepository()->wasSent($sent->drip_campaigns_id, $sent->lead->email_address);

        // Then I should return true
        self::assertTrue($wasSent);
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers CampaignRepository::wasSent
     */
    public function testWasSentFalse(): void {
        $this->seeder->seed();

        // Given I have a collection of campaign sent entries
        $sents = $this->seeder->campaignsUnsent;

        // Get Campaign Sent
        $sent = end($sents);

        // When I call wasSent with valid parameters
        /** @var bool $wasSent */
        $wasSent = $this->getConcreteRepository()->wasSent($sent->drip_campaigns_id, $sent->lead->email_address);

        // Then I should return true
        self::assertFalse($wasSent);
    }


    /**
     * Examples of parameters with expected total.
     *
     * @return array[]
     */
    public function validGetParametersProvider(): array
    {
        $campaignIdLambda = static function (CampaignSeeder $seeder) {
            return $seeder->createdCampaigns[0]->getKey();
        };

        return [                 // array $parameters
            'By dummy campaign' => [['id' => $campaignIdLambda]],
        ];
    }

    /**
     * Examples of duplicate customer-inventory id properties.
     *
     * @return array[]
     */
    public function duplicatePropertiesProvider(): array
    {
        $campaignIdLambda = static function (CampaignSeeder $seeder) {
            return $seeder->campaignsSent[0]->drip_campaigns_id;
        };

        $leadIdLambda = static function (CampaignSeeder $seeder) {
            return $seeder->campaignsSent[0]->lead_id;
        };

        return [                      // array $properties
            'With duplicate entry' => [['drip_campaigns_id' => $campaignIdLambda, 'lead_id' => $leadIdLambda]],
        ];
    }


    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new CampaignSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return CampaignRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): CampaignRepositoryInterface
    {
        return $this->app->make(CampaignRepositoryInterface::class);
    }
}