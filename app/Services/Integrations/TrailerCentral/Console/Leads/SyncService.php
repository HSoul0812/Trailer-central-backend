<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Console\Leads;

use App\Repositories\Integrations\TrailerCentral\LeadRepositoryInterface;
use App\Repositories\SyncProcessRepositoryInterface;
use App\Services\Integrations\TrailerCentral\Console\AbstractSyncService;
use App\Services\LoggerServiceInterface;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use JetBrains\PhpStorm\Pure;

class SyncService extends AbstractSyncService implements SyncServiceInterface
{
    #[Pure]
    public function __construct(
        LeadRepositoryInterface $sourceRepository,
        LogServiceInterface $targetRepository,
        LoggerServiceInterface $logger,
        SyncProcessRepositoryInterface $processRepository,
        ApplicationContract $app,
    ) {
        parent::__construct($sourceRepository, $targetRepository, $processRepository, $app, $logger);
    }

    protected function getProcessName(): string
    {
        return self::PROCESS_NAME;
    }

    protected function getChunkLimit(): int
    {
        return (int) config('trailercentral.leads.records_per_chunk');
    }

    protected function getMemoryLimit(): string
    {
        return config('trailercentral.memory_limit');
    }
}
