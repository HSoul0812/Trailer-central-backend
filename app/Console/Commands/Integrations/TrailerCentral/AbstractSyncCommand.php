<?php

declare(strict_types=1);

namespace App\Console\Commands\Integrations\TrailerCentral;

use App\Services\Integrations\TrailerCentral\Console\SyncServiceInterface;
use App\Services\LoggerServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use JsonException;
use PDOException;

abstract class AbstractSyncCommand extends Command
{
    public function __construct(private SyncServiceInterface $service, private LoggerServiceInterface $logger)
    {
        parent::__construct();
    }

    /**
     * @throws PDOException  when some unknown PDO error has been thrown
     * @throws JsonException when the metadata were unable to be serialized
     */
    public function handle(): void
    {
        $this->logger->info("[TrailerCentral\AbstractSyncCommand] starting $this->signature ...");

        $this->line(sprintf(
            '[TrailerCentral\AbstractSyncCommand][%s] starting %s ...',
            Date::now()->format('Y-m-d H:i:s'),
            $this->signature
        )
        );

        $numberOfRecordsSynchronized = $this->service->sync();

        $this->line(sprintf(
            '[TrailerCentral\AbstractSyncCommand][%s] total of records imported [%d]',
            Date::now()->format('Y-m-d H:i:s'),
            $numberOfRecordsSynchronized
        ));

        $this->logger->info(
            sprintf('[TrailerCentral\AbstractSyncCommand] total of records imported [%d]',
                $numberOfRecordsSynchronized
            )
        );
    }
}
