<?php

namespace Tests\Unit\App\Console\Commands\Images;

use App\Console\Commands\Images\DeleteOldLocalImagesCommand;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageService;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageServiceInterface;
use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;
use Tests\Common\TestCase;

class DeleteOldLocalImagesCommandTest extends TestCase
{
    public function testItCanSendCorrectNumberOfDaysToService()
    {
        $days = Carbon::now()->subMonths(DeleteOldLocalImagesCommand::DELETE_OLDER_THAN_MONTHS)->diffInDays();

        $this->instance(
            abstract: ImageServiceInterface::class,
            instance: Mockery::mock(ImageService::class, function (MockInterface $mock) use ($days) {
                $mock->shouldReceive('deleteOldLocalImages')->with($days)->andReturns();
            }),
        );

        $this
            ->artisan(DeleteOldLocalImagesCommand::class)
            ->expectsOutput("Images that are older than $days days have been deleted!")
            ->assertExitCode(0);
    }
}
