<?php

declare(strict_types=1);

namespace App\Console\Commands\Dms\Integration;

use App\Exceptions\Dms\Integration\SyncProcessIsStillWorking;
use App\Services\Common\LoggerServiceInterface;
use App\Services\Dms\Integration\InventorySyncServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class SyncInventory extends Command
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'sync:inventory';

    /** @var string The console command description. */
    protected $description = 'Sync the inventory history from the DMS database';

    public function __construct(
        private InventorySyncServiceInterface $service,
        private LoggerServiceInterface $logger
    ) {
        parent::__construct();
    }

    /**
     * Get the list of signals handled by the command.
     */
    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    /**
     * @throws \PDOException  when some unknown error has popped up
     * @throws \JsonException when the metadata were unable to be serialized
     */
    public function handle(): void
    {
        $this->line(sprintf(
                '[SyncInventory][%s] starting inventory synchronization...',
                Date::now()->format('Y-m-d H:i:s'))
        );

        try {
            $this->line(sprintf(
                '[SyncInventory][%s] total of records imported [%d]',
                Date::now()->format('Y-m-d H:i:s'),
                $this->service->sync()
            ));
        } catch (SyncProcessIsStillWorking $exception) {
            $this->logger->error(sprintf(
                    '[SyncInventory] it cannot be done due %s', $exception->getMessage()
                )
            );

            $this->line(sprintf(
                    '[SyncInventory][%s] it cannot be done due %s',
                    Date::now()->format('Y-m-d H:i:s'),
                    $exception->getMessage()
                )
            );
        }
    }
}
