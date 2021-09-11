<?php

declare(strict_types=1);

namespace App\Console\Commands\Integrations\TrailerCentral;

use App\Services\LoggerServiceInterface;
use App\Services\Integrations\TrailerCentral\Inventory\Console\SyncServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class SyncInventory extends Command
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'sync:inventory';

    /** @var string The console command description. */
    protected $description = 'Sync the inventory history from the Integrations database';

    public function __construct(
        private SyncServiceInterface $service,
        private LoggerServiceInterface $logger
    ) {
        parent::__construct();
    }

    /**
     * @throws \PDOException  when some unknown PDO error has been thrown
     * @throws \JsonException when the metadata were unable to be serialized
     */
    public function handle(): void
    {
        $this->logger->info('[SyncInventory] starting inventory synchronization...');

        $this->line(sprintf(
                '[SyncInventory][%s] starting inventory synchronization...',
                Date::now()->format('Y-m-d H:i:s'))
        );

        $numberOfRecordsSynchronized = $this->service->sync();

        $this->line(sprintf(
            '[SyncInventory][%s] total of records imported [%d]',
            Date::now()->format('Y-m-d H:i:s'),
            $numberOfRecordsSynchronized
        ));

        $this->logger->info(sprintf('[SyncInventory] total of records imported [%d]', $numberOfRecordsSynchronized));
    }
}
