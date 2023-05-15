<?php

namespace Tests\Unit\App\Console\Commands\DataDumper;

use App\Console\Commands\DataDumper\DumpFakeUserTrackingDataCommand;
use App\Models\UserTracking;
use Carbon\Exceptions\InvalidFormatException;
use Tests\Common\TestCase;

class DumpFakeUserTrackingDataCommandTest extends TestCase
{
    public function testItWillNotRunInProductionEnv(): void
    {
        config(['app.env' => 'production']);

        $this
            ->artisan(DumpFakeUserTrackingDataCommand::class, [
                'from' => 'invalid',
                'to' => 'invalid',
                'dataPointPerDay' => 'invalid',
            ])
            ->assertExitCode(1);
    }

    public function testItCanValidateDateArguments(): void
    {
        $this->expectException(InvalidFormatException::class);

        $this->artisan(DumpFakeUserTrackingDataCommand::class, [
            'from' => 'invalid',
            'to' => 'invalid',
            'dataPointPerDay' => 'invalid',
        ]);
    }

    public function testItCanValidateDataPointPerDayArguments(): void
    {
        $this
            ->artisan(DumpFakeUserTrackingDataCommand::class, [
                'from' => now()->format(DumpFakeUserTrackingDataCommand::DATE_FORMAT),
                'to' => now()->format(DumpFakeUserTrackingDataCommand::DATE_FORMAT),
                'dataPointPerDay' => 'invalid',
            ])
            ->assertExitCode(2);
    }

    public function testItCanGenerateFakeUserTrackingDataToDatabase(): void
    {
        $this
            ->artisan(DumpFakeUserTrackingDataCommand::class, [
                'from' => now()->subDays(5)->format(DumpFakeUserTrackingDataCommand::DATE_FORMAT),
                'to' => now()->format(DumpFakeUserTrackingDataCommand::DATE_FORMAT),
                'dataPointPerDay' => 10,
            ])
            ->assertExitCode(0);

        $this->assertDatabaseCount(UserTracking::class, 60);
    }
}
