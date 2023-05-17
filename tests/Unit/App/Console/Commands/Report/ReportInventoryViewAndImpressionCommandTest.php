<?php

namespace Tests\Unit\App\Console\Commands\Report;

use App\Console\Commands\Report\ReportInventoryViewAndImpressionCommand;
use App\Domains\Compression\Actions\CompressFileWithGzipAction;
use App\Domains\UserTracking\Exporters\InventoryViewAndImpressionCsvExporter;
use App\Domains\UserTracking\Mail\ReportInventoryViewAndImpressionEmail;
use Mail;
use Mockery;
use Mockery\MockInterface;
use Tests\Common\TestCase;

class ReportInventoryViewAndImpressionCommandTest extends TestCase
{
    public function testItCanSendReportViaEmail()
    {
        Mail::fake();

        $mailTo = [
            'abc@example.com',
            'def@example.com',
        ];

        config([
            'trailertrader.report.inventory-view-and-impression.send_mail' => true,
            'trailertrader.report.inventory-view-and-impression.mail_to' => $mailTo,
        ]);

        $this->instance(
            InventoryViewAndImpressionCsvExporter::class,
            Mockery::mock(InventoryViewAndImpressionCsvExporter::class, function (MockInterface $mock) {
                $mock->shouldReceive('setFrom')->once()->withAnyArgs()->andReturnSelf();
                $mock->shouldReceive('setTo')->once()->withAnyArgs()->andReturnSelf();
                $mock->shouldReceive('export')->once()->withAnyArgs()->andReturns('/tmp/dummy.csv');
            }),
        );

        $this->instance(
            CompressFileWithGzipAction::class,
            Mockery::mock(CompressFileWithGzipAction::class, function (MockInterface $mock) {
                $mock->shouldReceive('execute')->once()->withAnyArgs()->andReturns();
            })
        );

        $this
            ->artisan(ReportInventoryViewAndImpressionCommand::class, [
                'date' => now()->format(ReportInventoryViewAndImpressionCommand::DATE_FORMAT),
            ])
            ->assertExitCode(0);

        Mail::assertSent(function (ReportInventoryViewAndImpressionEmail $mail) use ($mailTo) {
            return $mail->hasTo($mailTo);
        });
    }

    public function testItCanValidateTheDate()
    {
        $this
            ->artisan(ReportInventoryViewAndImpressionCommand::class, [
                'date' => 'something-wrong',
            ])
            ->assertExitCode(1);
    }
}
