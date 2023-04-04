<?php

namespace Tests\Unit\App\Console\Commands\UserTracking;

use App\Console\Commands\UserTracking\PopulateUserLocationCommand;
use App\Models\UserTracking;
use Artisan;
use Tests\Common\TestCase;

class PopulateUserLocationCommandTest extends TestCase
{
    public function testItCanPopulateUserLocation()
    {
        /** @var UserTracking $userTracking */
        $userTracking = UserTracking::factory()
            ->noLocationData()
            ->locationUnprocessed()
            ->hasUSIpAddress()
            ->create();

        $locationProcessedIpAddress = '128.80.50.13';

        /** @var UserTracking $locationProcessedUserTracking */
        $locationProcessedUserTracking = UserTracking::factory()
            ->locationProcessed()
            ->noLocationData()
            ->create([
                'ip_address' => $locationProcessedIpAddress,
            ]);

        $this
            ->withoutMockingConsoleOutput()
            ->artisan(PopulateUserLocationCommand::class);

        $output = Artisan::output();

        $this->assertStringContainsString($userTracking->ip_address, $output);
        $this->assertStringNotContainsString($locationProcessedIpAddress, $output);

        $userTracking->refresh();

        $this->assertTrue($userTracking->location_processed);
        $this->assertNotNull($userTracking->city);
        $this->assertNotNull($userTracking->state);
        $this->assertNotNull($userTracking->country);

        $locationProcessedUserTracking->refresh();

        $this->assertTrue($locationProcessedUserTracking->location_processed);
        $this->assertNull($locationProcessedUserTracking->city);
        $this->assertNull($locationProcessedUserTracking->state);
        $this->assertNull($locationProcessedUserTracking->country);
    }
}
