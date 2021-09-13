<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Inventory\Console;

use App\Exceptions\CannotBeUsedBeyondConsole;
use App\Repositories\Integrations\TrailerCentral\InventoryRepositoryInterface;
use App\Repositories\SyncProcessRepositoryInterface;
use App\Services\LoggerServiceInterface;
use Exception;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Support\Facades\DB;

class SyncService implements SyncServiceInterface
{
    public function __construct(
        private InventoryRepositoryInterface $inventoryRepository,
        private SyncProcessRepositoryInterface $processRepository,
        private LogServiceInterface $inventoryLogService,
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

        $process = $this->processRepository->create(['name' => self::PROCESS_NAME]);

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

            $this->logger->info("[SyncService] $numberOfRecordsImported records imported on process $process->id");
        };
    }
}
