<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Console\Inventory;

use App\Repositories\Integrations\TrailerCentral\InventoryRepositoryInterface;
use App\Repositories\SyncProcessRepositoryInterface;
use App\Services\Integrations\TrailerCentral\Console\AbstractSyncService;
use App\Services\LoggerServiceInterface;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Database\ConnectionInterface;
use JetBrains\PhpStorm\Pure;

class SyncService extends AbstractSyncService implements SyncServiceInterface
{
    #[Pure]
    public function __construct(
        InventoryRepositoryInterface $sourceRepository,
        LogServiceInterface $targetRepository,
        LoggerServiceInterface $logger,
        SyncProcessRepositoryInterface $processRepository,
        ApplicationContract $app,
        ConnectionInterface $connection
    ) {
        parent::__construct($sourceRepository, $targetRepository, $processRepository, $app, $logger, $connection);
    }

    protected function getProcessName(): string
    {
        return self::PROCESS_NAME;
    }

    protected function getChunkLimit(): int
    {
        return (int) config('trailercentral.inventory.records_per_chunk');
    }

    protected function getMemoryLimit(): string
    {
        return config('trailercentral.memory_limit');
    }
}
