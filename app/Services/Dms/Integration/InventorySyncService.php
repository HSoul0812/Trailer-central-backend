<?php

declare(strict_types=1);

namespace App\Services\Dms\Integration;

use App\Exceptions\Dms\Integration\SyncProcessIsStillWorking;
use App\Models\Dms\Integration\SyncProcess;
use App\Repositories\Dms\Integration\InventoryRepositoryInterface;
use App\Repositories\Dms\Integration\SyncProcessRepositoryInterface;
use Exception;
use Illuminate\Support\LazyCollection;
use Swoole\Constant;
use Swoole\Coroutine;

class InventorySyncService implements InventorySyncServiceInterface
{
    public const RECORDS_PER_BULK = 8000;
    private const MEMORY_LIMIT = '250M';

    private int $limit;

    public function __construct(
        private InventoryRepositoryInterface $inventoryRepository,
        private SyncProcessRepositoryInterface $processRepository,
        private SyncProcessServiceInterface $processService,
        private StockLogServiceInterface $stockLogService
    ) {
        $this->limit = (int) env('DMS_SYNCHRONIZATION_MAX_RECORDS_PER_BATCH');
    }

    /**
     * @throws \App\Exceptions\Dms\Integration\SyncProcessIsStillWorking
     * @throws \PDOException                                             when some unknown error has popped up
     * @throws \JsonException                                            when the metadata were unable to be serialized
     */
    public function sync(): int
    {
        $lastProcess = $this->processRepository->lastByProcessName(self::PROCESS_NAME);

        if ($lastProcess?->isStillWorking()) {
            throw new SyncProcessIsStillWorking();
        }

        $process = $this->processService->start(self::PROCESS_NAME);

        try {
            $numberOfRecordsToImport = $this->inventoryRepository->getNumberOfRecordsToImport($lastProcess?->finished_at);

            if ($this->needsToBeSpawned($numberOfRecordsToImport)) {
                return $this->handleConcurrently($process, $lastProcess?->finished_at, $numberOfRecordsToImport);
            }

            return $this->bulk($this->inventoryRepository->getAllSince($lastProcess?->finished_at), $process);
        } catch (Exception $exception) {
            $this->processRepository->failById($process->id, array_merge(
                $process->refresh()->meta,
                ['hasFailed' => true, 'errorMessage' => $exception->getMessage()]
            ));

            throw $exception;
        }
    }

    /**
     * @throws \JsonException when the metadata were unable to be serialized
     * @throws \PDOException  when some unknown error has popped up
     */
    private function bulk(LazyCollection $records, ?SyncProcess $process = null): int
    {
        ini_set('memory_limit', self::MEMORY_LIMIT); // needed to create an insertion buffer

        $numberOfRecordsImported = 0;

        $totalRecordsOnBulk = 0;
        $insertValues = '';

        // only will be called once by task
        $isNotTheFirstImport = $this->processRepository->isNotTheFirstImport(self::PROCESS_NAME);

        foreach ($records as $record) {
            $insertValues .= $this->stockLogService->mapToInsertString($record, $isNotTheFirstImport);

            ++$totalRecordsOnBulk;
            ++$numberOfRecordsImported;

            if ($totalRecordsOnBulk === self::RECORDS_PER_BULK) {
                $this->stockLogService->execute($insertValues);

                $totalRecordsOnBulk = 0;
                $insertValues = '';
            }
        }

        if ($insertValues) { // leftover records
            $this->stockLogService->execute($insertValues);
        }

        if ($process) {
            $this->processRepository->finishById($process->id, ['numberOfRecordsImported' => $numberOfRecordsImported]);
        }

        return $numberOfRecordsImported;
    }

    private function needsToBeSpawned(int $numberOfRecordsToSynchronized): bool
    {
        return $numberOfRecordsToSynchronized > $this->limit;
    }

    private function handleConcurrently(SyncProcess $process, ?string $lastDateTimeSynchronized, int $numberOfRecordsToImport): int
    {
        Coroutine::set([Constant::OPTION_HOOK_FLAGS => SWOOLE_HOOK_TCP]);

        $numberOfRecordsImported = 0; // shared variable

        $numberOfTasks = (int) ceil($numberOfRecordsToImport / $this->limit);

        Coroutine\run(function () use ($lastDateTimeSynchronized, &$numberOfRecordsImported, $numberOfTasks) {
            for ($co = 0; $co < $numberOfTasks; ++$co) {
                go(function () use ($lastDateTimeSynchronized, &$numberOfRecordsImported, $co) {
                    $numberOfRecordsImported += $this->bulk(
                        $this->inventoryRepository->getAllSince($lastDateTimeSynchronized, $this->limit * $co, $this->limit)
                    );
                });
            }
        });

        $this->processRepository->finishById($process->id, ['numberOfRecordsImported' => $numberOfRecordsImported]);

        return $numberOfRecordsImported;
    }
}
