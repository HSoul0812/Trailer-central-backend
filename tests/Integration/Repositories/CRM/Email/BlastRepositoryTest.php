<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\CRM\Email;

use App\Models\CRM\Email\Blast;
use App\Models\CRM\Email\BlastSent;
use App\Repositories\CRM\Email\BlastRepository;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;
use PDOException;
use Tests\database\seeds\CRM\Email\BlastSeeder;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class BlastRepositoryTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    /**
     * @var BlastSeeder
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

        self::assertInstanceOf(BlastRepository::class, $concreteRepository);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers BlastRepository::get
     */
    public function testGet(): void
    {
        // Given I have a collection of leads
        $this->seeder->seed();

        // Given I have a collection of blast entries
        $blasts = $this->seeder->createdBlasts;

        // Get Blast
        $blast = reset($blasts);

        // When I call get
        // Then I got a single blast
        /** @var Blast $emailBlast */
        $emailBlast = $this->getConcreteRepository()->get(['id' => $blast->email_blasts_id]);

        // Get must be Blast
        self::assertInstanceOf(Blast::class, $blast);

        // Blast id matches param id
        self::assertSame($emailBlast->email_blasts_id, $blast->email_blasts_id);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers BlastRepository::get
     */
    public function testGetWithException(): void {
        // When I call create with invalid parameters
        // Then I expect see that one exception have been thrown with a specific message
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [App\Models\CRM\Email\Blast] 0');

        // When I call get
        // Then I got a single blast
        /** @var Blast $emailBlast */
        $emailBlast = $this->getConcreteRepository()->get(['id' => 0]);

        // Blast id matches param id
        self::assertNull($emailBlast);
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @covers BlastRepository::sent
     */
    public function testSent(): void {
        $this->seeder->seed();

        // Given I have a collection of blast sent entries
        $sents = $this->seeder->blastsUnsent;

        // Get Blast Sent
        $sent = end($sents);

        // Blast does not exist yet
        self::assertSame(0, BlastSent::where([
            'email_blasts_id' => $sent->email_blasts_id,
            'lead_id' => $sent->lead_id
        ])->count());

        // When I call create with valid parameters
        /** @var BlastSent $leadBlastToCustomer */
        $blastSent = $this->getConcreteRepository()->sent($sent->email_blasts_id, $sent->lead_id, $sent->message_id);

        // Then I should get a class which is an instance of LeadBlast
        self::assertInstanceOf(BlastSent::class, $blastSent);

        // Blast sent did not exist before but does now after sent
        self::assertSame(1, BlastSent::where([
            'email_blasts_id' => $blastSent->email_blasts_id,
            'lead_id' => $blastSent->lead_id
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
     * @covers BlastRepository::sent
     */
    public function testSentWithException(
        array $properties
    ): void {
        $this->seeder->seed();

        $properties = $this->seeder->extractValues($properties);

        // Campaign sent did not exist before but does now after sent
        self::assertSame(1, BlastSent::where([
            'email_blasts_id' => $properties['email_blasts_id'],
            'lead_id' => $properties['lead_id']
        ])->count());

        // When I call create with valid parameters
        /** @var BlastSent $leadCampaignToCustomer */
        $blastSent = $this->getConcreteRepository()->sent($properties['email_blasts_id'], $properties['lead_id']);

        // Blast sent did exist before and still only one exists now
        self::assertSame(1, BlastSent::where([
            'email_blasts_id' => $blastSent->email_blasts_id,
            'lead_id' => $blastSent->lead_id
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
     * @covers BlastRepository::wasSent
     */
    public function testWasSent(): void {
        $this->seeder->seed();

        // Given I have a collection of blast sent entries
        $sents = $this->seeder->blastsSent;

        // Get Blast Sent
        $sent = end($sents);

        // When I call wasSent with valid parameters
        /** @var bool $wasSent */
        $wasSent = $this->getConcreteRepository()->wasSent($sent->email_blasts_id, $sent->lead->email_address);

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
     * @covers BlastRepository::wasSent
     */
    public function testWasSentFalse(): void {
        $this->seeder->seed();

        // Given I have a collection of blast sent entries
        $sents = $this->seeder->blastsUnsent;

        // Get Blast Sent
        $sent = end($sents);

        // When I call wasSent with valid parameters
        /** @var bool $wasSent */
        $wasSent = $this->getConcreteRepository()->wasSent($sent->email_blasts_id, $sent->lead->email_address);

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
        $blastIdLambda = static function (BlastSeeder $seeder) {
            return $seeder->createdBlasts[0]->getKey();
        };

        return [                 // array $parameters
            'By dummy blast' => [['id' => $blastIdLambda]],
        ];
    }

    /**
     * Examples of duplicate customer-inventory id properties.
     *
     * @return array[]
     */
    public function duplicatePropertiesProvider(): array
    {
        $blastIdLambda = static function (BlastSeeder $seeder) {
            return $seeder->blastsSent[0]->email_blasts_id;
        };

        $leadIdLambda = static function (BlastSeeder $seeder) {
            return $seeder->blastsSent[0]->lead_id;
        };

        return [                      // array $properties
            'With duplicate entry' => [['email_blasts_id' => $blastIdLambda, 'lead_id' => $leadIdLambda]],
        ];
    }


    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new BlastSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return BlastRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): BlastRepositoryInterface
    {
        return $this->app->make(BlastRepositoryInterface::class);
    }
}