<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Console;

use App\Exceptions\CannotBeUsedBeyondConsole;
use App\Repositories\Integrations\TrailerCentral\SourceRepositoryInterface;
use App\Repositories\SyncProcessRepositoryInterface;
use App\Services\LoggerServiceInterface;
use Exception;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Support\Facades\DB;

abstract class AbstractSyncService
{
    public function __construct(
        private SourceRepositoryInterface $sourceRepository,
        private LogServiceInterface $targetRepository,
        private SyncProcessRepositoryInterface $processRepository,
        private ApplicationContract $app,
        private LoggerServiceInterface $logger
    ) {
    }

    /**
     * @throws \PDOException  when some unknown PDO error has been thrown
     * @throws \JsonException when the metadata were unable to be serialized
     * @throws \Exception     when some unknown exception has been thrown
     */
    public function sync(): int
    {
        if (!$this->app->runningInConsole()) {
            throw new CannotBeUsedBeyondConsole();
        }

        // needed to create an insertion buffer, surely it only will be at the very first time
        ini_set('memory_limit', $this->getMemoryLimit());

        $process = $this->processRepository->create(['name' => $this->getProcessName()]);

        try {
            $lastProcess = $this->processRepository->lastFinishedByProcessName($this->getProcessName());

            $numberOfRecordsImported = 0;

            $isNotTheFirstImport = $this->processRepository->isNotTheFirstImport($this->getProcessName());

            DB::transaction(function () use ($lastProcess, $isNotTheFirstImport, $process, &$numberOfRecordsImported) {
                $this->sourceRepository
                    ->queryAllSince($lastProcess?->finished_at)
                    ->chunk(
                        $this->getChunkLimit(),
                        function ($records) use ($isNotTheFirstImport, &$numberOfRecordsImported, $process): void {
                            $this->applyToChuck($records, $isNotTheFirstImport, $numberOfRecordsImported, $process);
                        }
                    );

                $this->processRepository->finishById($process->id, ['numberOfRecordsImported' => $numberOfRecordsImported]);
            });

            return $numberOfRecordsImported;
        } catch (Exception $exception) {
            $this->processRepository->failById($process->id, ['errorMessage' => $exception->getMessage()]);

            throw $exception;
        }
    }

    abstract protected function getProcessName(): string;

    abstract protected function getChunkLimit(): int;

    abstract protected function getMemoryLimit(): string;

    /**
     * @throws \PDOException  when some unknown PDO error has been thrown
     * @throws \JsonException when the metadata were unable to be serialized
     * @throws \Exception     when some unknown exception has been thrown
     */
    private function applyToChuck($records, $isNotTheFirstImport, &$numberOfRecordsImported, $process): void
    {
        $insertValues = '';

        foreach ($records as $record) {
            $insertValues .= $this->targetRepository->mapToInsertString($record, $isNotTheFirstImport);

            ++$numberOfRecordsImported;
        }

        $this->targetRepository->execute($insertValues);

        $this->logger->info(sprintf(
                '[SyncService::%s] %d records imported on process %d',
                $this->getProcessName(),
                $numberOfRecordsImported,
                $process->id
            )
        );
    }
}
