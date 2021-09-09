<?php

declare(strict_types=1);

namespace App\Services\TrailerCentral\Integration\Console;

use App\Exceptions\CannotBeUsedBeyondConsole;
use App\Repositories\TrailerCentral\Integration\InventoryRepositoryInterface;
use App\Repositories\TrailerCentral\Integration\SyncProcessRepositoryInterface;
use App\Services\Common\LoggerServiceInterface;
use App\Services\TrailerCentral\Integration\InventoryLogServiceInterface;
use App\Services\TrailerCentral\Integration\SyncProcessServiceInterface;
use Exception;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Support\Facades\DB;

class InventorySyncService implements InventorySyncServiceInterface
{
    public function __construct(
        private InventoryRepositoryInterface $inventoryRepository,
        private SyncProcessRepositoryInterface $processRepository,
        private SyncProcessServiceInterface $processService,
        private InventoryLogServiceInterface $inventoryLogService,
        private ApplicationContract $app,
        private LoggerServiceInterface $logger
    ) {
    }

    /**
     * @throws \PDOException  when some unknown PDO error has been thrown
     * @throws \JsonException when the metadata were unable to be serialized
     * @throws Exception      when some unknown exception has been thrown
     */
    public function sync(): int
    {
        if (!$this->app->runningInConsole()) {
            throw new CannotBeUsedBeyondConsole();
        }

        // needed to create an insertion buffer, surely it only will be at the very first time
        ini_set('memory_limit', config('trailercentral.memory_limit'));

        $process = $this->processService->start(self::PROCESS_NAME);

        try {
            $lastProcess = $this->processRepository->lastFinishedByProcessName(self::PROCESS_NAME);

            $numberOfRecordsImported = 0;

            $isNotTheFirstImport = $this->processRepository->isNotTheFirstImport(self::PROCESS_NAME);

            DB::transaction(function () use ($lastProcess, $isNotTheFirstImport, $process, &$numberOfRecordsImported) {
                $this->inventoryRepository
                    ->queryAllSince($lastProcess?->finished_at)
                    ->chunk(
                        (int) config('trailercentral.records_per_chunk'),
                        $this->applyToChuck($isNotTheFirstImport, $numberOfRecordsImported, $process)
                    );

                $this->processRepository->finishById($process->id, ['numberOfRecordsImported' => $numberOfRecordsImported]);
            });

            return $numberOfRecordsImported;
        } catch (Exception $exception) {
            $this->processRepository->failById($process->id, ['errorMessage' => $exception->getMessage()]);

            throw $exception;
        }
    }

    private function applyToChuck($isNotTheFirstImport, &$numberOfRecordsImported, $process): callable
    {
        return function ($records) use ($isNotTheFirstImport, &$numberOfRecordsImported, $process): void {
            $insertValues = '';

            foreach ($records as $record) {
                $insertValues .= $this->inventoryLogService->mapToInsertString($record, $isNotTheFirstImport);

                ++$numberOfRecordsImported;
            }

            $this->inventoryLogService->execute($insertValues);

            $this->logger->info("[InventorySyncService] $numberOfRecordsImported records imported on process {$process->id}");
        };
    }
}
