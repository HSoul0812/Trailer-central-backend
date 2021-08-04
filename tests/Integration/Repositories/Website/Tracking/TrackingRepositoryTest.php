<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Website\Tracking;

use App\Models\Website\Tracking\Tracking;
use App\Repositories\Website\Tracking\TrackingRepository;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\database\seeds\Website\Tracking\TrackingSeeder;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class TrackingRepositoryTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    /**
     * @var TrackingSeeder
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

        self::assertInstanceOf(TrackingRepository::class, $concreteRepository);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @typeOfTest IntegrationTestCase
     * @dataProvider validFindParametersProvider
     *
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     * @covers TrackingRepository::getAll
     */
    public function testFind(array $params): void
    {
        // Given I have a collection of tracking data
        $this->seeder->seed();

        // Parse Values
        $values = $this->seeder->extractValues($params);

        // When I call find
        // Then I got a single tracking data
        /** @var Tracking $tracking */
        $tracking = $this->getConcreteRepository()->find($values);

        // Find must be Tracking
        self::assertInstanceOf(Tracking::class, $tracking);

        // Tracking session id matches param session id
        if(isset($values['session_id'])) {
            self::assertSame($tracking->session_id, $values['session_id']);
        }
        // Tracking id matches param id
        else {
            self::assertSame($tracking->tracking_id, $values['id']);
        }
    }


    /**
     * Test that SUT is updating correctly
     *
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers TrackingRepository::update
     */
    public function testUpdate(): void {
        $this->seeder->seed();

        // Given I have a collection of created tracking
        $trackings = $this->seeder->createdTracking;

        // Get Tracking
        $tracking = $trackings[array_rand($trackings, 1)];

        // Lead tracking already exists
        self::assertSame(1, Tracking::where(['session_id' => $tracking->session_id])->count());

        // When I call update with valid parameters
        /** @var Tracking $tracking */
        $inquiryTracking = $this->getConcreteRepository()->update([
            'id' => $tracking->tracking_id,
            'lead_id' => $tracking->lead_id,
            'referrer' => $tracking->referrer,
            'domain' => $tracking->domain,
            'date_inquired' => NULL
        ]);

        // Then I should get a class which is an instance of Tracking
        self::assertInstanceOf(Tracking::class, $inquiryTracking);

        // Lead tracking should still exist after update
        self::assertSame(1, Tracking::where(['session_id' => $tracking->session_id])->count());
    }

    /**
     * Test that SUT is updating correctly using session ID
     *
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers TrackingRepository::update
     */
    public function testUpdateSessionId(): void {
        $this->seeder->seed();

        // Given I have a collection of created tracking
        $trackings = $this->seeder->createdTracking;

        // Get Tracking
        $tracking = $trackings[array_rand($trackings, 1)];

        // Lead tracking already exists
        self::assertSame(1, Tracking::where(['session_id' => $tracking->session_id])->count());

        // When I call update with valid parameters
        /** @var Tracking $tracking */
        $inquiryTracking = $this->getConcreteRepository()->update([
            'session_id' => $tracking->session_id,
            'lead_id' => $tracking->lead_id,
            'referrer' => $tracking->referrer,
            'domain' => $tracking->domain,
            'date_inquired' => NULL
        ]);

        // Then I should get a class which is an instance of Tracking
        self::assertInstanceOf(Tracking::class, $inquiryTracking);

        // Lead tracking should still exist after update
        self::assertSame(1, Tracking::where(['session_id' => $tracking->session_id])->count());
    }

    /**
     * Test that SUT is updating to track lead correctly
     *
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers TrackingRepository::update
     */
    public function testUpdateTrackLead(): void {
        $this->seeder->seed();

        // Given I have a collection of created tracking MISSING lead
        $trackings = $this->seeder->missingLeadTracking;

        // Get Tracking
        $tracking = $trackings[array_rand($trackings, 1)];

        // Lead tracking doesn't exists
        self::assertSame(0, Tracking::where(['session_id' => $tracking->session_id, 'lead_id' => $tracking->lead_id])->count());

        // When I call update with valid parameters
        /** @var Tracking $tracking */
        $inquiryTracking = $this->getConcreteRepository()->updateTrackLead($tracking->session_id, $tracking->lead_id);

        // Then I should get a class which is an instance of Tracking
        self::assertInstanceOf(Tracking::class, $inquiryTracking);

        // Lead tracking should still exist after update
        self::assertSame(1, Tracking::where(['session_id' => $tracking->session_id, 'lead_id' => $tracking->lead_id])->count());
    }


    /**
     * Examples of parameters with expected total.
     *
     * @return array[]
     */
    public function validFindParametersProvider(): array
    {
        $trackingIdLambda = static function (TrackingSeeder $seeder): int {
            $tracking = $seeder->createdTracking;
            return $tracking[array_rand($tracking, 1)]->getKey();
        };

        $sessionIdLambda = static function (TrackingSeeder $seeder): string {
            $tracking = $seeder->createdTracking;
            return $tracking[array_rand($tracking, 1)]->session_id;
        };

        return [                                // array $parameters, int $expectedTotal
            'By dummy dealer\'s tracking id' => [['id' => $trackingIdLambda], 1],
            'By dummy dealer\'s session id' => [['session_id' => $sessionIdLambda], 1],
        ];
    }


    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new TrackingSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return TrackingRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): TrackingRepositoryInterface
    {
        return $this->app->make(TrackingRepositoryInterface::class);
    }
}
