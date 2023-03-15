<?php

namespace Tests\Unit\App\Console\Commands\UserTracking;

use App\Console\Commands\UserTracking\PopulateMissingWebsiteUserIdCommand;
use App\Domains\UserTracking\Actions\PopulateMissingWebsiteUserIdAction;
use Exception;
use Mockery;
use Tests\Common\TestCase;

class PopulateMissingWebsiteUserIdCommandTest extends TestCase
{
    public function testItCanValidateTheInvalidDateFormat()
    {
        $this
            ->artisan(PopulateMissingWebsiteUserIdCommand::class, [
                'date' => 'something',
            ])
            ->assertExitCode(1);
    }

    public function testItCanTrapExceptionFromTheAction()
    {
        $this->instance(
            PopulateMissingWebsiteUserIdAction::class,
            Mockery::mock(PopulateMissingWebsiteUserIdAction::class, function (Mockery\MockInterface $mock) {
                $mock->shouldReceive('setFrom')->once()->withAnyArgs()->andReturnSelf();
                $mock->shouldReceive('setTo')->once()->withAnyArgs()->andReturnSelf();
                $mock->shouldReceive('execute')->once()->withAnyArgs()->andThrows(Exception::class, 'dummy');
            }),
        );

        $date = now()->format(PopulateMissingWebsiteUserIdCommand::DATE_FORMAT);

        $this
            ->artisan(PopulateMissingWebsiteUserIdCommand::class, [
                'date' => $date,
            ])
            ->expectsOutput('dummy')
            ->assertExitCode(2);
    }

    public function testItCallsTheActionCorrectly()
    {
        $this->instance(
            PopulateMissingWebsiteUserIdAction::class,
            Mockery::mock(PopulateMissingWebsiteUserIdAction::class, function (Mockery\MockInterface $mock) {
                $mock->shouldReceive('setFrom')->once()->withAnyArgs()->andReturnSelf();
                $mock->shouldReceive('setTo')->once()->withAnyArgs()->andReturnSelf();
                $mock->shouldReceive('execute')->once()->withAnyArgs()->andReturnSelf();
            }),
        );

        $date = now()->format(PopulateMissingWebsiteUserIdCommand::DATE_FORMAT);

        $this
            ->artisan(PopulateMissingWebsiteUserIdCommand::class, [
                'date' => $date,
            ])
            ->assertExitCode(0);
    }
}
